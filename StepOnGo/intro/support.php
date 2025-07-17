<?php
// PHP Error Reporting for Development (remove or adjust for production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable mysqli error reporting for better debugging of database issues
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection parameters
$servername = "localhost";
$username = "root"; // !! IMPORTANT: REPLACE WITH YOUR ACTUAL DATABASE USERNAME !!
$password = ""; // !! IMPORTANT: REPLACE WITH YOUR ACTUAL DATABASE PASSWORD !!
$dbname = "stepongo_new_db";

// Create database connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    // Catch specific mysqli connection errors
    die("Database connection failed: " . $e->getMessage() . " Please check your database credentials in support.php.");
}

// Function to sanitize and validate input
// Uses htmlspecialchars to prevent XSS, trim to remove whitespace, and stripslashes
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // ENT_QUOTES encodes both single and double quotes
    return $data;
}

// Define allowed attachment types and max size for security
const ALLOWED_MIME_TYPES = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/msword', // .doc
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'text/plain',
];
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB

$alert_messages = []; // Array to store feedback messages for the user

// --- Handle Ticket Submission ---
if (isset($_POST['submit_ticket'])) {
    $name = sanitize_input($_POST['name']);
    $user_type = sanitize_input($_POST['user_type']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Sanitize email
    $issue_title = sanitize_input($_POST['issue_title']);
    $description = sanitize_input($_POST['description']);
    $priority = sanitize_input($_POST['priority']);
    $attachment_path = null;

    // Validate inputs
    if (empty($name) || empty($user_type) || empty($email) || empty($issue_title) || empty($description) || empty($priority)) {
        $alert_messages[] = ['type' => 'danger', 'message' => 'All fields are required.'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alert_messages[] = ['type' => 'danger', 'message' => 'Please enter a valid email address.'];
    } elseif (!in_array($user_type, ['company', 'worker'])) {
        $alert_messages[] = ['type' => 'danger', 'message' => 'Invalid user type selected.'];
    } elseif (!in_array($priority, ['Low', 'Medium', 'High'])) {
        $alert_messages[] = ['type' => 'danger', 'message' => 'Invalid priority selected.'];
    } else {
        // File upload handling
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $target_dir = "uploads/";

            // Create uploads directory if it doesn't exist
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) { // 0777 for full permissions, adjust for production
                    $alert_messages[] = ['type' => 'danger', 'message' => 'Failed to create upload directory.'];
                }
            }

            // Generate a unique filename to prevent collisions and enhance security
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_filename = bin2hex(random_bytes(16)) . '.' . $file_extension;
            $target_file = $target_dir . $unique_filename;

            // Basic file validation
            if ($file['size'] > MAX_FILE_SIZE) {
                $alert_messages[] = ['type' => 'warning', 'message' => 'File is too large. Maximum size is 5MB.'];
            }

            // Check actual MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime_type, ALLOWED_MIME_TYPES)) {
                $alert_messages[] = ['type' => 'warning', 'message' => 'Invalid file type. Only JPG, PNG, GIF, PDF, DOC, DOCX, TXT are allowed.'];
            }

            // If no errors, move the uploaded file
            if (empty($alert_messages)) {
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $attachment_path = $target_file;
                } else {
                    $alert_messages[] = ['type' => 'danger', 'message' => 'Error uploading your file.'];
                }
            }
        } elseif (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle other file upload errors
            $alert_messages[] = ['type' => 'danger', 'message' => 'File upload error: ' . $_FILES['attachment']['error']];
        }


        // If no errors so far, proceed with database insertion
        if (empty($alert_messages)) {
            // Prepare and bind (prevent SQL injection)
            $stmt = $conn->prepare("INSERT INTO support_tickets (name, user_type, email, issue_title, description, priority, attachment) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $user_type, $email, $issue_title, $description, $priority, $attachment_path);

            if ($stmt->execute()) {
                $alert_messages[] = ['type' => 'success', 'message' => 'Support ticket submitted successfully!'];
            } else {
                $alert_messages[] = ['type' => 'danger', 'message' => 'Database error: ' . $stmt->error];
            }
            $stmt->close();
        }
    }
}

