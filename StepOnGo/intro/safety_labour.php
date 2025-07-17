<?php
// safety_labour.php - Safety Guidelines Admin Panel (Adapted from assignment.php)

// --- PHP Error Display (TEMPORARY - FOR DEBUGGING ONLY) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUGGING SETTINGS ---

$conn_local = null;
$conn_opened_by_safety_script = false; // Flag to track if this script opened the connection

// Check if a global connection exists (from dashboard.php or similar parent script)
if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli && !$GLOBALS['conn']->connect_error) {
    $conn_local = $GLOBALS['conn']; // Use the connection from the parent script
} else {
    // If no global connection, this script is likely accessed directly (e.g., AJAX or standalone)
    // Establish its own database connection
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');   // Your DB username
    define('DB_PASSWORD', '');       // Your DB password
    define('DB_NAME', 'stepongo_new_db'); // Your database name

    $conn_local = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn_local->connect_error) {
        // If it's an AJAX request, send JSON error. Otherwise, die.
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn_local->connect_error]);
            exit();
        } else {
            die("Database connection failed for safety guidelines panel: " . $conn_local->connect_error);
        }
    }
    $conn_opened_by_safety_script = true;
}

// --- Function to sanitize input using the provided connection ---
function sanitize_input_for_safety($data, $connection) {
    if (is_array($data)) {
        return array_map(function($item) use ($connection) {
            return htmlspecialchars(stripslashes(trim($connection->real_escape_string($item))));
        }, $data);
    }
    return htmlspecialchars(stripslashes(trim($connection->real_escape_string($data))));
}

// --- Constants and Definitions ---

// List of 22 official Indian languages + English (ISO 639-1 codes or similar)
$languages = [
    'en' => 'English', 'hi' => 'Hindi', 'bn' => 'Bengali', 'te' => 'Telugu',
    'mr' => 'Marathi', 'ta' => 'Tamil', 'ur' => 'Urdu', 'gu' => 'Gujarati',
    'kn' => 'Kannada', 'ml' => 'Malayalam', 'or' => 'Odia', 'pa' => 'Punjabi',
    'as' => 'Assamese', 'ks' => 'Kashmiri', 'sd' => 'Sindhi', 'sa' => 'Sanskrit',
    'ne' => 'Nepali', 'mni' => 'Manipuri (Meitei)', 'kok' => 'Konkani', 'brx' => 'Bodo',
    'doi' => 'Dogri', 'mai' => 'Maithili', 'sat' => 'Santali'
];

// Safety Categories
$categories = [
    'Electrical', 'Height Work', 'PPE', 'Excavation', 'Scaffolding',
    'Fire Safety', 'First Aid', 'Hazardous Materials', 'Machinery',
    'Confined Space', 'Road Safety', 'Environmental', 'General'
];

// Directory for uploaded video files
$upload_dir = 'uploads/videos/';
$max_file_size = 25 * 1024 * 1024; // 25 MB (adjust as needed)
$allowed_video_ext = ['mp4', 'avi', 'mov', 'webm']; // Allowed video extensions

// Create upload directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // 0777 grants full permissions, adjust for production
}

$message = ''; // For success messages
$message_type = ''; // 'success' or 'error'

// --- Handle Form Submissions (Add/Update/Delete) ---

