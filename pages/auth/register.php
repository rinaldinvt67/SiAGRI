<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';

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
    } elseif (strlen($password) < 6) {
        $pesan_error = "Password minimal 6 karakter!";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $pesan_error = "Password harus mengandung minimal 1 huruf kapital!";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $pesan_error = "Password harus mengandung minimal 1 angka!";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $pesan_error = "Password harus mengandung minimal 1 simbol!";
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
                header("refresh:2;url=login.php");
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
    <link rel="icon" type="image/png" href="../../assets/images/ICON.png">
    <link rel="stylesheet" href="../../assets/css/global.css">
</head>
<body class="auth-body">

<div class="register-container">
    <div class="register-card">

        <div class="register-logo">
            <img src="../../Assets/images/LOGO.png" alt="SiAGRI Logo">
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
                <span>Saya setuju dengan <a href="../../pages/general/terms-of-service.php">Syarat & Ketentuan</a></span>
            </div>

            <button type="submit" name="register" class="btn-register">Daftar Sekarang</button>

        </form>

        <div class="login-link">
            Sudah punya akun? <a href="../../pages/auth/login.php">Login di sini</a>
        </div>

    </div>

    <div class="terms-footer">
        By continuing, you agree to our
        <a href="../../pages/general/terms-of-service.php">Terms of Service</a> and
        <a href="../../pages/general/privacy-policy.php">Privacy Policy</a>
    </div>
</div>

<script src="../../Assets/js/main.js"></script>
<script src="../../Assets/js/auth.js"></script>
</body>
</html>