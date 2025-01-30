<?php
require '../vendor/autoload.php'; // Ensure this path is correct

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOrderEmail($order_id, $contact_name, $contact_email, $payment_method, $total_amount) {
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
        $mail->setFrom('no-reply@gmail.com', 'Car Marketplace');
        $mail->addAddress($contact_email); // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation';
        $mail->Body    = "<p>Hello $contact_name,</p>
                          <p>Thank you for your order. Your order ID is <strong>$order_id</strong>.</p>
                          <p>Payment Method: <strong>$payment_method</strong></p>
                          <p>Total Amount: <strong>₱" . number_format($total_amount, 2) . "</strong></p>
                          <p>We will process your order shortly.</p>
                          <p>Regards,<br>Car Marketplace</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Order email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>