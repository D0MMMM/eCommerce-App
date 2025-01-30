<?php

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function sendOTP($email, $otp) {
    
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();                                        
        $mail->Host = 'smtp.gmail.com';                         
        $mail->SMTPAuth = true;                                 
        $mail->Username = 'domenick.mahusay14@gmail.com';               
        $mail->Password = 'd r y q n g d m w d g h w a u m';                  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    
        $mail->Port = 587;
        
        $mail->setFrom('domenick.mahusay14@gmail.com');                
        $mail->addAddress($email); 
    
        $mail->isHTML(true);                                    
        $mail->Subject = 'Authentication verification for your account registration.';
        $mail->Body    = 'Use this OTP for confirmation: <b>' . $otp . '</b>';
    
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    
}


?>

