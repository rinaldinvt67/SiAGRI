<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="../../assets/images/ICON.png">
    <?php $page_title = 'Syarat & Ketentuan Layanan'; include '../../component/layout/head.php'; ?>
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
                <h1 class="text-3xl font-extrabold text-siagri-dark mt-3">Syarat & Ketentuan Layanan</h1>
                <p class="text-siagri-muted text-sm mt-1">Terakhir Diperbarui: 26 Mei 2026</p>
            </div>
            <!-- Language Switcher -->
            <div class="flex justify-center sm:justify-end shrink-0">
                <a href="terms-of-service.php" 
                   class="flex items-center gap-1.5 border border-siagri-dark/20 text-siagri-dark hover:bg-siagri-dark hover:text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-300">
                    🇺🇸 English Version
                </a>
            </div>
        </div>

        <!-- Contents -->
        <div class="prose prose-sm max-w-none text-siagri-slate space-y-6 leading-relaxed">
            <p>
                Selamat datang di <strong>SiAGRI</strong> (Sistem Informasi Agrikultur). Dengan mengakses atau menggunakan platform kami, Anda setuju untuk terikat oleh Syarat dan Ketentuan berikut. Harap baca dokumen ini dengan saksama sebelum menggunakan layanan kami.
            </p>

            <hr class="border-gray-100 my-6">

            <!-- Poin 1 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">1. Ketentuan Umum & Keanggotaan</h2>
                <p>
                    SiAGRI menyediakan platform e-marketplace agrikultur lokal serta media diskusi yang menghubungkan Petani, Mitra Kios resmi, dan Pakar Pertanian. Pengguna wajib mendaftarkan diri secara jujur menggunakan data yang valid.
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-siagri-muted">
                    <li><strong>Petani:</strong> Dapat melakukan pemesanan barang pertanian dari Mitra Kios resmi.</li>
                    <li><strong>Mitra Kios:</strong> Wajib mematuhi ketentuan verifikasi toko dan HET (Harga Eceran Tertinggi) resmi pemerintah untuk produk tertentu.</li>
                    <li><strong>Pakar (Expert):</strong> Ditugaskan secara tertutup oleh Admin untuk memberikan konsultasi terpercaya di forum diskusi.</li>
                </ul>
            </div>

            <!-- Poin 2 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">2. Proses Verifikasi Kios (KYC)</h2>
                <p>
                    Untuk memastikan keamanan dan integritas transaksi, setiap Mitra Kios diwajibkan untuk mengunggah dokumen legalitas usaha yang sah (seperti NIB, SIUP, atau dokumen SPJB resmi). 
                </p>
                <p class="mt-2 text-sm text-siagri-muted">
                    Admin SiAGRI berhak meninjau, menyetujui, atau menolak dokumen tersebut demi menyaring agen-agen yang tidak resmi guna melindungi hak Petani dari pemalsuan produk pertanian.
                </p>
            </div>

            <!-- Poin 3 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">3. Sistem Transaksi Click & Collect (Self-Pickup)</h2>
                <p>
                    SiAGRI memfasilitasi metode pembelian secara <em>Click & Collect</em> untuk menanggulangi kendala logistik barang pertanian yang cenderung berbobot berat (seperti pupuk karung, alat tani besar).
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-siagri-muted">
                    <li>Pemesanan produk bersifat mengikat dalam waktu 24 jam.</li>
                    <li>Pembeli (Petani) harus mendatangi kios fisik penjual untuk mengambil barang pertanian.</li>
                    <li>Pembayaran dilakukan secara tunai/kontan langsung di Kios saat barang diambil.</li>
                    <li>Jika barang tidak diambil dalam kurun waktu 24 jam, pesanan akan dibatalkan otomatis demi menjaga ketersediaan stok Kios.</li>
                </ul>
            </div>

            <!-- Poin 4 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">4. Kebijakan Harga Eceran Tertinggi (HET)</h2>
                <p>
                    Untuk pupuk bersubsidi pemerintah, Kios dilarang keras menjual dengan harga melebihi HET yang telah ditetapkan secara sah oleh pemerintah. Melanggar HET akan menyebabkan pencabutan badge verifikasi "Kios Resmi" oleh Admin secara sepihak.
                </p>
            </div>

            <!-- Poin 5 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">5. Batasan Tanggung Jawab</h2>
                <p>
                    SiAGRI berperan sebagai fasilitator yang menjembatani transaksi dan edukasi informasi. Kami tidak bertanggung jawab secara langsung atas kualitas fisik produk pertanian yang dibeli, maupun perselisihan personal antara Petani dan Kios di luar mekanisme platform.
                </p>
            </div>

            <!-- Poin 6 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">6. Perubahan Syarat & Ketentuan</h2>
                <p>
                    SiAGRI berhak untuk mengubah, menambah, atau memperbarui Syarat & Ketentuan ini kapan saja tanpa pemberitahuan tertulis sebelumnya demi penyesuaian regulasi pertanian dan hukum di Indonesia.
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