// Handle AJAX Delete Guideline
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_guideline') {
    header('Content-Type: application/json');
    $id = sanitize_input_for_safety($_POST['id'], $conn_local);

    if (!empty($id) && is_numeric($id)) {
        // Fetch guideline data to get video file path before deletion
        $stmt_select = $conn_local->prepare("SELECT video_file FROM safety_guidelines WHERE id = ?");
        if ($stmt_select) {
            $stmt_select->bind_param("i", $id);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            $guideline_to_delete = $result->fetch_assoc();
            $stmt_select->close();

            if ($guideline_to_delete) {
                // Delete associated video file if it exists on the server
                if (!empty($guideline_to_delete['video_file']) && file_exists($guideline_to_delete['video_file'])) {
                    if (!unlink($guideline_to_delete['video_file'])) {
                        error_log("Failed to delete video file: " . $guideline_to_delete['video_file']);
                        // Continue with DB deletion even if file deletion fails
                    }
                }

                // Delete guideline from database
                $stmt_delete = $conn_local->prepare("DELETE FROM safety_guidelines WHERE id = ?");
                if ($stmt_delete) {
                    $stmt_delete->bind_param("i", $id);
                    if ($stmt_delete->execute()) {
                        echo json_encode(['status' => 'success', 'message' => 'Safety guideline deleted successfully!']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Error deleting guideline: ' . $stmt_delete->error]);
                    }
                    $stmt_delete->close();
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Database prepare error (delete): ' . $conn_local->error]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Guideline not found for deletion.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database prepare error (select for delete): ' . $conn_local->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid guideline ID for deletion.']);
    }

    // Close connection ONLY if this script opened it for a direct AJAX call
    if ($conn_opened_by_safety_script) {
        $conn_local->close();
    }
    exit(); // Important: Exit after sending JSON response for AJAX requests
}

// Handle Add or Update Guideline (regular POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['action']) && ($_POST['action'] == 'add_guideline' || $_POST['action'] == 'update_guideline'))) {
    $action = sanitize_input_for_safety($_POST['action'], $conn_local);

    $title = sanitize_input_for_safety($_POST['title'], $conn_local);
    $description = sanitize_input_for_safety($_POST['description'], $conn_local);
    $language_code = sanitize_input_for_safety($_POST['language_code'], $conn_local);
    $category = sanitize_input_for_safety($_POST['category'], $conn_local);
    $video_link = sanitize_input_for_safety($_POST['video_link'], $conn_local);
    $video_file_path = ''; // Initialize video file path

    // Basic validation for required fields
    if (empty($title) || empty($description) || empty($language_code) || empty($category)) {
        $message = "Please fill all required fields (Title, Description, Language, Category).";
        $message_type = "error";
    } else {
        // --- Handle video file upload ---
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['video_file']['tmp_name'];
            $file_name = $_FILES['video_file']['name'];
            $file_size = $_FILES['video_file']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_size > $max_file_size) {
                $message = "Uploaded video file is too large. Max size is " . ($max_file_size / (1024 * 1024)) . " MB.";
                $message_type = "error";
            } elseif (!in_array($file_ext, $allowed_video_ext)) {
                $message = "Invalid video file type. Only " . implode(', ', $allowed_video_ext) . " are allowed.";
                $message_type = "error";
            } else {
                $new_file_name = uniqid('video_', true) . '.' . $file_ext;
                $video_file_path = $upload_dir . $new_file_name;

                if (!move_uploaded_file($file_tmp, $video_file_path)) {
                    $message = "Failed to upload video file. Check directory permissions.";
                    $message_type = "error";
                    $video_file_path = ''; // Clear path if upload fails
                }
            }
        }

        // If no errors from validation or file upload, proceed with DB operation
        if (empty($message_type)) {
            if ($action == 'add_guideline') {
                $stmt = $conn_local->prepare("INSERT INTO safety_guidelines (title, description, language_code, category, video_file, video_link) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    $message = "DB Prepare Error (add): " . $conn_local->error;
                    $message_type = "error";
                } else {
                    $stmt->bind_param("ssssss", $title, $description, $language_code, $category, $video_file_path, $video_link);
                    if ($stmt->execute()) {
                        $message = "Safety guideline added successfully! 🎉";
                        $message_type = "success";
                        // Clear $_POST for form reset (since it's a full page load)
                        $_POST = array();
                    } else {
                        $message = "Error adding guideline: " . $stmt->error;
                        $message_type = "error";
                    }
                    $stmt->close();
                }
            } elseif ($action == 'update_guideline') {
                $id = (int)$_POST['id']; // Get the ID of the guideline to update
                $current_guideline = null;
                $stmt_fetch_old = $conn_local->prepare("SELECT video_file FROM safety_guidelines WHERE id = ?");
                if ($stmt_fetch_old) {
                    $stmt_fetch_old->bind_param("i", $id);
                    $stmt_fetch_old->execute();
                    $result_old = $stmt_fetch_old->get_result();
                    $current_guideline = $result_old->fetch_assoc();
                    $stmt_fetch_old->close();
                }

                if ($current_guideline) {
                    // If a new video file was uploaded, delete the old one
                    if (!empty($video_file_path) && !empty($current_guideline['video_file']) && file_exists($current_guideline['video_file'])) {
                        unlink($current_guideline['video_file']); // Delete old file
                    } elseif (empty($video_file_path)) {
                        // If no new file uploaded, retain the old video_file path
                        $video_file_path = $current_guideline['video_file'];
                    }

                    // Update existing guideline
                    $stmt = $conn_local->prepare("UPDATE safety_guidelines SET title = ?, description = ?, language_code = ?, category = ?, video_file = ?, video_link = ? WHERE id = ?");
                    if ($stmt === false) {
                        $message = "DB Prepare Error (update): " . $conn_local->error;
                        $message_type = "error";
                    } else {
                        $stmt->bind_param("ssssssi", $title, $description, $language_code, $category, $video_file_path, $video_link, $id);
                        if ($stmt->execute()) {
                            $message = "Safety guideline updated successfully! ✅";
                            $message_type = "success";
                        } else {
                            $message = "Error updating guideline: " . $stmt->error;
                            $message_type = "error";
                        }
                        $stmt->close();
                    }
                } else {
                    $message = "Guideline not found for update.";
                    $message_type = "error";
                }
            }
        }
    }
}