// --- Handle Admin Actions ---

// Update Ticket Status and Reply
if (isset($_POST['update_ticket'])) {
    $ticket_id = filter_var($_POST['ticket_id'], FILTER_VALIDATE_INT);
    $new_status = sanitize_input($_POST['new_status']);
    $admin_reply = sanitize_input($_POST['admin_reply']);

    if ($ticket_id === false || !in_array($new_status, ['Pending', 'In Progress', 'Resolved'])) {
        $alert_messages[] = ['type' => 'danger', 'message' => 'Invalid ticket ID or status.'];
    } else {
        $stmt = $conn->prepare("UPDATE support_tickets SET status = ?, admin_reply = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_status, $admin_reply, $ticket_id);
        if ($stmt->execute()) {
            $alert_messages[] = ['type' => 'success', 'message' => 'Ticket updated successfully!'];
        } else {
            $alert_messages[] = ['type' => 'danger', 'message' => 'Error updating ticket: ' . $stmt->error];
        }
        $stmt->close();
    }
}

// Delete Ticket
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $ticket_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($ticket_id === false) {
        $alert_messages[] = ['type' => 'danger', 'message' => 'Invalid ticket ID for deletion.'];
    } else {
        // First, get attachment path to delete file from server
        $stmt_select = $conn->prepare("SELECT attachment FROM support_tickets WHERE id = ?");
        $stmt_select->bind_param("i", $ticket_id);
        $stmt_select->execute();
        $stmt_select->bind_result($attachment_to_delete);
        $stmt_select->fetch();
        $stmt_select->close();

        $stmt = $conn->prepare("DELETE FROM support_tickets WHERE id = ?");
        $stmt->bind_param("i", $ticket_id);
        if ($stmt->execute()) {
            // Delete the actual file if it exists
            if ($attachment_to_delete && file_exists($attachment_to_delete)) {
                unlink($attachment_to_delete);
            }
            $alert_messages[] = ['type' => 'success', 'message' => 'Ticket and its attachment (if any) deleted successfully!'];
        } else {
            $alert_messages[] = ['type' => 'danger', 'message' => 'Error deleting ticket: ' . $stmt->error];
        }
        $stmt->close();
    }
}

