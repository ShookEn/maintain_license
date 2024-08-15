<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\maintain_license\vendor\autoload.php';
require 'C:\xampp\htdocs\maintain_license\db_connection.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

function encrypt($plaintext, $key, $iv) {
    $cipher = 'aes-256-gcm';
    $tag = null;
    $ciphertext = openssl_encrypt($plaintext, $cipher, base64_decode($key), $options=0, base64_decode($iv), $tag);
    if ($ciphertext === false) {
        return ["success" => false, "message" => "Encryption failed"];
    }
    // Return the Base64 encoded ciphertext and tag
    return base64_encode($ciphertext . '::' . $tag);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $reset_token = $_POST['reset_token'];

    if ($new_password === $confirm_password) {
        $key = base64_encode(random_bytes(32)); 
        $iv = base64_encode(random_bytes(12));  
        $encrypted_password = encrypt($new_password, $key, $iv);

        if (!$encrypted_password) {
            die("Encryption error.");
        }

        $sql = "UPDATE users SET password = ?, `key` = ?, iv = ? WHERE email = (SELECT email FROM password_resets WHERE reset_token = ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssss", $encrypted_password, $key, $iv, $reset_token);
        if ($stmt->execute()) {
            echo "<script>
                alert('Password has been successfully updated.');
                window.location.href = 'login.html';
            </script>";

            // Optionally, delete the reset token
            $sql = "DELETE FROM password_resets WHERE reset_token = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $reset_token);
                $stmt->execute();
            }
        } else {
            echo "<script>alert('Failed to update password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
    }
} else {
    if (isset($_GET['reset_token'])) {
        $reset_token = htmlspecialchars($_GET['reset_token']); // Sanitize token input
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Your Password</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .container {
                    max-width: 400px;
                    width: 100%;
                    padding: 20px;
                    background-color: white;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    border-radius: 8px;
                    text-align: center;
                }
                input[type="password"], input[type="submit"] {
                    width: 100%;
                    padding: 10px;
                    margin: 10px 0;
                    border-radius: 4px;
                    border: 1px solid #ccc;
                    box-sizing: border-box;
                }
                button {
                    width: 100%;
                    padding: 10px;
                    background-color: #28a745;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                button:hover {
                    background-color: #218838;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Reset Your Password</h2>
                <form method="post" action="">
                    <input type="hidden" name="reset_token" value="' . $reset_token . '">
                    <input type="password" name="new_password" placeholder="Enter new password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                    <button type="submit">Reset Password</button>
                </form>
            </div>
        </body>
        </html>
        ';
    } else {
        echo "<script>alert('No reset token provided.');</script>";
    }
}

$conn->close();
?>
