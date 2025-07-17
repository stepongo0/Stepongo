<?php
// safety_company.php - A simple web application for managing company safety protocols.

// --- Database Configuration ---
// IMPORTANT: Replace these with your actual database credentials.
$dbHost = 'localhost'; // Usually 'localhost'
$dbName = 'stepongo_new_db'; // The database name you created or will use
$dbUser = 'root';      // Your database username
$dbPass = '';          // Your database password

// --- Establish Database Connection (PDO) ---
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    // Set the PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Disable emulation for security
} catch (PDOException $e) {
    // If connection fails, display an error message and stop execution
    die("Database connection failed: " . $e->getMessage());
}

// --- Initialize Variables for Form ---
$id = null;
$company_name = '';
$protocol_title = '';
$description = '';
$applicable_law = '';
$message = ''; // To display success or error messages

// --- Handle Form Submissions (Add or Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $company_name = htmlspecialchars($_POST['company_name']);
    $protocol_title = htmlspecialchars($_POST['protocol_title']);
    $description = htmlspecialchars($_POST['description']);
    $applicable_law = htmlspecialchars($_POST['applicable_law']);
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); // Get ID if updating

    if ($id) {
        // --- Update Existing Protocol ---
        $sql = "UPDATE safety_protocols SET company_name = ?, protocol_title = ?, description = ?, applicable_law = ? WHERE id = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$company_name, $protocol_title, $description, $applicable_law, $id]);
            $message = "<div style='color: green; font-weight: bold;'>Protocol updated successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color: red; font-weight: bold;'>Error updating protocol: " . $e->getMessage() . "</div>";
        }
    } else {
        // --- Add New Protocol ---
        $sql = "INSERT INTO safety_protocols (company_name, protocol_title, description, applicable_law) VALUES (?, ?, ?, ?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$company_name, $protocol_title, $description, $applicable_law]);
            $message = "<div style='color: green; font-weight: bold;'>New protocol added successfully!</div>";
            // Clear form fields after successful addition
            $company_name = '';
            $protocol_title = '';
            $description = '';
            $applicable_law = '';
        } catch (PDOException $e) {
            $message = "<div style='color: red; font-weight: bold;'>Error adding protocol: " . $e->getMessage() . "</div>";
        }
    }
}

// --- Handle Edit Request (Populate form for editing) ---
if (isset($_GET['edit'])) {
    $id_to_edit = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($id_to_edit) {
        $sql = "SELECT * FROM safety_protocols WHERE id = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_to_edit]);
            $protocol = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($protocol) {
                // Populate form fields with existing data
                $id = $protocol['id'];
                $company_name = $protocol['company_name'];
                $protocol_title = $protocol['protocol_title'];
                $description = $protocol['description'];
                $applicable_law = $protocol['applicable_law'];
            } else {
                $message = "<div style='color: orange; font-weight: bold;'>Protocol not found for editing.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div style='color: red; font-weight: bold;'>Error fetching protocol for editing: " . $e->getMessage() . "</div>";
        }
    }
}

// --- Fetch All Safety Protocols for Display ---
$protocols = [];
try {
    $stmt = $pdo->query("SELECT * FROM safety_protocols ORDER BY last_updated DESC");
    $protocols = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "<div style='color: red; font-weight: bold;'>Error fetching protocols: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Safety Protocols (Indian Law)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            line-height: 1.5;
            transition: border-color 0.2s;
        }
        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6; /* Blue-500 */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }
        .btn-primary {
            background-color: #3b82f6; /* Blue-500 */
            color: white;
            border: none;
        }
        .btn-primary:hover {
            background-color: #2563eb; /* Blue-600 */
            transform: translateY(-1px);
        }
        .btn-edit {
            background-color: #10b981; /* Emerald-500 */
            color: white;
            border: none;
        }
        .btn-edit:hover {
            background-color: #059669; /* Emerald-600 */
            transform: translateY(-1px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }
        tr:hover {
            background-color: #f3f4f6;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr {
                border: 1px solid #ccc;
                margin-bottom: 0.75rem;
                border-radius: 0.5rem;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }
            td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            td:before {
                position: absolute;
                top: 0;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: 600;
                color: #4b5563;
            }
            td:nth-of-type(1):before { content: "ID:"; }
            td:nth-of-type(2):before { content: "Company Name:"; }
            td:nth-of-type(3):before { content: "Protocol Title:"; }
            td:nth-of-type(4):before { content: "Description:"; }
            td:nth-of-type(5):before { content: "Applicable Law:"; }
            td:nth-of-type(6):before { content: "Last Updated:"; }
            td:nth-of-type(7):before { content: "Actions:"; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Company Safety Protocols & Guidelines</h1>
        <p class="text-gray-600 mb-8 text-center">Manage safety protocols and guidelines according to Indian laws.</p>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-md bg-opacity-10 <?php echo strpos($message, 'success') !== false ? 'bg-green-100 text-green-700' : (strpos($message, 'error') !== false ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700'); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Add/Update Protocol Form -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-inner mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4"><?php echo $id ? 'Edit Safety Protocol' : 'Add New Safety Protocol'; ?></h2>
            <form action="safety_company.php" method="POST" class="space-y-4">
                <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="company_name">Company Name:</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company_name); ?>" required class="mt-1">
                </div>

                <div class="form-group">
                    <label for="protocol_title">Protocol Title:</label>
                    <input type="text" id="protocol_title" name="protocol_title" value="<?php echo htmlspecialchars($protocol_title); ?>" required class="mt-1">
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="5" required class="mt-1"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="applicable_law">Applicable Indian Law/Act:</label>
                    <input type="text" id="applicable_law" name="applicable_law" value="<?php echo htmlspecialchars($applicable_law); ?>" placeholder="e.g., Factories Act, 1948" required class="mt-1">
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <?php echo $id ? 'Update Protocol' : 'Add Protocol'; ?>
                </button>
            </form>
        </div>

        <!-- Display Existing Protocols -->
        <h2 class="text-2xl font-semibold text-gray-700 mb-4 mt-8">Existing Safety Protocols</h2>
        <?php if (empty($protocols)): ?>
            <p class="text-gray-600">No safety protocols found. Add one using the form above!</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg shadow-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company Name</th>
                            <th>Protocol Title</th>
                            <th>Description</th>
                            <th>Applicable Law</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($protocols as $protocol): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($protocol['id']); ?></td>
                                <td><?php echo htmlspecialchars($protocol['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($protocol['protocol_title']); ?></td>
                                <td><?php echo htmlspecialchars($protocol['description']); ?></td>
                                <td><?php echo htmlspecialchars($protocol['applicable_law']); ?></td>
                                <td><?php echo htmlspecialchars($protocol['last_updated']); ?></td>
                                <td>
                                    <a href="safety_company.php?edit=<?php echo htmlspecialchars($protocol['id']); ?>" class="btn btn-edit text-sm">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
