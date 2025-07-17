<?php
// Configuration for Database Connection
define('DB_SERVER', 'localhost'); // Your database host
define('DB_USERNAME', 'root');   // Your database username
define('DB_PASSWORD', '');       // Your database password (empty for XAMPP default)
define('DB_NAME', 'stepongo_new_db'); // Your database name

// Establish database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages for operations (e.g., success/error)
$message = '';
$message_type = ''; // 'success' or 'danger'

// --- Handle Form Submissions (Add, Update, Delete, Mark Paid) ---

// Add New Payment
if (isset($_POST['add_payment'])) {
    $worker_id = trim($_POST['worker_id']);
    $worker_name = trim($_POST['worker_name']);
    $project_name = trim($_POST['project_name']);
    $payment_amount = trim($_POST['payment_amount']);
    $payment_method = trim($_POST['payment_method']);
    $payment_status = 'Pending'; // New records typically start as Pending
    $payment_date = trim($_POST['payment_date']);
    $remarks = trim($_POST['remarks']);

    // Basic validation (you should add more robust validation)
    if (empty($worker_id) || empty($worker_name) || empty($project_name) || empty($payment_amount) || empty($payment_method) || empty($payment_date)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        $sql = "INSERT INTO payments (worker_id, worker_name, project_name, payment_amount, payment_method, payment_status, payment_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssdssss", $worker_id, $worker_name, $project_name, $payment_amount, $payment_method, $payment_status, $payment_date, $remarks);
            if ($stmt->execute()) {
                $message = "Payment record added successfully.";
                $message_type = "success";
            } else {
                $message = "Error adding record: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "Error preparing statement: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Update Payment
if (isset($_POST['update_payment'])) {
    $id = $_POST['id'];
    $worker_id = trim($_POST['worker_id']);
    $worker_name = trim($_POST['worker_name']);
    $project_name = trim($_POST['project_name']);
    $payment_amount = trim($_POST['payment_amount']);
    $payment_method = trim($_POST['payment_method']);
    $payment_status = trim($_POST['payment_status']);
    $payment_date = trim($_POST['payment_date']);
    $remarks = trim($_POST['remarks']);

    if (empty($worker_id) || empty($worker_name) || empty($project_name) || empty($payment_amount) || empty($payment_method) || empty($payment_date)) {
        $message = "Please fill in all required fields for update.";
        $message_type = "danger";
    } else {
        $sql = "UPDATE payments SET worker_id = ?, worker_name = ?, project_name = ?, payment_amount = ?, payment_method = ?, payment_status = ?, payment_date = ?, remarks = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssdssssi", $worker_id, $worker_name, $project_name, $payment_amount, $payment_method, $payment_status, $payment_date, $remarks, $id);
            if ($stmt->execute()) {
                $message = "Payment record updated successfully.";
                $message_type = "success";
            } else {
                $message = "Error updating record: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "Error preparing statement: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Delete Payment
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $sql = "DELETE FROM payments WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Payment record deleted successfully.";
            $message_type = "success";
            // Redirect to clean URL after deletion to avoid re-deleting on refresh
            header("Location: finances.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "Error deleting record: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Error preparing statement: " . $conn->error;
        $message_type = "danger";
    }
}

// Mark Payment Status
if (isset($_GET['mark_id']) && isset($_GET['mark_status'])) {
    $id = $_GET['mark_id'];
    $new_status = $_GET['mark_status']; // 'Paid' or 'Pending'
    $sql = "UPDATE payments SET payment_status = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $new_status, $id);
        if ($stmt->execute()) {
            $message = "Payment status updated to '{$new_status}' successfully.";
            $message_type = "success";
            // Redirect to clean URL after update
            header("Location: finances.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "Error updating status: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Error preparing statement: " . $conn->error;
        $message_type = "danger";
    }
}

// Retrieve messages from URL parameters after redirect
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}

// --- Fetch Payment Records (with Filtering) ---

$sql = "SELECT * FROM payments WHERE 1"; // Start with a true condition for easy appending
$params = [];
$types = "";

// Filter by Project Name
if (isset($_GET['filter_project']) && !empty($_GET['filter_project'])) {
    $filter_project = '%' . trim($_GET['filter_project']) . '%';
    $sql .= " AND project_name LIKE ?";
    $params[] = $filter_project;
    $types .= "s";
}

// Filter by Worker Name
if (isset($_GET['filter_worker']) && !empty($_GET['filter_worker'])) {
    $filter_worker = '%' . trim($_GET['filter_worker']) . '%';
    $sql .= " AND worker_name LIKE ?";
    $params[] = $filter_worker;
    $types .= "s";
}

// Filter by Payment Status
if (isset($_GET['filter_status']) && !empty($_GET['filter_status'])) {
    $filter_status = trim($_GET['filter_status']);
    $sql .= " AND payment_status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$sql .= " ORDER BY payment_date DESC, id DESC"; // Order by date (newest first) and then ID

$payments = [];
if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    $stmt->close();
} else {
    $message = "Error fetching records: " . $conn->error;
    $message_type = "danger";
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepOnGo - Payments Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .btn-action {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">Payments Management</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="fas fa-plus"></i> Add New Payment
            </button>
            <div class="d-flex">
                <form class="d-flex me-2" method="GET" action="finances.php">
                    <input type="text" class="form-control me-2" name="filter_project" placeholder="Filter by Project Name" value="<?php echo htmlspecialchars($_GET['filter_project'] ?? ''); ?>">
                    <input type="text" class="form-control me-2" name="filter_worker" placeholder="Filter by Worker Name" value="<?php echo htmlspecialchars($_GET['filter_worker'] ?? ''); ?>">
                    <select class="form-select me-2" name="filter_status">
                        <option value="">Filter by Status</option>
                        <option value="Pending" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Paid" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    </select>
                    <button type="submit" class="btn btn-info"><i class="fas fa-filter"></i> Filter</button>
                </form>
                <a href="finances.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">All Payments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped caption-top">
                        <caption>List of Payments</caption>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Worker Name</th>
                                <th>Worker ID</th>
                                <th>Project Name</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($payments) > 0): ?>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['id'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($payment['worker_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($payment['worker_id'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($payment['project_name'] ?? ''); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($payment['payment_amount'] ?? 0, 2)); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge <?php echo (($payment['payment_status'] ?? 'Pending') == 'Paid') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                <?php echo htmlspecialchars($payment['payment_status'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($payment['payment_date'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($payment['remarks'] ?? ''); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning btn-action edit-btn"
                                                    data-bs-toggle="modal" data-bs-target="#editPaymentModal"
                                                    data-id="<?php echo htmlspecialchars($payment['id'] ?? ''); ?>"
                                                    data-workerid="<?php echo htmlspecialchars($payment['worker_id'] ?? ''); ?>"
                                                    data-workername="<?php echo htmlspecialchars($payment['worker_name'] ?? ''); ?>"
                                                    data-projectname="<?php echo htmlspecialchars($payment['project_name'] ?? ''); ?>"
                                                    data-amount="<?php echo htmlspecialchars($payment['payment_amount'] ?? ''); ?>"
                                                    data-method="<?php echo htmlspecialchars($payment['payment_method'] ?? ''); ?>"
                                                    data-status="<?php echo htmlspecialchars($payment['payment_status'] ?? ''); ?>"
                                                    data-date="<?php echo htmlspecialchars($payment['payment_date'] ?? ''); ?>"
                                                    data-remarks="<?php echo htmlspecialchars($payment['remarks'] ?? ''); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="finances.php?delete_id=<?php echo htmlspecialchars($payment['id'] ?? ''); ?>"
                                               class="btn btn-sm btn-danger btn-action"
                                               onclick="return confirm('Are you sure you want to delete this payment record?');">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                            <?php if (($payment['payment_status'] ?? 'Pending') == 'Pending'): ?>
                                                <a href="finances.php?mark_id=<?php echo htmlspecialchars($payment['id'] ?? ''); ?>&mark_status=Paid"
                                                   class="btn btn-sm btn-success btn-action"
                                                   onclick="return confirm('Mark this payment as Paid?');">
                                                    <i class="fas fa-check-circle"></i> Mark Paid
                                                </a>
                                            <?php else: ?>
                                                <a href="finances.php?mark_id=<?php echo htmlspecialchars($payment['id'] ?? ''); ?>&mark_status=Pending"
                                                   class="btn btn-sm btn-secondary btn-action"
                                                   onclick="return confirm('Mark this payment as Pending?');">
                                                    <i class="fas fa-clock"></i> Mark Pending
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">No payment records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="export.php?format=excel" class="btn btn-success me-2"><i class="fas fa-file-excel"></i> Export to Excel</a>
                    <a href="export.php?format=pdf" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Export to PDF</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add New Payment Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="finances.php" method="POST">
                        <div class="mb-3">
                            <label for="addWorkerId" class="form-label">Worker ID:</label>
                            <input type="text" class="form-control" id="addWorkerId" name="worker_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="addWorkerName" class="form-label">Worker Name:</label>
                            <input type="text" class="form-control" id="addWorkerName" name="worker_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="addProjectName" class="form-label">Project Name:</label>
                            <input type="text" class="form-control" id="addProjectName" name="project_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="addPaymentAmount" class="form-label">Payment Amount:</label>
                            <input type="number" class="form-control" id="addPaymentAmount" name="payment_amount" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="addPaymentMethod" class="form-label">Payment Method:</label>
                            <select class="form-select" id="addPaymentMethod" name="payment_method" required>
                                <option value="Bank">Bank</option>
                                <option value="UPI">UPI</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addPaymentDate" class="form-label">Payment Date:</label>
                            <input type="date" class="form-control" id="addPaymentDate" name="payment_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="addRemarks" class="form-label">Remarks:</label>
                            <textarea class="form-control" id="addRemarks" name="remarks" rows="3"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="add_payment" class="btn btn-primary">Add Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="finances.php" method="POST">
                        <input type="hidden" id="editPaymentId" name="id">
                        <div class="mb-3">
                            <label for="editWorkerId" class="form-label">Worker ID:</label>
                            <input type="text" class="form-control" id="editWorkerId" name="worker_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="editWorkerName" class="form-label">Worker Name:</label>
                            <input type="text" class="form-control" id="editWorkerName" name="worker_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProjectName" class="form-label">Project Name:</label>
                            <input type="text" class="form-control" id="editProjectName" name="project_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPaymentAmount" class="form-label">Payment Amount:</label>
                            <input type="number" class="form-control" id="editPaymentAmount" name="payment_amount" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPaymentMethod" class="form-label">Payment Method:</label>
                            <select class="form-select" id="editPaymentMethod" name="payment_method" required>
                                <option value="Bank">Bank</option>
                                <option value="UPI">UPI</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editPaymentStatus" class="form-label">Payment Status:</label>
                            <select class="form-select" id="editPaymentStatus" name="payment_status" required>
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editPaymentDate" class="form-label">Payment Date:</label>
                            <input type="date" class="form-control" id="editPaymentDate" name="payment_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRemarks" class="form-label">Remarks:</label>
                            <textarea class="form-control" id="editRemarks" name="remarks" rows="3"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update_payment" class="btn btn-warning">Update Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        // JavaScript to populate the Edit Modal when the Edit button is clicked
        document.addEventListener('DOMContentLoaded', function () {
            var editPaymentModal = document.getElementById('editPaymentModal');
            editPaymentModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget; // Button that triggered the modal

                // Get data attributes, providing empty string as fallback
                var id = button.getAttribute('data-id') || '';
                var workerId = button.getAttribute('data-workerid') || '';
                var workerName = button.getAttribute('data-workername') || '';
                var projectName = button.getAttribute('data-projectname') || '';
                var amount = button.getAttribute('data-amount') || '';
                var method = button.getAttribute('data-method') || '';
                var status = button.getAttribute('data-status') || '';
                var date = button.getAttribute('data-date') || '';
                var remarks = button.getAttribute('data-remarks') || '';

                // Get modal input elements
                var modalIdInput = editPaymentModal.querySelector('#editPaymentId');
                var modalWorkerIdInput = editPaymentModal.querySelector('#editWorkerId');
                var modalWorkerNameInput = editPaymentModal.querySelector('#editWorkerName');
                var modalProjectNameInput = editPaymentModal.querySelector('#editProjectName');
                var modalPaymentAmountInput = editPaymentModal.querySelector('#editPaymentAmount');
                var modalPaymentMethodSelect = editPaymentModal.querySelector('#editPaymentMethod');
                var modalPaymentStatusSelect = editPaymentModal.querySelector('#editPaymentStatus');
                var modalPaymentDateInput = editPaymentModal.querySelector('#editPaymentDate');
                var modalRemarksTextarea = editPaymentModal.querySelector('#editRemarks');

                // Populate modal inputs
                modalIdInput.value = id;
                modalWorkerIdInput.value = workerId;
                modalWorkerNameInput.value = workerName;
                modalProjectNameInput.value = projectName;
                modalPaymentAmountInput.value = amount;
                modalPaymentMethodSelect.value = method;
                modalPaymentStatusSelect.value = status;
                modalPaymentDateInput.value = date;
                modalRemarksTextarea.value = remarks;
            });
        });
    </script>
</body>
</html>