// --- Handle Report Generation ---
if (isset($_GET['report']) && ($_GET['report'] == 'csv' || $_GET['report'] == 'pdf')) {
    $query = "SELECT id, name, user_type, email, issue_title, description, priority, status, admin_reply, created_at, updated_at FROM support_tickets";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $data = [];
        // Get column headers
        $field_names = [];
        while ($fieldinfo = $result->fetch_field()) {
            $field_names[] = $fieldinfo->name;
        }
        $data[] = $field_names;

        // Get row data
        while ($row = $result->fetch_assoc()) {
            // Ensure all values are strings for CSV and PDF export
            $data[] = array_map('strval', array_values($row));
        }

        if ($_GET['report'] == 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="support_tickets_' . date('Ymd_His') . '.csv"');
            $output = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit(); // Terminate script after sending file
        } elseif ($_GET['report'] == 'pdf') {
            // IMPORTANT: For robust PDF generation, you need a library like mPDF or TCPDF.
            // Install via Composer: composer require mpdf/mpdf OR composer require tecnickcom/tcpdf
            // This is a basic example using mPDF (if installed).
            if (file_exists(__DIR__ . '/vendor/autoload.php')) {
                require_once __DIR__ . '/vendor/autoload.php';
                try {
                    $mpdf = new \Mpdf\Mpdf();
                    $html = '<h1 style="text-align: center;">Support Tickets Report</h1>';
                    $html .= '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
                    $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
                    $html .= '<thead><tr>';
                    foreach ($field_names as $col) {
                        $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $col))) . '</th>';
                    }
                    $html .= '</tr></thead><tbody>';

                    foreach ($data as $row_idx => $row) {
                        if ($row_idx == 0) continue; // Skip header row from data array
                        $html .= '<tr>';
                        foreach ($row as $cell) {
                            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</tbody></table>';

                    $mpdf->WriteHTML($html);
                    $mpdf->Output('support_tickets_' . date('Ymd_His') . '.pdf', 'D'); // 'D' for download
                    exit();
                } catch (\Mpdf\MpdfException $e) {
                    $alert_messages[] = ['type' => 'danger', 'message' => 'PDF generation failed: ' . $e->getMessage()];
                }
            } else {
                $alert_messages[] = ['type' => 'warning', 'message' => 'PDF generation requires mPDF library (install via Composer: `composer require mpdf/mpdf`).'];
            }
        }
    } else {
        $alert_messages[] = ['type' => 'info', 'message' => 'No tickets found for report generation.'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .admin-panel-container {
            max-width: 1400px; /* Wider for admin view */
        }
        .ticket-card {
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .ticket-header {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .ticket-body {
            padding: 15px;
        }
        .status-pending { background-color: #fff3cd; } /* Yellowish */
        .status-in-progress { background-color: #cfe2ff; } /* Light Blue */
        .status-resolved { background-color: #d1e7dd; } /* Light Green */
    </style>
</head>
<body>

    <div class="container mb-5">
        <h2 class="mb-4 text-center">Submit a Support Ticket</h2>

        <?php foreach ($alert_messages as $alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $alert['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <form action="support.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="John Doe">
            </div>
            <div class="mb-3">
                <label for="user_type" class="form-label">User Type <span class="text-danger">*</span></label>
                <select class="form-select" id="user_type" name="user_type" required>
                    <option value="">Select User Type</option>
                    <option value="company">Company</option>
                    <option value="worker">Worker</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
            </div>
            <div class="mb-3">
                <label for="issue_title" class="form-label">Issue Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="issue_title" name="issue_title" required placeholder="Problem with login / Payment issue">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="5" required placeholder="Please describe your issue in detail..."></textarea>
            </div>
            <div class="mb-3">
                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                <select class="form-select" id="priority" name="priority" required>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="attachment" class="form-label">Attachment (Optional)</label>
                <input type="file" class="form-control" id="attachment" name="attachment">
                <div class="form-text">Max 5MB. Allowed types: JPG, PNG, GIF, PDF, DOC, DOCX, TXT.</div>
            </div>
            <button type="submit" name="submit_ticket" class="btn btn-primary">Submit Ticket</button>
        </form>
    </div>

    <div class="container admin-panel-container">
        <h2 class="mb-4 text-center">Admin Panel: Support Tickets</h2>

        <?php foreach ($alert_messages as $alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $alert['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <form action="support.php" method="GET" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="filter_user_type" class="form-label">User Type</label>
                    <select class="form-select" id="filter_user_type" name="filter_user_type">
                        <option value="">All</option>
                        <option value="company" <?php echo (isset($_GET['filter_user_type']) && $_GET['filter_user_type'] == 'company') ? 'selected' : ''; ?>>Company</option>
                        <option value="worker" <?php echo (isset($_GET['filter_user_type']) && $_GET['filter_user_type'] == 'worker') ? 'selected' : ''; ?>>Worker</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_status" class="form-label">Status</label>
                    <select class="form-select" id="filter_status" name="filter_status">
                        <option value="">All</option>
                        <option value="Pending" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Resolved" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_priority" class="form-label">Priority</label>
                    <select class="form-select" id="filter_priority" name="filter_priority">
                        <option value="">All</option>
                        <option value="Low" <?php echo (isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo (isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo (isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search_query" class="form-label">Search (ID, Email, Keyword)</label>
                    <input type="text" class="form-control" id="search_query" name="search_query" value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>" placeholder="Search tickets...">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-info me-2"><i class="bi bi-funnel-fill"></i> Apply Filters/Search</button>
                    <a href="support.php" class="btn btn-secondary"><i class="bi bi-x-circle-fill"></i> Clear Filters</a>
                </div>
            </div>
        </form>

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4>Download Reports:</h4>
            <div>
                <a href="support.php?report=csv" class="btn btn-success me-2"><i class="bi bi-filetype-csv"></i> Download CSV</a>
                <a href="support.php?report=pdf" class="btn btn-danger"><i class="bi bi-filetype-pdf"></i> Download PDF</a>
            </div>
        </div>

        <?php
        // Build the SQL query for fetching tickets (with filters and search)
        $sql = "SELECT * FROM support_tickets WHERE 1=1";
        $params = [];
        $types = "";

        if (isset($_GET['filter_user_type']) && $_GET['filter_user_type'] != '') {
            $sql .= " AND user_type = ?";
            $params[] = $_GET['filter_user_type'];
            $types .= "s";
        }
        if (isset($_GET['filter_status']) && $_GET['filter_status'] != '') {
            $sql .= " AND status = ?";
            $params[] = $_GET['filter_status'];
            $types .= "s";
        }
        if (isset($_GET['filter_priority']) && $_GET['filter_priority'] != '') {
            $sql .= " AND priority = ?";
            $params[] = $_GET['filter_priority'];
            $types .= "s";
        }
        if (isset($_GET['search_query']) && $_GET['search_query'] != '') {
            $search_term = '%' . sanitize_input($_GET['search_query']) . '%';
            $sql .= " AND (id LIKE ? OR email LIKE ? OR issue_title LIKE ? OR description LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ssss";
        }

        $sql .= " ORDER BY created_at DESC"; // Order by newest tickets first

        $stmt = $conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="card ticket-card shadow-sm <?php echo 'status-' . str_replace(' ', '-', strtolower($row['status'])); ?>">
                    <div class="card-header ticket-header">
                        <h5 class="mb-0">Ticket #<?php echo htmlspecialchars($row['id']); ?>: <?php echo htmlspecialchars($row['issue_title']); ?></h5>
                        <small class="text-muted">Submitted by: <?php echo htmlspecialchars($row['name']); ?> (<?php echo htmlspecialchars(ucfirst($row['user_type'])); ?>) - <?php echo htmlspecialchars($row['email']); ?></small><br>
                        <small class="text-muted">Priority: <strong><?php echo htmlspecialchars($row['priority']); ?></strong> | Status: <strong><?php echo htmlspecialchars($row['status']); ?></strong></small><br>
                        <small class="text-muted">Created: <?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?> | Last Updated: <?php echo date('Y-m-d H:i', strtotime($row['updated_at'])); ?></small>
                    </div>
                    <div class="card-body ticket-body">
                        <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        <?php if ($row['attachment']): ?>
                            <p><strong>Attachment:</strong> <a href="<?php echo htmlspecialchars($row['attachment']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-arrow-down-fill"></i> View Attachment</a></p>
                        <?php endif; ?>

                        <?php if ($row['admin_reply']): ?>
                            <div class="alert alert-info mt-3 border-start border-info border-4">
                                <strong>Admin Reply:</strong><br>
                                <?php echo nl2br(htmlspecialchars($row['admin_reply'])); ?>
                            </div>
                        <?php endif; ?>

                        <hr>
                        <h6>Update Ticket:</h6>
                        <form action="support.php" method="POST">
                            <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <div class="mb-3">
                                <label for="new_status_<?php echo $row['id']; ?>" class="form-label">Status</label>
                                <select class="form-select" id="new_status_<?php echo $row['id']; ?>" name="new_status">
                                    <option value="Pending" <?php echo ($row['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo ($row['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Resolved" <?php echo ($row['status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="admin_reply_<?php echo $row['id']; ?>" class="form-label">Admin Reply</label>
                                <textarea class="form-control" id="admin_reply_<?php echo $row['id']; ?>" name="admin_reply" rows="3" placeholder="Type your reply here..."><?php echo htmlspecialchars($row['admin_reply']); ?></textarea>
                            </div>
                            <button type="submit" name="update_ticket" class="btn btn-success btn-sm me-2"><i class="bi bi-save"></i> Update Ticket</button>
                            <a href="support.php?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete Ticket #<?php echo $row['id']; ?>? This action cannot be undone and will also delete the attachment file.');"><i class="bi bi-trash"></i> Delete Ticket</a>
                        </form>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='alert alert-info text-center'>No support tickets found matching your criteria.</div>";
        }
        $stmt->close();
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
<?php
// Close the database connection at the very end of the script
if ($conn) {
    $conn->close();
}
?>