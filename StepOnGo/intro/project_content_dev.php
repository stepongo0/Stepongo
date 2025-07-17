<?php
// project_content_dev.php - Backend file for StepOnGo Developer Dashboard (New Version)

// --- Database Connection Configuration ---
$servername = "localhost"; // Your database server name
$username = "root";        // Your MySQL username
$password = "";            // Your MySQL password
$dbname = "stepongo_new_db";   // New database name

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Function to sanitize input data ---
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data); // Escape special characters for SQL queries
}

// --- Handle Project Deletion ---
if (isset($_GET['delete_id'])) {
    $id = sanitize_input($_GET['delete_id']);
    $sql = "DELETE FROM developer_projects WHERE id = ?"; // New table name
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Project deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error deleting project: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// --- Handle Project Update (Edit Form Submission) ---
if (isset($_POST['update_project'])) {
    $id = sanitize_input($_POST['project_id']);
    $project_title = sanitize_input($_POST['project_title']);
    $location = sanitize_input($_POST['location']);
    $description = sanitize_input($_POST['description']);
    $timeline = sanitize_input($_POST['timeline']);
    $cost = sanitize_input($_POST['cost']);
    $contractor_name = sanitize_input($_POST['contractor_name']);

    $sql = "UPDATE developer_projects SET project_title=?, location=?, description=?, timeline=?, cost=?, contractor_name=? WHERE id=?"; // New table name
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdsi", $project_title, $location, $description, $timeline, $cost, $contractor_name, $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Project updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error updating project: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// --- Handle Project Status Update ---
if (isset($_POST['update_status_id']) && isset($_POST['new_status'])) {
    $id = sanitize_input($_POST['update_status_id']);
    $new_status = sanitize_input($_POST['new_status']);

    $sql = "UPDATE developer_projects SET status=? WHERE id=?"; // New table name
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Project status updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error updating status: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// --- Handle Project Approval Status Update ---
if (isset($_POST['update_approval_id']) && isset($_POST['new_approval_status'])) {
    $id = sanitize_input($_POST['update_approval_id']);
    $new_approval_status = sanitize_input($_POST['new_approval_status']);

    $sql = "UPDATE developer_projects SET approval_status=? WHERE id=?"; // New table name
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_approval_status, $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Project approval status updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error updating approval status: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// --- Fetch Projects for Display (with optional search/filter) ---
$search_query = "";
$filter_status = "";
$filter_contractor = "";

if (isset($_GET['search_query'])) {
    $search_query = sanitize_input($_GET['search_query']);
}
if (isset($_GET['filter_status'])) {
    $filter_status = sanitize_input($_GET['filter_status']);
}
if (isset($_GET['filter_contractor'])) {
    $filter_contractor = sanitize_input($_GET['filter_contractor']);
}

$sql = "SELECT id, project_title, location, description, timeline, cost, contractor_name, status, upload_date, approval_status FROM developer_projects WHERE 1=1"; // New table name
$params = [];
$types = "";

if (!empty($search_query)) {
    $sql .= " AND (project_title LIKE ? OR location LIKE ? OR description LIKE ? OR contractor_name LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}
if (!empty($filter_status)) {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}
if (!empty($filter_contractor)) {
    $sql .= " AND contractor_name LIKE ?";
    $params[] = "%" . $filter_contractor . "%";
    $types .= "s";
}

$sql .= " ORDER BY upload_date DESC"; // Order by most recent uploads

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Fetch a single project for editing if 'edit_id' is present in GET request ---
$edit_project = null;
if (isset($_GET['edit_id'])) {
    $edit_id = sanitize_input($_GET['edit_id']);
    $sql = "SELECT id, project_title, location, description, timeline, cost, contractor_name FROM developer_projects WHERE id = ?"; // New table name
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    $edit_project = $edit_result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepOnGo - Developer Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-action {
            margin-right: 5px;
            border-radius: 8px;
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .alert {
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4 text-primary">StepOnGo Developer Dashboard - Project Management (New)</h2>

        <!-- Search and Filter Section -->
        <div class="card p-4 mb-4">
            <h4 class="mb-3">Search & Filter Projects</h4>
            <form class="row g-3 align-items-end" method="GET" action="project_content_dev.php">
                <div class="col-md-4">
                    <label for="searchQuery" class="form-label">Search (Title, Location, Description, Contractor)</label>
                    <input type="text" class="form-control" id="searchQuery" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Enter keywords">
                </div>
                <div class="col-md-3">
                    <label for="filterStatus" class="form-label">Filter by Status</label>
                    <select class="form-select" id="filterStatus" name="filter_status">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php echo ($filter_status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo ($filter_status == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo ($filter_status == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterContractor" class="form-label">Filter by Contractor</label>
                    <input type="text" class="form-control" id="filterContractor" name="filter_contractor" value="<?php echo htmlspecialchars($filter_contractor); ?>" placeholder="Contractor Name">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-action">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Project Edit Form (conditionally displayed) -->
        <?php if ($edit_project): ?>
        <div class="card p-4 mb-4 border-primary">
            <h4 class="mb-3 text-primary">Edit Project: <?php echo htmlspecialchars($edit_project['project_title']); ?></h4>
            <form method="POST" action="project_content_dev.php">
                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($edit_project['id']); ?>">
                <div class="mb-3">
                    <label for="editProjectTitle" class="form-label">Project Title</label>
                    <input type="text" class="form-control" id="editProjectTitle" name="project_title" value="<?php echo htmlspecialchars($edit_project['project_title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="editLocation" class="form-label">Location</label>
                    <input type="text" class="form-control" id="editLocation" name="location" value="<?php echo htmlspecialchars($edit_project['location']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="editDescription" class="form-label">Description</label>
                    <textarea class="form-control" id="editDescription" name="description" rows="3"><?php echo htmlspecialchars($edit_project['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="editTimeline" class="form-label">Timeline</label>
                    <input type="text" class="form-control" id="editTimeline" name="timeline" value="<?php echo htmlspecialchars($edit_project['timeline']); ?>">
                </div>
                <div class="mb-3">
                    <label for="editCost" class="form-label">Estimated Cost</label>
                    <input type="number" step="0.01" class="form-control" id="editCost" name="cost" value="<?php echo htmlspecialchars($edit_project['cost']); ?>">
                </div>
                <div class="mb-3">
                    <label for="editContractorName" class="form-label">Contractor Name</label>
                    <input type="text" class="form-control" id="editContractorName" name="contractor_name" value="<?php echo htmlspecialchars($edit_project['contractor_name']); ?>">
                </div>
                <button type="submit" name="update_project" class="btn btn-success btn-action">Update Project</button>
                <a href="project_content_dev.php" class="btn btn-secondary btn-action">Cancel</a>
            </form>
        </div>
        <?php endif; ?>

        <!-- Projects Display Table -->
        <div class="card p-4">
            <h4 class="mb-3">All Uploaded Projects (<?php echo count($projects); ?>)</h4>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Timeline</th>
                            <th>Cost</th>
                            <th>Contractor</th>
                            <th>Upload Date</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($projects) > 0): ?>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['id']); ?></td>
                                    <td><?php echo htmlspecialchars($project['project_title']); ?></td>
                                    <td><?php echo htmlspecialchars($project['location']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($project['description'], 0, 50)) . (strlen($project['description']) > 50 ? '...' : ''); ?></td>
                                    <td><?php echo htmlspecialchars($project['timeline']); ?></td>
                                    <td>$<?php echo number_format(htmlspecialchars($project['cost']), 2); ?></td>
                                    <td><?php echo htmlspecialchars($project['contractor_name']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime(htmlspecialchars($project['upload_date']))); ?></td>
                                    <td>
                                        <form method="POST" action="project_content_dev.php" class="d-inline">
                                            <input type="hidden" name="update_status_id" value="<?php echo htmlspecialchars($project['id']); ?>">
                                            <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="Pending" <?php echo ($project['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="In Progress" <?php echo ($project['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="Completed" <?php echo ($project['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="project_content_dev.php" class="d-inline">
                                            <input type="hidden" name="update_approval_id" value="<?php echo htmlspecialchars($project['id']); ?>">
                                            <select name="new_approval_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="Pending" <?php echo ($project['approval_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Approved" <?php echo ($project['approval_status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                <option value="Rejected" <?php echo ($project['approval_status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <!-- View Details Button (can be expanded to a modal later) -->
                                        <button type="button" class="btn btn-info btn-sm btn-action" data-bs-toggle="modal" data-bs-target="#projectDetailsModal"
                                                data-id="<?php echo htmlspecialchars($project['id']); ?>"
                                                data-title="<?php echo htmlspecialchars($project['project_title']); ?>"
                                                data-location="<?php echo htmlspecialchars($project['location']); ?>"
                                                data-description="<?php echo htmlspecialchars($project['description']); ?>"
                                                data-timeline="<?php echo htmlspecialchars($project['timeline']); ?>"
                                                data-cost="<?php echo number_format(htmlspecialchars($project['cost']), 2); ?>"
                                                data-contractor="<?php echo htmlspecialchars($project['contractor_name']); ?>"
                                                data-uploaddate="<?php echo date('Y-m-d H:i:s', strtotime(htmlspecialchars($project['upload_date']))); ?>"
                                                data-status="<?php echo htmlspecialchars($project['status']); ?>"
                                                data-approval="<?php echo htmlspecialchars($project['approval_status']); ?>">
                                            View
                                        </button>
                                        <a href="project_content_dev.php?edit_id=<?php echo htmlspecialchars($project['id']); ?>" class="btn btn-warning btn-sm btn-action">Edit</a>
                                        <a href="project_content_dev.php?delete_id=<?php echo htmlspecialchars($project['id']); ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Are you sure you want to delete this project?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center">No projects found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Project Details Modal -->
    <div class="modal fade" id="projectDetailsModal" tabindex="-1" aria-labelledby="projectDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header bg-primary text-white rounded-top-3">
                    <h5 class="modal-title" id="projectDetailsModalLabel">Project Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h4 id="modalProjectTitle" class="mb-3 text-primary"></h4>
                    <p><strong>ID:</strong> <span id="modalProjectId"></span></p>
                    <p><strong>Location:</strong> <span id="modalProjectLocation"></span></p>
                    <p><strong>Description:</strong> <span id="modalProjectDescription"></span></p>
                    <p><strong>Timeline:</strong> <span id="modalProjectTimeline"></span></p>
                    <p><strong>Estimated Cost:</strong> $<span id="modalProjectCost"></span></p>
                    <p><strong>Contractor Name:</strong> <span id="modalProjectContractor"></span></p>
                    <p><strong>Upload Date:</strong> <span id="modalProjectUploadDate"></span></p>
                    <p><strong>Current Status:</strong> <span id="modalProjectStatus" class="badge bg-secondary"></span></p>
                    <p><strong>Approval Status:</strong> <span id="modalProjectApproval" class="badge bg-info"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to populate the modal with project details
        var projectDetailsModal = document.getElementById('projectDetailsModal');
        projectDetailsModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var id = button.getAttribute('data-id');
            var title = button.getAttribute('data-title');
            var location = button.getAttribute('data-location');
            var description = button.getAttribute('data-description');
            var timeline = button.getAttribute('data-timeline');
            var cost = button.getAttribute('data-cost');
            var contractor = button.getAttribute('data-contractor');
            var uploadDate = button.getAttribute('data-uploaddate');
            var status = button.getAttribute('data-status');
            var approval = button.getAttribute('data-approval');

            var modalTitle = projectDetailsModal.querySelector('#modalProjectTitle');
            var modalId = projectDetailsModal.querySelector('#modalProjectId');
            var modalLocation = projectDetailsModal.querySelector('#modalProjectLocation');
            var modalDescription = projectDetailsModal.querySelector('#modalProjectDescription');
            var modalTimeline = projectDetailsModal.querySelector('#modalProjectTimeline');
            var modalCost = projectDetailsModal.querySelector('#modalProjectCost');
            var modalContractor = projectDetailsModal.querySelector('#modalProjectContractor');
            var modalUploadDate = projectDetailsModal.querySelector('#modalProjectUploadDate');
            var modalStatus = projectDetailsModal.querySelector('#modalProjectStatus');
            var modalApproval = projectDetailsModal.querySelector('#modalProjectApproval');

            modalTitle.textContent = title;
            modalId.textContent = id;
            modalLocation.textContent = location;
            modalDescription.textContent = description;
            modalTimeline.textContent = timeline;
            modalCost.textContent = cost;
            modalContractor.textContent = contractor;
            modalUploadDate.textContent = uploadDate;
            modalStatus.textContent = status;
            modalApproval.textContent = approval;

            // Update badge colors based on status/approval
            modalStatus.className = 'badge';
            if (status === 'Pending') {
                modalStatus.classList.add('bg-warning', 'text-dark');
            } else if (status === 'In Progress') {
                modalStatus.classList.add('bg-primary');
            } else if (status === 'Completed') {
                modalStatus.classList.add('bg-success');
            } else {
                modalStatus.classList.add('bg-secondary');
            }

            modalApproval.className = 'badge';
            if (approval === 'Pending') {
                modalApproval.classList.add('bg-warning', 'text-dark');
            } else if (approval === 'Approved') {
                modalApproval.classList.add('bg-success');
            } else if (approval === 'Rejected') {
                modalApproval.classList.add('bg-danger');
            } else {
                modalApproval.classList.add('bg-info');
            }
        });
    </script>
</body>
</html>
