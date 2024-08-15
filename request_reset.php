<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\maintain_license\vendor\autoload.php'; 
require 'C:\xampp\htdocs\maintain_license\db_connection.php'; 

// Retrieve email from the form
$email = $_POST['email'];

// Check if the email exists in the database
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Prepare failed: ' . $conn->error . ' SQL: ' . $sql);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $token = bin2hex(random_bytes(32)); 
    $expiry = date("Y-m-d H:i:s", strtotime('+30 minutes')); // Token valid for 30 minutes
    date_default_timezone_set('Asia/Kuala_Lumpur'); // Set your timezone

    // Store the token and expiry in the database
    $sql = "INSERT INTO password_resets (email, reset_token, reset_token_expire_at) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE reset_token = VALUES(reset_token), reset_token_expire_at = VALUES(reset_token_expire_at)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Prepare failed: ' . $conn->error . ' SQL: ' . $sql);
    }

    $stmt->bind_param("sss", $email, $token, $expiry);
    if (!$stmt->execute()) {
        die('Execute failed: ' . $stmt->error);
    }

    // Send the reset email
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '602ead6c903ca2';
        $mail->Password = '139ce10c8643ab';

        // Recipients
        $mail->setFrom('ennalim930309@gmail.com', 'Your Name');
        $mail->addAddress($email);

        // Content
        $reset_link = "http://localhost/maintain_license/update_password.html?token=" . urlencode($token);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "Please click the following link to reset your password: <a href='$reset_link'>$reset_link</a>";

        $mail->send();
        header("Location: reset_password.html?status=success");
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        header("Location: reset_password.html?status=mail_error");
    }
} else {
    header("Location: reset_password.html?status=invalid_email");
}

$conn->close();
?>
