<?php
// leave.php

// --- Configuration ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'stepongo_leave_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // !!! Change this to your MySQL password !!!

// --- Database Connection & Table Creation ---
function getDbConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("set names utf8");
            ensureTablesExist($conn); // Ensure tables are created on first connection
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $conn;
}

function ensureTablesExist(PDO $conn) {
    // Create database if not exists (less common with PDO in a single script, usually done separately)
    // However, if the user directly accesses this, it's safer.
    $conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    $conn->exec("USE `" . DB_NAME . "`");

    // Create leaves table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `leaves` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `labour_id` VARCHAR(50) NOT NULL,
            `labour_name` VARCHAR(255) NOT NULL,
            `leave_type` ENUM('Casual', 'Sick', 'Emergency', 'Paid', 'Unpaid') NOT NULL,
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `leave_duration` INT NOT NULL COMMENT 'Duration in days',
            `reason` TEXT,
            `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
    ");
    // No 'admins' table or related queries needed as authentication is removed.
}

// --- Helper Functions ---
function calculateLeaveDuration($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    return $interval->days + 1; // +1 to include the end day
}

// --- API Logic ---
if (isset($_GET['api'])) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $conn = getDbConnection();
    $request_method = $_SERVER["REQUEST_METHOD"];

    switch ($request_method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $query = "SELECT * FROM leaves WHERE id = ? LIMIT 0,1";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(1, $id);
                $stmt->execute();
                $leave = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($leave) {
                    http_response_code(200);
                    echo json_encode($leave);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Leave not found."));
                }
            } else {
                $query = "SELECT * FROM leaves ORDER BY created_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode($leaves);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));

            if (
                empty($data->labour_id) ||
                empty($data->labour_name) ||
                empty($data->leave_type) ||
                empty($data->start_date) ||
                empty($data->end_date) ||
                empty($data->reason)
            ) {
                http_response_code(400);
                echo json_encode(array("message" => "Unable to create leave. Data is incomplete."));
                break;
            }

            $leave_duration = calculateLeaveDuration($data->start_date, $data->end_date);

            $query = "INSERT INTO leaves (labour_id, labour_name, leave_type, start_date, end_date, leave_duration, reason, status) VALUES (:labour_id, :labour_name, :leave_type, :start_date, :end_date, :leave_duration, :reason, 'Pending')";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(":labour_id", htmlspecialchars(strip_tags($data->labour_id)));
            $stmt->bindParam(":labour_name", htmlspecialchars(strip_tags($data->labour_name)));
            $stmt->bindParam(":leave_type", htmlspecialchars(strip_tags($data->leave_type)));
            $stmt->bindParam(":start_date", htmlspecialchars(strip_tags($data->start_date)));
            $stmt->bindParam(":end_date", htmlspecialchars(strip_tags($data->end_date)));
            $stmt->bindParam(":leave_duration", $leave_duration);
            $stmt->bindParam(":reason", htmlspecialchars(strip_tags($data->reason)));

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Leave request created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create leave request."));
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(array("message" => "Unable to update leave. ID is missing."));
                break;
            }

            $query_parts = [];
            $params = [':id' => $data->id];

            if (!empty($data->labour_id)) {
                $query_parts[] = "labour_id = :labour_id";
                $params[':labour_id'] = htmlspecialchars(strip_tags($data->labour_id));
            }
            if (!empty($data->labour_name)) {
                $query_parts[] = "labour_name = :labour_name";
                $params[':labour_name'] = htmlspecialchars(strip_tags($data->labour_name));
            }
            if (!empty($data->leave_type)) {
                $query_parts[] = "leave_type = :leave_type";
                $params[':leave_type'] = htmlspecialchars(strip_tags($data->leave_type));
            }
            if (!empty($data->start_date)) {
                $query_parts[] = "start_date = :start_date";
                $params[':start_date'] = htmlspecialchars(strip_tags($data->start_date));
            }
            if (!empty($data->end_date)) {
                $query_parts[] = "end_date = :end_date";
                $params[':end_date'] = htmlspecialchars(strip_tags($data->end_date));
            }
            if (!empty($data->reason)) {
                $query_parts[] = "reason = :reason";
                $params[':reason'] = htmlspecialchars(strip_tags($data->reason));
            }
            if (!empty($data->status)) {
                $query_parts[] = "status = :status";
                $params[':status'] = htmlspecialchars(strip_tags($data->status));
            }

            // Recalculate duration if start_date or end_date are updated
            if (!empty($data->start_date) && !empty($data->end_date)) {
                $leave_duration = calculateLeaveDuration($data->start_date, $data->end_date);
                $query_parts[] = "leave_duration = :leave_duration";
                $params[':leave_duration'] = $leave_duration;
            } elseif (!empty($data->start_date) && empty($data->end_date)) {
                 // If only start date is updated, fetch current end date to recalculate duration
                 $current_leave_query = "SELECT end_date FROM leaves WHERE id = :id";
                 $stmt_current = $conn->prepare($current_leave_query);
                 $stmt_current->bindParam(':id', $data->id);
                 $stmt_current->execute();
                 $current_leave = $stmt_current->fetch(PDO::FETCH_ASSOC);
                 if ($current_leave) {
                     $leave_duration = calculateLeaveDuration($data->start_date, $current_leave['end_date']);
                     $query_parts[] = "leave_duration = :leave_duration";
                     $params[':leave_duration'] = $leave_duration;
                 }
            } elseif (empty($data->start_date) && !empty($data->end_date)) {
                // If only end date is updated, fetch current start date to recalculate duration
                $current_leave_query = "SELECT start_date FROM leaves WHERE id = :id";
                $stmt_current = $conn->prepare($current_leave_query);
                $stmt_current->bindParam(':id', $data->id);
                $stmt_current->execute();
                $current_leave = $stmt_current->fetch(PDO::FETCH_ASSOC);
                if ($current_leave) {
                    $leave_duration = calculateLeaveDuration($current_leave['start_date'], $data->end_date);
                    $query_parts[] = "leave_duration = :leave_duration";
                    $params[':leave_duration'] = $leave_duration;
                }
            }

            if (empty($query_parts)) {
                http_response_code(400);
                echo json_encode(array("message" => "No fields to update."));
                break;
            }

            $query = "UPDATE leaves SET " . implode(", ", $query_parts) . " WHERE id = :id";
            $stmt = $conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Leave request updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update leave request."));
            }
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(array("message" => "Unable to delete leave. ID is missing."));
                break;
            }

            $query = "DELETE FROM leaves WHERE id = :id";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(":id", htmlspecialchars(strip_tags($data->id)));

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Leave request deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete leave request."));
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Method Not Allowed."));
            break;
    }
    exit(); // Exit after API response
}

// --- UI Logic (HTML/CSS/JS) ---
// No authentication check needed here, directly render the panel
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management Panel - StepOnGo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .navbar { margin-bottom: 20px; }
        .card { border: none; border-radius: 0.5rem; margin-bottom: 20px; }
        .card-header { border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; padding: 1rem 1.5rem; font-weight: bold; }
        .table-responsive { margin-top: 20px; }
        .table th, .table td { vertical-align: middle; }
        .btn { margin-right: 5px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">StepOnGo Leave Panel</a>
            <span class="navbar-text text-white">
                (Public Access - No Authentication)
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Leave Requests</h2>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                Add New Leave Request
            </div>
            <div class="card-body">
                <form id="addLeaveForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="labourId" class="form-label">Labour ID</label>
                            <input type="text" class="form-control" id="labourId" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="labourName" class="form-label">Labour Name</label>
                            <input type="text" class="form-control" id="labourName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="leaveType" class="form-label">Leave Type</label>
                            <select class="form-select" id="leaveType" required>
                                <option value="">Select Type</option>
                                <option value="Casual">Casual</option>
                                <option value="Sick">Sick</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Paid">Paid</option>
                                <option value="Unpaid">Unpaid</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Leave</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                All Leave Requests
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="leaveTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Labour Name / ID</th>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Duration</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editLeaveModal" tabindex="-1" aria-labelledby="editLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLeaveModalLabel">Edit Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editLeaveForm">
                        <input type="hidden" id="editLeaveId">
                        <div class="mb-3">
                            <label for="editLabourId" class="form-label">Labour ID</label>
                            <input type="text" class="form-control" id="editLabourId" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLabourName" class="form-label">Labour Name</label>
                            <input type="text" class="form-control" id="editLabourName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLeaveType" class="form-label">Leave Type</label>
                            <select class="form-select" id="editLeaveType" required>
                                <option value="Casual">Casual</option>
                                <option value="Sick">Sick</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Paid">Paid</option>
                                <option value="Unpaid">Unpaid</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editStartDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="editStartDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEndDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="editEndDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="editReason" class="form-label">Reason</label>
                            <textarea class="form-control" id="editReason" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" required>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // API_URL points to the same file, but with a '?api' parameter
            const API_URL = '<?php echo $_SERVER["PHP_SELF"]; ?>?api';
            const leaveTableBody = document.querySelector('#leaveTable tbody');
            const addLeaveForm = document.getElementById('addLeaveForm');
            const editLeaveForm = document.getElementById('editLeaveForm');
            const editLeaveModal = new bootstrap.Modal(document.getElementById('editLeaveModal'));

            // Function to fetch and display leave requests
            async function fetchLeaves() {
                try {
                    const response = await fetch(API_URL);
                    const leaves = await response.json();
                    leaveTableBody.innerHTML = ''; // Clear existing rows

                    if (leaves.length === 0) {
                        leaveTableBody.innerHTML = '<tr><td colspan="10" class="text-center">No leave requests found.</td></tr>';
                        return;
                    }

                    leaves.forEach(leave => {
                        const row = `
                            <tr>
                                <td>${leave.id}</td>
                                <td>${leave.labour_name} / ${leave.labour_id}</td>
                                <td>${leave.leave_type}</td>
                                <td>${leave.start_date}</td>
                                <td>${leave.end_date}</td>
                                <td>${leave.leave_duration} days</td>
                                <td>${leave.reason}</td>
                                <td>
                                    <span class="badge ${getBadgeClass(leave.status)}">${leave.status}</span>
                                </td>
                                <td>${new Date(leave.created_at).toLocaleString()}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" data-id="${leave.id}">Edit</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${leave.id}">Delete</button>
                                </td>
                            </tr>
                        `;
                        leaveTableBody.innerHTML += row;
                    });

                    // Add event listeners for edit and delete buttons
                    document.querySelectorAll('.edit-btn').forEach(button => {
                        button.addEventListener('click', (e) => openEditModal(e.target.dataset.id));
                    });

                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', (e) => deleteLeave(e.target.dataset.id));
                    });

                } catch (error) {
                    console.error('Error fetching leaves:', error);
                    leaveTableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading leave requests.</td></tr>';
                }
            }

            function getBadgeClass(status) {
                switch (status) {
                    case 'Pending': return 'bg-secondary';
                    case 'Approved': return 'bg-success';
                    case 'Rejected': return 'bg-danger';
                    default: return 'bg-info';
                }
            }

            // Handle Add Leave Form Submission
            addLeaveForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const newLeave = {
                    labour_id: document.getElementById('labourId').value,
                    labour_name: document.getElementById('labourName').value,
                    leave_type: document.getElementById('leaveType').value,
                    start_date: document.getElementById('startDate').value,
                    end_date: document.getElementById('endDate').value,
                    reason: document.getElementById('reason').value,
                };

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(newLeave)
                    });

                    const result = await response.json();
                    if (response.ok) {
                        alert(result.message);
                        addLeaveForm.reset(); // Clear the form
                        fetchLeaves(); // Refresh the list
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error adding leave:', error);
                    alert('An error occurred while adding the leave.');
                }
            });

            // Open Edit Modal and populate with data
            async function openEditModal(id) {
                try {
                    const response = await fetch(`${API_URL}&id=${id}`); // Pass ID as query param for GET
                    const leave = await response.json();

                    if (response.ok) {
                        document.getElementById('editLeaveId').value = leave.id;
                        document.getElementById('editLabourId').value = leave.labour_id;
                        document.getElementById('editLabourName').value = leave.labour_name;
                        document.getElementById('editLeaveType').value = leave.leave_type;
                        document.getElementById('editStartDate').value = leave.start_date;
                        document.getElementById('editEndDate').value = leave.end_date;
                        document.getElementById('editReason').value = leave.reason;
                        document.getElementById('editStatus').value = leave.status;
                        editLeaveModal.show();
                    } else {
                        alert('Error fetching leave details: ' + leave.message);
                    }
                } catch (error) {
                    console.error('Error fetching leave for edit:', error);
                    alert('An error occurred while fetching leave details.');
                }
            }

            // Handle Edit Leave Form Submission
            editLeaveForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const updatedLeave = {
                    id: document.getElementById('editLeaveId').value,
                    labour_id: document.getElementById('editLabourId').value,
                    labour_name: document.getElementById('editLabourName').value,
                    leave_type: document.getElementById('editLeaveType').value,
                    start_date: document.getElementById('editStartDate').value,
                    end_date: document.getElementById('editEndDate').value,
                    reason: document.getElementById('editReason').value,
                    status: document.getElementById('editStatus').value,
                };

                try {
                    const response = await fetch(API_URL, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(updatedLeave)
                    });

                    const result = await response.json();
                    if (response.ok) {
                        alert(result.message);
                        editLeaveModal.hide(); // Close the modal
                        fetchLeaves(); // Refresh the list
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error updating leave:', error);
                    alert('An error occurred while updating the leave.');
                }
            });

            // Handle Delete Leave Request
            async function deleteLeave(id) {
                if (confirm('Are you sure you want to delete this leave request?')) {
                    try {
                        const response = await fetch(API_URL, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ id: id })
                        });

                        const result = await response.json();
                        if (response.ok) {
                            alert(result.message);
                            fetchLeaves(); // Refresh the list
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error deleting leave:', error);
                        alert('An error occurred while deleting the leave.');
                    }
                }
            }

            // Initial fetch of leaves when the page loads
            fetchLeaves();
        });
    </script>
</body>
</html>