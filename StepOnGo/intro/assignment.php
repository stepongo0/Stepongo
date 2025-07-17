<?php
// assignment.php - Work Assignment Panel

// --- PHP Error Display (TEMPORARY - FOR DEBUGGING ONLY) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUGGING SETTINGS ---

$conn_local = null;
$conn_opened_by_assignment_script = false; // Flag to track if this script opened the connection

// Check if a global connection exists (from dashboard.php)
if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli && !$GLOBALS['conn']->connect_error) {
    $conn_local = $GLOBALS['conn']; // Use the connection from dashboard.php
} else {
    // If no global connection, this script is likely accessed directly (e.g., AJAX or standalone)
    // Establish its own database connection
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root'); // Your DB username
    define('DB_PASSWORD', '');    // Your DB password
    define('DB_NAME', 'stepongo_new_db'); // Your database name

    $conn_local = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn_local->connect_error) {
        // If it's an AJAX request, send JSON error. Otherwise, die.
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn_local->connect_error]);
            exit();
        } else {
            die("Database connection failed for assignment panel: " . $conn_local->connect_error);
        }
    }
    $conn_opened_by_assignment_script = true;
}

// --- Function to sanitize input using the provided connection ---
function sanitize_input_for_assignment($data, $connection) {
    if (is_array($data)) {
        return array_map(function($item) use ($connection) {
            return htmlspecialchars(stripslashes(trim($connection->real_escape_string($item))));
        }, $data);
    }
    return htmlspecialchars(stripslashes(trim($connection->real_escape_string($data))));
}

// --- Handle Delete Assignment (via AJAX) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_assignment') {
    header('Content-Type: application/json');
    $assignment_id = sanitize_input_for_assignment($_POST['assignment_id'], $conn_local);

    if (!empty($assignment_id) && is_numeric($assignment_id)) {
        $stmt = $conn_local->prepare("DELETE FROM assignments WHERE assignment_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $assignment_id);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Assignment deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error deleting assignment: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database prepare error: ' . $conn_local->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid assignment ID for deletion.']);
    }

    // Close connection ONLY if this script opened it for a direct AJAX call
    if ($conn_opened_by_assignment_script) {
        $conn_local->close();
    }
    exit(); // Important: Exit after sending JSON response for AJAX requests
}

// --- Handle Add Assignment (via regular POST) ---
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_assignment') {
    // Only process if the action is 'add_assignment' and it's not an AJAX delete request
    if (!isset($_POST['action']) || $_POST['action'] !== 'delete_assignment') {

        $labour_id = sanitize_input_for_assignment($_POST['labour_id'], $conn_local);
        $project_id = sanitize_input_for_assignment($_POST['project_id'], $conn_local);
        $start_date = sanitize_input_for_assignment($_POST['start_date'], $conn_local);
        $end_date = sanitize_input_for_assignment($_POST['end_date'], $conn_local);
        $task_description = sanitize_input_for_assignment($_POST['task_description'], $conn_local);
        $assigned_supervisor = sanitize_input_for_assignment($_POST['assigned_supervisor'], $conn_local);
        $status = sanitize_input_for_assignment($_POST['status'], $conn_local);

        if (empty($labour_id) || empty($project_id) || empty($start_date) || empty($end_date) || empty($task_description) || empty($status)) {
            $message = "All required fields (Labour, Project, Dates, Description, Status) must be filled.";
            $message_type = "error";
        } else {
            $stmt = $conn_local->prepare("INSERT INTO assignments (labour_id, project_id, start_date, end_date, task_description, assigned_supervisor, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iisssss", $labour_id, $project_id, $start_date, $end_date, $task_description, $assigned_supervisor, $status);

                if ($stmt->execute()) {
                    $message = "Assignment added successfully!";
                    $message_type = "success";
                    // Clear form fields by clearing $_POST for the current request
                    $_POST = array();
                } else {
                    $message = "Error adding assignment: " . $stmt->error;
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "Database prepare error: " . $conn_local->error;
                $message_type = "error";
            }
        }
    }
}

