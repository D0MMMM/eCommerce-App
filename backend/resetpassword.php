<?php
session_start();
include '../config/db.php';

if (isset($_POST['reset'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];

    if ($password !== $confirmpassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../frontend/resetpassword.php?token=" . $token);
        exit();
    }

    // Check if the token is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Update the user's password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        // Delete the token from the database
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        $_SESSION['message'] = "Your password has been reset successfully.";
        header("Location: ../frontend/resetpassword.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: ../frontend/resetpassword.php?token=" . $token);
        exit();
    }
}
?>