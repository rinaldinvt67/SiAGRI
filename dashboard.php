<?php
session_start();
require_once 'koneksi.php';

// 1. Security Check: Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit;
}

// 2. Role Protection: Ensure only Kiosk can access this
if ($_SESSION['role'] !== 'Kiosk') {
    header("Location: catalog.php"); // Send Farmers back to catalog
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Dashboard | SiAGRI</title>
    <link rel="stylesheet" href="Assets/css/style.css">
    <style>
        .dashboard-container { padding: 20px; font-family: sans-serif; }
        .nav-card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn-action { display: inline-block; padding: 10px 20px; background: #2e7d32; color: #fff; text-decoration: none; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h1>Welcome, Store Manager! 👋</h1>
    <p>Logged in as: <strong><?php echo $_SESSION['username']; ?></strong></p>

    <div class="nav-card">
        <h3>Quick Actions</h3>
        <a href="add-product.php" class="btn-action">+ Add New Product</a>
        <a href="manage-catalog.php" class="btn-action">Manage My Catalog</a>
        <a href="edit-profile.php" class="btn-action">Edit Store Profile</a>
    </div>

    <div class="nav-card">
        <a href="logout.php" style="color: red;">Logout</a>
    </div>
</div>

</body>
</html>