<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\maintain_license\vendor\autoload.php'; // Ensure this path is correct
require 'C:\xampp\htdocs\maintain_license\db_connection.php'; // Ensure this path is correct

// Retrieve email from the form
$email = $_POST['email'];

// Check if the email exists in the database
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $error = 'Prepare failed: ' . $conn->error;
    header("Location: reset_password.html?status=error&message=" . urlencode($error));
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $token = bin2hex(random_bytes(32)); 

    $current_time = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')); 
    $expiry_time = $current_time->modify('+1 hour 30 minutes')->format('Y-m-d H:i:s'); 

$sql = "INSERT INTO password_resets (email, reset_token, reset_token_expire_at) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE reset_token = VALUES(reset_token), reset_token_expire_at = VALUES(reset_token_expire_at)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
        header("Location: reset_password.html?status=error&message=" . urlencode($error));
        exit();
    }

    $stmt->bind_param("sss", $email, $token, $expiry_time);
    if (!$stmt->execute()) {
        $error = 'Execute failed: ' . $stmt->error;
        header("Location: reset_password.html?status=error&message=" . urlencode($error));
        exit();
    }

    $mail = new PHPMailer(true);

    try {
        //SMTP server
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '602ead6c903ca2';
        $mail->Password = '139ce10c8643ab';

        $mail->setFrom('ennalim930309@gmail.com', 'Your Name');
        $mail->addAddress($email);

        // Content
        $reset_link = "http://localhost/maintain_license/update_password.php?reset_token=" . urlencode($token);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "Please click the following link to reset your password: <a href='$reset_link'>$reset_link</a>";

        $mail->send();
        header("Location: reset_password.html?status=success&message=Reset+email+sent+successfully");
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        header("Location: reset_password.html?status=error&message=" . urlencode("Failed to send email."));
    }
} else {
    header("Location: reset_password.html?status=error&message=" . urlencode("Email does not exist."));
}

$conn->close();
exit();
?>
