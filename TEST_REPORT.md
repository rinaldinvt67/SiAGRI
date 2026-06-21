# Laporan Pengujian QA SiAGRI

## 1. Verifikasi Navbar Farmer
- Desktop: Tautan "Pesanan Saya" terverifikasi dihapus dari navbar.php.
- Mobile: Menu mobile "Pesanan Saya" terverifikasi dihapus dari navbar.php.

## 2. Rute Navigasi Profil Farmer
- Klik "Pesanan Saya" pada widget Aksi Cepat profile.php berhasil dialihkan ke my-orders.php.

## 3. Verifikasi Favicon
- Ikon SiAGRI (ICON.png) muncul di tab browser pada halaman: index, catalog, login, register, profile, dashboard, forum, admin-dashboard, my-orders, incoming-orders.

## 4. Verifikasi Konsol JavaScript
- Pemuatan Chart.js CDN berjalan tanpa error/warnings pada developer tools console.

## 5. Validasi Data Statistik Omset
- Total transaksi sukses per bulan di grafik terverifikasi sinkron 100% dengan tabel `orders` database.

## 6. Log Transaksi Terbaru
- Data nama pemesan dan nominal harga pada panel samping menampilkan data terbaru secara real-time.

## 7. Papan Peringkat Kios
- Omset dan jumlah order selesai kios teraktif terverifikasi sesuai hasil agregasi SQL SUM & COUNT.

## 8. Responsivitas Layar
- Grafik Chart.js menyusut proporsional saat lebar browser diubah ke format mobile 360px.

## 9. Keterbacaan Teks Dashboard
- Seluruh teks log aktivitas dan peringkat kios terbaca jelas dengan kontras warna yang baik.

## 10. Hak Akses Dashboard
- Pengguna non-admin (Kios/Petani) terverifikasi diblokir dan dialihkan ke login-page.php.

## 11. Update Status KYC
- Log KYC pending otomatis hilang dari daftar sesaat setelah status diubah menjadi verified.

## 12. Verifikasi Migrasi CSS
- Seluruh halaman yang inline CSS-nya dipindahkan ke global.css tetap tampil identik secara visual.
- File global.css berhasil di-load tanpa error 404 pada semua halaman.

## 13. Alur Pembatalan Pesanan
- Tombol "Batalkan" pada my-orders.php berhasil memanggil cancel-order.php via fetch() dan memperbarui status pesanan menjadi 'cancelled'.

## 14. Countdown Timer Akurasi
- Timer countdown pada kartu pesanan di my-orders.php berdetak tepat setiap detik dan menampilkan "Kedaluwarsa" saat expired_at terlewati.

## 15. Auto-Cancel Pesanan Expired (Kios)
- Pesanan berstatus pending yang melewati expired_at otomatis berubah menjadi 'cancelled' saat halaman incoming-orders.php dimuat.

## 16. Kesimpulan Pengujian
| Modul | Status |
|-------|--------|
| Navbar & Navigasi | ✅ PASSED |
| Favicon (semua halaman) | ✅ PASSED |
| Migrasi CSS ke global.css | ✅ PASSED |
| Chart.js Admin & Kiosk | ✅ PASSED |
| Alur Pesanan & Cancel | ✅ PASSED |
| Hak Akses & Keamanan | ✅ PASSED |

**Hasil akhir: SELURUH MODUL DINYATAKAN PASSED. Repositori SiAGRI siap untuk rilis final.**
