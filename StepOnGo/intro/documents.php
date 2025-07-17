<?php
// documents.php - Document Verification Panel (All-in-one file)
// IMPORTANT: THIS PANEL CURRENTLY HAS NO AUTHENTICATION/AUTHORIZATION.
// It is DIRECTLY ACCESSIBLE. This is a MAJOR SECURITY VULNERABILITY
// and must be addressed before deployment to a production environment.
// Connects to 'stepongo_new_db'

// --- PHP Error Display (TEMPORARY - FOR DEBUGGING ONLY) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUGGING SETTINGS ---

// Set header for JSON response only if an API action is requested.
// Otherwise, it will serve the HTML page.
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
}

// --- 1. Configuration (Database) ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // <--- Your MySQL username (e.g., 'root')
define('DB_PASS', '');           // <--- Your MySQL password (often empty '' for XAMPP/WAMP default root)
define('DB_NAME', 'stepongo_new_db');   // <--- DATABASE NAME CONFIGURED AS 'stepongo_new_db'

/**
 * Establishes and returns a new MySQL database connection.
 * Exits if connection fails.
 * @return mysqli The database connection object.
 */
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        // If API call, return JSON error; otherwise, display simple HTML error
        if (isset($_GET['action'])) {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Database connection failed. Please check server logs.']);
        } else {
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Error</title><style>body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px; }</style></head><body><h1>Database Connection Error</h1><p>Could not connect to the database. Please check your `DB_HOST`, `DB_USER`, `DB_PASS`, and `DB_NAME` settings in `documents.php` and ensure your MySQL server is running.</p><p>Error: ' . $conn->connect_error . '</p></body></html>';
        }
        exit();
    }
    return $conn;
}

// --- 2. Document Class ---
class Document {
    private $conn;
    private $table_name = "labour_documents";

    public function __construct() {
        $this->conn = getDbConnection();
    }

