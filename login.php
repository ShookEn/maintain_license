<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_license_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

function decrypt($ciphertext_base64, $key, $iv) {
    $cipher = 'aes-256-gcm';

    $decoded_data = base64_decode($ciphertext_base64, true);

    if ($decoded_data === false) {
        return ["success" => false, "message" => "Base64 decoding error. Data might be corrupted or not properly encoded."];
    }

    $parts = explode('::', $decoded_data, 2);

    if (count($parts) !== 2) {
        return ["success" => false, "message" => "Decryption data format is invalid."];
    }

    list($ciphertext, $tag) = $parts;

    $decrypted = openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv, $tag);

    if ($decrypted === false) {
        $error = openssl_error_string();
        return ["success" => false, "message" => "Decryption error: " . $error];
    }

    return ["success" => true, "data" => $decrypted];
}

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username && $password) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $encrypted_password = $user['password'];
        $key = base64_decode($user['key']);
        $iv = base64_decode($user['iv']);

        if (strlen($key) !== 32 || strlen($iv) !== 12) {
            echo json_encode(["success" => false, "message" => "Invalid key or IV length."]);
            exit;
        }

        $decryption_result = decrypt($encrypted_password, $key, $iv);

        if (!$decryption_result['success']) {
            echo json_encode(["success" => false, "message" => $decryption_result['message']]);
            exit;
        }

        $decrypted_password = $decryption_result['data'];

        if ($password === $decrypted_password) {
            echo json_encode(["success" => true, "message" => "Login successful"]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid username or password."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
}

$conn->close();
?>
