<?php
// Create a demo blockchain block for educational purposes
function getExampleBlock() {
    return [
        'block_id' => 42,
        'election_id' => 5,
        'previous_hash' => 'a1b2c3d4e5f6...',
        'block_hash' => '1a2b3c4d5e6f...',
        'timestamp' => date('Y-m-d H:i:s'),
        'nonce' => 12345,
        'vote_data' => json_encode([
            'type' => 'vote',
            'election_id' => 5,
            'vote_id' => 123,
            'candidate_id' => 789,
            'voter_hash' => 'x9y8z7...',
            'timestamp' => date('Y-m-d H:i:s')
        ]),
        'is_valid' => 1
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Understanding Blockchain Voting Security</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>        
    .blockchain-diagram {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }

        @media (min-width: 768px) {
            .blockchain-diagram {
                padding: 2rem;
            }
        }
        
        .block {
            background-color: #fff;
            border: 2px solid #6c757d;
            border-radius: 8px;
            padding: 1rem;
            position: relative;
            margin-bottom: 2.5rem;
        }
        
        @media (max-width: 767px) {
            .block {
                margin-bottom: 3rem;
            }
            .block:last-child {
                margin-bottom: 1.5rem;
            }
        }
        
        .block-arrow {
            position: absolute;
            bottom: -2.5rem;
            left: 50%;
            transform: translateX(-50%);
            color: #6c757d;
            font-size: 1.5rem;
        }

        @media (max-width: 767px) {
            .block-arrow {
                transform: translateX(-50%) rotate(0deg);
            }
        }

        @media (min-width: 768px) {
            .block-arrow {
                right: -1.5rem;
                left: auto;
                top: 50%;
                bottom: auto;
                transform: translateY(-50%) rotate(-90deg);
            }
        }
        
        .hash {
            font-family: 'Inter', monospace;
            background-color: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .tamper-alert {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            border-left: 5px solid #dc3545;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        
        .code-block {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Understanding Blockchain Voting</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo $isAdmin ? 'dashboard.php' : 'student.php'; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="blockchain_verify.php">Blockchain Verification</a></li>
                    <li class="breadcrumb-item active">Learn About Blockchain</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">How Blockchain Secures Your Vote</h5>
                            
                            <div class="alert alert-primary" role="alert">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                This page explains how our election system uses blockchain technology to secure and verify votes.
                            </div>
                            
                            <h6 class="mt-4 mb-3">What is a Blockchain?</h6>
                            <p>
                                A blockchain is a digital ledger of transactions that is duplicated and distributed across a network of computer systems. 
                                In our voting system, each vote is secured as a "block" in a chain, creating an immutable record that cannot be altered
                                without detection.
                            </p>
                            
                            <div class="blockchain-diagram mt-4 mb-4">
                                <h6 class="text-center mb-4">Simplified Blockchain Structure</h6>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="block">
                                            <h6 class="text-center">Genesis Block</h6>
                                            <div class="small">
                                                <div><strong>Previous Hash:</strong> <span class="hash">NULL</span></div>
                                                <div><strong>Data:</strong> Election Start</div>
                                                <div><strong>Hash:</strong> <span class="hash">a1b2c3...</span></div>
                                            </div>
                                            <div class="block-arrow">
                                                <i class="bi bi-arrow-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="block">
                                            <h6 class="text-center">Vote Block #1</h6>
                                            <div class="small">
                                                <div><strong>Previous Hash:</strong> <span class="hash">a1b2c3...</span></div>
                                                <div><strong>Data:</strong> Vote for Candidate X</div>
                                                <div><strong>Hash:</strong> <span class="hash">d4e5f6...</span></div>
                                            </div>
                                            <div class="block-arrow">
                                                <i class="bi bi-arrow-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="block">
                                            <h6 class="text-center">Vote Block #2</h6>
                                            <div class="small">
                                                <div><strong>Previous Hash:</strong> <span class="hash">d4e5f6...</span></div>
                                                <div><strong>Data:</strong> Vote for Candidate Y</div>
                                                <div><strong>Hash:</strong> <span class="hash">g7h8i9...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="mt-4 mb-3">Key Benefits of Blockchain Voting</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-lock-fill fs-2 text-primary mb-3"></i>
                                            <h6>Immutability</h6>
                                            <p class="small text-muted">Once a vote is recorded, it cannot be altered or deleted without detection</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-search fs-2 text-primary mb-3"></i>
                                            <h6>Transparency</h6>
                                            <p class="small text-muted">Anyone can verify the integrity of the vote chain without seeing confidential voter data</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-shield-check fs-2 text-primary mb-3"></i>
                                            <h6>Security</h6>
                                            <p class="small text-muted">Cryptographic techniques ensure votes are secure and tamper-resistant</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="mt-4 mb-3">How Tamper Detection Works</h6>
                            <p>
                                If anyone tries to change a vote in the blockchain, it invalidates all subsequent blocks. This makes tampering immediately 
                                detectable through blockchain validation.
                            </p>
                            
                            <div class="tamper-alert">
                                <h6><i class="bi bi-exclamation-triangle-fill me-2"></i> Tampering Example</h6>
                                <p class="mb-0">
                                    If someone changed a vote in Block #1, its hash would change from <span class="hash">d4e5f6...</span> to something different.
                                    Block #2 would immediately show as invalid because it still references the original hash <span class="hash">d4e5f6...</span>
                                    as its "previous hash" value.
                                </p>
                            </div>
                            
                            <h6 class="mt-4 mb-3">How We Protect Voter Privacy</h6>
                            <p>
                                While the blockchain records every vote, it does not store your personal identity. Instead, it uses a one-way cryptographic hash
                                of your voter ID combined with a unique random value (salt). This makes it impossible to determine who cast a specific vote
                                by looking at the blockchain alone, while still allowing you to verify your own vote was recorded correctly.
                            </p>
                            
                            <p>Below is an example of how a vote is stored in our blockchain:</p>
                            
                            <?php 
                            $example = getExampleBlock();
                            ?>
                            
                            <div class="code-block">
                                <div class="d-flex justify-content-between mb-2">
            
                                    <button class="btn btn-sm btn-outline-light copy-btn" data-clipboard-target="#blockCode">
                                        <i class="bi bi-clipboard"></i> Copy
                                    </button>
                                </div>
<pre id="blockCode">{
  "block_id": <?= $example['block_id'] ?>,
  "previous_hash": "<?= $example['previous_hash'] ?>",
  "block_hash": "<?= $example['block_hash'] ?>",
  "timestamp": "<?= $example['timestamp'] ?>",
  "nonce": <?= $example['nonce'] ?>,
  "vote_data": {
    "type": "vote",
    "election_id": 5,
    "vote_id": 123,
    "candidate_id": 789,
    "voter_hash": "x9y8z7...",  <!-- This is NOT your student ID -->
    "timestamp": "<?= $example['timestamp'] ?>"
  }
}</pre>
                            </div>
                            
                            
                            <div class="text-center mt-5 mb-4">
                                <a href="blockchain_verify.php" class="btn btn-primary">
                                    <i class="bi bi-shield-check me-2"></i> Verify Election Blockchain
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Blockchain FAQ</h5>
                            
                            <div class="accordion" id="blockchainFAQ">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            Can I see who voted for whom?
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#blockchainFAQ">
                                        <div class="accordion-body">
                                            No. The blockchain only stores a one-way hash of your voter ID, not your actual identity. This ensures vote privacy while maintaining verifiability.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            How do I verify my vote was counted?
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#blockchainFAQ">
                                        <div class="accordion-body">
                                            After voting, you'll receive a vote ID. You can enter this ID in the blockchain verification tool to confirm your vote was recorded and remains unaltered.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            What happens if someone tries to change votes?
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#blockchainFAQ">
                                        <div class="accordion-body">
                                            Any change to a recorded vote would break the chain's cryptographic links. The blockchain validation tool would immediately detect this, showing which blocks were compromised.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            Is blockchain the same as cryptocurrency?
                                        </button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#blockchainFAQ">
                                        <div class="accordion-body">
                                            No. While cryptocurrencies use blockchain technology, our implementation only uses the secure record-keeping aspects of blockchain. No cryptocurrency or tokens are involved.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card bg-light mt-4">
                                <div class="card-body">
                                    <h6><i class="bi bi-lightbulb-fill text-warning me-2"></i> Did You Know?</h6>
                                    <p class="small mb-0">
                                        Blockchain technology was first conceptualized in 1991, long before Bitcoin. It was originally proposed as a way to timestamp digital documents so they couldn't be backdated or tampered with.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/js/main.js"></script>

<script>
   // Copy button functionality (would require clipboard.js library)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.copy-btn').addEventListener('click', function() {
                const codeText = document.getElementById('blockCode').textContent;
                navigator.clipboard.writeText(codeText).then(() => {
                    this.innerHTML = '<i class="bi bi-check"></i> Copied';
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                    }, 2000);
                });
            });
        });
    </script>
</body>
</html>
