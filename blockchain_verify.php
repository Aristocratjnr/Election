<?php
session_start();

// Check if user is logged in (either admin or student)
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'configs/dbconnection.php';
require_once 'classes/Blockchain.php';

// Check if user is admin or student
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;

// Initialize blockchain object
$blockchain = new Blockchain($conn);

// Get all elections
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");

// Initialize variables
$electionID = $_GET['election'] ?? null;
$action = $_GET['action'] ?? null;
$voteID = $_GET['vote'] ?? null;
$message = '';
$blockchainData = [];
$validationResult = [];
$verificationResult = [];
$stats = [];

// Handle actions
if ($electionID) {
    // Get blockchain statistics for the selected election
    $stats = $blockchain->getBlockchainStats($electionID);
    
    // Get election details
    $electionStmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $electionStmt->bind_param("i", $electionID);
    $electionStmt->execute();
    $electionDetails = $electionStmt->get_result()->fetch_assoc();
    
    // Handle specific actions
    switch ($action) {
        case 'validate':
            $validationResult = $blockchain->validateChain($electionID);
            break;
            
        case 'view':
            $blockchainData = $blockchain->getBlockchain($electionID);
            break;
            
        case 'verify':
            if ($voteID) {
                $verificationResult = $blockchain->verifyVote($electionID, $voteID);
            } else {
                $message = "Vote ID is required for verification";
            }
            break;
    }
}