    public function getDocuments($workerId = null, $documentType = null, $status = null, $uploadDate = null) {
        $query = "SELECT document_id, labour_id, document_type, file_path, status, admin_comments, upload_timestamp, verified_timestamp, updated_timestamp FROM " . $this->table_name . " WHERE 1=1";
        $params = [];
        $types = '';

        if ($workerId !== null) {
            $query .= " AND labour_id = ?";
            $params[] = $workerId;
            $types .= 'i';
        }
        if ($documentType !== null) {
            $query .= " AND document_type = ?";
            $params[] = $documentType;
            $types .= 's';
        }
        if ($status !== null) {
            $query .= " AND status = ?";
            $params[] = $status;
            $types .= 's';
        }
        if ($uploadDate !== null) {
            $query .= " AND DATE(upload_timestamp) = ?";
            $params[] = $uploadDate;
            $types .= 's';
        }

        $query .= " ORDER BY upload_timestamp DESC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Failed to prepare getDocuments statement: " . $this->conn->error);
            return false;
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            error_log("Failed to get result from getDocuments statement: " . $stmt->error);
            return false;
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getDocumentById($documentId) {
        $query = "SELECT document_id, labour_id, document_type, file_path, status, admin_comments, upload_timestamp, verified_timestamp, updated_timestamp FROM " . $this->table_name . " WHERE document_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Failed to prepare getDocumentById statement: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $documentId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            error_log("Failed to get result from getDocumentById statement: " . $stmt->error);
            return false;
        }
        return $result->fetch_assoc();
    }

    public function updateDocumentStatus($documentId, $status, $adminComments = null) {
        $currentTimestamp = date('Y-m-d H:i:s');
        $adminComments = $adminComments === '' ? null : $adminComments;

        $query = "UPDATE " . $this->table_name . " SET status = ?, admin_comments = ?, verified_timestamp = ?, updated_timestamp = ? WHERE document_id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Failed to prepare updateDocumentStatus statement: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssssi", $status, $adminComments, $currentTimestamp, $currentTimestamp, $documentId);
        $success = $stmt->execute();
        if (!$success) {
            error_log("Failed to execute updateDocumentStatus: " . $stmt->error);
        }
        return $success;
    }

    public function createOrUpdateDocument($labourId, $documentType, $filePath) {
        $query = "SELECT document_id FROM " . $this->table_name . " WHERE labour_id = ? AND document_type = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) { error_log("Failed to prepare createOrUpdateDocument (select) statement: " . $this->conn->error); return false; }
        $stmt->bind_param("is", $labourId, $documentType);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingDocument = $result->fetch_assoc();
        $stmt->close();

        if ($existingDocument) {
            $updateQuery = "UPDATE " . $this->table_name . " SET file_path = ?, status = 'Pending', admin_comments = NULL, verified_timestamp = NULL, upload_timestamp = CURRENT_TIMESTAMP() WHERE document_id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            if ($updateStmt === false) { error_log("Failed to prepare createOrUpdateDocument (update) statement: " . $this->conn->error); return false; }
            $updateStmt->bind_param("si", $filePath, $existingDocument['document_id']);
            $success = $updateStmt->execute();
            if (!$success) { error_log("Failed to execute createOrUpdateDocument (update): " . $updateStmt->error); }
            $updateStmt->close();
            return $success;
        } else {
            $insertQuery = "INSERT INTO " . $this->table_name . " (labour_id, document_type, file_path, status) VALUES (?, ?, ?, 'Pending')";
            $insertStmt = $this->conn->prepare($insertQuery);
            if ($insertStmt === false) { error_log("Failed to prepare createOrUpdateDocument (insert) statement: " . $this->conn->error); return false; }
            $insertStmt->bind_param("iss", $labourId, $documentType, $filePath);
            $success = $insertStmt->execute();
            if (!$success) { error_log("Failed to execute createOrUpdateDocument (insert): " . $insertStmt->error); }
            $insertStmt->close();
            return $success;
        }
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// --- 3. Main API Request Handling (Conditional) ---
$action = $_GET['action'] ?? ''; // Default action to empty string

// If an API action is requested, process it and exit.
if ($action) {
    $document = new Document();
    $allowedDocumentTypes = ['Aadhar', 'PAN', 'Certificate'];
    $allowedStatuses = ['Pending', 'Verified', 'Rejected'];

    switch ($action) {
        case 'get_documents':
            $workerId = filter_var($_GET['worker_id'] ?? null, FILTER_VALIDATE_INT);
            $workerId = ($workerId === false || $workerId <= 0) ? null : $workerId;

            $documentType = $_GET['document_type'] ?? null;
            $documentType = ($documentType !== null && in_array($documentType, $allowedDocumentTypes)) ? $documentType : null;

            $status = $_GET['status'] ?? null;
            $status = ($status !== null && in_array($status, $allowedStatuses)) ? $status : null;

            $uploadDate = $_GET['upload_date'] ?? null;
            $uploadDate = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $uploadDate)) ? $uploadDate : null;

            $documents = $document->getDocuments($workerId, $documentType, $status, $uploadDate);

            if ($documents === false) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to fetch documents.']);
            } else {
                echo json_encode(['success' => true, 'data' => $documents]);
            }
            break;

        case 'get_document_by_id':
            $documentId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
            if ($documentId === false || $documentId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid Document ID.']);
                exit();
            }
            $doc = $document->getDocumentById($documentId);
            if ($doc === false) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to fetch document details.']);
            } elseif ($doc === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Document not found.']);
            } else {
                echo json_encode(['success' => true, 'data' => $doc]);
            }
            break;

        case 'update_document_status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405); // Method Not Allowed
                echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
                exit();
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
                exit();
            }

            $documentId = filter_var($data['document_id'] ?? null, FILTER_VALIDATE_INT);
            $newStatus = $data['status'] ?? null;
            $adminComments = trim($data['comments'] ?? '');

            if ($documentId === false || $documentId <= 0 || !in_array($newStatus, $allowedStatuses)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid parameters for status update.']);
                exit();
            }

            if ($document->updateDocumentStatus($documentId, $newStatus, $adminComments)) {
                echo json_encode(['success' => true, 'message' => 'Document status updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update document status.']);
            }
            break;

        case 'create_or_update_document':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405); // Method Not Allowed
                echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
                exit();
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
                exit();
            }

            $labourId = filter_var($data['labour_id'] ?? null, FILTER_VALIDATE_INT);
            $documentType = $data['document_type'] ?? null;
            $filePath = $data['file_path'] ?? null; // In a real system, file uploads would be handled differently

            if ($labourId === false || $labourId <= 0 || !in_array($documentType, $allowedDocumentTypes) || empty($filePath)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid parameters for document creation/update.']);
                exit();
            }

            if ($document->createOrUpdateDocument($labourId, $documentType, $filePath)) {
                echo json_encode(['success' => true, 'message' => 'Document entry created/updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create/update document entry.']);
            }
            break;

        default:
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Unknown API action.']);
            break;
    }
    exit(); // Exit after API response
}
// If no 'action' parameter, render the HTML page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Verification Panel - StepOnGo</title>
    <style>
        /* style.css content goes here */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
            color: #333;
            background-color: #f4f7f6;
        }

        header {
            background-color: #e0f2f7;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1, h2, h3 {
            color: #0056b3;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        #message-container {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            display: none; /* Hidden by default, shown by JS */
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Forms and Inputs */
        form {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #eee;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: calc(100% - 20px); /* Adjusting for padding */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box; /* Include padding in width calculation */
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button {
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.2s ease, transform 0.1s ease;
            border: none;
            outline: none;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: white;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        button[type="button"] { /* For "Clear Filters" and "Back to Dashboard" */
            background-color: #6c757d;
            color: white;
        }

        button[type="button"]:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: hidden; /* Ensures rounded corners apply to content */
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
        }

        tr:nth-child(even) {
            background-color: #f6f6f6;
        }

        tr:hover {
            background-color: #e2f0fb;
        }

        .status-pending { color: orange; font-weight: bold; }
        .status-verified { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }

        /* Document Preview */
        .document-preview {
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 20px;
            max-width: 800px;
            height: 600px; /* Adjust as needed for preview area */
            overflow: auto;
            text-align: center;
            background-color: #fff;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .document-preview img,
        .document-preview iframe {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .document-preview iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .document-preview p {
            color: #666;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Document Verification Panel</h1>
        <p>Welcome to the Document Panel! You can view and manage documents here without any login.</p>
    </header>

    <main>
        <div id="message-container"></div>

        <section id="dashboard-view">
            <h2>Filter Documents</h2>
            <form id="filter-form">
                <label for="worker_id">Worker ID:</label>
                <input type="text" id="worker_id" name="worker_id" placeholder="Enter Worker ID">

                <label for="document_type">Document Type:</label>
                <select id="document_type" name="document_type">
                    <option value="">All</option>
                    <option value="Aadhar">Aadhar</option>
                    <option value="PAN">PAN</option>
                    <option value="Certificate">Certificate</option>
                </select>

                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="Pending">Pending Verification</option>
                    <option value="Verified">Verified</option>
                    <option value="Rejected">Rejected</option>
                </select>

                <label for="upload_date">Upload Date:</label>
                <input type="date" id="upload_date" name="upload_date">

                <button type="submit">Apply Filters</button>
                <button type="button" id="clear-filters">Clear Filters</button>
            </form>

            <hr>

            <h2>All Uploaded Documents</h2>
            <div id="document-list-container">
                <table>
                    <thead>
                        <tr>
                            <th>Doc ID</th>
                            <th>Worker ID</th>
                            <th>Document Type</th>
                            <th>Status</th>
                            <th>Uploaded On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="documents-table-body">
                        </tbody>
                </table>
                <p id="no-documents-message" style="display: none;">No documents found with the current filters.</p>
            </div>
        </section>

        <section id="view-document-section" style="display: none;">
            <button id="back-to-dashboard">Back to Dashboard</button>
            <h2>Document Details <span id="document-id-display"></span></h2>
            <div id="document-details-content">
                </div>

            <form id="update-status-form">
                <h3>Update Document Status</h3>
                <input type="hidden" id="update-document-id">
                <label for="update-status">Set Status:</label>
                <select id="update-status" name="status" required>
                    <option value="">Select Status</option>
                    <option value="Verified">Verified</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Pending">Pending (Revert)</option>
                </select>
                <br>
                <label for="update-comments">Admin Comments (Optional):</label>
                <textarea id="update-comments" name="comments" rows="4" placeholder="Enter comments here..."></textarea>
                <br>
                <button type="submit">Update Status</button>
            </form>
        </section>
    </main>

    <script>
        // script.js content goes here
        document.addEventListener('DOMContentLoaded', () => {
            const dashboardView = document.getElementById('dashboard-view');
            const viewDocumentSection = document.getElementById('view-document-section');
            const documentsTableBody = document.getElementById('documents-table-body');
            const noDocumentsMessage = document.getElementById('no-documents-message');
            const filterForm = document.getElementById('filter-form');
            const clearFiltersBtn = document.getElementById('clear-filters');
            const backToDashboardBtn = document.getElementById('back-to-dashboard');
            const updateStatusForm = document.getElementById('update-status-form');
            const documentIdDisplay = document.getElementById('document-id-display');
            const documentDetailsContent = document.getElementById('document-details-content');
            const updateDocumentIdInput = document.getElementById('update-document-id');
            const updateStatusSelect = document.getElementById('update-status');
            const updateCommentsTextarea = document.getElementById('update-comments');
            const messageContainer = document.getElementById('message-container');

            // Since everything is in documents.php, the API_URL is the same file
            const API_URL = 'documents.php'; 

            // --- Helper Functions ---

            /**
             * Displays a message to the user.
             * @param {string} message - The message text.
             * @param {string} type - 'success' or 'error'.
             */
            function showMessage(message, type) {
                messageContainer.textContent = message;
                messageContainer.className = ''; // Clear existing classes
                messageContainer.classList.add(type);
                messageContainer.style.display = 'block';
                // Hide message after 5 seconds
                setTimeout(() => {
                    messageContainer.style.display = 'none';
                }, 5000);
            }

            /**
             * Toggles between dashboard and single document view.
             * @param {boolean} showDashboard - True to show dashboard, false to show document view.
             */
            function toggleViews(showDashboard) {
                if (showDashboard) {
                    dashboardView.style.display = 'block';
                    viewDocumentSection.style.display = 'none';
                } else {
                    dashboardView.style.display = 'none';
                    viewDocumentSection.style.display = 'block';
                }
            }

            /**
             * Renders the list of documents in the table.
             * @param {Array} documents - Array of document objects.
             */
            function renderDocumentList(documents) {
                documentsTableBody.innerHTML = ''; // Clear existing rows
                if (documents.length === 0) {
                    noDocumentsMessage.style.display = 'block';
                    return;
                }
                noDocumentsMessage.style.display = 'none';

                documents.forEach(doc => {
                    const row = documentsTableBody.insertRow();
                    row.innerHTML = `
                        <td>${doc.document_id}</td>
                        <td>${doc.labour_id}</td>
                        <td>${doc.document_type}</td>
                        <td><strong class="status-${doc.status.toLowerCase()}">${doc.status}</strong></td>
                        <td>${doc.upload_timestamp}</td>
                        <td>
                            <button class="view-details-btn" data-id="${doc.document_id}">View/Verify</button>
                        </td>
                    `;
                });

                // Add event listeners to newly created "View/Verify" buttons
                document.querySelectorAll('.view-details-btn').forEach(button => {
                    button.addEventListener('click', (event) => {
                        const documentId = event.target.dataset.id;
                        loadDocumentDetails(documentId);
                    });
                });
            }

            /**
             * Renders detailed information for a single document.
             * @param {Object} docData - The document object.
             */
            function renderDocumentDetails(docData) {
                documentIdDisplay.textContent = `(ID: ${docData.document_id})`;
                updateDocumentIdInput.value = docData.document_id;
                updateStatusSelect.value = docData.status;
                updateCommentsTextarea.value = docData.admin_comments || '';

                let previewHtml = '';
                const fileExtension = docData.file_path.split('.').pop().toLowerCase();
                let mimeType = '';

                // Determine MIME type for a basic preview.
                // NOTE: This is a client-side guess. A robust system would
                // serve files with correct Content-Type headers from the server.
                switch (fileExtension) {
                    case 'jpg':
                    case 'jpeg':
                        mimeType = 'image/jpeg'; break;
                    case 'png':
                        mimeType = 'image/png'; break;
                    case 'gif':
                        mimeType = 'image/gif'; break;
                    case 'pdf':
                        mimeType = 'application/pdf'; break;
                    default:
                        mimeType = 'application/octet-stream';
                }

                if (mimeType.startsWith('image/')) {
                    previewHtml = `<img src="${docData.file_path}" alt="Document Image">`;
                } else if (mimeType === 'application/pdf') {
                    // For PDFs, use iframe. #toolbar=0&navpanes=0&scrollbar=0 hides default PDF viewer UI
                    previewHtml = `
                        <iframe src="${docData.file_path}#toolbar=0&navpanes=0&scrollbar=0" width="100%" height="100%"></iframe>
                        <p><a href="${docData.file_path}" target="_blank">View PDF in new tab</a></p>
                    `;
                } else {
                    previewHtml = `
                        <p>No preview available for this file type.</p>
                        <p><a href="${docData.file_path}" target="_blank" download>Download File</a></p>
                    `;
                }

                documentDetailsContent.innerHTML = `
                    <p><strong>Worker ID:</strong> ${docData.labour_id}</p>
                    <p><strong>Document Type:</strong> ${docData.document_type}</p>
                    <p><strong>Current Status:</strong> <strong class="status-${docData.status.toLowerCase()}">${docData.status}</strong></p>
                    <p><strong>Uploaded On:</strong> ${docData.upload_timestamp}</p>
                    ${docData.verified_timestamp ? `<p><strong>Verified/Rejected On:</strong> ${docData.verified_timestamp}</p>` : ''}
                    ${docData.admin_comments ? `<p><strong>Admin Comments:</strong> ${docData.admin_comments.replace(/\n/g, '<br>')}</p>` : ''}

                    <div class="document-preview">
                        ${previewHtml}
                    </div>
                `;

                toggleViews(false); // Show the single document view
            }

            // --- API Calls ---

            /**
             * Fetches documents based on filters.
             */
            async function fetchDocuments() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value) { // Only add non-empty values
                        params.append(key, value);
                    }
                }

                try {
                    const response = await fetch(`${API_URL}?action=get_documents&${params.toString()}`);
                    const data = await response.json();

                    if (data.success) {
                        renderDocumentList(data.data);
                    } else {
                        showMessage(data.message || 'Failed to load documents.', 'error');
                        renderDocumentList([]); // Clear table on error
                    }
                } catch (error) {
                    console.error('Error fetching documents:', error);
                    showMessage('An error occurred while fetching documents. Please try again.', 'error');
                    renderDocumentList([]);
                }
            }

            /**
             * Loads and displays details for a single document.
             * @param {number} documentId - The ID of the document to load.
             */
            async function loadDocumentDetails(documentId) {
                try {
                    const response = await fetch(`${API_URL}?action=get_document_by_id&id=${documentId}`);
                    const data = await response.json();

                    if (data.success) {
                        renderDocumentDetails(data.data);
                    } else {
                        showMessage(data.message || 'Document not found.', 'error');
                        toggleViews(true); // Go back to dashboard if document not found
                    }
                } catch (error) {
                    console.error('Error loading document details:', error);
                    showMessage('An error occurred while loading document details.', 'error');
                    toggleViews(true); // Go back to dashboard on error
                }
            }

            /**
             * Updates the status and comments of a document.
             * @param {number} documentId - The ID of the document.
             * @param {string} status - The new status.
             * @param {string} comments - Admin comments.
             */
            async function updateDocumentStatus(documentId, status, comments) {
                try {
                    const response = await fetch(`${API_URL}?action=update_document_status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            document_id: documentId,
                            status: status,
                            comments: comments
                        })
                    });
                    const data = await response.json();

                    if (data.success) {
                        showMessage(data.message, 'message');
                        loadDocumentDetails(documentId); // Reload details to reflect changes
                    } else {
                        showMessage(data.message || 'Failed to update document status.', 'error');
                    }
                } catch (error) {
                    console.error('Error updating document status:', error);
                    showMessage('An error occurred while updating document status.', 'error');
                }
            }

            // --- Event Listeners ---

            filterForm.addEventListener('submit', (event) => {
                event.preventDefault(); // Prevent full page reload
                fetchDocuments();
            });

            clearFiltersBtn.addEventListener('click', () => {
                filterForm.reset(); // Clear all form fields
                fetchDocuments(); // Fetch all documents again
            });

            backToDashboardBtn.addEventListener('click', () => {
                toggleViews(true); // Show dashboard
                fetchDocuments(); // Reload documents for dashboard
            });

            updateStatusForm.addEventListener('submit', (event) => {
                event.preventDefault(); // Prevent full page reload
                const documentId = updateDocumentIdInput.value;
                const newStatus = updateStatusSelect.value;
                const adminComments = updateCommentsTextarea.value;
                updateDocumentStatus(documentId, newStatus, adminComments);
            });

            // Initial load
            fetchDocuments();
        });
    </script>
</body>
</html>