<?php
$path_prefix = '../../';

session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SiAGRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Assets/images/ICON.png">
    <link rel="stylesheet" href="../../Assets/css/global.css">
</head>
<body class="auth-body">

<div class="forgot-container">
    <div class="forgot-card">
        <div class="forgot-icon"><svg class="inline-block w-12 h-12 mx-auto mb-3 fill-current text-siagri-gold" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="#f1b24a" fill-rule="evenodd" d="M22 8.293c0 3.476-2.83 6.294-6.32 6.294c-.636 0-2.086-.146-2.791-.732l-.882.878c-.519.517-.379.669-.148.919c.096.105.208.226.295.399c0 0 .735 1.024 0 2.049c-.441.585-1.676 1.404-3.086 0l-.294.292s.881 1.025.147 2.05c-.441.585-1.617 1.17-2.646.146l-1.028 1.024c-.706.703-1.568.293-1.91 0l-.883-.878c-.823-.82-.343-1.708 0-2.05l7.642-7.61s-.735-1.17-.735-2.78c0-3.476 2.83-6.294 6.32-6.294S22 4.818 22 8.293m-6.319 2.196a2.2 2.2 0 0 0 2.204-2.195a2.2 2.2 0 0 0-2.204-2.196a2.2 2.2 0 0 0-2.204 2.196a2.2 2.2 0 0 0 2.204 2.195" clip-rule="evenodd"/></svg></div>
        <h1 class="forgot-title">Lupa Password</h1>
        <p class="forgot-desc">
            Masukkan email yang terdaftar di akun kamu.<br>
            Kami akan mengirimkan link reset password.
        </p>

        <div class="float-group">
            <input type="email" id="email" placeholder=" " required>
            <label for="email">Email</label>
        </div>

        <button class="btn-submit" onclick="showForgotPasswordInfo()">Kirim Link Reset</button>

        <div class="info-box permanent" id="info-msg" style="display:none;">
            <svg style="width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <p>
                Fitur reset password via email <strong>akan segera tersedia</strong>.
                Untuk saat ini, silakan hubungi admin SiAGRI untuk reset password manual.
            </p>
        </div>

        <div class="back-link">
            <a href="../../pages/auth/login.php">
                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 24 24"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8l8 8l1.41-1.41L7.83 13H20z"/></svg>
                Kembali ke Login
            </a>
        </div>
    </div>
</div>

<script src="../../Assets/js/main.js"></script>
<script src="../../Assets/js/auth.js"></script>
</body>
</html>
