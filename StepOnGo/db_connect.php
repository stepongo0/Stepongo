<?php
$host = "localhost";
$user = "root";       // Default XAMPP username
$password = "";       // Default XAMPP password
$database = "test_db"; // Your database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>