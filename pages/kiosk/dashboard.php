<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../pages/auth/login.php");
    exit;
}

if ($_SESSION['role'] !== 'Kiosk') {
    header("Location: ../../pages/farmer/catalog.php");
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

// Total Pendapatan dari pesanan selesai
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(total_price) as total FROM orders
     WHERE kiosk_id = $kiosk_id AND status = 'completed'"
))['total'] ?? 0;

// Query pendapatan 7 hari terakhir untuk Kios
$kiosk_days_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $formatted_date = date('d M', strtotime($date));
    $kiosk_days_data[$date] = [
        'label' => $formatted_date,
        'revenue' => 0
    ];
}

$kiosk_sales_query = mysqli_query($conn, 
    "SELECT DATE(created_at) as order_date, SUM(total_price) as daily_revenue 
     FROM orders 
     WHERE kiosk_id = $kiosk_id AND status = 'completed'
     AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(created_at)"
);

if ($kiosk_sales_query) {
    while ($row = mysqli_fetch_assoc($kiosk_sales_query)) {
        $od = $row['order_date'];
        if (isset($kiosk_days_data[$od])) {
            $kiosk_days_data[$od]['revenue'] = (float)$row['daily_revenue'];
        }
    }
}

$kiosk_chart_labels = [];
$kiosk_chart_revenues = [];
foreach ($kiosk_days_data as $day) {
    $kiosk_chart_labels[] = $day['label'];
    $kiosk_chart_revenues[] = $day['revenue'];
}

// Query 5 produk terlaris kios ini
$top_products_query = mysqli_query($conn,
    "SELECT p.product_name, SUM(oi.quantity) as total_qty
     FROM order_items oi
     JOIN products p ON oi.product_id = p.product_id
     JOIN orders o ON oi.order_id = o.order_id
     WHERE o.kiosk_id = $kiosk_id AND o.status = 'completed'
     GROUP BY p.product_id
     ORDER BY total_qty DESC
     LIMIT 5"
);

$kiosk_top_labels = [];
$kiosk_top_qtys = [];
if ($top_products_query) {
    while ($row = mysqli_fetch_assoc($top_products_query)) {
        $kiosk_top_labels[] = $row['product_name'];
        $kiosk_top_qtys[] = (int)$row['total_qty'];
    }
}

