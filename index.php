<?php
session_start();
// Kalau sudah login, redirect sesuai role
if (isset($_SESSION['username'])) {
    // if ($_SESSION['role'] === 'Farmer') {
    //     header("Location: catalog.php");
    if ($_SESSION['role'] === 'Admin') {
        header("Location: admin-dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php
    $page_title = 'Sistem Informasi Agrikultur';
    $extra_head = '
    <style>
        .hero-bg {
            background-image:
                linear-gradient(to bottom, rgba(22,74,65,0.82), rgba(22,74,65,0.65)),
                url(Assets/images/sawah.jpg);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-12px); }
        }
        .float-anim { animation: float 4s ease-in-out infinite; }
        .fade-in-1 { animation: fadeInUp 0.7s ease forwards; }
        .fade-in-2 { animation: fadeInUp 0.7s ease 0.2s forwards; opacity: 0; }
        .fade-in-3 { animation: fadeInUp 0.7s ease 0.4s forwards; opacity: 0; }
        .fade-in-4 { animation: fadeInUp 0.7s ease 0.6s forwards; opacity: 0; }
        .feature-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .feature-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(22,74,65,0.15); }
        .stat-number { font-size: 2.5rem; font-weight: 800; color: #f1b24a; line-height: 1; }
    </style>';
    include 'komponen/layout/head.php';
    ?>
</head>
<body class="bg-siagri-light text-gray-800 overflow-x-hidden">

<?php $current_page = 'index'; include 'komponen/layout/navbar.php'; ?>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- HERO SECTION                                                -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hero-bg min-h-screen flex items-center justify-center pt-16">
    <div class="max-w-4xl mx-auto px-5 text-center text-white">

        <!-- Badge -->
        <!-- <div class="fade-in-1 inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm
                    border border-white/20 rounded-full px-4 py-2 text-sm mb-6">
            <span class="w-2 h-2 bg-siagri-gold rounded-full animate-pulse"></span>
            Platform Agrikultur Terpercaya di Mataram
        </div> -->

        <h1 class="fade-in-2 text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-6">
            Pertanian Lebih Mudah<br>
            <span class="text-siagri-gold">Dimulai dari Sini</span>
        </h1>

        <p class="fade-in-3 text-lg text-white/80 max-w-2xl mx-auto leading-relaxed mb-10">
            SiAGRI menghubungkan petani lokal dengan Mitra Kios terpercaya.
            Temukan pupuk, benih, dan alat tani dengan harga transparan.
            Pesan online, bayar & ambil langsung di kios.
        </p>

        <div class="fade-in-4 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="register.php"
               class="bg-siagri-gold text-siagri-dark font-bold px-8 py-4 rounded-full
                      text-base hover:bg-yellow-400 transition shadow-xl hover:scale-105 transform">
                Daftar sebagai Petani
            </a>
            <a href="register.php?role=kiosk"
               class="bg-white/10 backdrop-blur-sm border border-white/30 text-white
                      font-bold px-8 py-4 rounded-full text-base hover:bg-white/20 transition">
                Daftarkan Kios Anda
            </a>
        </div>

        <!-- Scroll indicator -->
        <div class="mt-16 flex justify-center animate-bounce">
            <svg class="w-6 h-6 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
</section>