// --- Fetch data for display ---
$labours = [];
$sql_labours = "SELECT id, name FROM labours ORDER BY name ASC";
$result_labours = $conn_local->query($sql_labours);
if ($result_labours) {
    while ($row = $result_labours->fetch_assoc()) {
        $labours[] = $row;
    }
    $result_labours->free();
} else {
    error_log("Error fetching labours: " . $conn_local->error);
    if (empty($message)) { $message = "Error fetching labours: " . $conn_local->error; $message_type = "error"; }
}

$projects = [];
$sql_projects = "SELECT id, project_name, client_name FROM projects ORDER BY project_name ASC";
$result_projects = $conn_local->query($sql_projects);
if ($result_projects) {
    while ($row = $result_projects->fetch_assoc()) {
        $projects[] = $row;
    }
    $result_projects->free();
} else {
    error_log("Error fetching projects: " . $conn_local->error);
    if (empty($message)) { $message = "Error fetching projects: " . $conn_local->error; $message_type = "error"; }
}

$assignments = [];
$sql_assignments = "SELECT a.assignment_id, a.start_date, a.end_date, a.task_description, a.assigned_supervisor, a.status, l.name AS labour_name, p.project_name FROM assignments a JOIN labours l ON a.labour_id = l.id JOIN projects p ON a.project_id = p.id ORDER BY a.created_at DESC";
$result_assignments = $conn_local->query($sql_assignments);
if ($result_assignments) {
    while ($row = $result_assignments->fetch_assoc()) {
        $assignments[] = $row;
    }
    $result_assignments->free();
} else {
    error_log("Error fetching assignments: " . $conn_local->error);
    if (empty($message)) { $message = "Error fetching assignments: " . $conn_local->error; $message_type = "error"; }
}

