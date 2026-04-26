<?php
session_start();
// Jika user sudah login, langsung lempar ke halaman mereka masing-masing
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'Farmer') {
        header("Location: catalog.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiAGRI | Empowering Agriculture</title>
    <link rel="stylesheet" href="Assets/css/tes.css">
    
    <style>
        .hero-section {
            text-align: center;
            padding: 100px 20px;
            background: linear-gradient(rgba(46, 125, 50, 0.05), rgba(46, 125, 50, 0.1));
            min-height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .hero-section h1 {
            font-size: 3rem;
            color: var(--primary-hover);
            margin-bottom: 20px;
        }
        .hero-section p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin-bottom: 30px;
        }
        .hero-buttons {
            display: flex;
            gap: 15px; /* Jarak antar tombol */
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-logo">🌱 SiAGRI</a>
        <div class="navbar-links">
            <a href="index.php">Home</a>
            <a href="#">About</a>
            <a href="login-page.php" class="btn btn-outline" style="margin-left: 20px;">Login</a>
        </div>
    </nav>

    <div class="hero-section">
        <h1>Smart Agriculture Marketplace</h1>
        <p>Connecting farmers directly with trusted agricultural kiosks. Find the best fertilizers, seeds, and tools at the right price.</p>
        
        <div class="hero-buttons">
            <a href="register.php" class="btn">Join as Farmer</a>
            <a href="register.php" class="btn btn-outline">Register Kiosk</a>
        </div>
    </div>

</body>
</html>