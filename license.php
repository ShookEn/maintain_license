<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_license_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch license data from the database
$query = "SELECT * FROM licenses";
$result = $conn->query($query);

// Prepare data for JSON response
$licenses = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $licenses[] = $row;
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($licenses);

$conn->close();
?>