// Close connection ONLY if this script opened it AND it's not an AJAX POST which already exited.
// When included by dashboard.php, $conn_opened_by_assignment_script will be false.
if ($conn_opened_by_assignment_script && (!isset($_POST['action']) || $_POST['action'] !== 'delete_assignment')) {
    $conn_local->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepOnGo - Manage Work Assignments</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        /* Your CSS styles here (same as before) */
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); }
        h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 25px; text-align: center; }
        h3 { color: #495057; margin-top: 30px; margin-bottom: 20px; }
        .form-section { margin-bottom: 40px; padding: 20px; border: 1px solid #e9ecef; border-radius: 6px; background-color: #fdfdfd; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input[type="text"], .form-group input[type="date"], .form-group select, .form-group textarea { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .btn { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease, transform 0.2s ease; }
        .btn:hover { background-color: #0056b3; transform: translateY(-1px); }
        .btn-delete { background-color: #dc3545; margin-left: 5px; }
        .btn-delete:hover { background-color: #c82333; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .table-section { margin-top: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); }
        table th, table td { border: 1px solid #e9ecef; padding: 12px 15px; text-align: left; vertical-align: top; }
        table th { background-color: #f8f9fa; color: #495057; font-weight: bold; text-transform: uppercase; font-size: 0.9em; }
        table tr:nth-child(even) { background-color: #fbfbfb; }
        table tr:hover { background-color: #eef2f5; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 15px; font-size: 0.8em; font-weight: bold; color: white; text-transform: capitalize; white-space: nowrap; }
        .status-Assigned { background-color: #007bff; }
        .status-In-Progress { background-color: #ffc107; color: #333;}
        .status-Completed { background-color: #28a745; }
        .status-Cancelled { background-color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h2>StepOnGo - Manage Work Assignments</h2>
        <p style="text-align: center; color: green; font-weight: bold;">(This panel is integrated into the dashboard.)</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h3>Add New Assignment</h3>
            <form id="addAssignmentForm" method="POST" action="?page=assignment"> <input type="hidden" name="action" value="add_assignment">
                <div class="form-group">
                    <label for="labour_id">Assign to Labour:</label>
                    <select id="labour_id" name="labour_id" required>
                        <option value="">Select a Labour</option>
                        <?php foreach ($labours as $labour): ?>
                            <option value="<?php echo htmlspecialchars($labour['id']); ?>"
                                <?php echo (isset($_POST['labour_id']) && $_POST['labour_id'] == $labour['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($labour['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="project_id">Select Project:</label>
                    <select id="project_id" name="project_id" required>
                        <option value="">Select a Project</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo htmlspecialchars($project['id']); ?>"
                                <?php echo (isset($_POST['project_id']) && $_POST['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['project_name']); ?> (Client: <?php echo htmlspecialchars($project['client_name']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" required value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" required value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="task_description">Task Description:</label>
                    <textarea id="task_description" name="task_description" required><?php echo htmlspecialchars($_POST['task_description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="assigned_supervisor">Assigned Supervisor (Optional):</label>
                    <input type="text" id="assigned_supervisor" name="assigned_supervisor" value="<?php echo htmlspecialchars($_POST['assigned_supervisor'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <?php
                        $statuses = ['Assigned', 'In Progress', 'Completed', 'Cancelled'];
                        foreach ($statuses as $s) {
                            echo '<option value="' . htmlspecialchars($s) . '"';
                            echo (isset($_POST['status']) && $_POST['status'] == $s) ? 'selected' : '';
                            echo '>' . htmlspecialchars($s) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn">Add Assignment</button>
            </form>
        </div>

        <div class="table-section">
            <h3>Existing Assignments</h3>
            <?php if (empty($assignments)): ?>
                <p id="no-assignments-message">No assignments found. Add one above!</p>
            <?php else: ?>
                <table id="assignmentsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Labour</th>
                            <th>Project</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Description</th>
                            <th>Supervisor</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr id="assignment-row-<?php echo htmlspecialchars($assignment['assignment_id']); ?>">
                                <td><?php echo htmlspecialchars($assignment['assignment_id']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['labour_name']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['end_date']); ?></td>
                                <td><?php echo htmlspecialchars(substr($assignment['task_description'], 0, 100)); ?><?php echo (strlen($assignment['task_description']) > 100) ? '...' : ''; ?></td>
                                <td><?php echo htmlspecialchars($assignment['assigned_supervisor'] ?: 'N/A'); ?></td>
                                <td><span class="status-badge status-<?php echo str_replace(' ', '-', htmlspecialchars($assignment['status'])); ?>">
                                    <?php echo htmlspecialchars($assignment['status']); ?>
                                </span></td>
                                <td>
                                    <button class="btn btn-delete" data-assignment-id="<?php echo htmlspecialchars($assignment['assignment_id']); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function displayMessage(message, type) {
                // Remove existing messages
                $('.message').remove();
                const messageHtml = '<div class="message ' + type + '">' + message + '</div>';
                $('.container').prepend(messageHtml);
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    $('.message.' + type).fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            $('#assignmentsTable').on('click', '.btn-delete', function(e) {
                e.preventDefault();

                const assignmentId = $(this).data('assignment-id');
                if (window.confirm('Are you sure you want to delete this assignment? This action cannot be undone.')) {
                    $.ajax({
                        url: 'assignment.php', // AJAX call still targets this file
                        type: 'POST',
                        data: {
                            action: 'delete_assignment',
                            assignment_id: assignmentId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#assignment-row-' + assignmentId).fadeOut(400, function() {
                                    $(this).remove();
                                    displayMessage(response.message, 'success');
                                    // If no more rows, show the "No assignments found" message
                                    if ($('#assignmentsTable tbody tr').length === 0) {
                                        if (!$('#no-assignments-message').length) {
                                            $('.table-section').append('<p id="no-assignments-message">No assignments found. Add one above!</p>');
                                        }
                                    }
                                });
                            } else {
                                displayMessage(response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            displayMessage('An unexpected error occurred during deletion. Please try again.', 'error');
                        }
                    });
                }
            });

            // If a message is present on page load (e.g., after adding an assignment)
            if ($('.message').length) {
                setTimeout(function() {
                    $('.message').fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>