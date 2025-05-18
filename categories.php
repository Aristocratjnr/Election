<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get all elections for dropdown with more details
$electionsQuery = $conn->prepare("
    SELECT e.electionID, e.name, e.status, 
           COUNT(c.categoryID) as category_count,
           e.startDate, e.endDate,
           e.start_time, e.end_time
    FROM elections e
    LEFT JOIN categories c ON e.electionID = c.electionID
    GROUP BY e.electionID
    ORDER BY 
        CASE e.status 
            WHEN 'Ongoing' THEN 1
            WHEN 'Scheduled' THEN 2
            WHEN 'Completed' THEN 3
            ELSE 4
        END,
        e.startDate DESC
");
$electionsQuery->execute();
$elections = $electionsQuery->get_result();

// Check if category_id parameter is set
$selectedCategoryID = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$selectedElectionID = null;

if ($selectedCategoryID) {
    $categoryQuery = $conn->prepare("SELECT electionID FROM categories WHERE categoryID = ?");
    $categoryQuery->bind_param("i", $selectedCategoryID);
    $categoryQuery->execute();
    $categoryResult = $categoryQuery->get_result();
    
    if ($categoryRow = $categoryResult->fetch_assoc()) {
        $selectedElectionID = $categoryRow['electionID'];
    }
}

// Get total categories count
$totalCategoriesQuery = $conn->prepare("SELECT COUNT(*) as total FROM categories");
$totalCategoriesQuery->execute();
$categoriesCount = $totalCategoriesQuery->get_result()->fetch_assoc()['total'];

$_SESSION['dashboard_stats']['total_active_categories'] = $categoriesCount;

$pageTitle = "Election Categories"; 
?>
<!doctype html>
<html lang="en" class="light-style">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Categories Management - Election System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-bg: #f8f9fa;
            --card-shadow: 0 0.15rem 1.75rem rgba(58, 59, 69, 0.15);
            --hover-shadow: 0 0.5rem 1.5rem rgba(58, 59, 69, 0.2);
            --transition-speed: 0.3s;
            --container-max-width: 1000px;
        }

        .main-content {
            max-width: var(--container-max-width);
            margin: 0 auto;
            padding-left: 70px;
        }

        /* Add these new gradient definitions */
        .bg-primary-gradient {
            background: linear-gradient(135deg, #0077b6 0%, #00b4d8 100%);
            color: white;
        }
        
        .bg-success-gradient {
            background: linear-gradient(135deg, #2a9d8f 0%, #40c9a2 100%);
            color: white;
        }
        
        .bg-info-gradient {
            background: linear-gradient(135deg, #48cae4 0%, #90e0ef 100%);
            color: white;
        }

        /* Enhanced card styling */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            background: white;
        }

      
        .stats-card {
            padding: 1.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            z-index: 1;
        }

        .card-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            font-size: 1.5rem;
            margin-right: 1rem;
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .card:hover .card-icon {
            transform: scale(1.1) rotate(10deg);
        }

        .stats-card h6 {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .stats-card h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0;
            position: relative;
            z-index: 2;
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] {
            --primary-bg: #2b3035;
            --card-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.3);
            --hover-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.4);
        }

        [data-bs-theme="dark"] .card {
            background: var(--surface);
        }

        [data-bs-theme="dark"] .stats-card h6 {
            color: rgba(255,255,255,0.7);
        }

        [data-bs-theme="dark"] .stats-card h3 {
            color: white;
        }

        [data-bs-theme="dark"] .bg-primary-gradient {
            background: linear-gradient(135deg, #0077b6 0%, #48cae4 100%);
        }

        [data-bs-theme="dark"] .bg-success-gradient {
            background: linear-gradient(135deg, #2a9d8f 0%, #52b788 100%);
        }

        [data-bs-theme="dark"] .bg-info-gradient {
            background: linear-gradient(135deg, #48cae4 0%, #00b4d8 100%);
        }

        /* Toast animations */
        @keyframes slideIn {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .toast {
            animation: slideIn 0.3s ease forwards;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .stats-card {
                padding: 1.25rem;
            }

            .card-icon {
                width: 45px;
                height: 45px;
                font-size: 1.25rem;
            }

            .stats-card h3 {
                font-size: 1.5rem;
            }

            .btn-floating {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
                bottom: 1.5rem;
                right: 1.5rem;
            }
        }

        /* Floating Action Button */
        .btn-floating {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .btn-floating:hover {
            transform: translateY(-5px);
            background: linear-gradient(135deg, #3a56d4 0%, #2e44c2 100%);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-floating i {
            transition: transform 0.3s ease;
        }

        .btn-floating:hover i {
            transform: rotate(45deg);
        }

        /* Enhanced Modal Styles */
        .modal-content {
            background-color: #ffffff;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white;
            padding: 1.5rem;
            border: none;
        }

        .modal-header .btn-close {
            color: white;
            opacity: 0.8;
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 2rem;
            background-color: #ffffff;
        }

        .modal-footer {
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .form-label {
            color: #374151;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            color: #374151;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] .modal-content {
            background-color: #2b3035;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .modal-body,
        [data-bs-theme="dark"] .modal-footer {
            background-color: #2b3035;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #343a40;
            border-color: #495057;
            color: #f8f9fa;
        }

        [data-bs-theme="dark"] .form-label {
            color: #f8f9fa;
        }

        @media (max-width: 768px) {
            .btn-floating {
                bottom: 1.5rem;
                right: 1.5rem;
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
        }

        /* DataTable and button styles */
        .table .btn-action {
            padding: 0.25rem 0.5rem;
            transition: all 0.2s ease;
        }

        .table .btn-action:hover {
            transform: translateY(-2px);
        }

        .table .btn-action i {
            font-size: 0.875rem;
        }

        .edit-category:hover {
            background-color: #17a2b8;
            color: white;
            border-color: #17a2b8;
        }

        .delete-category:hover {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        /* Loading animation */
        @keyframes rotating {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .rotating {
            animation: rotating 1s linear infinite;
        }

        /* Action button hover effects */
        .btn-action {
            position: relative;
            overflow: hidden;
        }

        .btn-action::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }

        .btn-action:active::after {
            width: 100px;
            height: 100px;
            opacity: 0;
        }
    </style>
</head>
<body>
    <!--include header -->
    <?php include 'includes/header.php'; ?><br><br>
    <div class="container-fluid px-2">
        
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header mb-1 py-5">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="page-title">
                                <i class="bi bi-bookmark-star me-2"></i>Categories Management
                            </h3>
                            <p class="text-muted">Manage election categories and positions</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row mb-4">
                    <!-- Total Categories Card -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-primary-gradient">
                                        <i class="bi bi-bookmarks-fill"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Total Categories</h6>
                                        <h3 class="mb-0 fw-bold" id="totalCategoriesCount"><?php echo $categoriesCount; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Elections Card -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-success-gradient">
                                        <i class="bi bi-calendar2-check-fill"></i>
                                    </div>
                                    <div>
                                        <?php
                                            $activeElectionsQuery = $conn->query("SELECT COUNT(*) as count FROM elections WHERE status = 'Ongoing'");
                                            $activeElections = $activeElectionsQuery->fetch_assoc()['count'];
                                        ?>
                                        <h6 class="text-muted mb-1">Active Elections</h6>
                                        <h3 class="mb-0 fw-bold"><?php echo $activeElections; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories in Use Card -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-info-gradient">
                                        <i class="bi bi-check2-circle"></i>
                                    </div>
                                    <div>
                                        <?php
                                            // Update the categories in use query to count properly using election matches 
                                            $categoriesInUseQuery = $conn->query("
                                                SELECT COUNT(DISTINCT c.categoryID) as count 
                                                FROM categories c 
                                                INNER JOIN positions p ON c.electionID = p.electionID
                                            ");
                                            $categoriesInUse = $categoriesInUseQuery->fetch_assoc()['count'];
                                        ?>
                                        <h6 class="text-muted mb-1">Categories In Use</h6>
                                        <h3 class="mb-0 fw-bold"><?php echo $categoriesInUse; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card mb-4 border-0 shadow-sm filter-section">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="bi bi-funnel-fill text-primary me-2"></i>
                            Filter Categories
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label"><i class="bi bi-calendar-event me-1"></i>Election</label>
                                <select name="election" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Elections</option>
                                    <?php
                                    $elections_query = "SELECT * FROM elections ORDER BY startDate DESC";
                                    $elections_result = $conn->query($elections_query);
                                    while($election = $elections_result->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $election['electionID']; ?>" <?php echo (isset($_GET['election']) && $_GET['election'] == $election['electionID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($election['name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label"><i class="bi bi-sort-alpha-down me-1"></i>Sort By</label>
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="name" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'name') ? 'selected' : ''; ?>>Category Name</option>
                                    <option value="date" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date') ? 'selected' : ''; ?>>Date Added</option>
                                    <option value="candidates" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'candidates') ? 'selected' : ''; ?>>Number of Candidates</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label"><i class="bi bi-search me-1"></i>Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" id="searchCategories" class="form-control" placeholder="Search categories..." 
                                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="categoriesTable">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Election</th>
                                        <th>Candidates</th>
                                        <th>Added By</th>
                                        <th>Date Added</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Build the query based on filters
                                    $query = "SELECT c.*, e.name as election_name, 
                                             (SELECT COUNT(*) FROM candidates WHERE categoryID = c.categoryID) as candidate_count,
                                             a.name as added_by_name, c.createdAt 
                                             FROM categories c 
                                             LEFT JOIN elections e ON c.electionID = e.electionID 
                                             LEFT JOIN students a ON c.addedBy = a.studentID
                                             WHERE 1=1";
                                    
                                    if(isset($_GET['election']) && !empty($_GET['election'])) {
                                        $query .= " AND c.electionID = " . intval($_GET['election']);
                                    }
                                    
                                    if(isset($_GET['search']) && !empty($_GET['search'])) {
                                        $search = $conn->real_escape_string($_GET['search']);
                                        $query .= " AND (c.name LIKE '%$search%' OR e.name LIKE '%$search%')";
                                    }
                                    
                                    // Add sorting
                                    $query .= match($_GET['sort'] ?? 'name') {
                                        'date' => " ORDER BY c.createdAt DESC",
                                        'candidates' => " ORDER BY candidate_count DESC",
                                        default => " ORDER BY c.name ASC"
                                    };
                                    
                                    $result = $conn->query($query);
                                    while($category = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="category-icon bg-primary-light me-3">
                                                    <i class="bi bi-tag-fill"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($category['election_name']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $category['candidate_count']; ?></span></td>
                                        <td><?php echo htmlspecialchars($category['added_by_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($category['createdAt'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-category" 
                                                    data-id="<?php echo $category['categoryID']; ?>" 
                                                    data-election-id="<?php echo $category['electionID']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($category['name']); ?>" 
                                                   >
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-category" 
                                                    data-id="<?php echo $category['categoryID']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($category['name']); ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5 d-none">
                    <div class="card">
                        <div class="card-body py-5">
                            <i class="bi bi-bookmark-x text-muted display-4 mb-3"></i>
                            <h4 class="mb-2">No Categories Found</h4>
                            <p class="text-muted mb-4">Start by creating a new category for your election.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-circle me-2"></i>Create Category
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Action Button -->
            <button class="btn btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Add New Category">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">
                        <i class="bi bi-bookmark-plus-fill me-2"></i>Add New Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCategoryForm">
                    <div class="modal-body">
                        <div class="mb-4">
                            <label for="electionID" class="form-label">
                                <i class="bi bi-calendar-event me-2"></i>Select Election
                            </label>
                            <select class="form-select" id="electionID" required>
                                <option value="" selected disabled>Choose an election...</option>
                                <?php 
                                $electionsQuery->execute();
                                $elections = $electionsQuery->get_result();
                                while ($election = $elections->fetch_assoc()): 
                                ?>
                                    <option value="<?= $election['electionID'] ?>">
                                        <?= htmlspecialchars($election['name']) ?>
                                        <?php if($election['status']): ?>
                                            <span class="badge bg-<?= $election['status'] == 'Ongoing' ? 'success' : ($election['status'] == 'Completed' ? 'secondary' : 'info') ?>">
                                                <?= $election['status'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="invalid-feedback">Please select an election</div>
                        </div>
                        <div class="mb-4">
                            <label for="categoryName" class="form-label">
                                <i class="bi bi-tag-fill me-2"></i>Category Name
                            </label>
                            <input type="text" class="form-control" id="categoryName" required 
                                   placeholder="Enter category name">
                            <div class="invalid-feedback">Please enter a category name</div>
                        </div>
                        <div class="mb-4">
                            <label for="categoryDescription" class="form-label">
                                <i class="bi bi-info-circle me-2"></i>Category Description
                            </label>
                            <textarea class="form-control" id="categoryDescription" rows="3" placeholder="Enter category description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editCategoryForm">
                    <input type="hidden" id="editCategoryId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-calendar-event me-2"></i>Election</label>
                            <select class="form-select" id="editElectionID" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-tag me-2"></i>Category Name</label>
                            <input type="text" class="form-control" id="editCategoryName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-info-circle me-2"></i>Category Description</label>
                            <textarea class="form-control" id="editCategoryDescription" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="bi bi-check-circle me-2"></i>Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-question-circle text-danger display-4 mb-3"></i>
                    <p class="mb-1">Are you sure you want to delete this category?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="bi bi-trash me-2"></i>Delete Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    
    <script>
    // Global variable to store DataTable instance
    let categoriesTable;
    
    $(document).ready(function() {
        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        // Initialize DataTable with enhanced features
        categoriesTable = $('#categoriesTable').DataTable({
            pageLength: 10,
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
            language: {
                search: "",
                searchPlaceholder: "Search categories...",
                emptyTable: "",
                info: "Showing _START_ to _END_ of _TOTAL_ categories",
                infoEmpty: "No categories found",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>'
                }
            },
            ordering: true,
            responsive: true,
            stateSave: true,
            columnDefs: [
                { orderable: false, targets: 4 },
                { className: "align-middle", targets: "_all" }
            ]
        });
        
        // Add keyboard shortcuts
        $(document).keydown(function(e) {
            // Alt + N to add new category
            if (e.altKey && e.keyCode === 78) {
                e.preventDefault();
                $('#addCategoryModal').modal('show');
            }
            // Alt + R to refresh
            if (e.altKey && e.keyCode === 82) {
                e.preventDefault();
                $('#refreshCategories').click();
            }
        });
        
        // Add drag and drop reordering for categories
        new Sortable(document.getElementById('categoriesTableBody'), {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function(evt) {
                updateCategoryOrder();
            }
        });
        
        // Enhanced form validation with better feedback
        $('#addCategoryForm, #editCategoryForm').each(function() {
            $(this).validate({
                rules: {
                    categoryName: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    electionID: "required"
                },
                messages: {
                    categoryName: {
                        required: "Please enter a category name",
                        minlength: "Category name must be at least 3 characters",
                        maxlength: "Category name cannot exceed 100 characters"
                    },
                    electionID: "Please select an election"
                },
                errorElement: 'div',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.input-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
        
        // Add export functionality
        $('#exportCategories').click(function() {
            const electionId = $('#electionSelect').val();
            const format = $('#exportFormat').val();
            window.location.href = `export_categories.php?electionID=${electionId}&format=${format}`;
        });
        
        // Check if a specific category is requested
        const urlParams = new URLSearchParams(window.location.search);
        const categoryId = urlParams.get('category_id');
        const electionId = <?php echo $selectedElectionID ? "'" . $selectedElectionID . "'" : 'null'; ?>;
        
        // If we have a selected election ID from a category_id parameter, use that
        if (electionId) {
            $('#electionSelect').val(electionId);
            loadCategories(electionId, categoryId);
        } else {
            // Otherwise load all categories initially
            loadCategories(null, categoryId);
        }
        
        // Election filter change event
        $('#electionSelect').change(function() {
            loadCategories($(this).val());
        });
        
        // Refresh button click event
        $('#refreshCategories').click(function() {
            $(this).find('i').addClass('rotating');
            loadCategories($('#electionSelect').val());
            setTimeout(() => {
                $(this).find('i').removeClass('rotating');
            }, 1000);
        });
        
        // Add category form submit
        $('#addCategoryForm').submit(function(e) {
            e.preventDefault();
            
            const electionID = $('#electionID').val();
            const categoryName = $('#categoryName').val().trim();
            const description = $('#categoryDescription').val().trim();
            
            // Form validation
            if (!electionID || !categoryName) {
                if (!electionID) $('#electionID').addClass('is-invalid');
                if (!categoryName) $('#categoryName').addClass('is-invalid');
                showToast('Warning', 'Please fill all required fields', 'warning');
                return;
            }
            
            // Show loading state
            const saveBtn = $('#addCategoryForm button[type="submit"]');
            const originalText = saveBtn.html();
            saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            saveBtn.prop('disabled', true);
            
            // AJAX request to add category
            $.ajax({
                url: 'api/save_category.php',
                type: 'POST',
                data: {
                    electionID: electionID,
                    name: categoryName,
                    description: description
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Show success toast
                            showToast('Success', 'Category added successfully!', 'success');
                            
                            // Reset form and close modal
                            $('#addCategoryForm')[0].reset();
                            $('#addCategoryModal').modal('hide');
                            
                            // Reload categories
                            loadCategories($('#electionSelect').val());
                            
                            // Update dashboard stats
                            updateDashboardCategoryStats();
                        } else {
                            showToast('Error', result.message || 'Failed to add category', 'danger');
                        }
                    } catch (e) {
                        showToast('Error', 'Invalid server response', 'danger');
                    }
                },
                error: function() {
                    showToast('Error', 'Server error occurred', 'danger');
                },
                complete: function() {
                    // Restore button state
                    saveBtn.html(originalText);
                    saveBtn.prop('disabled', false);
                }
            });
        });
        
        // Reset validation state on input
        $('#addCategoryForm input, #addCategoryForm select').on('input change', function() {
            $(this).removeClass('is-invalid');
        });

        // Handle edit button click
        $(document).on('click', '.edit-category', function() {
            const categoryId = $(this).data('id');
            const electionId = $(this).data('election-id');
            const categoryName = $(this).data('name');
            const description = $(this).data('description') || '';
            
            // Populate edit form
            $('#editCategoryId').val(categoryId);
            $('#editCategoryName').val(categoryName);
            $('#editCategoryDescription').val(description);
            
            // Clone options from the add category form's election dropdown
            const electionOptions = $('#electionID').html();
            $('#editElectionID').html(electionOptions);
            $('#editElectionID').val(electionId);
            
            // Show edit modal
            $('#editCategoryModal').modal('show');
        });
        
        // Edit category form submit
        $('#editCategoryForm').submit(function(e) {
            e.preventDefault();
            
            const categoryId = $('#editCategoryId').val();
            const electionId = $('#editElectionID').val();
            const categoryName = $('#editCategoryName').val().trim();
            const description = $('#editCategoryDescription').val().trim();
            
            // Form validation
            if (!electionId || !categoryName) {
                if (!electionId) $('#editElectionID').addClass('is-invalid');
                if (!categoryName) $('#editCategoryName').addClass('is-invalid');
                showToast('Warning', 'Please fill all required fields', 'warning');
                return;
            }
            
            // Show loading state
            const updateBtn = $('#editCategoryForm button[type="submit"]');
            const originalText = updateBtn.html();
            updateBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
            updateBtn.prop('disabled', true);
            
            // AJAX request to update category
            $.ajax({
                url: 'api/update_category.php',
                type: 'POST',
                data: {
                    categoryID: categoryId,
                    electionID: electionId,
                    name: categoryName,
                    description: description
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Category updated successfully', 'success');
                        $('#editCategoryModal').modal('hide');
                        location.reload(); // Reload to show updated data
                    } else {
                        showToast('Error', response.message || 'Failed to update category', 'danger');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Server error occurred';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        console.error('Error parsing response:', xhr.responseText);
                    }
                    showToast('Error', errorMessage, 'danger');
                },
                complete: function() {
                    updateBtn.html(originalText);
                    updateBtn.prop('disabled', false);
                }
            });
        });
        
        // Handle delete button click
        $(document).on('click', '.delete-category', function() {
            const categoryId = $(this).data('id');
            const categoryName = $(this).data('name');
            
            $('#deleteCategoryModal').modal('show');
            $('#confirmDelete').data('id', categoryId);
        });
        
        // Confirm delete action
        $('#confirmDelete').click(function() {
            const categoryId = $(this).data('id');
            
            const deleteBtn = $(this);
            const originalText = deleteBtn.html();
            deleteBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
            deleteBtn.prop('disabled', true);
            
            $.ajax({
                url: 'api/delete_category.php',
                type: 'POST',
                data: { categoryID: categoryId },
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Category deleted successfully', 'success');
                        $('#deleteCategoryModal').modal('hide');
                        location.reload(); // Reload to show updated data
                    } else {
                        showToast('Error', response.message || 'Failed to delete category', 'danger');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Server error occurred';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        console.error('Error parsing response:', xhr.responseText);
                    }
                    showToast('Error', errorMessage, 'danger');
                },
                complete: function() {
                    deleteBtn.html(originalText);
                    deleteBtn.prop('disabled', false);
                }
            });
        });
        
        // Reset form validation on input
        $('input, select').on('input change', function() {
            $(this).removeClass('is-invalid');
        });

        // Visual feedback on form submission
        $('#addCategoryForm, #editCategoryForm').on('submit', function() {
            $(this).find('button[type="submit"]').prepend('<span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true"></span>');
        });
    });
    
    // Function to load categories
    function loadCategories(electionId = '', selectedCategoryId = null) {
        $('#loadingState').removeClass('d-none');
        $('#categoriesTable, #emptyState').addClass('d-none');
        
        $.ajax({
            url: 'api/get_categories.php',
            type: 'GET',
            data: electionId ? { electionID: electionId } : {},
            success: function(response) {
                if (response.success) {
                    updateCategoriesTable(response.categories, selectedCategoryId);
                    updateStats(response.total);
                } else {
                    showError('Failed to load categories');
                }
            },
            error: function(xhr) {
                showError('Server error occurred');
                console.error('AJAX error:', xhr.responseText);
            },
            complete: function() {
                $('#loadingState').addClass('d-none');
            }
        });
    }
    
    // Add category order update function
    function updateCategoryOrder() {
        const categoryOrder = [];
        $('#categoriesTableBody tr').each(function(index) {
            categoryOrder.push({
                categoryID: $(this).data('category-id'),
                position: index + 1
            });
        });

        $.ajax({
            url: 'api/update_category_order.php',
            type: 'POST',
            data: { categories: categoryOrder },
            success: function(response) {
                if (response.success) {
                    showToast('Success', 'Category order updated successfully', 'success');
                }
            }
        });
    }
    
    // Enhanced error handling
    function showError(message) {
        showToast('Error', message, 'danger');
        console.error(message);
    }
    </script>
</body>
</html>