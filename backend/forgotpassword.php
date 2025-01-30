<?php
session_start();
include '../config/db.php';
require '../vendor/autoload.php'; // Ensure Composer's autoloader is included

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['submit'])) {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));

        // Store the token in the database with an expiration time
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();

        // Send the password reset link to the user's email using PHPMailer
        $resetLink = "http://localhost/final-project/frontend/resetpassword.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: <a href='" . $resetLink . "'>" . $resetLink . "</a>";

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'domenick.mahusay14@gmail.com'; // SMTP username
            $mail->Password = 'd r y q n g d m w d g h w a u m'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('no-reply@gmail.com', 'Group 7 Car Shop');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
            $_SESSION['message'] = "Password reset link has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send password reset link. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "Email not found.";
    }

    header("Location: ../frontend/forgotpassword.php");
    exit();
}
?>