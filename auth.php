<?php
session_start();

$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS siagri");

mysqli_select_db($conn, "siagri");

$createTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255)
)";

mysqli_query($conn, $createTable);

if (isset($_POST['action'])) {

    if ($_POST['action'] == 'register') {

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        if ($password !== $confirm) {
            echo "Password tidak sama!";
            exit;
        }

        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            echo "Username sudah digunakan!";
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password)
                VALUES ('$username', '$email', '$hashedPassword')";

        if (mysqli_query($conn, $sql)) {
            echo "Register berhasil! <br><a href='Login page.html'>Login</a>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    if ($_POST['action'] == 'login') {

        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $username;

            header("Location: dashboard.php");
            exit;
        } else {
            echo "Username atau password salah!";
        }
    }
}
?>