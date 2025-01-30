<?php include "config/db.php"?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="asset/index.css">
  <link rel="stylesheet" href="../font-awesome/css/all.css">
  <title>Admin Login</title>
</head>
<body>
    <div class="container">
        <h1>Admin Login</h1>
        <form id="login-form" action="backend/admin_login.php" method="post">
            <div class="input-container">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
                <i class="fa-solid fa-eye" id="togglePassword" style="cursor: pointer;"></i>
            </div>
            <input type="submit" value="LOGIN" name="login">
            <!-- <div class="process-link">
                <p><a href="forgotpassword.php">Forgot password?</a></p>
            </div> -->
        </form>
    </div>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
        <path fill="#ff0000" fill-opacity="1" d="M0,160L30,181.3C60,203,120,245,180,240C240,235,300,181,360,154.7C420,128,480,128,540,138.7C600,149,660,171,720,192C780,213,840,235,900,250.7C960,267,1020,277,1080,245.3C1140,213,1200,139,1260,106.7C1320,75,1380,85,1410,90.7L1440,96L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
    </svg>
    <script src="asset/js/show_password.js"></script>
</body>
</html>