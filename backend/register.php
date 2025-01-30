<?php

include '../config/db.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];
    $status = "unverified";
    $otp = rand(100000, 999999);

    $sql = "SELECT * FROM `user` WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($result);

    if ($num > 0) {
        header("Location: ../frontend/register.php?error=email_exists");
        exit();
    } else {
        if ($password == $confirmpassword) {
            include '../includes/sendotp.php';
            sendOTP($email, $otp);

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO `user` (`username`, `password`, `email`, `contact_number`, `otp`, `status`) VALUES ('$username', '$hash', '$email', '$contact', $otp, '$status')";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                header("Location: ../frontend/verifyaccount.php?email=$email&Register=Success");
                exit();
            } else {
                header("Location: ../frontend/register.php?error=register_failed");
                exit();
            }
        } else {
            header("Location: ../frontend/register.php?error=password_mismatch");
            exit();
        }
    }
}
?>
