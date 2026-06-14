<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit;
}

if ($_SESSION['role'] !== 'Kiosk') {
    header("Location: catalog.php");
    exit;
}

$kiosk_id = $_SESSION['kiosk_id'];

// Lazy check: batalkan pesanan expired
$now = date('Y-m-d H:i:s');
$expired = mysqli_query($conn,
    "SELECT o.order_id, oi.product_id, oi.quantity
     FROM orders o
     JOIN order_items oi ON o.order_id = oi.order_id
     WHERE o.status = 'pending' AND o.expired_at < '$now'
     AND o.kiosk_id = $kiosk_id"
);
if ($expired) {
    while ($exp = mysqli_fetch_assoc($expired)) {
        mysqli_query($conn,
            "UPDATE products SET stock = stock + {$exp['quantity']}
             WHERE product_id = {$exp['product_id']}"
        );
    }
    mysqli_query($conn,
        "UPDATE orders SET status = 'cancelled'
         WHERE status = 'pending' AND expired_at < '$now'
         AND kiosk_id = $kiosk_id"
    );
}

// Ambil info kiosk
$kiosk_info = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM kiosk_profiles WHERE kiosk_id = $kiosk_id"
));

// Statistik
$total_produk = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM products WHERE kiosk_id = $kiosk_id"
))['total'] ?? 0;

$pesanan_pending = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM orders
     WHERE kiosk_id = $kiosk_id AND status = 'pending'"
))['total'] ?? 0;

$pesanan_confirmed = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM orders
     WHERE kiosk_id = $kiosk_id AND status = 'confirmed'"
))['total'] ?? 0;

$pesanan_selesai = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM orders
     WHERE kiosk_id = $kiosk_id AND status = 'completed'"
))['total'] ?? 0;

// KYC status config
$kyc_config = [
    'unverified' => [
        'color' => 'blue',
        'icon'  => 'ℹ️',
        'label' => 'Belum Upload Dokumen',
        'desc'  => 'Upload dokumen legalitas untuk mulai berjualan di SiAGRI.',
        'show_btn' => true,
    ],
    'pending' => [
        'color' => 'yellow',
        'icon'  => '⏳',
        'label' => 'Sedang Ditinjau Admin',
        'desc'  => 'Dokumenmu sedang ditinjau. Harap tunggu 1x24 jam.',
        'show_btn' => false,
    ],
    'verified' => [
        'color' => 'green',
        'icon'  => '✅',
        'label' => 'Kios Resmi Terverifikasi',
        'desc'  => 'Akunmu sudah terverifikasi. Kamu bisa mulai berjualan!',
        'show_btn' => false,
    ],
    'rejected' => [
        'color' => 'red',
        'icon'  => '❌',
        'label' => 'Dokumen Ditolak',
        'desc'  => 'Alasan: ' . htmlspecialchars($kiosk_info['kyc_note'] ?? '-') . '. Silakan upload ulang.',
        'show_btn' => true,
    ],
];
$kyc_status = $kiosk_info['kyc_status'] ?? 'unverified';
$kyc = $kyc_config[$kyc_status];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $page_title = 'Dashboard Kios'; include 'component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'dashboard'; include 'component/layout/navbar.php'; ?>