// --- Fetch data for display ---
$edit_guideline = null; // Variable to hold guideline data if in edit mode
// Check if an edit action is requested via GET parameters (e.g., from an 'Edit' button click)
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_to_edit = (int)$_GET['id'];
    $stmt = $conn_local->prepare("SELECT * FROM safety_guidelines WHERE id = ?");
    if ($stmt === false) {
        $message = "DB Prepare Error (fetch for edit): " . $conn_local->error;
        $message_type = "error";
    } else {
        $stmt->bind_param("i", $id_to_edit);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_guideline = $result->fetch_assoc();
        $stmt->close();
        if (!$edit_guideline) {
            $message = "Guideline not found for editing.";
            $message_type = "error";
        }
    }
}

// Retrieve messages and errors passed from redirection or set by direct POST
// (This applies if you're NOT using AJAX for adds/updates and doing a full page redirect)
// Since we're handling POST directly and setting messages, this block might be redundant
// if there are no external redirects to this page with 'message'/'error' in GET.
// For consistency with assignment.php, we keep direct message setting.
// If you implement a redirect after POST, uncomment below:
/*
if (isset($_GET['message'])) {
    $message = sanitize_input_for_safety($_GET['message'], $conn_local);
    $message_type = isset($_GET['message_type']) ? sanitize_input_for_safety($_GET['message_type'], $conn_local) : 'success';
}
*/

// Filter options
$filter_language = isset($_GET['filter_language']) ? sanitize_input_for_safety($_GET['filter_language'], $conn_local) : '';
$filter_category = isset($_GET['filter_category']) ? sanitize_input_for_safety($_GET['filter_category'], $conn_local) : '';

$guidelines = [];
$sql_guidelines = "SELECT * FROM safety_guidelines WHERE 1=1";
$params = [];
$types = '';

if (!empty($filter_language)) {
    $sql_guidelines .= " AND language_code = ?";
    $params[] = $filter_language;
    $types .= 's';
}
if (!empty($filter_category)) {
    $sql_guidelines .= " AND category = ?";
    $params[] = $filter_category;
    $types .= 's';
}

$sql_guidelines .= " ORDER BY created_at DESC";

$stmt_guidelines = $conn_local->prepare($sql_guidelines);
if ($stmt_guidelines) {
    if (!empty($params)) {
        $stmt_guidelines->bind_param($types, ...$params);
    }
    $stmt_guidelines->execute();
    $result_guidelines = $stmt_guidelines->get_result();
    while ($row = $result_guidelines->fetch_assoc()) {
        $guidelines[] = $row;
    }
    $result_guidelines->free();
    $stmt_guidelines->close();
} else {
    error_log("Error fetching guidelines: " . $conn_local->error);
    if (empty($message)) { $message = "Error fetching guidelines: " . $conn_local->error; $message_type = "error"; }
}

