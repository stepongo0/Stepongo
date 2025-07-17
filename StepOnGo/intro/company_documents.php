<?php
// company_documents.php

// --- Database Configuration ---
$servername = "localhost"; // Usually "localhost"
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "stepongo_new_db"; // The database name we created

// --- Directory for uploaded files ---
$upload_dir = "uploads/"; // Make sure this directory exists and is writable by the web server!

// --- Create uploads directory if it doesn't exist ---
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Creates directory with full permissions (adjust as needed for security)
}

// --- Database Connection ---
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Function to sanitize input ---
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// --- Handle Document Upload ---
if (isset($_POST['upload_document'])) {
    $document_name = sanitize_input($_POST['document_name']);
    $notes = sanitize_input($_POST['notes']);

    // Check if file was uploaded without errors
    if (isset($_FILES["document_file"]) && $_FILES["document_file"]["error"] == 0) {
        $allowed_ext = array("pdf" => "application/pdf", "doc" => "application/msword", "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png");
        $filename = $_FILES["document_file"]["name"];
        $filetype = $_FILES["document_file"]["type"];
        $filesize = $_FILES["document_file"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed_ext)) die("Error: Please select a valid file format.");

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) die("Error: File size is larger than the allowed limit (5MB).");

        // Verify MIME type of the file
        if (in_array($filetype, $allowed_ext)) {
            // Generate a unique file name to prevent overwriting and security issues
            $new_filename = uniqid() . "." . $ext;
            $destination_path = $upload_dir . $new_filename;

            // Check if file exists
            if (file_exists($destination_path)) {
                echo "Error: " . $filename . " already exists.";
            } else {
                if (move_uploaded_file($_FILES["document_file"]["tmp_name"], $destination_path)) {
                    // Insert file info into database
                    $stmt = $conn->prepare("INSERT INTO company_documents (document_name, file_path, notes, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->bind_param("sss", $document_name, $destination_path, $notes);

                    if ($stmt->execute()) {
                        echo "<p style='color: green;'>Document uploaded successfully!</p>";
                    } else {
                        echo "<p style='color: red;'>Error: Could not save document info to database: " . $stmt->error . "</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p style='color: red;'>Error: There was a problem uploading your file. Please try again.</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>Error: There was a problem with your file type. Please try again.</p>";
        }
    } else {
        echo "<p style='color: red;'>Error: " . $_FILES["document_file"]["error"] . "</p>";
    }
}

// --- Handle Document Status Update (Verify/Reject) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Cast to int for security
    $action = sanitize_input($_GET['action']); // 'verify' or 'reject'

    if ($action == 'verify' || $action == 'reject') {
        $status = ($action == 'verify') ? 'verified' : 'rejected';
        $stmt = $conn->prepare("UPDATE company_documents SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Document status updated to '{$status}' successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error updating document status: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// --- Handle Document Deletion ---
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id']; // Cast to int for security

    // First, get the file path to delete the actual file
    $stmt_select = $conn->prepare("SELECT file_path FROM company_documents WHERE id = ?");
    $stmt_select->bind_param("i", $delete_id);
    $stmt_select->execute();
    $stmt_select->bind_result($file_to_delete);
    $stmt_select->fetch();
    $stmt_select->close();

    if ($file_to_delete && file_exists($file_to_delete)) {
        unlink($file_to_delete); // Delete the file from the server
        echo "<p style='color: green;'>File deleted from server.</p>";
    }

    // Then, delete the record from the database
    $stmt_delete = $conn->prepare("DELETE FROM company_documents WHERE id = ?");
    $stmt_delete->bind_param("i", $delete_id);

    if ($stmt_delete->execute()) {
        echo "<p style='color: green;'>Document record deleted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error deleting document record: " . $stmt_delete->error . "</p>";
    }
    $stmt_delete->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Document Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #1f2937;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input[type="text"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
        }
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        button {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s ease;
        }
        button.primary {
            background-color: #4f46e5;
            color: white;
        }
        button.primary:hover {
            background-color: #4338ca;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            font-weight: 700;
            color: #4b5563;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status-pending { color: #f59e0b; font-weight: 600; }
        .status-verified { color: #10b981; font-weight: 600; }
        .status-rejected { color: #ef4444; font-weight: 600; }
        .action-button {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            margin-right: 5px;
            text-decoration: none; /* For links */
            display: inline-block; /* For links */
            text-align: center;
        }
        .action-verify { background-color: #10b981; color: white; }
        .action-verify:hover { background-color: #059669; }
        .action-reject { background-color: #ef4444; color: white; }
        .action-reject:hover { background-color: #dc2626; }
        .action-delete { background-color: #6b7280; color: white; }
        .action-delete:hover { background-color: #4b5563; }
        .action-view { background-color: #3b82f6; color: white; }
        .action-view:hover { background-color: #2563eb; }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="container">
        <h1 class="text-3xl font-bold text-center mb-6">Company Document Management System</h1>

        <!-- Document Upload Form -->
        <h2 class="text-2xl font-semibold mb-4">Upload New Document</h2>
        <form action="company_documents.php" method="post" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-lg shadow-sm mb-8">
            <div class="form-group">
                <label for="document_name">Document Name:</label>
                <input type="text" id="document_name" name="document_name" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div class="form-group">
                <label for="document_file">Select Document File:</label>
                <input type="file" id="document_file" name="document_file" required
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                <p class="text-sm text-gray-500 mt-1">Max 5MB. Allowed formats: PDF, DOC, DOCX, JPG, JPEG, PNG.</p>
            </div>
            <div class="form-group">
                <label for="notes">Notes (Optional):</label>
                <textarea id="notes" name="notes" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
            </div>
            <button type="submit" name="upload_document" class="primary w-full sm:w-auto px-6 py-2">Upload Document</button>
        </form>

        <!-- Document List -->
        <h2 class="text-2xl font-semibold mb-4">Uploaded Documents</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Document Name</th>
                        <th>File</th>
                        <th>Uploaded At</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all documents from the database
                    $sql = "SELECT id, document_name, file_path, uploaded_at, status, notes FROM company_documents ORDER BY uploaded_at DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        // Output data of each row
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["id"] . "</td>";
                            echo "<td>" . htmlspecialchars($row["document_name"]) . "</td>";
                            echo "<td><a href='" . htmlspecialchars($row["file_path"]) . "' target='_blank' class='action-button action-view'>View File</a></td>";
                            echo "<td>" . $row["uploaded_at"] . "</td>";
                            echo "<td class='status-" . htmlspecialchars($row["status"]) . "'>" . ucfirst(htmlspecialchars($row["status"])) . "</td>";
                            echo "<td>" . nl2br(htmlspecialchars($row["notes"])) . "</td>";
                            echo "<td>";
                            echo "<a href='company_documents.php?action=verify&id=" . $row["id"] . "' class='action-button action-verify'>Verify</a>";
                            echo "<a href='company_documents.php?action=reject&id=" . $row["id"] . "' class='action-button action-reject'>Reject</a>";
                            echo "<a href='company_documents.php?delete_id=" . $row["id"] . "' class='action-button action-delete' onclick=\"return confirm('Are you sure you want to delete this document and its file?');\">Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-4'>No documents uploaded yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Close database connection
    $conn->close();
    ?>
</body>
</html>
