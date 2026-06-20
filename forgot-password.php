<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SiAGRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('Assets/images/sawah.jpg') center/cover no-repeat fixed;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, rgba(22,74,65,0.55) 0%, rgba(10,35,30,0.7) 100%);
            z-index: 0;
        }
        .forgot-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }
        .forgot-card {
            background: linear-gradient(165deg, rgba(22,74,65,0.92) 0%, rgba(16,56,48,0.96) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 48px 40px 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.06);
            text-align: center;
        }
        .forgot-icon {
            width: 64px;
            height: 64px;
            background: rgba(241,178,74,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
        }
        .forgot-title {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .forgot-desc {
            color: rgba(255,255,255,0.5);
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .float-group {
            position: relative;
            margin-bottom: 24px;
            text-align: left;
        }
        .float-group input {
            width: 100%;
            padding: 16px 18px 8px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            outline: none;
            transition: border-color 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
        }
        .float-group input::placeholder { color: transparent; }
        .float-group label {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            color: rgba(255,255,255,0.5);
            pointer-events: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            padding: 0 4px;
        }
        .float-group input:focus + label,
        .float-group input:not(:placeholder-shown) + label {
            top: 0;
            transform: translateY(-50%);
            font-size: 12px;
            font-weight: 600;
            color: #f1b24a;
            background: linear-gradient(to bottom, rgba(16,56,48,1) 50%, rgba(22,74,65,0.92) 50%);
            letter-spacing: 0.3px;
        }
        .float-group input:focus {
            border-color: #f1b24a;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(241,178,74,0.12);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            color: #164a41;
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, #f1b24a 0%, #e5a23d 100%);
        }
        .info-box {
            background: rgba(241,178,74,0.12);
            border: 1px solid rgba(241,178,74,0.2);
            color: #f1b24a;
            font-size: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            margin-top: 20px;
            line-height: 1.6;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: rgba(255,255,255,0.5);
        }
        .back-link a {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.25s;
        }
        .back-link a:hover { color: #f1b24a; }
        @media (max-width: 480px) {
            .forgot-card { padding: 36px 24px 32px; border-radius: 20px; }
        }
    </style>
</head>
<body>

<div class="forgot-container">
    <div class="forgot-card">
        <div class="forgot-icon">🔑</div>
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

        <div class="info-box" id="info-msg" style="display:none;">
            ⚠️ Fitur reset password via email <strong>akan segera tersedia</strong>.
            Untuk saat ini, silakan hubungi admin SiAGRI untuk reset password manual.
        </div>

        <div class="back-link">
            <a href="login-page.php">← Kembali ke Login</a>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script src="assets/js/auth.js"></script>
</body>
</html>
