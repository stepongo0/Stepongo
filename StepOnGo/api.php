<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow all domains (for development)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once 'db_connect.php';

// GET Request - Fetch All Users
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT * FROM users");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
}

// POST Request - Add New User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];
    $email = $data['email'];
    
    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User added successfully!"]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}

$conn->close();
?>