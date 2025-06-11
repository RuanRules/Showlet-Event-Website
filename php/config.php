<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', ''); 
define('DB_NAME', 'showlet_db'); 

// Suppress the default warning with '@' and check the error manually
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // If the connect_error property has a value, the connection failed.
    die("ERROR: Could not connect. " . $conn->connect_error);
}

?>