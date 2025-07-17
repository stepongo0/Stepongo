<?php
// Database configuration for MySQL
define('DB_SERVER', 'localhost'); // Your database server, usually 'localhost'
define('DB_USERNAME', 'root');   // Your MySQL username
define('DB_PASSWORD', '');       // Your MySQL password (often empty for XAMPP/WAMP default root)
define('DB_NAME', 'stepongo_new_db'); // Changed database name here

/* Attempt to connect to MySQL database */
// This part might be handled by the Database class, but sometimes a direct connection check is here.
// For now, it's just definitions. The actual connection object will be created in classes/database.php
?>