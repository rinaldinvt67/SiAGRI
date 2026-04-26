<?php
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
            } elseif ($user['role'] == 'Kiosk') {
                $kiosk_data = mysqli_query($conn, "SELECT * FROM kiosk_profiles WHERE user_id='".$user['user_id']."'");
                $kiosk_data = mysqli_fetch_assoc($kiosk_data);
                $_SESSION['kiosk_id'] = $kiosk_data['kiosk_id'];
                header("Location: dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
        } else {
            $error_message = "Incorrect password! Please try again.";
        }
    } else {
        $error_message = "Username not found! Please register first.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="Assets/css/style.css">    
</head>

<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h1>Login</h1>
            <div class="input-box">
                <label>Username</label>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="remember-forgot">
                <label><input type="checkbox"> Remember me</label>
                <a href="#">Forgot Password?</a>
            </div>

            <button type="submit" name="login" class="btn">Login</button>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php" class="register-link">Register</a></p>
            </div>
        </form>
    </div>
    <div class="terms-outside">
    <p>By continuing, you agree to our 
        <a href="#">Terms of Service</a> and 
        <a href="#">Privacy Policy</a>
    </p>
</div>
</body>

</html>