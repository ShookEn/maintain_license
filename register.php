<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_license_db";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function random_bytes_base64($length) {
    return base64_encode(random_bytes($length));
}

// Encrypt password using AES-256-GCM
function encrypt_password($password, &$key_base64, &$iv_base64) {
    $key = random_bytes(32); 
    $iv = random_bytes(12);  
    $tag = '';

    $cipher = "aes-256-gcm";
    $encrypted_password = openssl_encrypt($password, $cipher, $key, $options=0, $iv, $tag);

    if ($encrypted_password === false) {
        die('Encryption failed: ' . openssl_error_string());
    }

    $key_base64 = base64_encode($key);
    $iv_base64 = base64_encode($iv);
    $encrypted_password_base64 = base64_encode($encrypted_password . '::' . $tag);

    return $encrypted_password_base64;
}

$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password']; 

$key_base64 = '';
$iv_base64 = '';
$encrypted_password_base64 = encrypt_password($password, $key_base64, $iv_base64);

$sql = "INSERT INTO users (username, password, `key`, `iv`) VALUES ('$username', '$encrypted_password_base64', '$key_base64', '$iv_base64')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}


$conn->close();
?>
