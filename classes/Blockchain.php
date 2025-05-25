<?php
/**
 * Blockchain Class for Election Management System
 * 
 * This class provides blockchain functionality for securing votes in the EMS system.
 * It includes methods for creating blocks, validating the blockchain, and adding votes
 * to the blockchain in a secure, tamper-resistant way.
 */

class Blockchain {
    private $conn;
    
    /**
     * Constructor: Initialize blockchain with database connection
     * 
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->initBlockchainTable();
    }
    
    /**
     * Create blockchain_blocks table if it doesn't exist
     */
    private function initBlockchainTable() {
        $sql = "CREATE TABLE IF NOT EXISTS blockchain_blocks (
            block_id INT AUTO_INCREMENT PRIMARY KEY,
            election_id INT NOT NULL,
            previous_hash VARCHAR(64) DEFAULT NULL,
            block_hash VARCHAR(64) NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            nonce INT NOT NULL,
            vote_data TEXT NOT NULL,
            is_valid TINYINT(1) DEFAULT 1,
            INDEX (election_id),
            INDEX (previous_hash)
        ) ENGINE=InnoDB";
        
        if (!$this->conn->query($sql)) {
            error_log("Failed to create blockchain table: " . $this->conn->error);
            throw new Exception("Failed to initialize blockchain storage");
        }
    }
    
    /**
     * Create genesis block for an election if it doesn't exist
     * 
     * @param int $electionID The ID of the election
     * @return bool True if successful
     */
    public function createGenesisBlock($electionID) {
        // Check if genesis block already exists for this election
        $stmt = $this->conn->prepare("SELECT block_id FROM blockchain_blocks WHERE election_id = ? AND previous_hash IS NULL LIMIT 1");
        $stmt->bind_param("i", $electionID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Genesis block already exists
            return true;
        }
        
        // Create genesis block
        $data = [
            'type' => 'genesis',
            'election_id' => $electionID,
            'message' => 'Genesis Block for Election ID: ' . $electionID,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $jsonData = json_encode($data);
        $nonce = $this->proofOfWork($jsonData, "0");
        $hash = $this->calculateHash($jsonData, "0", $nonce);
        
        $stmt = $this->conn->prepare("INSERT INTO blockchain_blocks 
            (election_id, previous_hash, block_hash, nonce, vote_data) 
            VALUES (?, NULL, ?, ?, ?)");
        $stmt->bind_param("isis", $electionID, $hash, $nonce, $jsonData);
        
        if (!$stmt->execute()) {
            error_log("Failed to create genesis block: " . $stmt->error);
            return false;
        }
        
        return true;
    }
      /**
     * Add a new vote to the blockchain
     * 
     * @param int $electionID The election ID
     * @param int $studentID The student's ID
     * @param int $candidateID The candidate's ID
     * @param int $voteID The vote ID from the votes table
     * @return bool True if successful
     */
    public function addVote($electionID, $studentID, $candidateID, $voteID) {
        try {
            // Input validation
            $electionID = filter_var($electionID, FILTER_VALIDATE_INT);
            $studentID = filter_var($studentID, FILTER_VALIDATE_INT);
            $candidateID = filter_var($candidateID, FILTER_VALIDATE_INT);
            $voteID = filter_var($voteID, FILTER_VALIDATE_INT);
            
            if (!$electionID || !$studentID || !$candidateID || !$voteID) {
                throw new Exception("Invalid input parameters");
            }
            
            // Make sure genesis block exists
            $this->createGenesisBlock($electionID);
            
            // Get the last block hash for this election
            $stmt = $this->conn->prepare(
                "SELECT block_hash FROM blockchain_blocks 
                WHERE election_id = ? 
                ORDER BY block_id DESC LIMIT 1"
            );
            $stmt->bind_param("i", $electionID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("No previous blocks found for election ID: $electionID");
            }
            
            $previousHash = $result->fetch_assoc()['block_hash'];
            
            // Prepare vote data - do not include the student ID directly in the blockchain
            // Instead use a one-way hash of the student ID + salt to maintain voter privacy
            $salt = bin2hex(random_bytes(32)); // Increased from 16 to 32 bytes
            $anonymizedVoter = hash('sha256', $studentID . $salt);
            
            // Get current timestamp with microseconds for added entropy
            $timestamp = microtime(true);
            $formattedTime = date('Y-m-d H:i:s', (int)$timestamp);
            
            $data = [
                'type' => 'vote',
                'election_id' => $electionID,
                'vote_id' => $voteID,
                'candidate_id' => $candidateID,
                'voter_hash' => $anonymizedVoter,
                'salt' => $salt, // Store salt for verification (can't reverse the hash)
                'timestamp' => $formattedTime,
                'ip_hash' => hash('sha256', $_SERVER['REMOTE_ADDR']), // Store hashed IP for audit
                'user_agent_hash' => hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), // Add browser fingerprint
                'created_at_micro' => $timestamp // Store precise timestamp with microseconds
            ];
            
            $jsonData = json_encode($data);
            $nonce = $this->proofOfWork($jsonData, $previousHash);
            $blockHash = $this->calculateHash($jsonData, $previousHash, $nonce);
            
            // Add block to blockchain
            $stmt = $this->conn->prepare(
                "INSERT INTO blockchain_blocks 
                (election_id, previous_hash, block_hash, nonce, vote_data) 
                VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("issis", $electionID, $previousHash, $blockHash, $nonce, $jsonData);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert block: " . $stmt->error);
            }
            
            // Verify the block was added correctly
            $blockID = $this->conn->insert_id;
            $verifyStmt = $this->conn->prepare(
                "SELECT * FROM blockchain_blocks WHERE block_id = ?"
            );
            $verifyStmt->bind_param("i", $blockID);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            
            if ($verifyResult->num_rows === 0) {
                throw new Exception("Failed to verify block after insertion");
            }
            
            $addedBlock = $verifyResult->fetch_assoc();
            $verifyHash = $this->calculateHash($addedBlock['vote_data'], $addedBlock['previous_hash'], $addedBlock['nonce']);
            
            if ($verifyHash !== $addedBlock['block_hash']) {
                // This should never happen, but if it does, we have a serious issue
                error_log("Critical blockchain error: Block hash verification failed immediately after insertion");
                throw new Exception("Block hash verification failed");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Blockchain Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate the hash of a block
     * 
     * @param string $data The block data
     * @param string $previousHash The previous block's hash
     * @param int $nonce The nonce value for proof of work
     * @return string The calculated hash
     */
    public function calculateHash($data, $previousHash, $nonce) {
        return hash('sha256', $previousHash . $data . $nonce);
    }
      /**
     * Simplified proof of work implementation
     * Finds a nonce that produces a hash with leading zeros
     * 
     * @param string $data The block data
     * @param string $previousHash The previous block's hash
     * @param int $difficulty The number of leading zeros required (default: 4)
     * @return int The nonce that satisfies the difficulty requirement
     */
    private function proofOfWork($data, $previousHash, $difficulty = 4) {
        $target = str_repeat("0", $difficulty);
        $nonce = 0;
        $maxAttempts = 1000000; // Safety limit
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            $hash = $this->calculateHash($data, $previousHash, $nonce);
            if (substr($hash, 0, $difficulty) === $target) {
                break;
            }
            $nonce++;
            $attempts++;
        }
        
        if ($attempts >= $maxAttempts) {
            error_log("Warning: Maximum proof of work attempts reached for blockchain");
            // Fall back to a simpler difficulty if we hit the limit
            $difficulty = 2;
            $target = str_repeat("0", $difficulty);
            $nonce = 0;
            
            while (true) {
                $hash = $this->calculateHash($data, $previousHash, $nonce);
                if (substr($hash, 0, $difficulty) === $target) {
                    break;
                }
                $nonce++;
            }
        }
        
        return $nonce;
    }
    
    /**
     * Validate the blockchain for an election
     * 
     * @param int $electionID The election ID to validate
     * @return array Result with status and details
     */
    public function validateChain($electionID) {
        $result = [
            'valid' => true,
            'blocks_checked' => 0,
            'invalid_blocks' => [],
            'message' => ''
        ];
        
        // Get all blocks for this election in order
        $stmt = $this->conn->prepare("SELECT * FROM blockchain_blocks WHERE election_id = ? ORDER BY block_id ASC");
        $stmt->bind_param("i", $electionID);
        $stmt->execute();
        $blocks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (count($blocks) === 0) {
            $result['valid'] = false;
            $result['message'] = "No blocks found for election ID: $electionID";
            return $result;
        }
        
        // Validate genesis block
        $genesisBlock = $blocks[0];
        if ($genesisBlock['previous_hash'] !== null) {
            $result['valid'] = false;
            $result['message'] = "Invalid genesis block: previous hash should be NULL";
            $result['invalid_blocks'][] = $genesisBlock['block_id'];
        }
        
        $previousHash = $genesisBlock['block_hash'];
        $result['blocks_checked']++;
        
        // Validate the chain
        for ($i = 1; $i < count($blocks); $i++) {
            $currentBlock = $blocks[$i];
            $result['blocks_checked']++;
            
            // Check if previous hash matches
            if ($currentBlock['previous_hash'] !== $previousHash) {
                $result['valid'] = false;
                $result['invalid_blocks'][] = $currentBlock['block_id'];
                continue;
            }
            
            // Recalculate hash to verify
            $recalculatedHash = $this->calculateHash(
                $currentBlock['vote_data'], 
                $currentBlock['previous_hash'], 
                $currentBlock['nonce']
            );
            
            if ($recalculatedHash !== $currentBlock['block_hash']) {
                $result['valid'] = false;
                $result['invalid_blocks'][] = $currentBlock['block_id'];
            }
            
            $previousHash = $currentBlock['block_hash'];
        }
        
        if ($result['valid']) {
            $result['message'] = "Blockchain for election ID: $electionID is valid.";
        } else {
            $result['message'] = "Blockchain validation failed! Found " . count($result['invalid_blocks']) . " invalid blocks.";
        }
        
        return $result;
    }
    
    /**
     * Get the blockchain for an election
     * 
     * @param int $electionID The election ID
     * @return array Array of blocks in the chain
     */
    public function getBlockchain($electionID) {
        $stmt = $this->conn->prepare("SELECT * FROM blockchain_blocks WHERE election_id = ? ORDER BY block_id ASC");
        $stmt->bind_param("i", $electionID);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
      /**
     * Verify if a specific vote exists in the blockchain
     * 
     * @param int $electionID The election ID
     * @param int $voteID The vote ID to check
     * @return array Result with verification status
     */
    public function verifyVote($electionID, $voteID) {
        // Input validation
        $electionID = filter_var($electionID, FILTER_VALIDATE_INT);
        $voteID = filter_var($voteID, FILTER_VALIDATE_INT);
        
        if (!$electionID || !$voteID) {
            return [
                'exists' => false,
                'valid' => false,
                'block_id' => null,
                'message' => 'Invalid input parameters'
            ];
        }
        
        $result = [
            'exists' => false,
            'valid' => false,
            'block_id' => null,
            'message' => ''
        ];
        
        try {
            // Find the block containing this vote
            $stmt = $this->conn->prepare("
                SELECT * FROM blockchain_blocks 
                WHERE election_id = ? AND JSON_EXTRACT(vote_data, '$.vote_id') = ?
            ");
            $stmt->bind_param("ii", $electionID, $voteID);
            $stmt->execute();
            $block = $stmt->get_result()->fetch_assoc();
            
            if (!$block) {
                $result['message'] = "Vote ID $voteID not found in blockchain for election ID $electionID";
                return $result;
            }
            
            $result['exists'] = true;
            $result['block_id'] = $block['block_id'];
            
            // Recalculate hash to verify block integrity
            $recalculatedHash = $this->calculateHash(
                $block['vote_data'], 
                $block['previous_hash'], 
                $block['nonce']
            );
            
            if ($recalculatedHash !== $block['block_hash']) {
                $result['message'] = "Vote found but block integrity is compromised!";
                
                // Log the tampering attempt
                error_log("Blockchain tampering detected: Block {$block['block_id']} hash mismatch for election $electionID");
                
                return $result;
            }
            
            // Verify the previous and next blocks to ensure chain integrity
            $prevNextValid = $this->verifyAdjacentBlocks($block['block_id'], $electionID);
            if (!$prevNextValid) {
                $result['message'] = "Vote found but blockchain chain links are compromised!";
                return $result;
            }
            
            // Check if this block is part of a valid chain
            $chainValidation = $this->validateChain($electionID);
            if (!$chainValidation['valid']) {
                $result['message'] = "Vote found but blockchain integrity is compromised!";
                return $result;
            }
            
            $result['valid'] = true;
            $result['message'] = "Vote ID $voteID verified successfully in the blockchain.";
            
            return $result;
        } catch (Exception $e) {
            error_log("Error verifying vote: " . $e->getMessage());
            $result['message'] = "Error verifying vote: Internal server error";
            return $result;
        }
    }
    
    /**
     * Verify the integrity of blocks adjacent to a given block
     * 
     * @param int $blockID The block ID to check
     * @param int $electionID The election ID
     * @return bool True if adjacent blocks are valid
     */
    private function verifyAdjacentBlocks($blockID, $electionID) {
        try {
            // Get the current block
            $stmt = $this->conn->prepare("SELECT * FROM blockchain_blocks WHERE block_id = ? AND election_id = ?");
            $stmt->bind_param("ii", $blockID, $electionID);
            $stmt->execute();
            $currentBlock = $stmt->get_result()->fetch_assoc();
            
            if (!$currentBlock) {
                return false;
            }
            
            // Check previous block if not genesis
            if ($currentBlock['previous_hash']) {
                $prevStmt = $this->conn->prepare("SELECT * FROM blockchain_blocks WHERE block_hash = ? AND election_id = ?");
                $prevStmt->bind_param("si", $currentBlock['previous_hash'], $electionID);
                $prevStmt->execute();
                $prevBlock = $prevStmt->get_result()->fetch_assoc();
                
                if (!$prevBlock) {
                    return false;
                }
                
                // Verify previous block's hash
                $recalcPrevHash = $this->calculateHash(
                    $prevBlock['vote_data'], 
                    $prevBlock['previous_hash'], 
                    $prevBlock['nonce']
                );
                
                if ($recalcPrevHash !== $prevBlock['block_hash']) {
                    return false;
                }
            }
            
            // Check next block if exists
            $nextStmt = $this->conn->prepare("SELECT * FROM blockchain_blocks WHERE previous_hash = ? AND election_id = ?");
            $nextStmt->bind_param("si", $currentBlock['block_hash'], $electionID);
            $nextStmt->execute();
            $nextBlock = $nextStmt->get_result()->fetch_assoc();
            
            if ($nextBlock) {
                // Verify next block's hash
                $recalcNextHash = $this->calculateHash(
                    $nextBlock['vote_data'], 
                    $nextBlock['previous_hash'], 
                    $nextBlock['nonce']
                );
                
                if ($recalcNextHash !== $nextBlock['block_hash']) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error verifying adjacent blocks: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get statistics about the blockchain
     * 
     * @param int $electionID The election ID
     * @return array Statistics about the blockchain
     */
    public function getBlockchainStats($electionID) {
        $stats = [
            'total_blocks' => 0,
            'total_votes' => 0,
            'first_block_time' => null,
            'last_block_time' => null,
            'chain_valid' => false
        ];
        
        // Get count of blocks and votes
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_blocks,
                SUM(CASE WHEN JSON_EXTRACT(vote_data, '$.type') = 'vote' THEN 1 ELSE 0 END) as total_votes,
                MIN(timestamp) as first_block_time,
                MAX(timestamp) as last_block_time
            FROM blockchain_blocks 
            WHERE election_id = ?
        ");
        $stmt->bind_param("i", $electionID);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $stats['total_blocks'] = (int)$result['total_blocks'];
            $stats['total_votes'] = (int)$result['total_votes'];
            $stats['first_block_time'] = $result['first_block_time'];
            $stats['last_block_time'] = $result['last_block_time'];
        }
        
        // Check if chain is valid
        $validationResult = $this->validateChain($electionID);
        $stats['chain_valid'] = $validationResult['valid'];
        
        return $stats;
    }
}
