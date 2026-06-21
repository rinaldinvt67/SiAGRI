<?php
$path_prefix = '';

session_start();
// Kalau sudah login, redirect sesuai role
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] === 'Farmer') {
        header("Location: pages/farmer/catalog.php");
    } elseif ($_SESSION['role'] === 'Kiosk') {
        header("Location: pages/kiosk/dashboard.php");
    } elseif ($_SESSION['role'] === 'Admin') {
        header("Location: pages/admin/dashboard.php");
    } else {
        header("Location: pages/farmer/catalog.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="../../assets/images/ICON.png">
    <?php
    $page_title = 'Sistem Informasi Agrikultur';
    $extra_head = '';
    include 'component/layout/head.php';
    ?>
</head>
<body class="bg-siagri-light text-gray-800 overflow-x-hidden">

<?php $current_page = 'index'; include 'component/layout/navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero-bg min-h-screen flex items-center justify-center pt-16">
    <div class="max-w-4xl mx-auto px-5 text-center text-white">

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
            <a href="pages/auth/register.php"
               class="bg-siagri-gold text-siagri-dark font-bold px-8 py-4 rounded-full
                      text-base hover:bg-yellow-400 transition shadow-xl hover:scale-105 transform">
                Daftar sebagai Petani
            </a>
            <a href="pages/auth/register.php?role=kiosk"
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

<!-- STATS -->
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

<!-- FITUR UNGGULAN -->
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
                    <span class="text-2xl"><svg class="inline-block w-7 h-7 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg></span>
                </div>
                <h3 class="font-bold text-siagri-dark text-lg mb-3">Kios Terverifikasi (KYC)</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Setiap Mitra Kios wajib mengunggah dokumen legalitas (NIB/SIUP/SPJB).
                    Admin memverifikasi manual, belanja hanya dari kios resmi berlisensi.
                </p>
                <div class="mt-4 inline-flex items-center gap-1 text-siagri-green text-xs font-semibold">
                    Badge "Kios Resmi" di katalog
                </div>
            </div>

            <!-- Fitur 2 -->
            <div class="feature-card bg-siagri-light rounded-2xl p-8 border border-green-100">
                <div class="w-14 h-14 bg-siagri-dark rounded-2xl flex items-center justify-center mb-5">
                    <span class="text-2xl"><svg class="inline-block w-7 h-7 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="white" d="M2.237 2.288a.75.75 0 1 0-.474 1.423l.265.089c.676.225 1.124.376 1.453.529c.312.145.447.262.533.382s.155.284.194.626c.041.361.042.833.042 1.546v2.672c0 1.367 0 2.47.117 3.337c.12.9.38 1.658.982 2.26c.601.602 1.36.86 2.26.981c.866.117 1.969.117 3.336.117H18a.75.75 0 0 0 0-1.5h-7c-1.435 0-2.436-.002-3.192-.103c-.733-.099-1.122-.28-1.399-.556c-.235-.235-.4-.551-.506-1.091h10.12c.959 0 1.438 0 1.814-.248s.565-.688.943-1.57l.428-1c.81-1.89 1.215-2.834.77-3.508S18.506 6 16.45 6H5.745a9 9 0 0 0-.047-.833c-.055-.485-.176-.93-.467-1.333c-.291-.404-.675-.66-1.117-.865c-.417-.194-.946-.37-1.572-.58zM7.5 18a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3m9 0a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3"/></svg></span>
                </div>
                <h3 class="font-bold text-siagri-dark text-lg mb-3">Klik & Ambil</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Pesan produk secara online, stok langsung terkunci 24 jam.
                    Datang ke kios, bayar kontan, dan bawa pulang barangnya.
                    Mudah, aman, tanpa ribet.
                </p>
                <div class="mt-4 inline-flex items-center gap-1 text-siagri-green text-xs font-semibold">
                    <span class="text-siagri-gold"></span>
                    Timer otomatis 24 jam
                </div>
            </div>

            <!-- Fitur 3 -->
            <div class="feature-card bg-siagri-light rounded-2xl p-8 border border-green-100">
                <div class="w-14 h-14 bg-siagri-dark rounded-2xl flex items-center justify-center mb-5">
                    <span class="text-2xl"><svg class="inline-block w-7 h-7 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="white" fill-rule="evenodd" d="M12.052 1.25h-.104c-.899 0-1.648 0-2.242.08c-.628.084-1.195.27-1.65.725c-.456.456-.642 1.023-.726 1.65c-.057.427-.074 1.446-.078 2.32c-2.022.067-3.237.303-4.08 1.147C2 8.343 2 10.229 2 14s0 5.657 1.172 6.828S6.229 22 10 22h4c3.771 0 5.657 0 6.828-1.172S22 17.771 22 14s0-5.657-1.172-6.828c-.843-.844-2.058-1.08-4.08-1.146c-.004-.875-.02-1.894-.078-2.32c-.084-.628-.27-1.195-.726-1.65c-.455-.456-1.022-.642-1.65-.726c-.594-.08-1.344-.08-2.242-.08m3.196 4.752c-.005-.847-.019-1.758-.064-2.097c-.063-.461-.17-.659-.3-.789s-.328-.237-.79-.3c-.482-.064-1.13-.066-2.094-.066s-1.612.002-2.095.067c-.461.062-.659.169-.789.3s-.237.327-.3.788c-.045.34-.06 1.25-.064 2.097Q9.34 5.999 10 6h4q.662 0 1.248.002M12 9.25a.75.75 0 0 1 .75.75v.01c1.089.274 2 1.133 2 2.323a.75.75 0 0 1-1.5 0c0-.384-.426-.916-1.25-.916s-1.25.532-1.25.916s.426.917 1.25.917c1.385 0 2.75.96 2.75 2.417c0 1.19-.911 2.048-2 2.323V18a.75.75 0 0 1-1.5 0v-.01c-1.089-.274-2-1.133-2-2.323a.75.75 0 0 1 1.5 0c0 .384.426.916 1.25.916s1.25-.532 1.25-.916s-.426-.917-1.25-.917c-1.385 0-2.75-.96-2.75-2.417c0-1.19.911-2.049 2-2.323V10a.75.75 0 0 1 .75-.75" clip-rule="evenodd"/></svg></span>
                </div>
                <h3 class="font-bold text-siagri-dark text-lg mb-3">Transparansi HET</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Harga Eceran Tertinggi (HET) pupuk subsidi ditampilkan jelas.
                    Label hijau jika harga aman, label merah jika melanggar HET.
                    Petani terlindungi dari harga tidak wajar.
                </p>
                <div class="mt-4 inline-flex items-center gap-1 text-siagri-green text-xs font-semibold">
                    <span class="text-green-500"></span>
                    Indikator harga real-time
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CARA KERJA -->
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
                ['num'=>'1', 'icon'=>'<svg class="inline-block w-6 h-6 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M21.194 2.806a2.753 2.753 0 0 1 0 3.893l-.496.496a5 5 0 0 1-.533-.151a5.2 5.2 0 0 1-1.968-1.241a5.2 5.2 0 0 1-1.241-1.968a5 5 0 0 1-.15-.533l.495-.496a2.753 2.753 0 0 1 3.893 0M14.58 13.313c-.404.404-.606.606-.829.78a4.6 4.6 0 0 1-.848.524c-.255.121-.526.211-1.068.392l-2.858.953a.742.742 0 0 1-.939-.94l.953-2.857c.18-.542.27-.813.392-1.068q.217-.453.524-.848c.174-.223.376-.425.78-.83l4.916-4.915a6.7 6.7 0 0 0 1.533 2.36a6.7 6.7 0 0 0 2.36 1.533z"/><path fill="currentColor" d="M20.536 20.536C22 19.07 22 16.714 22 12c0-1.548 0-2.842-.052-3.934l-6.362 6.362c-.351.352-.615.616-.912.847a6 6 0 0 1-1.125.696c-.34.162-.694.28-1.166.437l-2.932.977a2.242 2.242 0 0 1-2.836-2.836l.977-2.932c.157-.472.275-.826.437-1.166q.287-.6.696-1.125c.231-.297.495-.56.847-.912l6.362-6.362C14.842 2 13.548 2 12 2C7.286 2 4.929 2 3.464 3.464C2 4.93 2 7.286 2 12s0 7.071 1.464 8.535C4.93 22 7.286 22 12 22s7.071 0 8.535-1.465"/></svg>', 'title'=>'Daftar Akun',       'desc'=>'Buat akun sebagai Petani atau Mitra Kios. Gratis dan cepat.'],
                ['num'=>'2', 'icon'=>'<svg class="inline-block w-6 h-6 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14"></path></svg>', 'title'=>'Cari Produk',       'desc'=>'Filter berdasarkan kategori. Pupuk, Benih, Alat, Pestisida.'],
                ['num'=>'3', 'icon'=>'<svg class="inline-block w-6 h-6 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m17.578 4.432l-2-1.05C13.822 2.461 12.944 2 12 2s-1.822.46-3.578 1.382l-.321.169l8.923 5.099l4.016-2.01c-.646-.732-1.688-1.279-3.462-2.21m4.17 3.534l-3.998 2V13a.75.75 0 0 1-1.5 0v-2.286l-3.5 1.75v9.44c.718-.179 1.535-.607 2.828-1.286l2-1.05c2.151-1.129 3.227-1.693 3.825-2.708c.597-1.014.597-2.277.597-4.8v-.117c0-1.893 0-3.076-.252-3.978M11.25 21.904v-9.44l-8.998-4.5C2 8.866 2 10.05 2 11.941v.117c0 2.525 0 3.788.597 4.802c.598 1.015 1.674 1.58 3.825 2.709l2 1.049c1.293.679 2.11 1.107 2.828 1.286M2.96 6.641l9.04 4.52l3.411-1.705l-8.886-5.078l-.103.054c-1.773.93-2.816 1.477-3.462 2.21"/></svg>', 'title'=>'Pesan & Kunci Stok','desc'=>'Klik pesan, stok otomatis terkunci 24 jam hanya untukmu.'],
                ['num'=>'4', 'icon'=>'<svg class="inline-block w-6 h-6 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"/></svg>', 'title'=>'Ambil & Bayar',     'desc'=>'Datang ke kios, bayar kontan, dan bawa pulang produknya.'],
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

<!-- KATEGORI PRODUK -->
<section id="kategori" class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-5">

        <div class="text-center mb-12">
            <span class="text-siagri-green text-sm font-semibold uppercase tracking-widest">Tersedia di Platform</span>
            <h2 class="text-3xl md:text-4xl font-bold text-siagri-dark mt-2">Kategori Produk</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-5">
            <?php
            $cats = [
                ['icon'=>'<svg class="inline-block w-8 h-8 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M11.25 2.083a3.5 3.5 0 0 0-.713.243C6.553 4.188 4 9.395 4 13.856c0 4.24 3.183 7.724 7.25 8.109zm1.5 19.882c4.067-.385 7.25-3.868 7.25-8.108q0-.61-.063-1.234l-7.187 7.188zM18.26 7.18a13.4 13.4 0 0 0-1.34-2.04l-4.17 4.17v3.38zm-2.352-3.15a9.2 9.2 0 0 0-2.445-1.704a3.5 3.5 0 0 0-.713-.243v5.106zm3.028 4.594l-6.186 6.187v2.878l6.75-6.75l.132-.132a15 15 0 0 0-.696-2.183"/></svg>', 'name'=>'Pupuk Subsidi', 'color'=>'bg-green-50 border-green-200'],
                ['icon'=>'<svg class="inline-block w-8 h-8 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M8.267 1.618a.75.75 0 0 1 1.027-.264l.832.492l9.247 5.307a.75.75 0 1 1-.747 1.301l-.843-.484l-1.505 2.598l-.002-.002l-2.558-1.471a.75.75 0 1 0-.748 1.3l2.556 1.47l-.961 1.66l-.002-.001l-4.203-2.418a.75.75 0 1 0-.748 1.3l4.2 2.417l-.885 1.529l-.002-.002l-2.613-1.503a.75.75 0 0 0-.748 1.3l2.611 1.502l-1.12 1.932a4.86 4.86 0 0 1-6.628 1.77a4.827 4.827 0 0 1-1.776-6.605L9.373 3.143l-.006-.003l-.836-.494a.75.75 0 0 1-.264-1.028M20 17c1.105 0 2-.933 2-2.083c0-.72-.783-1.681-1.37-2.3a.86.86 0 0 0-1.26 0c-.587.619-1.37 1.58-1.37 2.3c0 1.15.895 2.083 2 2.083"/></svg>', 'name'=>'Pupuk Non-Subsidi', 'color'=>'bg-blue-50 border-blue-200'],
                ['icon'=>'<svg class="inline-block w-10 h-10 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12 12.5c0-3.5-2.8-6.5-6.5-6.5C5.5 9.7 8.5 12.5 12 12.5z"/>
                    <path fill="currentColor" d="M12 12.5 C12 8 15 4.5 19.5 4 C19.8 9 16.5 12.3 12 12.5 Z"/>
                    <path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" d="M12 22v-9.5"/>
                    </svg>', 'name'=>'Benih', 'color'=>'bg-yellow-50 border-yellow-200'],
                ['icon'=>'<svg class="inline-block w-8 h-8 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9c-2-2-5-2.4-7.4-1.3L9 6L6 9L1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4"/></svg>', 'name'=>'Alat Tani', 'color'=>'bg-orange-50 border-orange-200'],
                ['icon'=>'<svg class="inline-block w-8 h-8 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M17.416 2.624a.75.75 0 1 0-.832-1.248L13.669 3.32A4.5 4.5 0 0 0 12 3c-.59 0-1.153.113-1.669.32L7.416 1.376a.75.75 0 0 0-.832 1.248l2.36 1.573a4.5 4.5 0 0 0-1.368 2.475A5.5 5.5 0 0 1 8.938 6.5h6.125q.707.002 1.361.172a4.5 4.5 0 0 0-1.368-2.475zM1.25 14a.75.75 0 0 1 .75-.75h3v-1.312c0-.836.26-1.611.704-2.248l-2.483-.994a.75.75 0 0 1 .558-1.392l3.136 1.254A3.9 3.9 0 0 1 8.938 8h6.124c.74 0 1.432.204 2.023.558l3.136-1.254a.75.75 0 1 1 .558 1.392l-2.483.994A3.9 3.9 0 0 1 19 11.938v1.312h3a.75.75 0 0 1 0 1.5h-3V15a7 7 0 0 1-.808 3.269l2.587 1.035a.75.75 0 0 1-.558 1.393l-2.892-1.158a7 7 0 0 1-4.579 2.421V15a.75.75 0 1 0-1.5 0v6.96a7 7 0 0 1-4.579-2.42L3.78 20.696a.75.75 0 1 1-.558-1.393l2.588-1.035A7 7 0 0 1 5 15v-.25H2a.75.75 0 0 1-.75-.75"/></svg>', 'name'=>'Pestisida', 'color'=>'bg-red-50 border-red-200'],
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
            <a href="pages/auth/register.php"
               class="inline-flex items-center gap-2 bg-siagri-dark text-white font-bold px-8 py-3 rounded-full
                      hover:bg-siagri-green transition shadow-lg">
                Lihat Semua Produk
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current">
                    <path d="M4 11h12.17l-5.59-5.59L12 4l8 8l-8 8l-1.41-1.41L16.17 13H4z"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="hero-bg py-24">
    <div class="max-w-3xl mx-auto px-5 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-extrabold mb-5">
            Siap Mulai Bertani Lebih Cerdas?
        </h2>
        <p class="text-white/70 mb-10 text-lg">
            Bergabung dengan ribuan petani yang sudah merasakan kemudahan berbelanja kebutuhan pertanian secara digital.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="pages/auth/register.php"
               class="bg-siagri-gold text-siagri-dark font-bold px-8 py-4 rounded-full
                      hover:bg-yellow-400 transition shadow-xl text-base">
                Daftar Sekarang!
            </a>
            <a href="pages/auth/login.php"
               class="bg-white/10 border border-white/30 text-white font-bold px-8 py-4
                      rounded-full hover:bg-white/20 transition text-base">
                Sudah punya akun? Masuk
            </a>
        </div>
    </div>
</section>

<?php include 'component/layout/footer.php'; ?>

</body>
</html>