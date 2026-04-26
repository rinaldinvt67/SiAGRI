<?php
session_start();
// Pakai require_once sesuai pertanyaanmu biar lebih aman
require_once 'koneksi.php'; 

$pesan_error = "";
$pesan_sukses = "";

// Jika tombol register ditekan
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    
    // Pastikan role diambil dari dropdown form kamu
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm) || empty($role)) {
        $pesan_error = "Semua kolom wajib diisi!";
    } elseif ($password !== $confirm) {
        $pesan_error = "Password tidak sama!";
    } else {
        // Cek email kembar
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        
        if (mysqli_num_rows($check) > 0) {
            $pesan_error = "Email sudah terdaftar!";
        } else {
            // Enkripsi dan simpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";
            
            if (mysqli_query($conn, $sql)) {
                $new_user_id = mysqli_insert_id($conn);

                // Buatkan profil Kios kalau yang daftar adalah kios
                if ($role === 'Kiosk') {
                    mysqli_query($conn, "INSERT INTO kiosk_profiles (user_id, store_name, full_address, whatsapp_number) VALUES ('$new_user_id', 'Unnamed Store', '-', '-')");
                }

                $pesan_sukses = "Daftar sukses! Silakan login.";
                header("refresh:2;url=login-page.php");
            } else {
                $pesan_error = "Gagal: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="Assets/css/style.css">
    <style>
    .kelas-dropdown {
    background-color: transparent; /* Atau ganti dengan kode warna hijau gelap yang kamu pakai, misal: #4A7A59 */
    color: white; /* Mengubah warna teks menjadi putih */
    border: 1px solid white; /* Menambahkan garis pinggir putih */
    border-radius: 8px; /* Membuat sudutnya melengkung (Sesuaikan angkanya dengan kolom inputmu) */
    padding: 10px 15px; /* Memberikan ruang lega di dalam kotak */
    width: 100%; /* Agar lebarnya sama panjang dengan input di atas dan bawahnya */
    font-size: 16px;
    font-family: inherit; /* Mengikuti jenis huruf web kamu */
    cursor: pointer;
}

/* GAYA RAHASIA: Mengubah warna anak panah bawaan menjadi putih (Bekerja di browser modern) */
.kelas-dropdown {
    color-scheme: dark; 
}

/* PENTING! Gaya saat opsi diklik agar tulisannya bisa dibaca */
.kelas-dropdown option {
    background-color: #4A7A59; /* GANTI DENGAN WARNA HIJAU BACKGROUND-MU */
    color: yellow;
} 
</style>
</head>

<body>
    <div class="wrapper">
        <form action="" method="POST" class="form-step2">
            <h1>Create Account</h1>
            <!-- <input type="hidden" name="action" value="register"> -->
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
            </div>
    
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div>
                <select name="role" class="kelas-dropdown" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="Farmer">Petani</option>
                    <option value="Kiosk">Mitra Kios</option>
                </select>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-box">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
    
            <div class="terms-check">
                <label>
                    <input type="checkbox" required> I agree to Terms & Privacy
                </label>
            </div>
    
            <button type="submit" name="register" class="btn">Register</button>
                <input type="radio" name="step" id="step1" checked hidden>
                <input type="radio" name="step" id="step2" hidden>
            </form>
            <!-- <button type="button" for="step1" class="btn back-btn" onclick="window.location.href='login-page.php'">Back</button> -->
            <a href="login-page.php" class="btn back-btn" style="text-decoration: none; display: inline-block; text-align: center;">Back</a>
    </div>
</body>

</html>