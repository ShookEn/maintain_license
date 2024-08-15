<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_license_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $license_key = $conn->real_escape_string($_GET['id']);
    $sql = "DELETE FROM licenses WHERE license_key = '$license_key'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Successfully deleted"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Deletion failed, please try again"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

$conn->close();
?>
