<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

// Fetch active elections
$elections_query = "SELECT electionID, name FROM elections WHERE status = 'Ongoing' OR status = 'Scheduled'";
$elections_result = $conn->query($elections_query);
$elections = $elections_result->fetch_all(MYSQLI_ASSOC);

// Fetch positions for the selected election
$positions = [];
$selected_election = null;
$election_details = [];
$existing_design = [];

if (isset($_GET['election_id'])) {
    $election_id = $_GET['election_id'];
    $selected_election = $election_id;
    
    // Get election details
    $election_details_query = "SELECT * FROM elections WHERE electionID = ?";
    $stmt = $conn->prepare($election_details_query);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $election_details = $stmt->get_result()->fetch_assoc();
    
    // Get positions
    $positions_query = "SELECT * FROM positions WHERE electionID = ?";
    $stmt = $conn->prepare($positions_query);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $positions_result = $stmt->get_result();
    $positions = $positions_result->fetch_all(MYSQLI_ASSOC);
    
    // Check for existing ballot design
    $design_query = "SELECT * FROM ballot_designs WHERE electionID = ?";
    $stmt = $conn->prepare($design_query);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $existing_design = $stmt->get_result()->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $election_id = $_POST['election_id'];
    $ballot_title = $_POST['ballot_title'];
    $ballot_description = $_POST['ballot_description'];
    $ballot_style = $_POST['ballot_style'];
    $show_logo = isset($_POST['show_logo']) ? 1 : 0;
    $show_header = isset($_POST['show_header']) ? 1 : 0;
    $show_footer = isset($_POST['show_footer']) ? 1 : 0;
    $logo_position = isset($_POST['logo_position']) ? $_POST['logo_position'] : 'center';
    $header_color = isset($_POST['header_color']) ? $_POST['header_color'] : '#4361ee';
    $font_family = isset($_POST['font_family']) ? $_POST['font_family'] : 'Poppins';
    
    // Check if design exists for this election
    $check_query = "SELECT designID FROM ballot_designs WHERE electionID = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    
    if ($exists) {
        // Update existing design
        $stmt = $conn->prepare("UPDATE ballot_designs SET title=?, description=?, style=?, show_logo=?, show_header=?, show_footer=?, logo_position=?, header_color=?, font_family=? WHERE electionID=?");
        $stmt->bind_param("sssiiiissi", $ballot_title, $ballot_description, $ballot_style, $show_logo, $show_header, $show_footer, $logo_position, $header_color, $font_family, $election_id);
    } else {
        // Insert new design
        $stmt = $conn->prepare("INSERT INTO ballot_designs (electionID, title, description, style, show_logo, show_header, show_footer, logo_position, header_color, font_family) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiiiiss", $election_id, $ballot_title, $ballot_description, $ballot_style, $show_logo, $show_header, $show_footer, $logo_position, $header_color, $font_family);
    }
    
    if ($stmt->execute()) {
        $success_message = "Ballot design saved successfully!";
        // Refresh the page to show updated design
        header("Location: ballots.php?election_id=" . $election_id);
        exit();
    } else {
        $error_message = "Error saving ballot design: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballot Design - SmartVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
        }

        /* Sidebar styles */
        .sidebar {
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            z-index: 100;
        }

        .sidebar .nav-link {
            color: #666;
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .sidebar .nav-link i {
            color: var(--primary-color);
            transition: all 0.2s;
        }

        .sidebar .nav-link.active i,
        .sidebar .nav-link:hover i {
            color: inherit;
        }

        /* Main content area */
        main {
            background-color: var(--light-bg);
            min-height: 100vh;
        }

        /* Update ballot container styles */
        .ballot-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
        }

        .ballot-preview {
            background: white;
            border-radius: 0;
            box-shadow: none;
            padding: 2rem;
            transition: all 0.3s ease;
            min-height: 500px;
        }

        /* Ballot Styles */
        .ballot-preview.modern {
            --header-color: #4361ee;
            --text-color: #333;
            --border-color: #dee2e6;
            --highlight-color: #f8f9fa;
        }

        .ballot-preview.classic {
            --header-color: #2c3e50;
            --text-color: #2c3e50;
            --border-color: #bdc3c7;
            --highlight-color: #ecf0f1;
            font-family: 'Times New Roman', serif;
        }

        .ballot-preview.minimal {
            --header-color: #333;
            --text-color: #555;
            --border-color: #eee;
            --highlight-color: #fff;
            font-family: 'Helvetica Neue', sans-serif;
        }

        .ballot-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--header-color);
            color: var(--header-color);
            transition: all 0.3s ease;
        }

        .ballot-title {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .ballot-description {
            color: var(--text-color);
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .position-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: white;
            transition: all 0.3s ease;
        }

        .position-title {
            font-size: 1.5rem;
            color: var(--header-color);
            margin-bottom: 1rem;
            font-weight: 500;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed var(--border-color);
            display: flex;
            align-items: center;
        }

        .candidate-list {
            list-style: none;
            padding: 0;
        }

        .candidate-item {
            padding: 1rem;
            margin-bottom: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
            background-color: white;
        }

        .candidate-item:hover {
            background-color: var(--highlight-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        .candidate-item .form-check {
            display: flex;
            align-items: center;
        }

        .candidate-item .form-check-input {
            margin-right: 1rem;
            width: 1.2rem;
            height: 1.2rem;
        }

        .candidate-info {
            flex: 1;
        }

        .ballot-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-color);
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Style selectors */
        .style-preview {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            margin: 0.5rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid transparent;
            overflow: hidden;
            position: relative;
        }

        .style-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .style-preview.selected {
            border: 3px solid var(--primary-color);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
        }

        .style-preview .style-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px;
            text-align: center;
            font-size: 0.8rem;
        }

        /* Form stylings */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Card styles */
        .settings-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            border: none;
            transition: all 0.3s ease;
        }

        .settings-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .settings-card .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-radius: 10px 10px 0 0;
        }

        .settings-card .card-body {
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-control, .form-select {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }

        /* Color options */
        .color-option {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .color-option:hover {
            transform: scale(1.1);
        }

        .color-option.selected {
            border-color: #333;
            transform: scale(1.15);
        }

        /* Font options */
        .font-option {
            padding: 0.5rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            display: inline-block;
            transition: all 0.2s;
        }

        .font-option:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .font-option.selected {
            border-color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            font-weight: 500;
        }

        /* Logo position options */
        .logo-position-option {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            flex: 1;
            min-width: 80px;
        }

        .logo-position-option:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .logo-position-option.selected {
            border-color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .logo-position-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Zoom controls */
        .preview-controls {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: white;
            padding: 0.75rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
            z-index: 1000;
            display: flex;
            gap: 0.5rem;
        }

        .preview-controls button {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            padding: 0;
        }

        .no-positions {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
            font-size: 1.1rem;
            background-color: rgba(0,0,0,0.02);
            border-radius: 10px;
            margin: 2rem 0;
        }

        .no-positions i {
            color: var(--warning-color);
        }

        /* Form switches */
        .form-switch {
            padding-left: 2.5rem;
        }

        .form-switch .form-check-input {
            height: 1.25rem;
            width: 2.25rem;
            margin-left: -2.5rem;
        }

        .form-switch .form-check-input:focus {
            border-color: rgba(0, 0, 0, 0.25);
            box-shadow: none;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            .sidebar, nav, .preview-controls, header, footer {
                display: none !important;
            }
            
            .ballot-preview {
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
            }
            
            .position-section {
                page-break-inside: avoid;
            }

            main, .col-md-9, .col-lg-10 {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .card-header, .card-body {
                padding: 0 !important;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 1000;
                padding: 0;
                overflow-x: hidden;
                overflow-y: auto;
                visibility: hidden;
                width: 100%;
                max-width: 250px;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out, visibility 0.3s ease-in-out;
            }

            .sidebar.show {
                visibility: visible;
                transform: translateX(0);
            }
            
            .preview-controls {
                bottom: 1rem;
                right: 1rem;
            }

            .ballot-container {
                padding: 0 1rem;
            }
        }

        @media (max-width: 576px) {
            .style-preview {
                width: 70px;
                height: 70px;
            }

            .font-option, .logo-position-option {
                margin-bottom: 0.5rem;
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            .preview-controls {
                padding: 0.5rem;
                gap: 0.25rem;
            }

            .preview-controls button {
                width: 32px;
                height: 32px;
            }

            .position-title {
                font-size: 1.25rem;
            }

            .ballot-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- New sidebar navigation -->
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" style="min-height: 100vh;">
                <div class="position-sticky pt-3">
                    <div class="d-flex align-items-center mb-3 px-3">
                        <img src="assets/img/logo.png" alt="Logo" style="height: 40px;" class="me-2">
                        <h4 class="m-0 text-primary">SmartVote</h4>
                    </div>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="elections.php">
                                <i class="bi bi-calendar-event me-2"></i> Elections
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="ballots.php" aria-current="page">
                                <i class="bi bi-file-earmark-text me-2"></i> Ballots
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="candidates.php">
                                <i class="bi bi-people me-2"></i> Candidates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="voters.php">
                                <i class="bi bi-person-check me-2"></i> Voters
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="results.php">
                                <i class="bi bi-bar-chart me-2"></i> Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-gear me-2"></i> Settings
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="px-3">
                        <a href="logout.php" class="btn btn-outline-danger w-100">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Ballot Design</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print Preview
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" id="saveDesign">
                                <i class="bi bi-save me-1"></i> Save Design
                            </button>
                        </div>
                        <!-- Mobile menu toggle -->
                        <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" id="sidebarToggle">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Design Controls -->
                    <div class="col-12 col-lg-4">
                        <form id="ballotForm" method="POST">
                            <div class="settings-card card mb-4">
                                <div class="card-header d-flex align-items-center">
                                    <i class="bi bi-sliders me-2 text-primary"></i>Basic Settings
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Select Election</label>
                                        <select class="form-select form-select-sm" name="election_id" id="electionSelect" required onchange="this.form.submit()">
                                            <option value="">Choose an election...</option>
                                            <?php foreach ($elections as $election): ?>
                                                <option value="<?php echo $election['electionID']; ?>" <?php echo ($selected_election == $election['electionID']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($election['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-type me-1"></i>Ballot Title</label>
                                        <input type="text" class="form-control form-control-sm" name="ballot_title" required
                                            value="<?php echo isset($existing_design['title']) ? htmlspecialchars($existing_design['title']) : (isset($election_details['name']) ? htmlspecialchars($election_details['name']) : ''); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-card-text me-1"></i>Description</label>
                                        <textarea class="form-control form-control-sm" name="ballot_description" rows="3"><?php echo isset($existing_design['description']) ? htmlspecialchars($existing_design['description']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-card card mb-4">
                                <div class="card-header d-flex align-items-center">
                                    <i class="bi bi-palette-fill me-2 text-primary"></i>Design Style
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-brush me-1"></i>Ballot Style</label>
                                        <div class="d-flex flex-wrap">
                                            <div class="style-option">
                                                <div class="style-preview <?php echo (isset($existing_design['style']) && $existing_design['style'] == 'modern') || !isset($existing_design['style']) ? 'selected' : ''; ?>" 
                                                     data-style="modern" style="background: linear-gradient(135deg, #4361ee, #4895ef);">
                                                    <div class="style-label"><i class="bi bi-stars me-1"></i>Modern</div>
                                                </div>
                                            </div>
                                            <div class="style-option">
                                                <div class="style-preview <?php echo isset($existing_design['style']) && $existing_design['style'] == 'classic' ? 'selected' : ''; ?>" 
                                                     data-style="classic" style="background: linear-gradient(135deg, #2c3e50, #4ca1af);">
                                                    <div class="style-label"><i class="bi bi-book me-1"></i>Classic</div>
                                                </div>
                                            </div>
                                            <div class="style-option">
                                                <div class="style-preview <?php echo isset($existing_design['style']) && $existing_design['style'] == 'minimal' ? 'selected' : ''; ?>" 
                                                     data-style="minimal" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); color: #333;">
                                                    <div class="style-label"><i class="bi bi-grid me-1"></i>Minimal</div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="ballot_style" id="selected_style" 
                                               value="<?php echo isset($existing_design['style']) ? htmlspecialchars($existing_design['style']) : 'modern'; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-palette2 me-1"></i>Header Color</label>
                                        <div>
                                            <div class="color-option <?php echo (!isset($existing_design['header_color']) || (isset($existing_design['header_color']) && $existing_design['header_color'] == '#4361ee')) ? 'selected' : ''; ?>" 
                                                 style="background-color: #4361ee;" data-color="#4361ee"></div>
                                            <div class="color-option <?php echo isset($existing_design['header_color']) && $existing_design['header_color'] == '#2c3e50' ? 'selected' : ''; ?>" 
                                                 style="background-color: #2c3e50;" data-color="#2c3e50"></div>
                                            <div class="color-option <?php echo isset($existing_design['header_color']) && $existing_design['header_color'] == '#d90429' ? 'selected' : ''; ?>" 
                                                 style="background-color: #d90429;" data-color="#d90429"></div>
                                            <div class="color-option <?php echo isset($existing_design['header_color']) && $existing_design['header_color'] == '#3a0ca3' ? 'selected' : ''; ?>" 
                                                 style="background-color: #3a0ca3;" data-color="#3a0ca3"></div>
                                            <div class="color-option <?php echo isset($existing_design['header_color']) && $existing_design['header_color'] == '#2b9348' ? 'selected' : ''; ?>" 
                                                 style="background-color: #2b9348;" data-color="#2b9348"></div>
                                        </div>
                                        <input type="hidden" name="header_color" id="header_color" 
                                               value="<?php echo isset($existing_design['header_color']) ? htmlspecialchars($existing_design['header_color']) : '#4361ee'; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-type-bold me-1"></i>Font Family</label>
                                        <div>
                                            <span class="font-option <?php echo (!isset($existing_design['font_family']) || (isset($existing_design['font_family']) && $existing_design['font_family'] == 'Poppins')) ? 'selected' : ''; ?>" 
                                                  data-font="Poppins" style="font-family: 'Poppins'">Poppins</span>
                                            <span class="font-option <?php echo isset($existing_design['font_family']) && $existing_design['font_family'] == 'Roboto' ? 'selected' : ''; ?>" 
                                                  data-font="Roboto" style="font-family: 'Roboto'">Roboto</span>
                                            <span class="font-option <?php echo isset($existing_design['font_family']) && $existing_design['font_family'] == 'Open Sans' ? 'selected' : ''; ?>" 
                                                  data-font="Open Sans" style="font-family: 'Open Sans'">Open Sans</span>
                                            <span class="font-option <?php echo isset($existing_design['font_family']) && $existing_design['font_family'] == 'Times New Roman' ? 'selected' : ''; ?>" 
                                                  data-font="Times New Roman" style="font-family: 'Times New Roman'">Times</span>
                                            <span class="font-option <?php echo isset($existing_design['font_family']) && $existing_design['font_family'] == 'Arial' ? 'selected' : ''; ?>" 
                                                  data-font="Arial" style="font-family: 'Arial'">Arial</span>
                                        </div>
                                        <input type="hidden" name="font_family" id="font_family" 
                                               value="<?php echo isset($existing_design['font_family']) ? htmlspecialchars($existing_design['font_family']) : 'Poppins'; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="settings-card card mb-4">
                                <div class="card-header d-flex align-items-center">
                                    <i class="bi bi-grid-1x2-fill me-2 text-primary"></i>Layout Options
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-image me-1"></i>Logo Position</label>
                                        <div class="d-flex flex-wrap">
                                            <div class="logo-position-option <?php echo (!isset($existing_design['logo_position']) || (isset($existing_design['logo_position']) && $existing_design['logo_position'] == 'center')) ? 'selected' : ''; ?>" 
                                                 data-position="center">
                                                <i class="bi bi-align-center"></i>
                                                Center
                                            </div>
                                            <div class="logo-position-option <?php echo isset($existing_design['logo_position']) && $existing_design['logo_position'] == 'left' ? 'selected' : ''; ?>" 
                                                 data-position="left">
                                                <i class="bi bi-align-start"></i>
                                                Left
                                            </div>
                                            <div class="logo-position-option <?php echo isset($existing_design['logo_position']) && $existing_design['logo_position'] == 'right' ? 'selected' : ''; ?>" 
                                                 data-position="right">
                                                <i class="bi bi-align-end"></i>
                                                Right
                                            </div>
                                        </div>
                                        <input type="hidden" name="logo_position" id="logo_position" 
                                               value="<?php echo isset($existing_design['logo_position']) ? htmlspecialchars($existing_design['logo_position']) : 'center'; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" name="show_logo" id="showLogo" 
                                                   <?php echo (!isset($existing_design['show_logo']) || (isset($existing_design['show_logo']) && $existing_design['show_logo'] == 1)) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="showLogo">
                                                <i class="bi bi-image me-1"></i>Show School Logo
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" name="show_header" id="showHeader" 
                                                   <?php echo (!isset($existing_design['show_header']) || (isset($existing_design['show_header']) && $existing_design['show_header'] == 1)) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="showHeader">
                                                <i class="bi bi-type-h1 me-1"></i>Show Header
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="show_footer" id="showFooter" 
                                                   <?php echo (!isset($existing_design['show_footer']) || (isset($existing_design['show_footer']) && $existing_design['show_footer'] == 1)) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="showFooter">
                                                <i class="bi bi-type-underline me-1"></i>Show Footer
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Ballot Preview -->
                    <div class="col-12 col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div><i class="bi bi-eye me-2"></i>Ballot Preview</div>
                                <div>
                                    <button class="btn btn-sm btn-light" onclick="resetZoom()">
                                        <i class="bi bi-aspect-ratio me-1"></i>100%
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="ballot-preview <?php echo isset($existing_design['style']) ? htmlspecialchars($existing_design['style']) : 'modern'; ?>">
                                    <?php if (!isset($existing_design['show_header']) || (isset($existing_design['show_header']) && $existing_design['show_header'] == 1)): ?>
                                    <div class="ballot-header">
                                        <?php if (!isset($existing_design['show_logo']) || (isset($existing_design['show_logo']) && $existing_design['show_logo'] == 1)): ?>
                                        <img src="assets/img/logo.png" alt="School Logo" class="mb-3" style="max-height: 80px; 
                                            <?php echo isset($existing_design['logo_position']) ? 'float: ' . htmlspecialchars($existing_design['logo_position']) . ';' : ''; ?>">
                                        <?php endif; ?>
                                        <h1 class="ballot-title"><?php echo isset($existing_design['title']) ? htmlspecialchars($existing_design['title']) : (isset($election_details['name']) ? htmlspecialchars($election_details['name']) : 'Official Ballot'); ?></h1>
                                        <p class="ballot-description"><?php echo isset($existing_design['description']) ? htmlspecialchars($existing_design['description']) : 'Student Council Election'; ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($positions)): ?>
                                        <?php foreach ($positions as $position): ?>
                                            <div class="position-section">
                                                <h3 class="position-title">
                                                    <i class="bi bi-person-badge me-2"></i><?php echo htmlspecialchars($position['title']); ?>
                                                </h3>
                                                <ul class="candidate-list">
                                                    <?php
                                                    // Check if positionID column exists
                                                    $check_column_query = "SHOW COLUMNS FROM candidates LIKE 'positionID'";
                                                    $column_exists = $conn->query($check_column_query)->num_rows > 0;

                                                    if ($column_exists) {
                                                        $candidates_query = "SELECT c.*, s.name, s.department FROM candidates c 
                                                                           LEFT JOIN students s ON c.studentID = s.studentID 
                                                                           WHERE c.positionID = ?";
                                                        $stmt = $conn->prepare($candidates_query);
                                                        $stmt->bind_param("i", $position['positionID']);
                                                    } else {
                                                        // Fallback query if positionID doesn't exist
                                                        $candidates_query = "SELECT c.*, s.name, s.department FROM candidates c 
                                                                           LEFT JOIN students s ON c.studentID = s.studentID";
                                                        $stmt = $conn->prepare($candidates_query);
                                                    }
                                                    
                                                    $stmt->execute();
                                                    $candidates_result = $stmt->get_result();
                                                    while ($candidate = $candidates_result->fetch_assoc()):
                                                    ?>
                                                        <li class="candidate-item">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="vote_<?php echo $position['positionID']; ?>" 
                                                                       id="candidate_<?php echo $candidate['candidateID']; ?>"
                                                                       value="<?php echo $candidate['candidateID']; ?>" required>
                                                                <label class="form-check-label" for="candidate_<?php echo $candidate['candidateID']; ?>">
                                                                    <div class="candidate-info">
                                                                        <strong><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($candidate['name'] ?? 'Candidate #'.$candidate['studentID']); ?></strong>
                                                                        <small class="text-muted d-block">
                                                                            <?php 
                                                                            if (!empty($candidate['department'])) {
                                                                                echo '<i class="bi bi-building me-1"></i>' . htmlspecialchars($candidate['department']); 
                                                                            }
                                                                            
                                                                            if (!empty($candidate['manifesto'])) {
                                                                                echo !empty($candidate['department']) ? ' - ' : '';
                                                                                echo '<i class="bi bi-chat-quote me-1"></i>' . htmlspecialchars($candidate['manifesto']);
                                                                            }
                                                                            
                                                                            if (empty($candidate['department']) && empty($candidate['manifesto'])) {
                                                                                echo '<i class="bi bi-info-circle me-1"></i>No additional information';
                                                                            }
                                                                            ?>
                                                                        </small>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </li>
                                                    <?php endwhile; ?>
                                                </ul>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-positions">
                                            <i class="bi bi-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                                            <p>No positions found for this election or no election selected.</p>
                                            <p>Please select an election with positions to design the ballot.</p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ((!isset($existing_design['show_footer']) || (isset($existing_design['show_footer']) && $existing_design['show_footer'] == 1))): ?>
                                    <div class="ballot-footer">
                                        <p><i class="bi bi-info-circle me-1"></i>This is an official ballot. Please mark your choices clearly.</p>
                                        <small><i class="bi bi-upc-scan me-1"></i>Ballot ID: <?php echo uniqid('BALLOT_'); ?></small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="preview-controls no-print">
        <button class="btn btn-sm btn-outline-secondary" onclick="zoomOut()">
            <i class="bi bi-zoom-out"></i>
        </button>
        <button class="btn btn-sm btn-outline-secondary" onclick="zoomIn()">
            <i class="bi bi-zoom-in"></i>
        </button>
        <button class="btn btn-sm btn-outline-secondary" onclick="resetZoom()">
            <i class="bi bi-zoom-reset"></i>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside of it
                document.addEventListener('click', function(event) {
                    if (window.innerWidth < 992 && 
                        sidebar.classList.contains('show') &&
                        !sidebar.contains(event.target) && 
                        event.target !== sidebarToggle) {
                        sidebar.classList.remove('show');
                    }
                });
            }

            // Enhanced style preview selection
            document.querySelectorAll('.style-preview').forEach(preview => {
                preview.addEventListener('click', function() {
                    document.querySelectorAll('.style-preview').forEach(p => p.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('selected_style').value = this.dataset.style;
                    
                    // Add a brief animation when selecting
                    this.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                    
                    updateBallotPreview();
                });
            });

            // Enhanced color selection
            document.querySelectorAll('.color-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('header_color').value = this.dataset.color;
                    
                    // Add a brief animation when selecting
                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                    
                    updateBallotPreview();
                });
            });

            // Enhanced font selection
            document.querySelectorAll('.font-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.font-option').forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('font_family').value = this.dataset.font;
                    updateBallotPreview();
                });
            });

            // Enhanced logo position selection
            document.querySelectorAll('.logo-position-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.logo-position-option').forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('logo_position').value = this.dataset.position;
                    updateBallotPreview();
                });
            });

            // Form controls with visual feedback
            document.getElementById('showLogo').addEventListener('change', function() {
                highlightLabel(this);
                updateBallotPreview();
            });
            
            document.getElementById('showHeader').addEventListener('change', function() {
                highlightLabel(this);
                updateBallotPreview();
            });
            
            document.getElementById('showFooter').addEventListener('change', function() {
                highlightLabel(this);
                updateBallotPreview();
            });
            
            function highlightLabel(checkbox) {
                const label = checkbox.nextElementSibling;
                if (checkbox.checked) {
                    label.style.color = 'var(--primary-color)';
                    setTimeout(() => {
                        label.style.color = '';
                    }, 500);
                }
            }

            // Save design button with loading state
            document.getElementById('saveDesign').addEventListener('click', function() {
                this.innerHTML = '<i class="bi bi-arrow-repeat me-1 spinner-border spinner-border-sm"></i> Saving...';
                this.disabled = true;
                document.getElementById('ballotForm').submit();
            });

            // Election select change
            document.getElementById('electionSelect').addEventListener('change', function() {
                if (this.value) {
                    this.form.submit();
                }
            });

            function updateBallotPreview() {
                const style = document.getElementById('selected_style').value;
                const showLogo = document.getElementById('showLogo').checked;
                const showHeader = document.getElementById('showHeader').checked;
                const showFooter = document.getElementById('showFooter').checked;
                const headerColor = document.getElementById('header_color').value;
                const fontFamily = document.getElementById('font_family').value;
                const logoPosition = document.getElementById('logo_position').value;

                const preview = document.querySelector('.ballot-preview');
                
                // Add a subtle transition effect
                preview.style.transition = 'all 0.3s ease';
                preview.style.opacity = '0.7';
                
                setTimeout(() => {
                    // Remove all style classes
                    preview.classList.remove('modern', 'classic', 'minimal');
                    // Add selected style
                    preview.classList.add(style);

                    // Update visibility based on checkboxes
                    const logoImg = preview.querySelector('.ballot-header img');
                    if (logoImg) {
                        logoImg.style.display = showLogo ? 'block' : 'none';
                        logoImg.style.float = logoPosition;
                    }
                    
                    const header = preview.querySelector('.ballot-header');
                    if (header) {
                        header.style.display = showHeader ? 'block' : 'none';
                        header.style.borderBottomColor = headerColor;
                        header.style.color = headerColor;
                    }
                    
                    const footer = preview.querySelector('.ballot-footer');
                    if (footer) {
                        footer.style.display = showFooter ? 'block' : 'none';
                    }
                    
                    // Update header color in CSS variables
                    preview.style.setProperty('--header-color', headerColor);
                    
                    // Update font family
                    preview.style.fontFamily = fontFamily;
                    
                    // Update position titles and candidate items
                    document.querySelectorAll('.position-title').forEach(title => {
                        title.style.color = headerColor;
                    });
                    
                    // Restore opacity
                    preview.style.opacity = '1';
                }, 150);
            }

            // Initialize preview
            updateBallotPreview();
        });

        // Enhanced zoom functions
        function zoomIn() {
            const preview = document.querySelector('.ballot-preview');
            const current = parseFloat(getComputedStyle(preview).zoom) || 1;
            preview.style.transition = 'zoom 0.2s ease';
            preview.style.zoom = current + 0.1;
            
            // Show zoom level in a temporary toast
            showZoomLevel(Math.round((current + 0.1) * 100) + '%');
        }

        function zoomOut() {
            const preview = document.querySelector('.ballot-preview');
            const current = parseFloat(getComputedStyle(preview).zoom) || 1;
            if (current > 0.5) {
                preview.style.transition = 'zoom 0.2s ease';
                preview.style.zoom = current - 0.1;
                
                // Show zoom level in a temporary toast
                showZoomLevel(Math.round((current - 0.1) * 100) + '%');
            }
        }

        function resetZoom() {
            const preview = document.querySelector('.ballot-preview');
            preview.style.transition = 'zoom 0.2s ease';
            preview.style.zoom = 1;
            
            // Show zoom level in a temporary toast
            showZoomLevel('100%');
        }
        
        function showZoomLevel(level) {
            // Create or update zoom level indicator
            let zoomIndicator = document.getElementById('zoomIndicator');
            
            if (!zoomIndicator) {
                zoomIndicator = document.createElement('div');
                zoomIndicator.id = 'zoomIndicator';
                zoomIndicator.style.position = 'fixed';
                zoomIndicator.style.bottom = '80px';
                zoomIndicator.style.right = '20px';
                zoomIndicator.style.backgroundColor = 'rgba(0,0,0,0.7)';
                zoomIndicator.style.color = 'white';
                zoomIndicator.style.padding = '5px 10px';
                zoomIndicator.style.borderRadius = '4px';
                zoomIndicator.style.zIndex = '9999';
                zoomIndicator.style.fontSize = '12px';
                zoomIndicator.style.fontWeight = 'bold';
                zoomIndicator.style.transition = 'opacity 0.5s ease';
                document.body.appendChild(zoomIndicator);
            }
            
            zoomIndicator.textContent = level;
            zoomIndicator.style.opacity = '1';
            
            // Hide indicator after a delay
            clearTimeout(window.zoomTimeout);
            window.zoomTimeout = setTimeout(() => {
                zoomIndicator.style.opacity = '0';
            }, 1500);
        }
    </script>
</body>
</html>