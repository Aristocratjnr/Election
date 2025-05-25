<?php
// Use the standard authentication check
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';
$message = '';
$alertClass = '';

// Process the setup if requested
if (isset($_POST['setup_blockchain'])) {
    // Check if table exists
    $tableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'blockchain_blocks'");
    if ($result->num_rows > 0) {
        $tableExists = true;
    }

    // Create table if it doesn't exist
    if (!$tableExists) {
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
        
        if ($conn->query($sql)) {
            $message = "Blockchain table created successfully! The voting system now has blockchain security enabled.";
            $alertClass = "alert-success";
        } else {
            $message = "Error creating table: " . $conn->error;
            $alertClass = "alert-danger";
        }
    } else {
        $message = "Blockchain table already exists! Your system is ready for blockchain voting security.";
        $alertClass = "alert-info";
    }
}

// Get blockchain status
$blockchainEnabled = false;
$result = $conn->query("SHOW TABLES LIKE 'blockchain_blocks'");
if ($result->num_rows > 0) {
    $blockchainEnabled = true;
}

// Count votes in blockchain if enabled
$blockchainVotes = 0;
if ($blockchainEnabled) {
    $countResult = $conn->query("SELECT COUNT(*) as vote_count FROM blockchain_blocks WHERE JSON_EXTRACT(vote_data, '$.type') = 'vote'");
    if ($countResult && $countResult->num_rows > 0) {
        $blockchainVotes = $countResult->fetch_assoc()['vote_count'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Blockchain Setup - Election Management System</title>
    
    <!-- Include your CSS files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
      <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>
    
    <main id="main" class="main">
        <div class="display-7">
            <h1>Blockchain Security Setup</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Blockchain Setup</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Blockchain Security Setup</h5>
                            
                            <?php if (!empty($message)): ?>
                            <div class="alert <?php echo $alertClass; ?>" role="alert">
                                <?php echo $message; ?>
                            </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <h6>What is Blockchain Security?</h6>
                                <p>
                                    Blockchain technology enhances your election system by creating an immutable record of all votes cast. This provides:
                                </p>
                                <ul>
                                    <li><strong>Transparency:</strong> All votes can be verified without revealing voter identities</li>
                                    <li><strong>Tamper-proof:</strong> Once recorded, votes cannot be altered</li>
                                    <li><strong>Integrity:</strong> The entire voting chain can be validated at any time</li>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <h6>Current Status</h6>
                                <p>
                                    <span class="badge bg-<?php echo $blockchainEnabled ? 'success' : 'warning'; ?>">
                                        Blockchain Security: <?php echo $blockchainEnabled ? 'ENABLED' : 'DISABLED'; ?>
                                    </span>
                                    
                                    <?php if ($blockchainEnabled): ?>
                                    <span class="badge bg-primary ms-2">
                                        <?php echo $blockchainVotes; ?> votes secured
                                    </span>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <?php if (!$blockchainEnabled): ?>
                            <div class="text-center mt-4">
                                <form action="" method="post">
                                    <button type="submit" name="setup_blockchain" class="btn btn-primary">
                                        <i class="bi bi-shield-lock"></i> Enable Blockchain Security
                                    </button>
                                </form>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle me-2"></i> Blockchain security is already enabled for your election system!
                            </div>
                            <div class="text-center mt-4">
                                <a href="blockchain_verify.php" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i> Verify Blockchain Integrity
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Information</h5>
                            <div class="info-box">
                                <h6><i class="bi bi-info-circle me-2"></i> How It Works</h6>
                                <p>When enabled, the blockchain system:</p>
                                <ol>
                                    <li>Creates a genesis block for each election</li>
                                    <li>Records each vote as a block in the chain</li>
                                    <li>Links each block using cryptographic hashing</li>
                                    <li>Provides verification tools for transparency</li>
                                </ol>
                                <p class="mt-3">
                                    <strong>Note:</strong> Enabling blockchain security will not affect existing votes, but will secure all future votes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Include your JS files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
