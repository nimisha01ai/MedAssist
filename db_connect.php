<?php
// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'medassist_db';
$port = 3306;

// Create connection using object-oriented mysqli, explicitly specifying the port
$conn = new mysqli($host, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} elseif (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    // Only display this message when db_connect.php is opened/tested directly,
    // so including it in other pages (like index.php) won't print stray output.
    echo 'Database Connected Successfully!';
}
?>