// Function to display block data in a readable format
function formatBlockData($data) {
    $jsonData = json_decode($data, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Format genesis block differently
        if (isset($jsonData['type']) && $jsonData['type'] === 'genesis') {
            return [
                'type' => 'Genesis Block',
                'message' => $jsonData['message'],
                'timestamp' => $jsonData['created_at']
            ];
        }
        
        // Format vote blocks
        $voterHash = $jsonData['voter_hash'] ?? 'N/A';
        $voterHashShort = substr($voterHash, 0, 8) . '...' . substr($voterHash, -8);
        
        return [
            'type' => 'Vote',
            'vote_id' => $jsonData['vote_id'] ?? 'N/A',
            'candidate_id' => $jsonData['candidate_id'] ?? 'N/A',
            'voter_hash' => $voterHashShort,
            'timestamp' => $jsonData['timestamp'] ?? 'N/A'
        ];
    }
    
    return ['error' => 'Invalid JSON data'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blockchain Verification - SmartVote</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>

        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #6c757d;
            --success: #2ecc71;
            --success-dark: #27ae60;
            --info: #00cec9;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #2d3436;
            --blockchain-green: #00b894;
            --blockchain-blue: #0984e3;
            --blockchain-purple: #6c5ce7;
            --blockchain-gradient: linear-gradient(135deg, #6c5ce7, #0984e3);
            --blockchain-gradient-2: linear-gradient(135deg, #00b894, #0984e3);
        }
          body {
            background-color: #f7f9fc;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .header {
            background: var(--blockchain-gradient);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .block-card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
            margin-bottom: 1.25rem;
            background-color: white;
        }
        
        .block-card.genesis {
            border: none;
            background: linear-gradient(135deg, #fff 85%, var(--blockchain-purple) 85%);
        }
        
        .block-card.vote {
            border: none;
            background: linear-gradient(135deg, #fff 85%, var(--blockchain-green) 85%);
        }
          .block-hash {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 0.9rem;
            background-color: #f8f9fd;
            padding: 0.75rem;
            border-radius: 0.5rem;
            word-break: break-all;
            border: 1px solid #e9ecef;
        }
        .block-hash.genesis {
            background-color: var(--blockchain-purple);
            color: white;
        }
        .block-link {
            position: relative;
            height: 50px;
            margin-bottom: 1rem;
            display: flex;
            justify-content: center;
        }
          .block-link::before {
            content: '';
            position: absolute;
            top: 0;
            height: 80%;
            width: 3px;
            background: var(--blockchain-gradient-2);
            border-radius: 3px;
        }
        
        .block-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--blockchain-gradient-2);
            box-shadow: 0 2px 8px rgba(0, 184, 148, 0.5);
            animation: pulseGlow 3s infinite;
        }
        
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(0, 184, 148, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(0, 184, 148, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 184, 148, 0); }
        }
          .verification-pill {
            padding: 0.5rem 1.5rem;
            font-weight: 700;
            border-radius: 2rem;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            letter-spacing: 1px;
        }
        
        .blockchain-stats {
            background: var(--blockchain-gradient);
            color: white;
            border-radius: 1.5rem;
            padding: 1.75rem;
            box-shadow: 0 12px 24px rgba(108, 92, 231, 0.15);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .blockchain-stats::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 80%;
            height: 80%;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            z-index: -1;
        }
          .stats-icon {
            width: 10px;
            height: 10px;
            background-color: rgba(255,255,255,0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }
        .stats-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .stats-value {
            font-size: 2rem;
            font-weight: 400;
            margin-bottom: 0.25rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 400;
            letter-spacing: 0.5px;
        }
        
        /* Add custom card styles */
        .custom-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        
    
        
        /* Add button styles */
        .btn {
            border-radius: 0.75rem;
            padding: 0.6rem 1.5rem;
            font-weight: 200;
            text-transform: capitalize;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        .btn-info {
            background-color: var(--info);
            border-color: var(--info);
        }
        /* Badge styling */
        .badge {
            padding: 0.5em 1em;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        
        .bg-purple {
            background-color: var(--blockchain-purple) !important;
        }
    </style>
</head>

<body>    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-link-45deg fs-2"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="h3 mb-0 fw-bold">Blockchain Vote Verification</h1>
                        <p class="mb-0 text-white text-opacity-75">Verify and validate votes using blockchain technology</p>
                    </div>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="dashboard.php" class="text-white text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Blockchain Verification</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
      <div class="container pb-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="custom-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div style="width: 48px; height: 48px; background: var(--blockchain-gradient); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <h5 class="card-title mb-0 fw-bold">Vote Verification System</h5>
                        </div>
                        
                        <div class="p-3 rounded-4 mb-4" style="background: rgba(255,255,255,0.1);">
                            <p class="mb-0">
                                This system allows you to verify the integrity of votes using blockchain technology. 
                                Select an election below to explore its blockchain data and verify the authenticity of votes.<br>
                                <a href="blockchain_learn.php" class="ms-2 btn btn-sm btn-primary">
                                    <i class="bi bi-info-circle me-1"></i> Learn how blockchain works
                                </a>
                            </p>
                        </div>
                          <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 h-100" >
                                    <label class="form-label fw-bold text-primary mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-event me-2" style="font-size: 1.2rem;"></i>
                                            <span>Select Election</span>
                                        </div>
                                    </label>
                                    <select name="election" class="form-select form-select-md shadow-sm border-0" style="border-radius: 10px;" onchange="this.form.submit()">
                                        <option value="">Choose an election...</option>
                                        <?php while ($election = $elections->fetch_assoc()): ?>
                                            <option value="<?php echo $election['electionID']; ?>" <?php echo (isset($electionID) && $electionID == $election['electionID']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($election['name']); ?>
                                                <span class="text-muted">(<?php echo $election['status']; ?>)</span>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 h-100" >
                                    <label class="form-label fw-bold text-primary mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-search me-2" style="font-size: 1.2rem;"></i>
                                            <span>Verify a Vote</span>
                                        </div>
                                    </label>
                                    <div class="input-group">
                                        <input type="hidden" name="action" value="verify">
                                        <input type="text" name="vote" class="form-control form-control-md shadow-sm border-0" 
                                            style="border-top-left-radius: 10px; border-bottom-left-radius: 10px;" 
                                            placeholder="Enter Vote ID" aria-label="Vote ID" value="<?php echo $voteID ?? ''; ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-check-circle me-1"></i> Verify
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($electionID && isset($electionDetails)): ?>            <!-- Blockchain Stats Cards -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center mb-3">
                        <div style="width: 40px; height: 40px; background: var(--blockchain-gradient); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; margin-right: 15px;">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">
                            Blockchain Statistics for: <span class="text-primary"><?php echo htmlspecialchars($electionDetails['name']); ?></span>
                        </h5>
                    </div>
                </div>
                  <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                    <div class="blockchain-stats h-100">
                        <div class="stats-icon">
                            <i class="bi bi-box fs-3"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['total_blocks']); ?></div>
                        <div class="stats-label">Total Blocks</div>
                        <div class="position-absolute bottom-0 end-0 p-3 opacity-10">
                            <i class="bi bi-box-seam" style="font-size: 5rem;"></i>
                        </div>
                    </div>
                </div>
                  <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                    <div class="blockchain-stats h-100" style="background: linear-gradient(135deg, #00b894, #00d1a0);">
                        <div class="stats-icon">
                            <i class="bi bi-check2-circle fs-3"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['total_votes']); ?></div>
                        <div class="stats-label">Recorded Votes</div>
                        <div class="position-absolute bottom-0 end-0 p-3 opacity-10">
                            <i class="bi bi-check2-all" style="font-size: 5rem;"></i>
                        </div>
                    </div>
                </div>
                  <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                    <div class="blockchain-stats h-100" style="background: linear-gradient(135deg, #0984e3, #74b9ff);">
                        <div class="stats-icon">
                            <i class="bi bi-calendar-check fs-3"></i>
                        </div>
                        <div class="stats-value">
                            <?php 
                            if ($stats['first_block_time']) {
                                echo date('M j', strtotime($stats['first_block_time']));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                        <div class="stats-label">Blockchain Started</div>
                        <div class="position-absolute bottom-0 end-0 p-3 opacity-10">
                            <i class="bi bi-calendar-week" style="font-size: 5rem;"></i>
                        </div>
                    </div>
                </div>
                  <div class="col-md-3 col-sm-6">
                    <div class="blockchain-stats h-100" style="background: <?php echo $stats['chain_valid'] ? 'linear-gradient(135deg, #00b894, #55efc4)' : 'linear-gradient(135deg, #d63031, #ff7675)'; ?>">
                        <div class="stats-icon" style="background-color: rgba(255,255,255,0.2);">
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>
                        <div class="stats-value">
                            <?php if ($stats['chain_valid']): ?>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2" style="font-size: 1.0rem;"></i>
                                    <span>Valid</span>
                                </div>
                            <?php else: ?>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.0rem;"></i>
                                    <span>Invalid</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="stats-label">Chain Integrity</div>
                        <div class="position-absolute bottom-0 end-0 p-3 opacity-10">
                            <i class="bi bi-shield-lock" style="font-size: 5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
              <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-body p-4">
                            <h6 class="mb-3 fw-bold text-primary"><i class="bi bi-gear-fill me-2"></i>Blockchain Actions</h6>
                            <div class="d-flex flex-wrap gap-3">
                                <a href="?election=<?php echo $electionID; ?>&action=view" class="btn btn-primary btn-md">
                                    <i class="bi bi-eye-fill me-2"></i> View Blockchain
                                </a>
                                <a href="?election=<?php echo $electionID; ?>&action=validate" class="btn btn-success btn-md">
                                    <i class="bi bi-shield-check me-2"></i> Validate Blockchain
                                </a>
                                <button type="button" class="btn btn-info btn-md text-white" style="background-color: var(--blockchain-blue);" data-bs-toggle="modal" data-bs-target="#explainBlockchainModal">
                                    <i class="bi bi-question-circle-fill me-2"></i> How It Works
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
              <!-- Validation Results -->
            <?php if ($action === 'validate' && !empty($validationResult)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-<?php echo $validationResult['valid'] ? 'success' : 'danger'; ?> text-white">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-<?php echo $validationResult['valid'] ? 'check-circle' : 'exclamation-triangle'; ?> fs-3"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Blockchain Validation Results</h5>
                                        <small class="opacity-75">Completed on <?php echo date('M j, Y g:i a'); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-<?php echo $validationResult['valid'] ? 'success' : 'danger'; ?> d-flex align-items-center">
                                    <i class="bi bi-<?php echo $validationResult['valid'] ? 'shield-check' : 'shield-exclamation'; ?> fs-4 me-3"></i>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1"><?php echo $validationResult['message']; ?></h6>
                                        <?php if (!$validationResult['valid']): ?>
                                            <p class="mb-0">Detected issues with <?php echo count($validationResult['invalid_blocks']); ?> block(s).</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h3 class="mb-0 fw-bold"><?php echo $validationResult['blocks_checked']; ?></h3>
                                                        <p class="text-muted mb-0">Blocks Checked</p>
                                                    </div>
                                                    <div class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                                                        <i class="bi bi-layers"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h3 class="mb-0 fw-bold text-<?php echo count($validationResult['invalid_blocks']) > 0 ? 'danger' : 'success'; ?>">
                                                            <?php echo count($validationResult['invalid_blocks']); ?>
                                                        </h3>
                                                        <p class="text-muted mb-0">Invalid Blocks</p>
                                                    </div>
                                                    <div class="p-2 rounded bg-<?php echo count($validationResult['invalid_blocks']) > 0 ? 'danger' : 'success'; ?> bg-opacity-10 text-<?php echo count($validationResult['invalid_blocks']) > 0 ? 'danger' : 'success'; ?>">
                                                        <i class="bi bi-<?php echo count($validationResult['invalid_blocks']) > 0 ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (count($validationResult['invalid_blocks']) > 0): ?>
                                    <div class="alert alert-danger">
                                        <div class="d-flex">
                                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                            <div>
                                                <h6 class="alert-heading fw-bold">Invalid Blocks Detected</h6>
                                                <p class="mb-2">The following blocks appear to have been tampered with:</p>
                                                <div class="bg-light p-2 rounded mb-2">
                                                    <code>Block IDs: <?php echo implode(', ', $validationResult['invalid_blocks']); ?></code>
                                                </div>
                                                <p class="mb-0">This indicates potential tampering with the vote data.</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                            <div>
                                                <h6 class="alert-heading fw-bold mb-1">Blockchain Integrity Verified</h6>
                                                <p class="mb-0">All blocks are valid and properly linked.</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Vote Verification Results -->
            <?php if ($action === 'verify' && !empty($verificationResult)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-<?php echo ($verificationResult['exists'] && $verificationResult['valid']) ? 'success' : 'danger'; ?> text-white">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-search fs-3"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Vote Verification Results</h5>
                                        <small class="opacity-75">Completed on <?php echo date('M j, Y g:i a'); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-4 p-3 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 p-2 rounded bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-fingerprint"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Vote Identifier</h6>
                                            <h4 class="mb-0 fw-bold">#<?php echo $voteID; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            
                                <?php if ($verificationResult['exists']): ?>
                                    <div class="text-center p-4 mb-4 rounded">
                                        <div class="verification-badge <?php echo $verificationResult['valid'] ? 'verification-success' : 'verification-danger'; ?> mb-3">
                                            <i class="bi bi-<?php echo $verificationResult['valid'] ? 'check-circle-fill' : 'x-circle-fill'; ?>"></i>
                                            <?php echo $verificationResult['valid'] ? 'VERIFIED' : 'COMPROMISED'; ?>
                                        </div>
                                        <p class="mb-0"><?php echo $verificationResult['message']; ?></p>
                                    </div>
                                    
                                    <?php if ($verificationResult['block_id']): ?>
                                        <div class="bg-light p-3 rounded mb-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <h6 class="text-muted mb-1">Block Reference</h6>
                                                    <p class="mb-0 fw-bold">Block ID: <?php echo $verificationResult['block_id']; ?></p>
                                                </div>
                                                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                                    <a href="?election=<?php echo $electionID; ?>&action=view" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-box me-1"></i> View in Blockchain
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-light">
                                            <div class="d-flex">
                                                <i class="bi bi-info-circle-fill text-primary fs-4 me-3"></i>
                                                <div>
                                                    <h6 class="fw-bold mb-1">What does this mean?</h6>
                                                    <p class="mb-0">
                                                        <?php if ($verificationResult['valid']): ?>
                                                            Your vote has been securely recorded in the blockchain and verified as authentic.
                                                        <?php else: ?>
                                                            The verification process detected inconsistencies that suggest the vote data may have been tampered with.
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <div class="d-flex">
                                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                            <div>
                                                <h6 class="alert-heading fw-bold mb-1">Vote Not Found</h6>
                                                <p class="mb-0"><?php echo $verificationResult['message']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php endif; ?>
             <!-- Blockchain View -->
            <?php if ($action === 'view' && !empty($blockchainData)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-link-45deg fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">Blockchain Data</h5>
                                            <small class="opacity-75">Full blockchain record for this election</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-white text-dark">
                                        <i class="bi bi-layers me-1"></i> <?php echo count($blockchainData); ?> Blocks
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-light mb-4">
                                    <div class="d-flex">
                                        <i class="bi bi-info-circle-fill text-primary fs-4 me-3"></i>
                                        <div>
                                            <p class="mb-0">Each block contains encrypted vote data and is cryptographically linked to the previous block, ensuring data integrity.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <h5 class="fw-bold mb-3 text-primary">
                                    <i class="bi bi-diagram-3 me-2"></i> Blockchain Structure
                                </h5>
                                
                                <?php foreach ($blockchainData as $index => $block): ?>
                                    <?php 
                                    $blockData = formatBlockData($block['vote_data']);
                                    $isGenesis = isset($blockData['type']) && $blockData['type'] === 'Genesis Block';
                                    ?>
                                    
                                    <div class="block-card <?php echo $isGenesis ? 'genesis' : 'vote'; ?> mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="d-flex align-items-center">
                                                    <?php if ($isGenesis): ?>
                                                        <div class="me-3 p-2 rounded bg-purple bg-opacity-10 text-purple">
                                                            <i class="bi bi-stars"></i>
                                                        </div>
                                                        <h5 class="mb-0 fw-bold">Genesis Block</h5>
                                                    <?php else: ?>
                                                        <div class="me-3 p-2 rounded bg-success bg-opacity-10 text-success">
                                                            <i class="bi bi-check2-circle"></i>
                                                        </div>
                                                        <h5 class="mb-0 fw-bold">Vote Block</h5>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="badge bg-dark bg-opacity-10">Block #<?php echo $block['block_id']; ?></span>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-key me-1"></i> Cryptographic Data</h6>
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <div class="p-2 rounded bg-light">
                                                            <small class="text-muted d-block">Block Hash:</small>
                                                            <div class="block-hash"><?php echo $block['block_hash']; ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="p-2 rounded bg-light">
                                                            <small class="text-muted d-block">Previous Hash:</small>
                                                            <div class="block-hash"><?php echo $block['previous_hash'] ? $block['previous_hash'] : '<span class="badge bg-purple bg-opacity-10 text-purple">NULL (Genesis)</span>'; ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-3 col-6">
                                                        <div class="p-2 rounded bg-light">
                                                            <small class="text-muted d-block">Nonce:</small>
                                                            <div class="fw-bold"><?php echo $block['nonce']; ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-3 col-6">
                                                        <div class="p-2 rounded bg-light">
                                                            <small class="text-muted d-block">Timestamp:</small>
                                                            <div>
                                                                <div class="fw-bold"><?php echo date('H:i:s', strtotime($block['timestamp'])); ?></div>
                                                                <small><?php echo date('M j, Y', strtotime($block['timestamp'])); ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-database me-1"></i> Block Data</h6>
                                                <?php if (is_array($blockData)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <tbody>
                                                                <?php foreach ($blockData as $key => $value): ?>
                                                                    <tr>
                                                                        <th style="width: 150px;" class="text-muted"><?php echo ucfirst($key); ?></th>
                                                                        <td><?php echo $value; ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($index < count($blockchainData) - 1): ?>
                                        <div class="block-link"></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Explanation Modal -->
    <div class="modal fade" id="explainModal" tabindex="-1" aria-labelledby="explainModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="explainModalLabel">
                        <i class="bi bi-info-circle me-2"></i> How Blockchain Verification Works
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h5 class="mb-3">Blockchain Technology for Voting</h5>
                        <p>
                            Blockchain creates an immutable, transparent record of votes that cannot be altered without detection.
                            Each vote is cryptographically secured and linked to the previous vote in the chain.
                        </p>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="me-3 p-2 rounded bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-shield-lock"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-2">Secure Vote Recording</h6>
                                            <p class="mb-0 small">Each vote is stored in a block with a unique hash, creating a tamper-proof record.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="me-3 p-2 rounded bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-person-lock"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-2">Voter Privacy</h6>
                                            <p class="mb-0 small">Your identity is protected through cryptographic hashing while allowing verification.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="me-3 p-2 rounded bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-check2-circle"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-2">Vote Verification</h6>
                                            <p class="mb-0 small">Verify your vote was counted correctly using your vote ID without revealing your choice.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="me-3 p-2 rounded bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-database-lock"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-2">Tamper Detection</h6>
                                            <p class="mb-0 small">Any modification to the blockchain will be immediately detectable.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-light">
                        <h6 class="fw-bold mb-2">Key Terms</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Block</dt>
                            <dd class="col-sm-9">A container for vote data with cryptographic protections</dd>
                            
                            <dt class="col-sm-3">Hash</dt>
                            <dd class="col-sm-9">A unique digital fingerprint derived from the block's data</dd>
                            
                            <dt class="col-sm-3">Genesis Block</dt>
                            <dd class="col-sm-9">The first block in the chain marking the election start</dd>
                            
                            <dt class="col-sm-3">Chain Validation</dt>
                            <dd class="col-sm-9">Process of verifying the integrity of the entire blockchain</dd>
                        </dl>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="blockchain_learn.php" class="btn btn-primary">Learn More</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
