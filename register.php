<?php
session_start();
require_once 'koneksi.php';

$pesan_error = "";
$pesan_sukses = "";

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = isset($_POST['role']) ? $_POST['role'] : '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm) || empty($role)) {
        $pesan_error = "Semua kolom wajib diisi!";
    } elseif ($password !== $confirm) {
        $pesan_error = "Password tidak sama!";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

        if (mysqli_num_rows($check) > 0) {
            $pesan_error = "Email sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";

            if (mysqli_query($conn, $sql)) {
                $new_user_id = mysqli_insert_id($conn);

                if ($role === 'Kiosk') {
                    mysqli_query($conn, "INSERT INTO kiosk_profiles (user_id, store_name, full_address, whatsapp_number) VALUES ('$new_user_id', 'Unnamed Store', '-', '-')");
                }

                $pesan_sukses = "Daftar sukses! Mengarahkan ke halaman login...";
                header("refresh:2;url=login-page.php");
            } else {
                $pesan_error = "Gagal: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SiAGRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0; padding: 0; box-sizing: border-box;
        }

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

        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        .register-card {
            background: linear-gradient(165deg, rgba(22,74,65,0.92) 0%, rgba(16,56,48,0.96) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 44px 40px 36px;
            box-shadow:
                0 25px 60px rgba(0,0,0,0.35),
                inset 0 1px 0 rgba(255,255,255,0.06);
        }

        .register-logo {
            text-align: center;
            margin-bottom: 8px;
        }
        .register-logo img {
            height: 44px;
            filter: brightness(0) invert(1);
            opacity: 0.9;
        }

        .register-title {
            text-align: center;
            color: #fff;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .register-subtitle {
            text-align: center;
            color: rgba(255,255,255,0.45);
            font-size: 13px;
            margin-bottom: 28px;
        }

        .alert-error {
            background: rgba(220,38,38,0.15);
            border: 1px solid rgba(220,38,38,0.3);
            color: #fca5a5;
            font-size: 13px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            animation: shake 0.4s ease;
        }
        .alert-success {
            background: rgba(34,197,94,0.15);
            border: 1px solid rgba(34,197,94,0.3);
            color: #86efac;
            font-size: 13px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        /* ───── FLOATING LABEL GROUP ───── */
        .float-group {
            position: relative;
            margin-bottom: 22px;
        }

        .float-group input {
            width: 100%;
            padding: 16px 18px 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            outline: none;
            transition: border-color 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
        }

        .float-group input::placeholder {
            color: transparent;
        }

        .float-group label {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
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
            font-size: 11px;
            font-weight: 600;
            background: #143f37;
            padding: 0 6px;
            border-radius: 4px;
            letter-spacing: 0.3px;
        }

        /* Label berwarna abu-abu mengikuti garis input saat tidak fokus namun terisi */
        .float-group input:not(:placeholder-shown) + label {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Label berwarna kuning keemasan saat sedang difokuskan/diklik */
        .float-group input:focus + label {
            color: #f1b24a;
        }

        .float-group input:focus {
            border-color: #f1b24a;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(241,178,74,0.12);
        }

        /* ───── DROPDOWN (Role) ───── */
        .select-group {
            margin-bottom: 22px;
        }

        .select-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.55);
            margin-bottom: 8px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .select-group select {
            width: 100%;
            padding: 14px 18px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            outline: none;
            cursor: pointer;
            transition: border-color 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='rgba(255,255,255,0.5)' d='M1.41.59L6 5.17 10.59.59 12 2 6 8 0 2z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
        }

        .select-group select:focus {
            border-color: #f1b24a;
            background-color: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(241,178,74,0.12);
        }

        .select-group select option {
            background: #164a41;
            color: #fff;
            padding: 8px;
        }

        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .terms-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .terms-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #f1b24a;
            cursor: pointer;
            flex-shrink: 0;
        }

        .terms-check span {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
        }
        .terms-check a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-weight: 500;
        }
        .terms-check a:hover {
            color: #f1b24a;
        }

        /* ───── BUTTON ───── */
        .btn-register {
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
            letter-spacing: 0.3px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, #f1b24a 0%, #e5a23d 100%);
            color: #164a41;
        }

        .btn-register:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .login-link {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
        }
        .login-link a {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.25s ease;
        }
        .login-link a:hover {
            color: #f1b24a;
        }

        .terms-footer {
            text-align: center;
            margin-top: 18px;
            font-size: 11px;
            color: rgba(255,255,255,0.3);
            position: relative;
            z-index: 1;
        }
        .terms-footer a {
            color: rgba(255,255,255,0.45);
            text-decoration: none;
        }
        .terms-footer a:hover { color: #f1b24a; }

        @media (max-width: 480px) {
            .register-card {
                padding: 32px 22px 28px;
                border-radius: 20px;
            }
            .register-title { font-size: 22px; }
            .row-2 {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-card">

        <div class="register-logo">
            <img src="Assets/images/LOGO.png" alt="SiAGRI Logo">
        </div>

        <h1 class="register-title">Buat Akun</h1>
        <p class="register-subtitle">Daftar untuk mulai menggunakan SiAGRI</p>

        <?php if (!empty($pesan_error)): ?>
        <div class="alert-error"><?= htmlspecialchars($pesan_error) ?></div>
        <?php endif; ?>

        <?php if (!empty($pesan_sukses)): ?>
        <div class="alert-success"><?= htmlspecialchars($pesan_sukses) ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" autocomplete="off" id="register-form">

            <!-- Username -->
            <div class="float-group">
                <input type="text" id="username" name="username" placeholder=" " required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <label for="username">Username</label>
            </div>

            <!-- Email -->
            <div class="float-group">
                <input type="email" id="email" name="email" placeholder=" " required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <label for="email">Email</label>
            </div>

            <!-- Role (default dropdown) -->
            <div class="select-group">
                <label for="role">Daftar Sebagai</label>
                <select id="role" name="role" required>
                    <option value="" disabled <?= empty($_POST['role'] ?? '') ? 'selected' : '' ?>>
                        — Pilih Role —
                    </option>
                    <option value="Farmer" <?= ($_POST['role'] ?? '') === 'Farmer' ? 'selected' : '' ?>>
                        Petani (Farmer)
                    </option>
                    <option value="Kiosk" <?= ($_POST['role'] ?? '') === 'Kiosk' ? 'selected' : '' ?>>
                        Mitra Kios (Kiosk)
                    </option>
                    <option value="Expert" <?= ($_POST['role'] ?? '') === 'Expert' ? 'selected' : '' ?>>
                        Pakar Pertanian (Expert)
                    </option>
                </select>
            </div>

            <!-- Password Row -->
            <div class="row-2">
                <div class="float-group">
                    <input type="password" id="password" name="password" placeholder=" " required minlength="6">
                    <label for="password">Password</label>
                </div>
                <div class="float-group">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder=" " required>
                    <label for="confirm_password">Konfirmasi</label>
                </div>
            </div>

            <!-- Terms -->
            <div class="terms-check">
                <input type="checkbox" id="terms" required>
                <span>Saya setuju dengan <a href="terms-of-service.php">Syarat & Ketentuan</a></span>
            </div>

            <button type="submit" name="register" class="btn-register">Daftar Sekarang</button>

        </form>

        <div class="login-link">
            Sudah punya akun? <a href="login-page.php">Login di sini</a>
        </div>

    </div>

    <div class="terms-footer">
        By continuing, you agree to our
        <a href="terms-of-service.php">Terms of Service</a> and
        <a href="privacy-policy.php">Privacy Policy</a>
    </div>
</div>

<script src="Assets/js/main.js"></script>
<script src="Assets/js/auth.js"></script>
</body>
</html>