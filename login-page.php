<?php
$path_prefix = '';

session_start();
require_once 'koneksi.php';
$error_message = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    
    if (mysqli_num_rows($check) === 1) {
        $user = mysqli_fetch_assoc($check);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'Farmer') {
                header("Location: catalog.php");
                exit;
            } elseif ($user['role'] == 'Kiosk') {
                $kiosk_data = mysqli_query($conn, "SELECT * FROM kiosk_profiles WHERE user_id='".$user['user_id']."'");
                $kiosk_data = mysqli_fetch_assoc($kiosk_data);
                $_SESSION['kiosk_id'] = $kiosk_data['kiosk_id'];
                header("Location: dashboard.php");
                exit;
            } elseif ($user['role'] == 'Admin') {
                header("Location: admin-dashboard.php");
                exit;
            } elseif ($user['role'] == 'Expert') {
                header("Location: forum.php");
                exit;
            }
        } else {
            $error_message = "Password salah! Silakan coba lagi.";
        }
    } else {
        $error_message = "Username tidak ditemukan! Silakan daftar terlebih dahulu.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SiAGRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/images/ICON.png">
    <link rel="stylesheet" href="assets/css/global.css">
</head>
<body class="auth-body">

<div class="login-container">
    <div class="login-card">

        <div class="login-logo">
            <img src="assets/images/LOGO.png" alt="SiAGRI Logo">
        </div>

        <h1 class="login-title">Login</h1>

        <?php if (!empty($error_message)): ?>
        <div class="error-box">
            <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">

            <!-- Username -->
            <div class="float-group">
                <input type="text" id="username" name="username" placeholder=" " required>
                <label for="username">Username</label>
            </div>

            <!-- Password -->
            <div class="float-group">
                <input type="password" id="password" name="password" placeholder=" " required>
                <label for="password">Password</label>
            </div>

            <!-- Remember / Forgot -->
            <div class="login-options">
                <label class="remember-me">
                    <input type="checkbox">
                    <span>Remember me</span>
                </label>
                <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" name="login" class="btn-login">Login</button>

        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register</a>
        </div>

        <div style="text-align: center; margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
            <a href="index.php" style="color: rgba(255,255,255,0.6); font-size: 13px; font-weight: 400; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: color 0.25s ease;" class="forgot-link">
                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 24 24"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8l8 8l1.41-1.41L7.83 13H20z"/></svg>
                Kembali ke Beranda
            </a>
        </div>

    </div>

    <div class="terms-text">
        By continuing, you agree to our
        <a href="terms-of-service.php">Terms of Service</a> and
        <a href="privacy-policy.php">Privacy Policy</a>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script src="assets/js/auth.js"></script>
</body>
</html>