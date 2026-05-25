<?php
session_start();
// Kalau sudah login, redirect sesuai role
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] === 'Farmer') {
        header("Location: catalog.php");
    } elseif ($_SESSION['role'] === 'Admin') {
        header("Location: admin-dashboard.php");
    } elseif ($_SESSION['role'] === 'Expert') {
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiAGRI — Sistem Informasi Agrikultur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'siagri-dark':  '#164a41',
                        'siagri-green': '#4d774e',
                        'siagri-gold':  '#f1b24a',
                        'siagri-light': '#f0f7f4',
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }

        /* Sawah background overlay */
        .hero-bg {
            background-image:
                linear-gradient(to bottom, rgba(22,74,65,0.82), rgba(22,74,65,0.65)),
                url('Assets/images/sawah.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-12px); }
        }
        .float-anim { animation: float 4s ease-in-out infinite; }

        /* Fade in up */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0);    }
        }
        .fade-in-1 { animation: fadeInUp 0.7s ease forwards; }
        .fade-in-2 { animation: fadeInUp 0.7s ease 0.2s forwards; opacity: 0; }
        .fade-in-3 { animation: fadeInUp 0.7s ease 0.4s forwards; opacity: 0; }
        .fade-in-4 { animation: fadeInUp 0.7s ease 0.6s forwards; opacity: 0; }

        /* Card hover */
        .feature-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .feature-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(22,74,65,0.15); }

        /* Stat counter */
        .stat-number { font-size: 2.5rem; font-weight: 800; color: #f1b24a; line-height: 1; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #164a41; border-radius: 3px; }
    </style>
</head>
<body class="bg-siagri-light text-gray-800 overflow-x-hidden">

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- NAVBAR                                                      -->
<!-- ═══════════════════════════════════════════════════════════ -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-siagri-dark/95 backdrop-blur-sm shadow-lg">
    <div class="max-w-7xl mx-auto px-5 py-3 flex items-center justify-between">

        <!-- Logo -->
        <a href="index.php" class="flex items-center gap-2">
            <img src="Assets/images/LOGO.png" alt="SiAGRI"
                 class="h-10 w-auto" onerror="this.style.display='none'">
            <span class="text-white font-bold text-xl tracking-wide">SiAGRI</span>
        </a>

        <!-- Nav links desktop -->
        <div class="hidden md:flex items-center gap-8">
            <a href="#fitur"    class="text-white/80 hover:text-siagri-gold text-sm font-medium transition">Fitur</a>
            <a href="#cara-kerja" class="text-white/80 hover:text-siagri-gold text-sm font-medium transition">Cara Kerja</a>
            <a href="#kategori" class="text-white/80 hover:text-siagri-gold text-sm font-medium transition">Produk</a>
        </div>

        <!-- CTA -->
        <div class="flex items-center gap-3">
            <a href="login-page.php"
               class="text-white/80 hover:text-white text-sm font-medium transition hidden sm:block">
                Masuk
            </a>
            <a href="register.php"
               class="bg-siagri-gold text-siagri-dark text-sm font-bold px-4 py-2 rounded-full
                      hover:bg-yellow-400 transition shadow-md">
                Daftar Gratis
            </a>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- HERO SECTION                                                -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hero-bg min-h-screen flex items-center justify-center pt-16">
    <div class="max-w-4xl mx-auto px-5 text-center text-white">

        <!-- Badge -->
        <div class="fade-in-1 inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm
                    border border-white/20 rounded-full px-4 py-2 text-sm mb-6">
            <span class="w-2 h-2 bg-siagri-gold rounded-full animate-pulse"></span>
            Platform Agrikultur Terpercaya
        </div>

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
                🌾 Daftar sebagai Petani
            </a>
            <a href="register.php?role=kiosk"
               class="bg-white/10 backdrop-blur-sm border border-white/30 text-white
                      font-bold px-8 py-4 rounded-full text-base hover:bg-white/20 transition">
                🏪 Daftarkan Kios Anda
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

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- STATS                                                       -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="bg-siagri-dark py-12">
    <div class="max-w-5xl mx-auto px-5 grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
        <div>
            <div class="stat-number">500+</div>
            <p class="text-white/60 text-sm mt-1">Petani Terdaftar</p>
        </div>
        <div>
            <div class="stat-number">80+</div>
            <p class="text-white/60 text-sm mt-1">Mitra Kios</p>
        </div>
        <div>
            <div class="stat-number">1.200+</div>
            <p class="text-white/60 text-sm mt-1">Produk Tersedia</p>
        </div>
        <div>
            <div class="stat-number">5K+</div>
            <p class="text-white/60 text-sm mt-1">Transaksi Sukses</p>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- FITUR UNGGULAN                                              -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section id="fitur" class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-5">

        <div class="text-center mb-14">
            <span class="text-siagri-green text-sm font-semibold uppercase tracking-widest">Kenapa SiAGRI?</span>
            <h2 class="text-3xl md:text-4xl font-bold text-siagri-dark mt-2">
                Platform yang Dirancang untuk Petani
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Fitur 1 -->
            <div class="feature-card bg-siagri-light rounded-2xl p-8 border border-green-100">
                <div class="w-14 h-14 bg-siagri-dark rounded-2xl flex items-center justify-center mb-5">
                    <span class="text-2xl">✅</span>
                </div>
                <h3 class="font-bold text-siagri-dark text-lg mb-3">Kios Terverifikasi (KYC)</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Setiap Mitra Kios wajib mengunggah dokumen legalitas (NIB/SIUP/SPJB).
                    Admin memverifikasi manual — kamu belanja hanya dari kios resmi berlisensi.
                </p>
                <div class="mt-4 inline-flex items-center gap-1 text-siagri-green text-xs font-semibold">
                    <span class="w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs">✓</span>
                    Badge "Kios Resmi" di katalog
                </div>
            </div>

            <!-- Fitur 2 -->
            <div class="feature-card bg-siagri-light rounded-2xl p-8 border border-green-100">
                <div class="w-14 h-14 bg-siagri-dark rounded-2xl flex items-center justify-center mb-5">
                    <span class="text-2xl">🛒</span>
                </div>
                <h3 class="font-bold text-siagri-dark text-lg mb-3">Klik & Ambil</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Pesan produk secara online, stok langsung terkunci 24 jam.
                    Datang ke kios, bayar kontan, dan bawa pulang barangnya.
                    Mudah, aman, tanpa ribet.
                </p>
                <div class="mt-4 inline-flex items-center gap-1 text-siagri-green text-xs font-semibold">
                    <span class="text-siagri-gold">⏱</span>
                    Timer otomatis 24 jam
                </div>
            </div>

            <!-- Fitur 3 -->
            <div class="feature-card bg-siagri-light rounded-2xl p-8 border border-green-100">
                <div class="w-14 h-14 bg-siagri-dark rounded-2xl flex items-center justify-center mb-5">
                    <span class="text-2xl">💰</span>
                </div>
                <h3 class="font-bold text-siagri-dark text-lg mb-3">Transparansi HET</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Harga Eceran Tertinggi (HET) pupuk subsidi ditampilkan jelas.
                    Label hijau ✅ jika harga aman, label merah 🔴 jika melanggar HET.
                    Petani terlindungi dari harga tidak wajar.
                </p>
                <div class="mt-4 inline-flex items-center gap-1 text-siagri-green text-xs font-semibold">
                    <span class="text-green-500">🟢</span>
                    Indikator harga real-time
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- CARA KERJA                                                  -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section id="cara-kerja" class="py-20 bg-siagri-light">
    <div class="max-w-5xl mx-auto px-5">

        <div class="text-center mb-14">
            <span class="text-siagri-green text-sm font-semibold uppercase tracking-widest">Prosesnya Sederhana</span>
            <h2 class="text-3xl md:text-4xl font-bold text-siagri-dark mt-2">Cara Kerja SiAGRI</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">

            <!-- Connector line desktop -->
            <div class="hidden md:block absolute top-8 left-[12.5%] right-[12.5%] h-0.5 bg-siagri-dark/20 z-0"></div>

            <?php
            $steps = [
                ['num'=>'1', 'icon'=>'📝', 'title'=>'Daftar Akun',       'desc'=>'Buat akun sebagai Petani atau Mitra Kios. Gratis dan cepat.'],
                ['num'=>'2', 'icon'=>'🔍', 'title'=>'Cari Produk',       'desc'=>'Filter berdasarkan kategori — Pupuk, Benih, Alat, Pestisida.'],
                ['num'=>'3', 'icon'=>'📦', 'title'=>'Pesan & Kunci Stok','desc'=>'Klik pesan, stok otomatis terkunci 24 jam hanya untukmu.'],
                ['num'=>'4', 'icon'=>'🏪', 'title'=>'Ambil & Bayar',     'desc'=>'Datang ke kios, bayar kontan, dan bawa pulang produknya.'],
            ];
            foreach ($steps as $s): ?>
            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-siagri-dark text-white rounded-full flex items-center justify-center
                             text-2xl shadow-lg mb-4 border-4 border-siagri-light">
                    <?= $s['icon'] ?>
                </div>
                <div class="bg-siagri-gold/10 text-siagri-dark text-xs font-bold px-2 py-0.5 rounded-full mb-2">
                    Langkah <?= $s['num'] ?>
                </div>
                <h4 class="font-bold text-siagri-dark mb-2"><?= $s['title'] ?></h4>
                <p class="text-gray-500 text-sm"><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- KATEGORI PRODUK                                             -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section id="kategori" class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-5">

        <div class="text-center mb-12">
            <span class="text-siagri-green text-sm font-semibold uppercase tracking-widest">Tersedia di Platform</span>
            <h2 class="text-3xl md:text-4xl font-bold text-siagri-dark mt-2">Kategori Produk</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-5">
            <?php
            $cats = [
                ['icon'=>'🌿', 'name'=>'Pupuk Subsidi',     'color'=>'bg-green-50 border-green-200'],
                ['icon'=>'🧪', 'name'=>'Pupuk Non-Subsidi', 'color'=>'bg-blue-50 border-blue-200'],
                ['icon'=>'🌾', 'name'=>'Benih',             'color'=>'bg-yellow-50 border-yellow-200'],
                ['icon'=>'🔧', 'name'=>'Alat Tani',         'color'=>'bg-orange-50 border-orange-200'],
                ['icon'=>'🐛', 'name'=>'Pestisida',         'color'=>'bg-red-50 border-red-200'],
            ];
            foreach ($cats as $c): ?>
            <div class="<?= $c['color'] ?> border rounded-2xl p-5 text-center hover:scale-105
                         transition cursor-pointer">
                <div class="text-4xl mb-3"><?= $c['icon'] ?></div>
                <p class="text-sm font-semibold text-siagri-dark"><?= $c['name'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="register.php"
               class="inline-block bg-siagri-dark text-white font-bold px-8 py-3 rounded-full
                      hover:bg-siagri-green transition shadow-lg">
                Lihat Semua Produk →
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- CTA SECTION                                                 -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hero-bg py-24">
    <div class="max-w-3xl mx-auto px-5 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-extrabold mb-5">
            Siap Mulai Bertani Lebih Cerdas?
        </h2>
        <p class="text-white/70 mb-10 text-lg">
            Bergabung dengan ribuan petani yang sudah merasakan kemudahan berbelanja kebutuhan pertanian secara digital.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="register.php"
               class="bg-siagri-gold text-siagri-dark font-bold px-8 py-4 rounded-full
                      hover:bg-yellow-400 transition shadow-xl text-base">
                Daftar Sekarang!
            </a>
            <a href="login-page.php"
               class="bg-white/10 border border-white/30 text-white font-bold px-8 py-4
                      rounded-full hover:bg-white/20 transition text-base">
                Sudah punya akun? Masuk
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- FOOTER                                                      -->
<!-- ═══════════════════════════════════════════════════════════ -->
<?php include 'component/layout/footer.php'; ?>

</body>
</html>
