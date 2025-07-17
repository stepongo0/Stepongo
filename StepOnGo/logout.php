<?php
session_start(); // Start the session if it's not already started

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to the login page (replace 'login.php' with your actual login page file)
header("Location: index.php");
exit; // Always call exit after a header redirect
?>