<?php
include 'configs/session.php';
include 'configs/isadmin.php';

$query = "
    SELECT
        (SELECT COUNT(*) FROM election) AS total_elections,
        IFNULL((SELECT COUNT(*) FROM categories c WHERE c.election_id = e.id AND e.status = 1), 0) AS total_active_categories,
        (SELECT COUNT(*) FROM users WHERE type = 1) AS total_voters,
        COUNT(DISTINCT v.voter_id) AS total_voted,
        e.title AS election_title,
        e.id AS election_id
    FROM
        election e
    LEFT JOIN
        users u ON u.type = 1
    LEFT JOIN
        votes v ON e.id = v.election_id
    WHERE
        e.status = 1;
";

$result = $conn->query($query);
?>

<div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section dashboard">
    <?php if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); 
        // Calculate participation percentage
        $participationPercentage = ($row["total_voters"] > 0) ? round(($row["total_voted"] / $row["total_voters"]) * 100) : 0;
    ?>
    
    <div class="row">
        <!-- Elections Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card info-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Elections</h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-light">
                            <i class="bi bi-box-seam-fill text-primary"></i>
                        </div>
                        <div class="ps-3">
                            <h6 class="fs-2 fw-bold"><?php echo $row["total_elections"]; ?></h6>
                            <span class="text-muted small">Total Elections</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card info-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Categories</h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-light">
                            <i class="bi bi-bookmark text-success"></i>
                        </div>
                        <div class="ps-3">
                            <h6 class="fs-2 fw-bold"><?php echo $row["total_active_categories"]; ?></h6>
                            <span class="text-muted small">Active Categories</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voters Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card info-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Voters</h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info-light">
                            <i class="bi bi-people-fill text-info"></i>
                        </div>
                        <div class="ps-3">
                            <h6 class="fs-2 fw-bold"><?php echo $row["total_voters"]; ?></h6>
                            <span class="text-muted small">Registered Voters</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participation Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card info-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Participation</h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning-light">
                            <i class="bi bi-check2-circle text-warning"></i>
                        </div>
                        <div class="ps-3">
                            <h6 class="fs-2 fw-bold"><?php echo $row["total_voted"]; ?> <small class="fs-6 text-<?php echo ($participationPercentage > 50) ? 'success' : 'danger'; ?>">(<?php echo $participationPercentage; ?>%)</small></h6>
                            <span class="text-muted small">Votes Cast</span>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-<?php echo ($participationPercentage > 50) ? 'success' : 'warning'; ?>" role="progressbar" style="width: <?php echo $participationPercentage; ?>%" aria-valuenow="<?php echo $participationPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- Current Election Summary Card (New) -->
    <?php if (isset($row) && isset($row["election_title"])) { ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title m-0">Active Election: <?php echo $row["election_title"]; ?></h5>
                        <a href="election_details.php?id=<?php echo $row["election_id"]; ?>" class="btn btn-sm btn-primary view-details">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-calendar-check fs-1 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Status</h6>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-people fs-1 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Participation Rate</h6>
                                    <div class="progress" style="height: 10px; width: 200px;">
                                        <div class="progress-bar bg-<?php echo ($participationPercentage > 50) ? 'success' : 'warning'; ?>" role="progressbar" style="width: <?php echo $participationPercentage; ?>%" aria-valuenow="<?php echo $participationPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $row["total_voted"]; ?> of <?php echo $row["total_voters"]; ?> voters (<?php echo $participationPercentage; ?>%)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- Users Table -->
    <div class="row">
        <?php
        $users = $conn->prepare("SELECT * FROM users");
        $users->execute();
        $row = $users->get_result();
        ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">System Users</h5>
                    <div class="d-flex">
                        <div class="search-box me-2">
                            <input type="text" id="searchUsers" class="form-control" placeholder="Search users...">
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item filter-option" href="#" data-filter="all">All Users</a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="admin">Admins Only</a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="user">Regular Users Only</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="users-table" class="table text-nowrap table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" width="80">Profile</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($row as $key => $user) { ?>
                                <tr class="user-row" data-user-type="<?php echo ($user['type'] === 0) ? 'admin' : 'user'; ?>">
                                    <td class="text-center">
                                        <div class="position-relative d-inline-block">
                                            <?php if ($user['profile_picture']) { ?>
                                                <img src="assets/img/profile/users/<?php echo $user['profile_picture']; ?>" class="rounded-circle shadow-sm" width="50" height="50" alt="<?php echo $user['name']; ?>">
                                            <?php } else { ?>
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                                                    <span class="fw-bold text-secondary"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                                                </div>
                                            <?php } ?>
                                            <span class="position-absolute bottom-0 end-0 badge rounded-pill <?php echo ($user['type'] === 0) ? 'bg-primary' : 'bg-secondary'; ?>" style="width: 12px; height: 12px;"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold"><?php echo $user['name']; ?></span>
                                            <small class="text-muted">@<?php echo $user['username']; ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <?php if ($user['type'] === 0) { ?>
                                            <span class="badge bg-primary-light text-primary">Admin</span>
                                        <?php } else { ?>
                                            <span class="badge bg-secondary-light text-secondary">User</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($user['type'] === 0) { ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary user-type" data-id="<?php echo $user['id']; ?>" data-type="1" data-name="Normal user">
                                                    <i class="bi bi-arrow-down-circle"></i> Change to User
                                                </button>
                                            <?php } else { ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary user-type" data-id="<?php echo $user['id']; ?>" data-type="0" data-name="Admin">
                                                    <i class="bi bi-arrow-up-circle"></i> Make Admin
                                                </button>
                                            <?php } ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary reset" data-id="<?php echo $user['id']; ?>" data-name="Reset">
                                                <i class="bi bi-key"></i> Reset Password
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    
                   
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add this JavaScript for enhanced functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchUsers');
    const table = document.getElementById('users-table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    searchInput.addEventListener('keyup', function() {
        const searchText = searchInput.value.toLowerCase();
        filterTable(searchText, currentFilter);
    });
    
    // View Details button functionality
    const viewDetailsBtn = document.querySelector('.view-details');
    if (viewDetailsBtn) {
        viewDetailsBtn.addEventListener('click', function(e) {
            const electionId = this.getAttribute('href').split('=')[1];
            
            if (!electionId) {
                e.preventDefault();
                alert('No active election found or election ID is missing.');
            }
        });
    }
    
    // Filter functionality
    let currentFilter = 'all'; // Default filter
    const filterOptions = document.querySelectorAll('.filter-option');
    
    filterOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update the filter button text
            const filterType = this.getAttribute('data-filter');
            const filterDropdown = document.getElementById('filterDropdown');
            
            // Set the current filter
            currentFilter = filterType;
            
            // Update button text
            filterDropdown.innerHTML = `<i class="bi bi-funnel"></i> ${this.textContent}`;
            
            // Apply the filter
            filterTable(searchInput.value.toLowerCase(), filterType);
            
            // Add active class to the selected filter
            filterOptions.forEach(opt => {
                opt.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
    
    // Function to filter the table based on search text and filter type
    function filterTable(searchText, filterType) {
        for (let i = 0; i < rows.length; i++) {
            const nameCol = rows[i].getElementsByTagName('td')[1];
            const emailCol = rows[i].getElementsByTagName('td')[2];
            const userType = rows[i].getAttribute('data-user-type');
            
            let showRow = true;
            
            // First check the filter type
            if (filterType === 'admin' && userType !== 'admin') {
                showRow = false;
            } else if (filterType === 'user' && userType !== 'user') {
                showRow = false;
            }
            
            // Then check the search text if row passes the filter
            if (showRow && searchText) {
                showRow = false;
                if (nameCol && emailCol) {
                    const nameText = nameCol.textContent.toLowerCase();
                    const emailText = emailCol.textContent.toLowerCase();
                    
                    if (nameText.indexOf(searchText) > -1 || emailText.indexOf(searchText) > -1) {
                        showRow = true;
                    }
                }
            }
            
            // Show or hide the row based on the combined criteria
            rows[i].style.display = showRow ? '' : 'none';
        }
        
        // Update "no results" message
        updateNoResultsMessage();
    }
    
    // Function to show a message when no results are found
    function updateNoResultsMessage() {
        let visibleRows = 0;
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].style.display !== 'none') {
                visibleRows++;
            }
        }
        
        // Check if there's already a no-results message
        let noResultsMsg = document.getElementById('no-results-message');
        
        if (visibleRows === 0) {
            if (!noResultsMsg) {
                // Create and insert message
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'no-results-message';
                noResultsMsg.className = 'alert alert-info text-center my-3';
                noResultsMsg.textContent = 'No users found matching your criteria.';
                
                const tableParent = table.parentNode;
                tableParent.insertBefore(noResultsMsg, table.nextSibling);
            }
        } else if (noResultsMsg) {
            // Remove the message if we have visible rows
            noResultsMsg.remove();
        }
    }
    
    // User type change button functionality
    const userTypeButtons = document.querySelectorAll('.user-type');
    userTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const newType = this.getAttribute('data-type');
            const userName = this.getAttribute('data-name');
            
            if (confirm(`Are you sure you want to change this user's role to ${userName}?`)) {
               
                alert(`User role updated successfully to ${userName}!`);
                
            
            }
        });
    });
    
    // Reset password button functionality
    const resetButtons = document.querySelectorAll('.reset');
    resetButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            
            if (confirm('Are you sure you want to reset this user\'s password?')) {
                // In a real application, this would be an AJAX call to reset the password
                // For demonstration purposes, we'll just show a success message
                alert('Password has been reset successfully!');
            }
        });
    });
});
</script>

<!-- Add this CSS to your stylesheet -->
<style>
.bg-primary-light {
    background-color: rgba(13, 110, 253, 0.15);
}
.bg-success-light {
    background-color: rgba(25, 135, 84, 0.15);
}
.bg-info-light {
    background-color: rgba(13, 202, 240, 0.15);
}
.bg-warning-light {
    background-color: rgba(255, 193, 7, 0.15);
}
.bg-secondary-light {
    background-color: rgba(108, 117, 125, 0.15);
}
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card-icon {
    width: 50px;
    height: 50px;
}
.search-box {
    position: relative;
}
.search-box input {
    padding-left: 30px;
}
.search-box:before {
    content: "\F52A";
    font-family: bootstrap-icons;
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 10;
}
.dropdown-item.active {
    background-color: #0d6efd;
    color: white;
}
.filter-option:hover {
    background-color: #f8f9fa;
}
</style>