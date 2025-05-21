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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
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
        }          body {
            background-color: #f7f9fc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            letter-spacing: -0.01em;
        }

        /* Typography adjustments */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        p {
            line-height: 1.6;
        }

        .fw-bold {
            letter-spacing: -0.02em;
        }

        .form-label {
            font-weight: 500;
            letter-spacing: -0.01em;
        }

        .btn {
            font-weight: 500;
            letter-spacing: -0.01em;
        }
          .header {
            background: var(--primary);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .header {
                padding: 0.75rem 0;
            }
            .header h1 {
                font-size: 1.25rem;
            }
            .header p {
                font-size: 0.875rem;
            }
        }
        
        .block-card {
            position: relative;
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            margin-bottom: 2rem;
            background: var(--card-bg);
            transition: all 0.3s ease;
        }


        .block-card.genesis {
            background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 100%);
            color: white;
        }

        .block-card.vote {
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
            color: white;
        }          .block-hash {
            font-family: 'Inter', monospace;
            font-size: 0.9rem;
            background-color: #f8f9fd;
            padding: 0.75rem;
            border-radius: 0.5rem;
            word-break: break-all;
            border: 1px solid #e9ecef;
            letter-spacing: 0;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }
        .block-hash.genesis {
            background-color: var(--blockchain-purple);
            color: white;
        }
        .block-link {
            position: relative;
            height: 30px;
            margin: -15px 0;
        }

        .block-link::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 100%;
            background: linear-gradient(to bottom, rgba(67,97,238,0.5), rgba(67,97,238,0.3));
            animation: pulse-line 2s infinite;
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
        
        .bg-primary-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark, #0a58ca) 100%);
            color: white;
        }

        .bg-success-gradient {
            background: linear-gradient(135deg, var(--success-color) 0%, var(--success-dark, #146c43) 100%);
            color: white;
        }

        .bg-info-gradient {
            background: linear-gradient(135deg, var(--info-color) 0%, var(--info-dark, #0891b2) 100%);
            color: white;
        }

        .bg-warning-gradient {
            background: linear-gradient(135deg, var(--warning-color) 0%, var(--warning-dark, #d97706) 100%);
            color: white;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .card-icon i {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .blockchain-stats {
            padding: 1.5rem;
            border-radius: 1rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
        }

        .blockchain-stats .stats-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-bottom: 1rem;
            background: rgba(255,255,255,0.2);
        }

        .blockchain-stats .stats-value {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .blockchain-stats .stats-label {
            font-size: 0.875rem;
            opacity: 0.8;
        }

        .custom-card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            background-color: var(--card-bg);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
        
        /* New blockchain explorer styles */
        .blockchain-explorer {
            background: var(--explorer-bg);
            border-radius: 0.5rem;
        }
        
        .blockchain-timeline {
            position: relative;
            padding: 1rem 0;
        }
        
        .blockchain-timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 1px;
            background: linear-gradient(to bottom, transparent, var(--explorer-accent) 10%, var(--explorer-accent) 90%, transparent);
            transform: translateX(-50%);
            z-index: 0;
            display: none; /* Hidden for horizontal layout */
        }
        
        .blockchain-block-card {
            position: relative;
            transition: all 0.2s ease;
        }
        
        
        
        .block-card {
            border-radius: 0.75rem;
            border: 1px solid var(--explorer-border) !important;
            background: var(--explorer-card-bg) !important;
            overflow: hidden;
        }
        
        .block-badge {
            background: var(--explorer-hash-bg);
            border: 1px solid var(--explorer-border);
            color: var(--explorer-text);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .block-hash {
            font-family: 'JetBrains Mono', monospace !important;
            word-break: break-all;
            background: var(--explorer-hash-bg) !important;
            border: 1px solid var(--explorer-border) !important;
            border-radius: 0.375rem;
            padding: 0.5rem !important;
            color: var(--explorer-text) !important;
            font-size: 0.75rem !important;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .block-hash:hover {
            white-space: normal;
            word-wrap: break-word;
        }
        
        .block-icon {
            background: var(--explorer-hash-bg);
            color: var(--explorer-accent);
        }
        
        .block-header {
            border-bottom: 1px solid var(--explorer-border);
            padding-bottom: 0.75rem;
            margin-bottom: 0.75rem;
        }
        
        .blockchain-arrow .bi {
            color: var(--explorer-accent);
            filter: drop-shadow(0 1px 3px rgba(59, 130, 246, 0.3));
        }
        
        .block-section {
            margin-bottom: 0.75rem;
        }
        
        .block-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: var(--explorer-text-muted);
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .block-data-table th {
            font-weight: 500;
            color: var(--explorer-text-muted);
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-top: none;
        }
        
        .block-data-table td {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .explorer-heading {
            color: var(--explorer-block-heading);
            font-weight: 600;
        }
        
        .explorer-tag {
            font-family: 'JetBrains Mono', monospace;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>

<body>    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 mb-2 mb-md-0">
                    <h1 class="display-6 font-weight-bold mb-3 text-primary">Blockchain Vote Verification</h1>
                    <p class="lead text-muted mb-0">Verify and validate votes using blockchain technology</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb" class="d-flex justify-content-md-end">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="login.php" class="text-white text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Blockchain Verification</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
      <div class="container pb-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="custom-card card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="card-icon me-3">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Blockchain Vote Verification</h5>
                        </div>
                            <p class="mb-0">
                                This system allows you to verify the integrity of votes using blockchain technology. 
                                <a href="blockchain_learn.php" class="ms-2 btn btn-sm btn-outline-primary">
                                    <i class="bi bi-info-circle me-1"></i> Learn how blockchain works
                                </a>
                            </p>
                          <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 h-100" >
                                    <label class="form-label fw-bold  mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-event me-2" style="font-size: 1.2rem;"></i>
                                            <span>Select Election</span>
                                        </div>
                                    </label>
                                    <select name="election" class="form-select form-select-md" style="border-radius: 10px;" onchange="this.form.submit()">
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
                                    <label class="form-label fw-bold mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-search me-2" style="font-size: 1.2rem;"></i>
                                            <span>Verify a Vote</span>
                                        </div>
                                    </label>
                                    <div class="input-group">
                                        <input type="hidden" name="action" value="verify">
                                        <input type="text" name="vote" class="" 
                                            style="border-top-left-radius: 10px; border-bottom-left-radius: 10px;" 
                                            placeholder="Enter Vote ID" aria-label="Vote ID" value="<?php echo $voteID ?? ''; ?>">
                                        <button class="btn btn-sm btn-outline-primary" type="submit">
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
        
        <?php if ($electionID && isset($electionDetails)): ?>          
            <div class="row mb-4" data-aos="fade-up">
                <div class="col-12">
                    <div class="d-flex align-items-center mb-3">
                        <div class=" me-3">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">
                            Blockchain Statistics for: <span class="text-secondary"><?php echo htmlspecialchars($electionDetails['name']); ?></span>
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
                            <h6 class="mb-3"><i class="bi bi-gear-fill me-2"></i>Blockchain Actions</h6>
                            <div class="d-flex flex-wrap gap-3">
                                <a href="?election=<?php echo $electionID; ?>&action=view" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye-fill me-2"></i> View Blockchain
                                </a>
                                <a href="?election=<?php echo $electionID; ?>&action=validate" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-shield-check me-2"></i> Validate Blockchain
                                </a>
                                
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
            
            <?php endif; ?>            <!-- Blockchain View -->
            <?php if ($action === 'view' && !empty($blockchainData)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-0 blockchain-explorer">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="p-2 rounded-circle me-3 block-icon">
                                <i class="bi bi-box-seam-fill fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold explorer-heading">Blockchain Explorer</h4>
                                <p class="text-muted mb-0 small">Election ID: <?php echo $electionID; ?> - <?php echo htmlspecialchars($electionDetails['name']); ?></p>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark border ms-3"><i class="bi bi-layers me-1"></i> <?php echo count($blockchainData); ?> Total Blocks</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex">
                            <i class="bi bi-info-circle text-primary me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">About This Blockchain</h6>
                                <p class="mb-0">This is a visual representation of the complete election blockchain. Each block contains a unique hash, data, and a link to the previous block forming an immutable chain of votes.</p>
                            </div>
                        </div>
                    </div>
                    <div class="blockchain-timeline d-flex flex-nowrap overflow-auto py-3" style="gap: 1.5rem;">
                    <?php foreach ($blockchainData as $index => $block): ?>                        <?php 
                        $blockData = formatBlockData($block['vote_data']);
                        $isGenesis = isset($blockData['type']) && $blockData['type'] === 'Genesis Block';
                        $blockAge = time() - strtotime($block['timestamp']);
                        $timeAgo = ($blockAge < 60) ? 'just now' : 
                                  (($blockAge < 3600) ? floor($blockAge/60) . ' min ago' : 
                                  (($blockAge < 86400) ? floor($blockAge/3600) . ' hr ago' : 
                                  floor($blockAge/86400) . ' days ago'));
                        ?>
                        <div class="blockchain-block-card position-relative px-2" style="min-width: 350px; max-width: 380px;">
                            <div class="block-card mb-0 border shadow-sm">
                                <!-- Block Header -->
                                <div class="block-header p-3 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi <?php echo $isGenesis ? 'bi-diamond-fill' : 'bi-box-fill'; ?> me-2 text-<?php echo $isGenesis ? 'warning' : 'primary'; ?>"></i>
                                                <h6 class="mb-0 fw-bold text-<?php echo $isGenesis ? 'warning' : 'dark'; ?>">
                                                    <?php echo $isGenesis ? 'Genesis Block' : 'Block'; ?> #<?php echo $block['block_id']; ?>
                                                </h6>
                                                <?php if ($isGenesis): ?>
                                                    <span class="badge bg-warning text-dark ms-2 explorer-tag">Genesis</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-clock me-1"></i> <?php echo $timeAgo; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small fw-bold text-secondary"><?php echo date('M j, Y', strtotime($block['timestamp'])); ?></div>
                                            <div class="text-muted small"><?php echo date('H:i:s', strtotime($block['timestamp'])); ?> UTC</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Block Content -->
                                <div class="p-3">
                                    <!-- Hash -->
                                    <div class="block-section">
                                        <div class="block-section-title d-flex justify-content-between">
                                            <span><i class="bi bi-key-fill me-1"></i> Block Hash</span>
                                        </div>
                                        <div class="block-hash" title="<?php echo $block['block_hash']; ?>">
                                            <?php echo $block['block_hash']; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Previous Hash -->
                                    <div class="block-section">
                                        <div class="block-section-title">
                                            <i class="bi bi-link-45deg me-1"></i> Previous Block Hash
                                        </div>
                                        <?php if ($block['previous_hash']): ?>
                                            <div class="block-hash" title="<?php echo $block['previous_hash']; ?>">
                                                <?php echo $block['previous_hash']; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-1">
                                                <span class="badge bg-light text-secondary border explorer-tag">NULL (Genesis)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Technical Details -->
                                    <div class="block-section">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="block-section-title">
                                                    <i class="bi bi-dice-5 me-1"></i> Nonce
                                                </div>
                                                <div class="block-badge px-2 py-1 text-center rounded">
                                                    <?php echo $block['nonce']; ?>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="block-section-title">
                                                    <i class="bi bi-clock-history me-1"></i> Timestamp
                                                </div>
                                                <div class="block-badge px-2 py-1 text-center rounded">
                                                    <?php echo strtotime($block['timestamp']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Transaction Data -->
                                    <div class="block-section">
                                        <div class="block-section-title">
                                            <i class="bi bi-database-fill me-1"></i> Data
                                        </div>
                                        <?php if (is_array($blockData)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm block-data-table mb-0 border">
                                                    <tbody>
                                                        <?php foreach ($blockData as $key => $value): ?>
                                                            <tr>
                                                                <th><?php echo ucfirst($key); ?></th>
                                                                <td class="text-break"><?php echo $value; ?></td>
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
                                <div class="blockchain-arrow d-flex flex-column align-items-center" style="position: absolute; right: -25px; top: 50%; transform: translateY(-50%); z-index: 2;">
                                    <i class="bi bi-arrow-right fs-3 text-primary"></i>
                                </div>
                            <?php endif; ?>
                        </div>                    <?php endforeach; ?>
                    </div>
                    
                    <!-- Blockchain Explorer Legend -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-2 p-1 rounded bg-light">
                                        <i class="bi bi-diamond-fill text-warning"></i>
                                    </div>
                                    <div class="small">
                                        <strong>Genesis Block</strong> - The first block in the chain
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-2 p-1 rounded bg-light">
                                        <i class="bi bi-box-fill text-primary"></i>
                                    </div>
                                    <div class="small">
                                        <strong>Vote Block</strong> - Contains vote transaction data
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-2 p-1 rounded bg-light">
                                        <i class="bi bi-arrow-right text-primary"></i>
                                    </div>
                                    <div class="small">
                                        <strong>Chain Link</strong> - Shows block connections
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light py-3 px-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Scroll horizontally to view all blocks
                        </div>
                        <div>
                            <a href="?election=<?php echo $electionID; ?>&action=validate" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-shield-check me-1"></i> Validate Chain
                            </a>
                        </div>
                    </div>
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
