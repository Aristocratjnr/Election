<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get all elections for dropdown with more details
$electionsQuery = $conn->prepare("SELECT electionID, name, status FROM elections");
$electionsQuery->execute();
$elections = $electionsQuery->get_result();

// Check if category_id parameter is set from election_details.php
$selectedCategoryID = null;
$selectedElectionID = null;

if (isset($_GET['category_id'])) {
    $selectedCategoryID = $_GET['category_id'];
    
    // Get the election ID for this category to pre-select in the dropdown
    $categoryQuery = $conn->prepare("SELECT electionID FROM categories WHERE categoryID = ?");
    $categoryQuery->bind_param("i", $selectedCategoryID);
    $categoryQuery->execute();
    $categoryResult = $categoryQuery->get_result();
    
    if ($categoryRow = $categoryResult->fetch_assoc()) {
        $selectedElectionID = $categoryRow['electionID'];
    }
}

// Total categories count for dashboard
$totalCategoriesQuery = $conn->prepare("SELECT COUNT(*) as total FROM categories");
$totalCategoriesQuery->execute();
$categoriesCount = $totalCategoriesQuery->get_result()->fetch_assoc()['total'];

// Update session data for dashboard
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
            padding: 2rem 1rem;
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

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>
    <div class="container-fluid px-0">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header mb-4">
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
                                    <input type="text" name="search" class="form-control" placeholder="Search categories..." 
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
                            <table class="table table-hover align-middle">
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
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo $category['categoryID']; ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $category['categoryID']; ?>)">
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
    
    <script>
    // Global variable to store DataTable instance
    let categoriesTable;
    
    $(document).ready(function() {
        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        // Initialize DataTable
        categoriesTable = $('#categoriesTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthChange: false,
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search categories...",
                emptyTable: ""
            },
            columnDefs: [
                { orderable: false, targets: 4 }, // Disable sorting on Actions column
                { className: "align-middle", targets: "_all" } // Center align all cells vertically
            ],
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
            initComplete: function() {
                // Hide the default search box
                $('.dataTables_filter').hide();
                
                // Use custom search box
                $('#searchCategories').on('keyup', function() {
                    categoriesTable.search(this.value).draw();
                });
            }
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
            const categoryName = $('#categoryName').val();
            
            // Form validation
            if (!electionID || !categoryName) {
                if (!electionID) $('#electionID').addClass('is-invalid');
                if (!categoryName) $('#categoryName').addClass('is-invalid');
                showToast('Warning', 'Please fill all required fields', 'warning');
                return;
            }
            
            // Log submission data
            console.log('Submitting category:', {
                electionID: electionID,
                name: categoryName
            });
            
            // Show loading state
            const saveBtn = $('#saveCategory');
            const originalText = saveBtn.html();
            saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            saveBtn.prop('disabled', true);
            
            // AJAX request to add category
            $.ajax({
                url: 'api/save_category.php',
                type: 'POST',
                data: {
                    electionID: electionID,
                    name: categoryName
                },
                success: function(response) {
                    console.log('Save response:', response);
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Show success toast
                            showToast('Success', 'Category added successfully!', 'success');
                            
                            // Reset form and close modal
                            $('#addCategoryForm')[0].reset();
                            $('#addCategoryModal').modal('hide');
                            
                            // Show success animation on refresh button
                            $('#refreshCategories').find('i').addClass('rotating');
                            
                            // Reload categories
                            loadCategories($('#electionSelect').val());
                            
                            // Stop animation after a delay
                            setTimeout(() => {
                                $('#refreshCategories').find('i').removeClass('rotating');
                            }, 1000);
                            
                            // Update dashboard stats
                            updateDashboardCategoryStats();
                        } else {
                            showToast('Error', result.message || 'Failed to add category', 'danger');
                        }
                    } catch (e) {
                        showToast('Error', 'Invalid server response', 'danger');
                        console.error('Error parsing response:', e);
                    }
                    
                    // Restore button state
                    saveBtn.html(originalText);
                    saveBtn.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    showToast('Error', 'Server error occurred', 'danger');
                    console.error('AJAX error:', error, xhr.responseText);
                    
                    // Restore button state
                    saveBtn.html(originalText);
                    saveBtn.prop('disabled', false);
                }
            });
        });
        
        // Handle edit button click
        $(document).on('click', '.edit-category', function() {
            const categoryId = $(this).data('id');
            const electionId = $(this).data('election-id');
            const categoryName = $(this).data('name');
            
            // Populate edit form
            $('#editCategoryId').val(categoryId);
            $('#editElectionID').val(electionId);
            $('#editCategoryName').val(categoryName);
            
            // Show edit modal with a small animation
            $(this).addClass('rotating');
            setTimeout(() => {
                $(this).removeClass('rotating');
                $('#editCategoryModal').modal('show');
            }, 300);
        });
        
        // Edit category form submit
        $('#editCategoryForm').submit(function(e) {
            e.preventDefault();
            
            const categoryID = $('#editCategoryId').val();
            const electionID = $('#editElectionID').val();
            const categoryName = $('#editCategoryName').val();
            
            // Form validation
            if (!electionID || !categoryName) {
                if (!electionID) $('#editElectionID').addClass('is-invalid');
                if (!categoryName) $('#editCategoryName').addClass('is-invalid');
                return;
            }
            
            // AJAX request to update category
            $.ajax({
                url: 'api/update_category.php',
                type: 'POST',
                data: {
                    categoryID: categoryID,
                    electionID: electionID,
                    name: categoryName
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Show success toast
                            showToast('Success', 'Category updated successfully!', 'success');
                            
                            // Close modal
                            $('#editCategoryModal').modal('hide');
                            
                            // Reload categories
                            loadCategories($('#electionSelect').val());
                        } else {
                            showToast('Error', result.message || 'Failed to update category', 'danger');
                        }
                    } catch (e) {
                        showToast('Error', 'Invalid server response', 'danger');
                    }
                },
                error: function() {
                    showToast('Error', 'Server error occurred', 'danger');
                }
            });
        });
        
        // Handle delete button click
        $(document).on('click', '.delete-category', function() {
            const categoryId = $(this).data('id');
            const categoryName = $(this).data('name');
            
            // Set values in delete confirmation modal
            $('#deleteCategoryName').text(categoryName);
            $('#confirmDelete').data('category-id', categoryId);
            
            // Show delete confirmation modal
            $('#deleteCategoryModal').modal('show');
        });
        
        // Confirm delete button click
        $('#confirmDelete').click(function() {
            const categoryId = $(this).data('category-id');
            
            // AJAX request to delete category
            $.ajax({
                url: 'api/delete_category.php',
                type: 'POST',
                data: { categoryID: categoryId },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Show success toast
                            showToast('Success', 'Category deleted successfully!', 'success');
                            
                            // Close modal
                            $('#deleteCategoryModal').modal('hide');
                            
                            // Reload categories
                            loadCategories($('#electionSelect').val());
                            
                            // Update dashboard stats
                            updateDashboardCategoryStats();
                        } else {
                            showToast('Error', result.message || 'Failed to delete category', 'danger');
                        }
                    } catch (e) {
                        showToast('Error', 'Invalid server response', 'danger');
                    }
                },
                error: function() {
                    showToast('Error', 'Server error occurred', 'danger');
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
        // Show loading state
        $('#categoriesTable').addClass('d-none');
        $('#emptyState').addClass('d-none');
        $('#loadingState').removeClass('d-none');
        
        $.ajax({
            url: 'api/get_categories.php',
            type: 'GET',
            data: electionId ? { electionID: electionId } : {},
            success: function(response) {
                try {
                    const categories = JSON.parse(response);
                    
                    // Clear the existing table data
                    categoriesTable.clear();
                    
                    if (categories.length > 0) {
                        // Hide empty state and show table
                        $('#emptyState').addClass('d-none');
                        $('#loadingState').addClass('d-none');
                        $('#categoriesTable').removeClass('d-none');
                        
                        // Update category count
                        $('#categoryCount').text(categories.length);
                        $('#categoryCount').removeClass('bg-secondary').addClass('bg-primary');
                        
                        // Add each category to the table
                        categories.forEach(function(category, index) {
                            // Prepare status badge for election
                            let statusBadge = '';
                            if (category.election_status) {
                                let statusClass = 'secondary';
                                if (category.election_status === 'Ongoing') statusClass = 'success';
                                else if (category.election_status === 'Completed') statusClass = 'primary';
                                else if (category.election_status === 'Scheduled') statusClass = 'info';
                                
                                statusBadge = `<span class="badge bg-${statusClass} ms-2">${category.election_status}</span>`;
                            }
                            
                            // Format the category name with description tooltip if available
                            let categoryName = category.category_name || category.name;
                            let rowClass = '';
                            
                            // Highlight the selected category if it matches the requested one
                            if (selectedCategoryId && category.categoryID == selectedCategoryId) {
                                rowClass = 'bg-light-success';
                            }
                            
                            if (category.description) {
                                categoryName = `<div class="d-flex align-items-center">
                                    <span>${categoryName}</span>
                                    <i class="bi bi-info-circle-fill ms-2 text-muted" 
                                       data-bs-toggle="tooltip" 
                                       title="${category.description}"></i>
                                </div>`;
                            }
                            
                            // Format the election name with status badge
                            let electionName = `
                                <div>
                                    <span>${category.election_name || 'N/A'}</span>
                                    ${statusBadge}
                                </div>
                            `;
                            
                            // Meta information about category creation/updates
                            let metaInfo = 'N/A';
                            if (category.added_by_name) {
                                metaInfo = `<div class="small text-muted">
                                    <i class="bi bi-person-plus me-1"></i> ${category.added_by_name}
                                </div>`;
                            }
                            
                            categoriesTable.row.add([
                                index + 1,
                                categoryName,
                                electionName,
                                `<div>
                                    <span class="text-muted"><i class="bi bi-clock me-1"></i>${formatDate(category.created_at || 'N/A')}</span>
                                    ${metaInfo}
                                </div>`,
                                `<div class="text-end">
                                    <button class="btn btn-sm btn-outline-info btn-action edit-category me-1" 
                                        data-id="${category.categoryID}" 
                                        data-election-id="${category.electionID}"
                                        data-name="${categoryName}"
                                        data-bs-toggle="tooltip"
                                        title="Edit this category">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-action delete-category" 
                                        data-id="${category.categoryID}" 
                                        data-name="${categoryName}"
                                        data-bs-toggle="tooltip"
                                        title="Delete this category">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>`
                            ]);
                        });
                    } else {
                        // Show empty state with context-aware message
                        $('#emptyState').removeClass('d-none');
                        $('#loadingState').addClass('d-none');
                        $('#categoriesTable').addClass('d-none');
                        
                        // Update category count
                        $('#categoryCount').text(0);
                        $('#categoryCount').removeClass('bg-primary').addClass('bg-secondary');
                        
                        // Update empty state message based on filter
                        if (electionId) {
                            const electionName = $('#electionSelect option:selected').text();
                            $('#emptyStateTitle').text('No Categories for this Election');
                            $('#emptyStateMessage').html(`No categories found for <strong>${electionName}</strong>. Create your first category now.`);
                        } else {
                            $('#emptyStateTitle').text('No Categories Found');
                            $('#emptyStateMessage').text('Start by creating a new category for any election.');
                        }
                    }
                    
                    // Redraw the table with new data
                    categoriesTable.draw();
                    
                    // If a specific category was requested, highlight and scroll to it
                    if (selectedCategoryId) {
                        setTimeout(() => {
                            const $rows = $('#categoriesTableBody tr');
                            $rows.each(function() {
                                const $row = $(this);
                                const rowData = $row.find('button.edit-category').data('id');
                                
                                if (rowData == selectedCategoryId) {
                                    $row.addClass('bg-light-success');
                                    // Scroll the row into view
                                    $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    
                                    // Show a toast notification
                                    const categoryName = $row.find('td:nth-child(2)').text().trim();
                                    showToast('Category Selected', `Viewing details for category: ${categoryName}`, 'info');
                                }
                            });
                        }, 300);
                    }
                    
                    // Re-initialize tooltips after table is redrawn
                    setTimeout(() => {
                        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
                    }, 100);
                } catch (e) {
                    console.error('Error parsing response:', e);
                    showToast('Error', 'Failed to load categories', 'danger');
                }
            },
            error: function() {
                showToast('Error', 'Server error occurred', 'danger');
            }
        });
    }
    
    // Function to update dashboard category stats
    function updateDashboardCategoryStats() {
        // Make an AJAX request to update stats
        $.ajax({
            url: 'api/update_dashboard_stats.php',
            type: 'POST',
            data: { update_type: 'categories' },
            success: function(response) {
                console.log('Dashboard stats updated');
            }
        });
    }
    
    // Function to format date
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Function to show toast notifications
    function showToast(title, message, type = 'info') {
        const toastId = 'toast-' + Date.now();
        let iconClass = 'info-circle-fill';
        
        if (type === 'success') iconClass = 'check-circle-fill';
        else if (type === 'danger') iconClass = 'exclamation-triangle-fill';
        else if (type === 'warning') iconClass = 'exclamation-circle-fill';
        
        const html = `
            <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type} text-white">
                    <i class="bi bi-${iconClass} me-2"></i>
                    <strong class="me-auto">${title}</strong>
                    <small><i class="bi bi-clock me-1"></i>Just now</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-lg' : type === 'danger' ? 'x-lg' : 'info-lg'} me-2"></i>
                    ${message}
                </div>
            </div>
        `;
        
        $('.toast-container').append(html);
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
        
        toast.show();
        
        // Remove the toast from DOM after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
    </script>
</body>
</html>