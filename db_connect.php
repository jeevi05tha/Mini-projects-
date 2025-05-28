<?php
// Database connection parameters
$servername = "localhost";    // Usually localhost
$username = "root";  // Replace with your DB username
$password = "";  // Replace with your DB password
$dbname = "dbms";  // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// You can uncomment the below line for debugging connection
// echo "Connected successfully";
?>