// Close connection ONLY if this script opened it AND it's not an AJAX POST which already exited.
// When included by dashboard.php, $conn_opened_by_safety_script will be false.
if ($conn_opened_by_safety_script && (!isset($_POST['action']) || ($_POST['action'] !== 'delete_guideline'))) {
    $conn_local->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepOnGo - Manage Safety Guidelines</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        /* General Body & Container */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
        h1, h2, h3 { color: #2c3e50; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; }
        h1 { text-align: center; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Form Styling */
        .form-section { margin-bottom: 40px; padding: 25px; border: 1px solid #e9ecef; border-radius: 8px; background-color: #fdfdfd; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #4a4a4a; }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="file"],
        .form-group textarea,
        .form-group select {
            width: calc(100% - 22px); /* Account for padding and border */
            padding: 10px;
            border: 1px solid #cdd4da;
            border-radius: 6px;
            box-sizing: border-box; /* Include padding and border in element's total width and height */
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="file"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .current-file-info { margin-top: -10px; margin-bottom: 15px; font-size: 0.85em; color: #666; }
        .current-file-info a { color: #007bff; text-decoration: none; }
        .current-file-info a:hover { text-decoration: underline; }
        .current-file-info small { display: block; margin-top: 5px; }

        /* Buttons */
        .btn { background-color: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1rem; font-weight: 600; transition: background-color 0.3s ease, transform 0.2s ease; margin-right: 10px; }
        .btn:hover { background-color: #0056b3; transform: translateY(-2px); }
        .btn:active { transform: translateY(0); }
        .btn-cancel { background-color: #6c757d; }
        .btn-cancel:hover { background-color: #5a6268; }
        .btn-delete { background-color: #dc3545; margin-left: 5px; padding: 8px 15px; font-size: 0.9em; }
        .btn-delete:hover { background-color: #c82333; }
        .btn-edit { background-color: #ffc107; color: #333; padding: 8px 15px; font-size: 0.9em; }
        .btn-edit:hover { background-color: #e0a800; }
        .btn-preview { background-color: #17a2b8; color: white; padding: 8px 15px; font-size: 0.9em; }
        .btn-preview:hover { background-color: #138496; }

        /* Filter Section */
        .filter-section { margin-bottom: 25px; padding: 20px; background-color: #f8f9fa; border-radius: 10px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; }
        .filter-section label { margin-right: 10px; font-weight: 600; color: #4a4a4a; flex-shrink: 0; margin-bottom: 5px; }
        .filter-section select { padding: 10px 15px; border-radius: 6px; border: 1px solid #cdd4da; font-size: 1rem; }
        .filter-section .btn { padding: 10px 15px; font-size: 1rem; margin-right: 0; }
        .filter-section .btn-apply { background-color: #28a745; border-color: #28a745; }
        .filter-section .btn-apply:hover { background-color: #218838; }
        .filter-section .btn-clear { background-color: #6c757d; border-color: #6c757d; }
        .filter-section .btn-clear:hover { background-color: #5a6268; }

        /* Table Styling */
        .table-section { margin-top: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
        table th, table td { border: 1px solid #e9ecef; padding: 12px 15px; text-align: left; vertical-align: top; }
        table th { background-color: #e0f2f7; color: #2c3e50; font-weight: 700; text-transform: uppercase; font-size: 0.9em; }
        table tr:nth-child(even) { background-color: #f9fbfd; }
        table tr:hover { background-color: #f1f7fc; }
        table td .video-links a { color: #007bff; text-decoration: none; margin-right: 10px; }
        table td .video-links a:hover { text-decoration: underline; }

        /* Preview Modal Styles */
        #previewModal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.6); /* Black w/ opacity */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #previewModalContent {
            background-color: #fefefe;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 90%;
            position: relative;
        }
        #previewModalContent h3 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        #previewModalContent p {
            margin-bottom: 10px;
            color: #555;
        }
        #previewModalContent p strong {
            color: #333;
        }
        #previewVideoContainer {
            margin-top: 20px;
            background-color: #f0f2f5;
            padding: 10px;
            border-radius: 8px;
            overflow: hidden; /* Ensures video stays within bounds */
        }
        #previewVideoContainer video, #previewVideoContainer iframe {
            width: 100%;
            height: auto;
            min-height: 250px; /* Minimum height for video players */
            display: block;
            border-radius: 6px;
        }
        .modal-close-button {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 25px;
            transition: background-color 0.3s ease;
        }
        .modal-close-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Construction Safety Guidelines Admin Panel 👷</h1>
        <p style="text-align: center; color: green; font-weight: bold;">(This panel is designed to be integrated into a dashboard, or can function standalone.)</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2><?php echo $edit_guideline ? 'Edit Safety Guideline' : 'Add New Safety Guideline'; ?></h2>
            <form id="guidelineForm" action="safety_labour.php<?php echo $edit_guideline ? '?action=edit&id=' . htmlspecialchars($edit_guideline['id']) : ''; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_guideline ? 'update_guideline' : 'add_guideline'; ?>">
                <?php if ($edit_guideline): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_guideline['id']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo $edit_guideline ? htmlspecialchars($edit_guideline['title']) : (isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description/Content:</label>
                    <textarea id="description" name="description" rows="8" required><?php echo $edit_guideline ? htmlspecialchars($edit_guideline['description']) : (isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="language_code">Language:</label>
                    <select id="language_code" name="language_code" required>
                        <option value="">Select Language</option>
                        <?php foreach ($languages as $code => $name): ?>
                            <option value="<?php echo htmlspecialchars($code); ?>"
                                <?php
                                $selected_lang = $edit_guideline ? $edit_guideline['language_code'] : (isset($_POST['language_code']) ? $_POST['language_code'] : '');
                                echo ($selected_lang == $code) ? 'selected' : '';
                                ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"
                                <?php
                                $selected_cat = $edit_guideline ? $edit_guideline['category'] : (isset($_POST['category']) ? $_POST['category'] : '');
                                echo ($selected_cat == $cat) ? 'selected' : '';
                                ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="video_file">Upload Video File (MP4, AVI, MOV, WEBM - Max <?php echo ($max_file_size / (1024 * 1024)); ?> MB):</label>
                    <input type="file" id="video_file" name="video_file" accept="video/*">
                    <?php if ($edit_guideline && $edit_guideline['video_file']): ?>
                        <div class="current-file-info">
                            Current uploaded video: <a href="<?php echo htmlspecialchars($edit_guideline['video_file']); ?>" target="_blank"><?php echo htmlspecialchars(basename($edit_guideline['video_file'])); ?></a>
                            <small>(Upload a new file to replace the existing one, or leave blank to keep.)</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="video_link">YouTube or Video Link (e.g., `https://www.youtube.com/watch?v=dQw4w9WgXcQ` or `https://vimeo.com/76979871`):</label>
                    <input type="text" id="video_link" name="video_link" value="<?php echo $edit_guideline ? htmlspecialchars($edit_guideline['video_link']) : (isset($_POST['video_link']) ? htmlspecialchars($_POST['video_link']) : ''); ?>" placeholder="Optional YouTube or direct video link">
                </div>

                <button type="submit" class="btn"><?php echo $edit_guideline ? 'Update Guideline' : 'Add Guideline'; ?></button>
                <?php if ($edit_guideline): ?>
                    <button type="button" class="btn btn-cancel" onclick="window.location.href='safety_labour.php'">Cancel Edit</button>
                <?php endif; ?>
            </form>
        </div>

        <hr>

        <div class="table-section">
            <h2>Existing Safety Guidelines 📖</h2>

            <div class="filter-section">
                <form action="safety_labour.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="filter_language">Filter by Language:</label>
                        <select id="filter_language" name="filter_language">
                            <option value="">All Languages</option>
                            <?php foreach ($languages as $code => $name): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>" <?php echo ($filter_language == $code) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="filter_category">Filter by Category:</label>
                        <select id="filter_category" name="filter_category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($filter_category == $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-apply">Apply Filters 🔍</button>
                    <button type="button" class="btn btn-clear" onclick="window.location.href='safety_labour.php'">Clear Filters 🔄</button>
                </form>
            </div>

            <?php if (empty($guidelines)): ?>
                <p id="no-guidelines-message">No safety guidelines found. Add one above! ✨</p>
            <?php else: ?>
                <table id="guidelinesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Language</th>
                            <th>Category</th>
                            <th>Video</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guidelines as $guideline): ?>
                            <tr id="guideline-row-<?php echo htmlspecialchars($guideline['id']); ?>">
                                <td><?php echo htmlspecialchars($guideline['id']); ?></td>
                                <td><?php echo htmlspecialchars($guideline['title']); ?></td>
                                <td><?php echo htmlspecialchars($languages[$guideline['language_code']] ?? $guideline['language_code']); ?></td>
                                <td><?php echo htmlspecialchars($guideline['category']); ?></td>
                                <td class="video-links">
                                    <?php if ($guideline['video_file']): ?>
                                        <a href="<?php echo htmlspecialchars($guideline['video_file']); ?>" target="_blank">Uploaded Video 🎥</a>
                                    <?php endif; ?>
                                    <?php if ($guideline['video_link']): ?>
                                        <?php if ($guideline['video_file']): ?> | <?php endif; ?>
                                        <a href="<?php echo htmlspecialchars($guideline['video_link']); ?>" target="_blank">External Link 🔗</a>
                                    <?php endif; ?>
                                    <?php if (empty($guideline['video_file']) && empty($guideline['video_link'])): ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-edit" onclick="window.location.href='safety_labour.php?action=edit&id=<?php echo htmlspecialchars($guideline['id']); ?>'">Edit</button>
                                    <button class="btn btn-delete" data-guideline-id="<?php echo htmlspecialchars($guideline['id']); ?>">Delete</button>
                                    <button class="btn btn-preview" onclick="previewGuideline(<?php echo htmlspecialchars(json_encode($guideline), ENT_QUOTES, 'UTF-8'); ?>)">Preview</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div id="previewModal">
        <div id="previewModalContent">
            <h3 id="previewTitle"></h3>
            <p><strong>Language:</strong> <span id="previewLanguage"></span></p>
            <p><strong>Category:</strong> <span id="previewCategory"></span></p>
            <p id="previewDescription"></p>
            <div id="previewVideoContainer"></div>
            <button onclick="document.getElementById('previewModal').style.display='none';" class="modal-close-button">Close</button>
        </div>
    </div>

    <script>
        // Mapping of language codes to full names (must match PHP's $languages array)
        const languageNames = <?php echo json_encode($languages, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

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

            // Handle Delete via AJAX
            $('#guidelinesTable').on('click', '.btn-delete', function(e) {
                e.preventDefault(); // Prevent default form submission if wrapped in a form (though here it's a button)

                const guidelineId = $(this).data('guideline-id');
                if (window.confirm('Are you sure you want to delete this guideline? This action cannot be undone.')) {
                    $.ajax({
                        url: 'safety_labour.php', // AJAX call still targets this file
                        type: 'POST',
                        data: {
                            action: 'delete_guideline',
                            id: guidelineId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#guideline-row-' + guidelineId).fadeOut(400, function() {
                                    $(this).remove();
                                    displayMessage(response.message, 'success');
                                    // If no more rows, show the "No guidelines found" message
                                    if ($('#guidelinesTable tbody tr').length === 0) {
                                        if (!$('#no-guidelines-message').length) {
                                            $('.table-section').append('<p id="no-guidelines-message">No safety guidelines found. Add one above! ✨</p>');
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

            // If a message is present on page load (e.g., after adding/updating an assignment)
            if ($('.message').length) {
                setTimeout(function() {
                    $('.message').fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        });

        // Function for Preview Modal (remains largely same)
        function previewGuideline(guideline) {
            document.getElementById('previewTitle').innerText = guideline.title;
            document.getElementById('previewLanguage').innerText = languageNames[guideline.language_code] || guideline.language_code;
            document.getElementById('previewCategory').innerText = guideline.category;
            document.getElementById('previewDescription').innerText = guideline.description;

            const videoContainer = document.getElementById('previewVideoContainer');
            videoContainer.innerHTML = ''; // Clear previous content

            if (guideline.video_file) {
                // Display uploaded video
                const videoElement = document.createElement('video');
                videoElement.controls = true;
                videoElement.style.width = '100%';
                videoElement.src = guideline.video_file;
                videoElement.alt = "Uploaded Safety Video";
                videoContainer.appendChild(videoElement);
            } else if (guideline.video_link) {
                // Attempt to embed YouTube or other common video links
                let embedHtml = '';
                const youtubeRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|)([\w-]{11})(?:\S+)?/;
                const vimeoRegex = /(?:https?:\/\/)?(?:www\.)?(?:vimeo\.com)\/(?:channels\/\w+\/)?(?:\w+\/)?(\d+)(?:\S+)?/;

                const youtubeMatch = guideline.video_link.match(youtubeRegex);
                const vimeoMatch = guideline.video_link.match(vimeoRegex);

                if (youtubeMatch && youtubeMatch[1]) {
                    // YouTube embed (using a direct embed URL)
                    embedHtml = `<iframe width="100%" height="auto" src="https://www.youtube.com/embed/${youtubeMatch[1]}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                } else if (vimeoMatch && vimeoMatch[1]) {
                    // Vimeo embed
                    embedHtml = `<iframe src="https://player.vimeo.com/video/${vimeoMatch[1]}" width="100%" height="auto" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`;
                } else {
                    // Fallback for direct video link if not YouTube/Vimeo
                    embedHtml = `<video controls style="width:100%;" src="${guideline.video_link}" alt="External Safety Video"></video>`;
                }
                videoContainer.innerHTML = embedHtml;
            } else {
                videoContainer.innerHTML = '<p>No video available for this guideline.</p>';
            }

            document.getElementById('previewModal').style.display = 'flex'; // Show modal
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>