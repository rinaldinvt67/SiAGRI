<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $page_title = 'Kebijakan Privasi'; include '../../component/layout/head.php'; ?>
</head>
<body class="bg-siagri-light min-h-screen flex flex-col">

<?php $current_page = ''; include '../../component/layout/navbar.php'; ?>

<main class="flex-1 max-w-4xl mx-auto px-6 py-12">
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 md:p-12 fade-in">
        <!-- Header -->
        <div class="border-b border-gray-100 pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-left">
                <span class="text-xs bg-siagri-gold/20 text-siagri-dark font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                    Dokumen Legal
                </span>
                <h1 class="text-3xl font-extrabold text-siagri-dark mt-3">Kebijakan Privasi</h1>
                <p class="text-siagri-muted text-sm mt-1">Terakhir Diperbarui: 26 Mei 2026</p>
            </div>
            <!-- Language Switcher -->
            <div class="flex justify-center sm:justify-end shrink-0">
                <a href="privacy-policy-en.php" 
                   class="flex items-center gap-1.5 border border-siagri-dark/20 text-siagri-dark hover:bg-siagri-dark hover:text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-300">
                    🇺🇸 English Version
                </a>
            </div>
        </div>

        <!-- Contents -->
        <div class="prose prose-sm max-w-none text-siagri-slate space-y-6 leading-relaxed">
            <p>
                Di <strong>SiAGRI</strong>, kami sangat menghargai dan berkomitmen penuh untuk melindungi privasi data pribadi Anda sebagai Petani, Kios, maupun Pakar. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi Anda saat berinteraksi dengan platform kami.
            </p>

            <hr class="border-gray-100 my-6">

            <!-- Poin 1 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">1. Informasi yang Kami Kumpulkan</h2>
                <p>
                    Kami mengumpulkan informasi yang diperlukan untuk jalannya operasional platform secara optimal, aman, dan tepercaya:
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-siagri-muted">
                    <li><strong>Informasi Pendaftaran:</strong> Username, alamat email, password, dan pilihan role saat Anda mendaftar.</li>
                    <li><strong>Informasi Profil (Tambahan):</strong> Alamat lengkap Kios, nomor WhatsApp, bidang keahlian Pakar, serta foto profil pendukung.</li>
                    <li><strong>Dokumen Legalitas KYC (Kios):</strong> Berkas legalitas usaha (NIB, SIUP, SPJB) dalam bentuk gambar/PDF untuk proses verifikasi.</li>
                    <li><strong>Informasi Pesanan:</strong> Detail produk tani yang dipesan, riwayat transaksi, dan status pemesanan.</li>
                </ul>
            </div>

            <!-- Poin 2 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">2. Cara Kami Menggunakan Informasi</h2>
                <p>
                    Data pribadi yang dikumpulkan digunakan secara bijak untuk tujuan berikut:
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-siagri-muted">
                    <li>Mengoperasikan, merawat, dan meningkatkan fitur-fitur transaksi SiAGRI.</li>
                    <li>Menjalankan proses manual verifikasi KYC bagi Kios guna meminimalisir penipuan produk tani bersubsidi.</li>
                    <li>Memfasilitasi komunikasi langsung via WhatsApp API antara Petani dan Kios saat melakukan konfirmasi pesanan secara fisik.</li>
                    <li>Menjaga keamanan sistem dan memverifikasi identitas pengguna saat terjadi kendala akses.</li>
                </ul>
            </div>

            <!-- Poin 3 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">3. Perlindungan & Keamanan Data</h2>
                <p>
                    Kami menerapkan standar perlindungan data yang kuat demi menjaga kerahasiaan informasi Anda:
                </p>
                <p class="mt-2 text-sm text-siagri-muted">
                    Semua password pengguna dienkripsi secara satu arah di server kami menggunakan algoritma kriptografi <strong>Bcrypt</strong>. Akses database diproteksi secara ketat dan file dokumen KYC disimpan dalam direktori khusus yang terlindungi dari manipulasi langsung.
                </p>
            </div>

            <!-- Poin 4 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">4. Pembagian Informasi dengan Pihak Ketiga</h2>
                <p>
                    SiAGRI **tidak pernah** menjual, menyewakan, memperdagangkan, atau membagikan informasi pribadi Anda kepada pihak ketiga untuk kepentingan komersial/iklan tanpa persetujuan tertulis eksplisit dari Anda.
                </p>
            </div>

            <!-- Poin 5 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">5. Hak Akses & Penghapusan Data</h2>
                <p>
                    Setiap pengguna memiliki hak penuh untuk memperbarui data profil pribadinya kapan saja melalui halaman **Profil**. Jika Anda ingin mengajukan permohonan penutupan akun secara permanen dari server kami, Anda dapat menghubungi Admin resmi SiAGRI.
                </p>
            </div>
        </div>

        <!-- Call to Action / Back Button -->
        <div class="border-t border-gray-100 mt-10 pt-6 flex justify-center">
            <a href="javascript:history.back()" 
               class="bg-siagri-dark text-white px-6 py-2.5 rounded-xl font-semibold text-sm hover:bg-siagri-green btn-lift flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8l8 8l1.41-1.41L7.83 13H20z"/>
                </svg>
                <span>Kembali ke Halaman Sebelumnya</span>
            </a>
        </div>
    </div>
</main>

<?php include '../../component/layout/footer.php'; ?>

</body>
</html>
