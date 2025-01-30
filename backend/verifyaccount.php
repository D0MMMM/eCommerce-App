<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/verifyaccount.css">

<?php 
session_start(); 
include '../config/db.php';

if (isset($_POST['verify'])) {
    $otp = $_POST['otp'];
    $email = $_POST['email']; 

    $stmt = $conn->prepare("SELECT * FROM `user` WHERE otp = ? AND email = ?");
    $stmt->bind_param("is", $otp, $email); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $updateStmt = $conn->prepare("UPDATE `user` SET status = 'verified', otp = NULL WHERE otp = ? AND email = ?");
        $updateStmt->bind_param("is", $otp, $email);
        $updateResult = $updateStmt->execute();

        if ($updateResult) {
            $row = $result->fetch_assoc();
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            // Redirect with success status
            echo "<script>
                window.onload = function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Your account has been verified successfully!'
                    }).then(function() {
                        window.location = '../frontend/login.php';
                    });
                };
            </script>";
        } else {
            // Redirect with failure status
            echo "<script>
                window.onload = function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error updating user status. Please try again.'
                    }).then(function() {
                        window.location = '../frontend/verifyaccount.php?email=$email';
                    });
                };
            </script>";
        }
    } else {
        // Redirect with failure status for invalid OTP or email
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid OTP or email. Please try again.'
                }).then(function() {
                    window.location = '../frontend/verifyaccount.php?email=$email';
                });
            };
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
