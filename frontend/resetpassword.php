<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/resetpassword.css">
    <link rel="stylesheet" href="../assets/css/includes-css/footer.css">
    <link rel="stylesheet" href="../font-awesome/css/all.css">
    <title>Reset Password</title>
</head>
<body>
    <a class="back-button" href="login.php"><i class="fa-solid fa-arrow-left"></i></a>
    <div class="container">
        <h1>Reset Password</h1>
        <form id="resetpassword-form" action="../backend/resetpassword.php" method="post">
            
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']) ?>">
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="New Password" required>
                <i class="fa-solid fa-eye" id="togglePassword" style="cursor: pointer;"></i>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="confirmpassword" placeholder="Confirm New Password" required>
                <i class="fa-solid fa-eye" id="toggleConfirmPassword" style="cursor: pointer;"></i>
            </div>
            <input type="submit" value="Reset Password" name="reset">
        </form>
    </div>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
        <path fill="#ff0000" fill-opacity="1" d="M0,160L30,181.3C60,203,120,245,180,240C240,235,300,181,360,154.7C420,128,480,128,540,138.7C600,149,660,171,720,192C780,213,840,235,900,250.7C960,267,1020,277,1080,245.3C1140,213,1200,139,1260,106.7C1320,75,1380,85,1410,90.7L1440,96L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
    </svg>
    <script src="../assets/js/show_password.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php session_start(); ?>
        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $_SESSION['message']; ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
            });
            <?php unset($_SESSION['message']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo $_SESSION['error']; ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>