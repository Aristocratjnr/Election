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

// Total categories count for dashboard
$totalCategoriesQuery = $conn->prepare("SELECT COUNT(*) as total FROM categories");
$totalCategoriesQuery->execute();
$categoriesCount = $totalCategoriesQuery->get_result()->fetch_assoc()['total'];

// Update session data for dashboard
$_SESSION['dashboard_stats']['total_active_categories'] = $categoriesCount;

$pageTitle = "Election Categories"; // Used in header.php
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Categories Management - SmartVote</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    
    <style>
        .page-title-box {
            background-color: #fff;
            padding: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .election-selector {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }

        .category-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
            border-radius: 1rem;
        }

        .category-item {
            transition: all 0.2s ease;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .category-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .btn-action {
            border-radius: 50%;
            width: 2.2rem;
            height: 2.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }

        /* Toast styling */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1060;
        }
        
        /* Rotating animation for refresh icon */
        @keyframes rotating {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .rotating {
            animation: rotating 1s linear infinite;
        }

        /* Enhanced UI Elements */
        .table th {
            font-weight: 600;
            color: #495057;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        
        .btn {
            font-weight: 500;
            letter-spacing: 0.3px;
            padding: 0.5rem 1.2rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
        }
        
        .card-header {
            background-color: rgba(0, 0, 0, 0.025);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        
        .search-box {
            max-width: 250px;
            transition: max-width 0.3s ease;
        }
        
        .search-box:focus-within {
            max-width: 300px;
        }
        
        .modal-header {
            align-items: center;
            padding: 1rem 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
        }
        
        /* Pulse animation for icons */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .pulse-icon {
            animation: pulse 2s infinite;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <div class="main-content">
                <!-- Include Header -->
                <?php include 'includes/header.php'; ?>
                
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-4"><br>
                    <!-- Page Header -->
                    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="h2"><i class="bi bi-bookmark-fill me-2 text-primary"></i>Categories Management</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                                    <li class="breadcrumb-item active"><i class="bi bi-bookmark me-1"></i>Categories</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-circle-fill me-1 pulse-icon"></i> Add New Category
                            </button>
                        </div>
                    </div>

                    <!-- Election Selector -->
                    <div class="card">
                        <div class="card-body election-selector">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="electionSelect" class="form-label fw-bold mb-0">
                                        <i class="bi bi-funnel-fill me-1 text-primary"></i> Select Election:
                                    </label>
                                </div>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-calendar2-event text-primary"></i>
                                        </span>
                                        <select class="form-select" id="electionSelect">
                                            <option value=""><i class="bi bi-collection"></i> All Elections</option>
                                            <?php while ($election = $elections->fetch_assoc()): 
                                                $statusBadge = "";
                                                if (isset($election['status'])) {
                                                    $statusColor = "secondary";
                                                    if ($election['status'] == 'Ongoing') $statusColor = "success";
                                                    elseif ($election['status'] == 'Completed') $statusColor = "primary";
                                                    elseif ($election['status'] == 'Scheduled') $statusColor = "info";
                                                    $statusBadge = " <span class='badge bg-".$statusColor." rounded-pill'>".$election['status']."</span>";
                                                }
                                            ?>
                                                <option value="<?= $election['electionID'] ?>">
                                                    <?= htmlspecialchars($election['name']) ?><?= $statusBadge ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Display -->
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-check me-2 text-primary"></i> 
                                Categories
                                <span class="badge bg-secondary category-count ms-2" id="categoryCount">0</span>
                            </h5>
                            <div class="d-flex gap-2">
                                <button id="refreshCategories" class="btn btn-outline-primary btn-sm shadow-sm">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                                <div class="input-group search-box shadow-sm">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="bi bi-search text-primary"></i>
                                    </span>
                                    <input type="text" id="searchCategories" class="form-control border-0 bg-light" placeholder="Search categories...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="categoriesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="60"><i class="bi bi-hash me-1 text-primary"></i>#</th>
                                            <th><i class="bi bi-bookmark me-1 text-primary"></i>Category Name</th>
                                            <th><i class="bi bi-calendar-event me-1 text-primary"></i>Election</th>
                                            <th><i class="bi bi-clock-history me-1 text-primary"></i>Created</th>
                                            <th class="text-end"><i class="bi bi-gear-fill me-1 text-primary"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoriesTableBody">
                                        <!-- Categories will be loaded here via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                            <!-- Empty state for when no categories exist -->
                            <div id="emptyState" class="empty-state d-none">
                                <i class="bi bi-bookmark-x-fill text-muted pulse-icon"></i>
                                <h5 id="emptyStateTitle" class="mt-3">No Categories Found</h5>
                                <p class="text-muted" id="emptyStateMessage">Start by creating a new category or select a different election.</p>
                                <button class="btn btn-primary mt-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Create Category
                                </button>
                            </div>
                            
                            <!-- Loading state -->
                            <div id="loadingState" class="empty-state">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted"><i class="bi bi-hourglass-split me-1"></i> Loading categories...</p>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addCategoryModalLabel">
                        <i class="bi bi-bookmark-plus-fill me-2"></i> Add New Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCategoryForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="electionID" class="form-label">
                                <i class="bi bi-calendar2-event me-1"></i> Election
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-calendar-check text-primary"></i>
                                </span>
                                <select class="form-select" id="electionID" name="electionID" required>
                                    <option value="" selected disabled>Select Election</option>
                                    <?php 
                                    // Reset the elections result pointer
                                    $electionsQuery->execute();
                                    $elections = $electionsQuery->get_result();
                                    while ($election = $elections->fetch_assoc()): 
                                    ?>
                                    <option value="<?= $election['electionID'] ?>">
                                        <?= htmlspecialchars($election['name']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">Please select an election.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">
                                <i class="bi bi-bookmark me-1"></i> Category Name
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-tag-fill text-primary"></i>
                                </span>
                                <input type="text" class="form-control" id="categoryName" name="categoryName" placeholder="Enter category name" required>
                                <div class="invalid-feedback">Please enter a category name.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle-fill me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary shadow-sm" id="saveCategory">
                            <i class="bi bi-save-fill me-1"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editCategoryModalLabel">
                        <i class="bi bi-pencil-square me-2"></i> Edit Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCategoryForm">
                    <input type="hidden" id="editCategoryId" name="categoryID">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editElectionID" class="form-label">
                                <i class="bi bi-calendar2-event me-1"></i> Election
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-calendar-check text-info"></i>
                                </span>
                                <select class="form-select" id="editElectionID" name="electionID" required>
                                    <?php 
                                    // Reset the elections result pointer
                                    $electionsQuery->execute();
                                    $elections = $electionsQuery->get_result();
                                    while ($election = $elections->fetch_assoc()): 
                                    ?>
                                    <option value="<?= $election['electionID'] ?>">
                                        <?= htmlspecialchars($election['name']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">Please select an election.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">
                                <i class="bi bi-bookmark me-1"></i> Category Name
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-tag-fill text-info"></i>
                                </span>
                                <input type="text" class="form-control" id="editCategoryName" name="categoryName" placeholder="Enter category name" required>
                                <div class="invalid-feedback">Please enter a category name.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle-fill me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-info text-white shadow-sm" id="updateCategory">
                            <i class="bi bi-check-circle-fill me-1"></i> Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Category Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-question-circle-fill text-danger me-3" style="font-size: 2rem;"></i>
                        <div>
                            <p class="mb-1">Are you sure you want to delete the category "<span id="deleteCategoryName" class="fw-bold"></span>"?</p>
                            <p class="text-danger mb-0"><i class="bi bi-exclamation-octagon-fill me-1"></i> This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle-fill me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger shadow-sm" id="confirmDelete" data-category-id="">
                        <i class="bi bi-trash-fill me-1"></i> Delete Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container for notifications -->
    <div class="toast-container"></div>

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    
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
        
        // Load all categories initially
        loadCategories();
        
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
    function loadCategories(electionId = '') {
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