// KYC status config
$kyc_config = [
    'unverified' => [
        'color' => 'blue',
        'icon'  => '<svg class="inline-block w-9 h-9 mr-1.5 align-middle text-blue-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-10 5.75a.75.75 0 0 0 .75-.75v-6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75M12 7a1 1 0 1 1 0 2a1 1 0 0 1 0-2" clip-rule="evenodd"/></svg>',
        'label' => 'Belum Upload Dokumen',
        'desc'  => 'Upload dokumen legalitas untuk mulai berjualan di SiAGRI.',
        'show_btn' => true,
    ],
    'pending' => [
        'color' => 'yellow',
        'icon'  => '<svg class="inline-block w-9 h-9 mr-1.5 align-middle text-yellow-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><defs><mask id="SVGnNgsclOC"><g fill="none"><path fill="#fff" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10"/><path fill="#000" fill-rule="evenodd" d="M12 7.25a.75.75 0 0 1 .75.75v3.69l2.28 2.28a.75.75 0 1 1-1.06 1.06l-2.5-2.5a.75.75 0 0 1-.22-.53V8a.75.75 0 0 1 .75-.75" clip-rule="evenodd"/></g></mask></defs><path fill="currentColor" d="M0 0h24v24H0z" mask="url(#SVGnNgsclOC)"/></svg>',
        'label' => 'Sedang Ditinjau Admin',
        'desc'  => 'Dokumenmu sedang ditinjau. Harap tunggu 1x24 jam.',
        'show_btn' => false,
    ],
    'verified' => [
        'color' => 'green',
        'icon'  => '<svg class="inline-block w-9 h-9 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg>',
        'label' => 'Kios Resmi Terverifikasi',
        'desc'  => 'Akunmu sudah terverifikasi. Kamu bisa mulai berjualan!',
        'show_btn' => false,
    ],
    'rejected' => [
        'color' => 'red',
        'icon'  => '<svg class="inline-block w-9 h-9 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg>',
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
    <?php $page_title = 'Dashboard Kios'; include '../../component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'dashboard'; include '../../component/layout/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-5 py-8">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-siagri-dark">
            Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>!
        </h1>
        <p class="text-gray-500 text-sm mt-1">
            <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"/></svg> <?= htmlspecialchars($kiosk_info['store_name'] ?? 'Toko Baru') ?>
            &nbsp;·&nbsp;
            <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M12 2c-4.418 0-8 4.003-8 8.5c0 4.462 2.553 9.312 6.537 11.174a3.45 3.45 0 0 0 2.926 0C17.447 19.812 20 14.962 20 10.5C20 6.003 16.418 2 12 2m0 10a2 2 0 1 0 0-4a2 2 0 0 0 0 4" clip-rule="evenodd"/></svg> <?= htmlspecialchars($kiosk_info['full_address'] ?? '-') ?>
        </p>
    </div>

    <!-- Banner KYC -->
    <?php
    $color_map = [
        'blue'   => 'bg-blue-50 border-blue-400 text-blue-800',
        'yellow' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
        'green'  => 'bg-green-50 border-green-500 text-green-800',
        'red'    => 'bg-red-50 border-red-400 text-red-800',
    ];
    ?>
    <div class="mb-6 p-4 rounded-xl border-l-4 <?= $color_map[$kyc['color']] ?>
                flex items-start gap-4">
        <span class="text-2xl"><?= $kyc['icon'] ?></span>
        <div class="flex-1">
            <p class="font-semibold">Status Verifikasi: <?= $kyc['label'] ?></p>
            <p class="text-sm mt-0.5"><?= $kyc['desc'] ?></p>
            <?php if ($kyc['show_btn']): ?>
            <a href="../../pages/kiosk/kyc-upload.php"
               class="inline-flex items-center gap-2 mt-2 bg-siagri-dark text-white text-sm
                      px-4 py-1.5 rounded-lg hover:bg-siagri-green transition">
                Upload Dokumen KYC
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current">
                    <path d="M4 11h12.17l-5.59-5.59L12 4l8 8l-8 8l-1.41-1.41L16.17 13H4z"></path>
                </svg>
            </a>
            <?php endif; ?>
        </div>
        <?php if ($kyc_status === 'verified'): ?>
        <span class="flex-shrink-0 bg-green-500 text-white text-xs font-bold
                     px-3 py-1 rounded-full">
            ✓ Kios Resmi
        </span>
        <?php endif; ?>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
            <p class="text-3xl font-bold text-siagri-dark"><?= $total_produk ?></p>
            <p class="text-gray-500 text-sm mt-1">Produk Aktif</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 relative">
            <p class="text-3xl font-bold text-yellow-500"><?= $pesanan_pending ?></p>
            <p class="text-gray-500 text-sm mt-1">Pesanan Masuk</p>
            <?php if ($pesanan_pending > 0): ?>
            <span class="absolute top-3 right-3 w-2.5 h-2.5 bg-red-500
                         rounded-full animate-ping"></span>
            <?php endif; ?>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
            <p class="text-3xl font-bold text-blue-500"><?= $pesanan_confirmed ?></p>
            <p class="text-gray-500 text-sm mt-1">Siap Diambil</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
            <p class="text-3xl font-bold text-green-500"><?= $pesanan_selesai ?></p>
            <p class="text-gray-500 text-sm mt-1">Selesai</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 col-span-2 md:col-span-1">
            <p class="text-2xl font-bold text-emerald-600">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
            <p class="text-gray-500 text-sm mt-1">Total Pendapatan</p>
        </div>
    </div>

    <!-- Section Grafik Utama Kios -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Grafik Pendapatan -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-siagri-dark text-base">Grafik Pendapatan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Penjualan selesai dalam 7 hari terakhir (Rp)</p>
                </div>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                    Kas Masuk (Tunai)
                </span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="kioskRevenueChart"></canvas>
            </div>
        </div>

        <!-- Grafik Barang Terjual -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-siagri-dark text-base">Produk Terlaris Kios</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Kuantitas produk yang paling banyak terjual (unit)</p>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">
                    Volume Barang
                </span>
            </div>
            <div class="relative h-64 w-full">
                <?php if (empty($kiosk_top_labels)): ?>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 text-sm">
                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        Belum ada data barang terjual
                    </div>
                <?php else: ?>
                    <canvas id="kioskTopProductsChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

        <!-- Kelola Produk -->
        <a href="../../pages/kiosk/manage-catalog.php"
           class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100
                  hover:shadow-md hover:-translate-y-1 transition group">
            <div class="w-12 h-12 bg-siagri-dark rounded-xl flex items-center
                        justify-center mb-4 group-hover:bg-siagri-green transition">
                <span class="text-2xl"><svg class="inline-block w-5 h-5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="white" d="m17.578 4.432l-2-1.05C13.822 2.461 12.944 2 12 2s-1.822.46-3.578 1.382l-.321.169l8.923 5.099l4.016-2.01c-.646-.732-1.688-1.279-3.462-2.21m4.17 3.534l-3.998 2V13a.75.75 0 0 1-1.5 0v-2.286l-3.5 1.75v9.44c.718-.179 1.535-.607 2.828-1.286l2-1.05c2.151-1.129 3.227-1.693 3.825-2.708c.597-1.014.597-2.277.597-4.8v-.117c0-1.893 0-3.076-.252-3.978M11.25 21.904v-9.44l-8.998-4.5C2 8.866 2 10.05 2 11.941v.117c0 2.525 0 3.788.597 4.802c.598 1.015 1.674 1.58 3.825 2.709l2 1.049c1.293.679 2.11 1.107 2.828 1.286M2.96 6.641l9.04 4.52l3.411-1.705l-8.886-5.078l-.103.054c-1.773.93-2.816 1.477-3.462 2.21"/></svg></span>
            </div>
            <h3 class="font-bold text-siagri-dark">Kelola Produk</h3>
            <p class="text-gray-400 text-sm mt-1">
                Tambah, edit, atau hapus produk katalogmu
            </p>
        </a>

        <!-- Pesanan Masuk -->
        <a href="../../pages/kiosk/incoming-orders.php"
           class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100
                  hover:shadow-md hover:-translate-y-1 transition group relative">
            <div class="w-12 h-12 bg-siagri-dark rounded-xl flex items-center
                        justify-center mb-4 group-hover:bg-siagri-green transition">
                <span class="text-2xl"><svg class="inline-block w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="white" d="M9.5 2A1.5 1.5 0 0 0 8 3.5v1A1.5 1.5 0 0 0 9.5 6h5A1.5 1.5 0 0 0 16 4.5v-1A1.5 1.5 0 0 0 14.5 2z"/><path fill="white" fill-rule="evenodd" d="M6.5 4.037c-1.258.07-2.052.27-2.621.84C3 5.756 3 7.17 3 9.998v6c0 2.829 0 4.243.879 5.122c.878.878 2.293.878 5.121.878h6c2.828 0 4.243 0 5.121-.878c.879-.88.879-2.293.879-5.122v-6c0-2.828 0-4.242-.879-5.121c-.569-.57-1.363-.77-2.621-.84V4.5a3 3 0 0 1-3 3h-5a3 3 0 0 1-3-3zM7 9.75a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5zm3.5 0a.75.75 0 0 0 0 1.5H17a.75.75 0 0 0 0-1.5zM7 13.25a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5zm3.5 0a.75.75 0 0 0 0 1.5H17a.75.75 0 0 0 0-1.5zM7 16.75a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5zm3.5 0a.75.75 0 0 0 0 1.5H17a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/></svg></span>
            </div>
            <h3 class="font-bold text-siagri-dark">Pesanan Masuk</h3>
            <p class="text-gray-400 text-sm mt-1">
                Konfirmasi dan kelola pesanan dari Petani
            </p>
            <?php if ($pesanan_pending > 0): ?>
            <span class="absolute top-4 right-4 bg-red-500 text-white text-xs
                         font-bold px-2 py-0.5 rounded-full">
                <?= $pesanan_pending ?> baru
            </span>
            <?php endif; ?>
        </a>

        <!-- Profil Toko -->
        <a href="../../pages/general/profile.php"
           class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100
                  hover:shadow-md hover:-translate-y-1 transition group">
            <div class="w-12 h-12 bg-siagri-dark rounded-xl flex items-center
                        justify-center mb-4 group-hover:bg-siagri-green transition">
                <span class="text-2xl"><svg class="inline-block w-5 h-5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="white" d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"/></svg></span>
            </div>
            <h3 class="font-bold text-siagri-dark">Profil Toko</h3>
            <p class="text-gray-400 text-sm mt-1">
                Edit nama toko, alamat, dan nomor WA
            </p>
        </a>

    </div>

    <!-- Pesanan Terbaru -->
    <?php
    $recent_orders = mysqli_query($conn,
        "SELECT o.*, u.username, p.product_name, oi.quantity, oi.price
         FROM orders o
         JOIN users u           ON o.user_id     = u.user_id
         JOIN order_items oi    ON o.order_id    = oi.order_id
         JOIN products p        ON oi.product_id = p.product_id
         WHERE o.kiosk_id = $kiosk_id
         ORDER BY o.created_at DESC
         LIMIT 5"
    );
    ?>
    <?php if ($recent_orders && mysqli_num_rows($recent_orders) > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-siagri-dark text-lg">Pesanan Terbaru</h2>
            <a href="../../pages/kiosk/incoming-orders.php"
               class="text-sm text-siagri-dark hover:underline flex items-center gap-1">
                <span>Lihat semua</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current">
                    <path d="M4 11h12.17l-5.59-5.59L12 4l8 8l-8 8l-1.41-1.41L16.17 13H4z"></path>
                </svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-siagri-dark text-white">
                        <th class="px-4 py-3 text-left rounded-tl-lg">#</th>
                        <th class="px-4 py-3 text-left">Petani</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center rounded-tr-lg">Expired</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($recent_orders)): ?>
                <?php
                $status_cfg = [
                    'pending'   => 'bg-yellow-100 text-yellow-700',
                    'confirmed' => 'bg-blue-100 text-blue-700',
                    'completed' => 'bg-green-100 text-green-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                ];
                $sc = $status_cfg[$row['status']] ?? 'bg-gray-100 text-gray-700';
                ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400">#<?= $row['order_id'] ?></td>
                    <td class="px-4 py-3 font-medium">
                        <?= htmlspecialchars($row['username']) ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <?= htmlspecialchars($row['product_name']) ?>
                        <span class="text-gray-400">(×<?= $row['quantity'] ?>)</span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold">
                        Rp <?= number_format($row['quantity'] * $row['price'], 0, ',', '.') ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="<?= $sc ?> text-xs px-2 py-1 rounded-full font-medium">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-400">
                        <?= date('d/m H:i', strtotime($row['expired_at'])) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Chart 1: kioskRevenueChart
    const ctxKiosk = document.getElementById('kioskRevenueChart');
    if (ctxKiosk) {
        const ctx = ctxKiosk.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($kiosk_chart_labels) ?>,
                datasets: [{
                    label: 'Pendapatan Kios (Rp)',
                    data: <?= json_encode($kiosk_chart_revenues) ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        titleFont: { family: 'Poppins', weight: 'bold' },
                        bodyFont: { family: 'Poppins' },
                        callbacks: {
                            label: function(context) {
                                return 'Pendapatan: Rp ' + Math.round(context.raw).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Poppins', size: 10 } }
                    },
                    y: {
                        min: 0,
                        max: 5000000,
                        ticks: {
                            font: { family: 'Poppins', size: 10 },
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.03)' }
                    }
                }
            }
        });
    }

    // Chart 2: kioskTopProductsChart (Horizontal Bar Chart)
    const ctxTop = document.getElementById('kioskTopProductsChart');
    if (ctxTop) {
        const ctx = ctxTop.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($kiosk_top_labels) ?>,
                datasets: [{
                    label: 'Barang Terjual (Unit)',
                    data:  <?= json_encode($kiosk_top_qtys) ?>,
                    backgroundColor: 'rgba(77, 119, 78, 0.75)',
                    borderColor: '#4d774e',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        titleFont: { family: 'Poppins', weight: 'bold' },
                        bodyFont: { family: 'Poppins' }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Poppins', size: 10 } }
                    },
                    y: {
                        ticks: { font: { family: 'Poppins', size: 10 }, stepSize: 1 },
                        grid: { color: 'rgba(0, 0, 0, 0.03)' }
                    }
                }
            }
        });
    }
});
</script>
<?php include '../../component/layout/footer.php'; ?>

</body>
</html>
