<?php
session_start();
require_once 'koneksi.php';

// === AUTH GUARD ===
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: login-page.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Petani';
$message  = '';

// === AMBIL DATA CART ===
$sql_cart = "
    SELECT c.cart_id, c.quantity,
           p.product_id, p.product_name, p.selling_price, p.het_price,
           p.is_subsidized, p.stock, p.product_image, p.kiosk_id,
           k.store_name, k.full_address, k.whatsapp_number, k.kyc_status
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    JOIN kiosk_profiles k ON p.kiosk_id = k.kiosk_id
    WHERE c.user_id = ?
    ORDER BY k.kiosk_id, p.product_name
";
$stmt = mysqli_prepare($conn, $sql_cart);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result_cart = mysqli_stmt_get_result($stmt);

$cart_items   = [];
$kiosk_groups = [];
$grand_total  = 0;

while ($row = mysqli_fetch_assoc($result_cart)) {
    $cart_items[] = $row;
    $kid = $row['kiosk_id'];
    if (!isset($kiosk_groups[$kid])) {
        $kiosk_groups[$kid] = [
            'store_name'      => $row['store_name'],
            'full_address'    => $row['full_address'],
            'whatsapp_number' => $row['whatsapp_number'],
            'kyc_status'      => $row['kyc_status'],
            'items'           => [],
            'subtotal'        => 0,
        ];
    }
    $sub = $row['selling_price'] * $row['quantity'];
    $kiosk_groups[$kid]['items'][]  = $row;
    $kiosk_groups[$kid]['subtotal'] += $sub;
    $grand_total += $sub;
}

// === HANDLE POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    if (empty($cart_items)) {
        $message = "Keranjang belanja kamu kosong.";

    } else {
        // Cek KYC
        $has_unverified = false;
        foreach ($kiosk_groups as $kg) {
            if ($kg['kyc_status'] !== 'verified') { $has_unverified = true; break; }
        }

        if ($has_unverified) {
            $message = "Beberapa kios belum terverifikasi KYC. Pesanan tidak dapat diproses.";
        } else {
            // Cek stok real-time
            $stock_error = false;
            foreach ($cart_items as $item) {
                $st = mysqli_prepare($conn, "SELECT stock FROM products WHERE product_id = ?");
                mysqli_stmt_bind_param($st, 'i', $item['product_id']);
                mysqli_stmt_execute($st);
                $sr = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
                if (!$sr || $sr['stock'] < $item['quantity']) {
                    $message = "Stok produk \"" . htmlspecialchars($item['product_name']) . "\" tidak mencukupi.";
                    $stock_error = true;
                    break;
                }
            }

            if (!$stock_error) {
                mysqli_begin_transaction($conn);
                try {
                    $expired_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

                    foreach ($kiosk_groups as $kid => $kg) {
                        $st = mysqli_prepare($conn,
                            "INSERT INTO orders (user_id, kiosk_id, total_price, status, expired_at)
                             VALUES (?, ?, ?, 'pending', ?)");
                        mysqli_stmt_bind_param($st, 'iids', $user_id, $kid, $kg['subtotal'], $expired_at);
                        mysqli_stmt_execute($st);
                        $order_id = mysqli_insert_id($conn);

                        foreach ($kg['items'] as $item) {
                            $si = mysqli_prepare($conn,
                                "INSERT INTO order_items (order_id, product_id, quantity, price)
                                 VALUES (?, ?, ?, ?)");
                            mysqli_stmt_bind_param($si, 'iiid', $order_id, $item['product_id'], $item['quantity'], $item['selling_price']);
                            mysqli_stmt_execute($si);

                            $su = mysqli_prepare($conn,
                                "UPDATE products SET stock = stock - ? WHERE product_id = ?");
                            mysqli_stmt_bind_param($su, 'ii', $item['quantity'], $item['product_id']);
                            mysqli_stmt_execute($su);
                        }
                    }

                    $sc = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
                    mysqli_stmt_bind_param($sc, 'i', $user_id);
                    mysqli_stmt_execute($sc);

                    mysqli_commit($conn);
                    header("Location: my-orders.php?order_success=1");
                    exit;

                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $message = "Terjadi kesalahan sistem. Silakan coba lagi.";
                }
            }
        }
    }
}

function rupiah($n) {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

$has_unverified = false;
foreach ($kiosk_groups as $kg) {
    if ($kg['kyc_status'] !== 'verified') { $has_unverified = true; break; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <?php $page_title = 'Checkout'; include 'component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'checkout'; include 'component/layout/navbar.php'; ?>


<!-- BREADCRUMB -->
<div class="max-w-7xl mx-auto px-4 pt-5 pb-1">
    <nav class="text-sm text-gray-500 flex items-center gap-1.5">
        <a href="catalog.php" class="text-siagri-dark hover:underline font-medium">Katalog</a>
        <span>›</span>
        <span class="text-gray-400">Checkout</span>
    </nav>
</div>

<div class="max-w-7xl mx-auto px-4 py-4">
    <div class="flex items-center gap-2 max-w-xs">

        <!-- Step 1: Keranjang (done) -->
        <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full bg-siagri-dark text-white flex items-center justify-center text-xs font-bold">✓</div>
            <span class="text-xs font-medium text-gray-400">Keranjang</span>
        </div>

        <div class="flex-1 h-px bg-siagri-dark"></div>

        <!-- Step 2: Checkout (active) -->
        <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full bg-siagri-gold text-siagri-dark flex items-center justify-center text-xs font-bold">2</div>
            <span class="text-xs font-semibold text-siagri-dark">Checkout</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        <!-- Step 3: Selesai -->
        <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold">3</div>
            <span class="text-xs font-medium text-gray-400">Pesanan Aktif</span>
        </div>

    </div>
</div>

<div class="max-w-7xl mx-auto px-4 pb-12">

    <!-- Alert error -->
    <?php if ($message): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
    <!-- EMPTY STATE -->
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <p class="text-5xl mb-4">🛒</p>
        <h2 class="font-semibold text-siagri-dark text-lg mb-2">Keranjang Kamu Kosong</h2>
        <p class="text-sm text-gray-400 mb-6">Belum ada produk yang dipilih.</p>
        <a href="catalog.php"
           class="inline-block bg-siagri-dark text-white text-sm font-medium px-6 py-2.5
                  rounded-lg hover:bg-siagri-green transition">
            Jelajahi Katalog
        </a>
    </div>

    <?php else: ?>

    <form method="POST">
    <div class="flex flex-col lg:flex-row gap-6 items-start">

        <!-- KIRI: Daftar Produk per Kios -->
        <div class="flex-1 min-w-0 flex flex-col gap-5">

            <?php foreach ($kiosk_groups as $kid => $kg): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">

                <!-- Header kios -->
                <div class="flex items-center gap-3 px-5 py-3.5 border-b bg-gray-50">
                    <svg class="w-5 h-5 text-siagri-dark flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-siagri-dark text-sm truncate">
                            <?= htmlspecialchars($kg['store_name']) ?>
                        </p>
                        <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($kg['full_address']) ?></p>
                    </div>
                    <?php if ($kg['kyc_status'] === 'verified'): ?>
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold flex-shrink-0">
                        ✓ Terverifikasi
                    </span>
                    <?php else: ?>
                    <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold flex-shrink-0">
                        Belum Verified
                    </span>
                    <?php endif; ?>
                </div>

                <!-- KYC warning -->
                <?php if ($kg['kyc_status'] !== 'verified'): ?>
                <div class="mx-5 mt-3 mb-1 p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded-lg text-xs flex gap-2">
                    <span>⚠️</span>
                    <span>Kios ini belum terverifikasi KYC — pesanan tidak dapat diproses.</span>
                </div>
                <?php endif; ?>

                <!-- Produk -->
                <?php foreach ($kg['items'] as $item): ?>
                <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-100 last:border-b-0">

                    <!-- Gambar -->
                    <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                        <?php if (!empty($item['product_image']) && file_exists($item['product_image'])): ?>
                            <img src="<?= htmlspecialchars($item['product_image']) ?>"
                                 class="w-full h-full object-cover" alt="">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info produk -->
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm text-gray-800 truncate">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <?= rupiah($item['selling_price']) ?> / unit
                            <?php if ($item['is_subsidized']): ?>
                                &nbsp;<span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-medium">Subsidi</span>
                                <span class="text-xs text-blue-500 ml-1">HET <?= rupiah($item['het_price']) ?></span>
                            <?php endif; ?>
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Qty: <span class="font-medium text-gray-600"><?= $item['quantity'] ?></span>
                        </p>
                    </div>

                    <!-- Subtotal item -->
                    <div class="text-right flex-shrink-0">
                        <p class="font-bold text-siagri-dark text-sm">
                            <?= rupiah($item['selling_price'] * $item['quantity']) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Subtotal kios -->
                <div class="flex justify-between items-center px-5 py-3 bg-gray-50 border-t">
                    <span class="text-sm text-gray-500">Subtotal <?= htmlspecialchars($kg['store_name']) ?></span>
                    <span class="font-bold text-siagri-dark"><?= rupiah($kg['subtotal']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Info Klik & Ambil -->
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-siagri-dark flex gap-3">
                <span class="text-2xl">ℹ️</span>
                <div class="text-sm text-gray-600 leading-relaxed">
                    <strong class="text-siagri-dark">Sistem Klik &amp; Ambil</strong><br>
                    Bayar <strong>tunai</strong> saat mengambil barang langsung di kios.
                    Pesanan aktif selama <strong>24 jam</strong> setelah dikonfirmasi kios — lewat dari itu pesanan dibatalkan otomatis.
                </div>
            </div>

        </div><!-- /kiri -->

        <!-- KANAN: Ringkasan Pembayaran -->
        <div class="w-full lg:w-72 flex-shrink-0 sticky top-24">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">

                <!-- Header -->
                <div class="px-5 py-4 border-b bg-siagri-dark text-white">
                    <h3 class="font-semibold text-base">Ringkasan Pesanan</h3>
                </div>

                <div class="p-5">
                    <!-- Per kios -->
                    <?php foreach ($kiosk_groups as $kg): ?>
                    <div class="flex justify-between items-center text-sm mb-3">
                        <span class="text-gray-500 truncate max-w-[160px]">
                            <?= htmlspecialchars($kg['store_name']) ?>
                        </span>
                        <span class="font-medium text-gray-700"><?= rupiah($kg['subtotal']) ?></span>
                    </div>
                    <?php endforeach; ?>

                    <!-- Ongkir -->
                    <div class="flex justify-between items-center text-sm mb-4">
                        <span class="text-gray-500">Ongkos Kirim</span>
                        <span class="text-siagri-green font-semibold text-xs">Gratis (Ambil Sendiri)</span>
                    </div>

                    <div class="border-t border-dashed border-gray-200 my-3"></div>

                    <!-- Total -->
                    <div class="flex justify-between items-center mb-5">
                        <span class="font-semibold text-gray-700">Total Bayar</span>
                        <span class="font-bold text-siagri-dark text-xl"><?= rupiah($grand_total) ?></span>
                    </div>

                    <!-- Metode bayar -->
                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs font-semibold text-yellow-800 flex items-center gap-1.5">
                            Bayar Tunai di Kios
                        </p>
                        <p class="text-xs text-yellow-700 mt-1">
                            Siapkan uang pas saat pengambilan.
                        </p>
                    </div>

                    <!-- Tombol Konfirmasi -->
                    <button
                        type="submit"
                        name="place_order"
                        value="1"
                        <?= $has_unverified ? 'disabled' : '' ?>
                        class="block w-full text-center
                               <?= $has_unverified
                                   ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                   : 'bg-siagri-gold text-siagri-dark hover:bg-yellow-400' ?>
                               font-bold py-3 rounded-xl transition text-sm">
                        <?= $has_unverified ? '⚠️ Kios Belum Terverifikasi' : 'Lanjut Pesan →' ?>
                    </button>

                    <?php if ($has_unverified): ?>
                    <p class="text-xs text-red-500 text-center mt-2">
                        Hapus produk dari kios yang belum terverifikasi untuk melanjutkan.
                    </p>
                    <?php endif; ?>

                    <p class="text-xs text-gray-400 text-center mt-2">
                        Bayar kontan saat ambil di kios
                    </p>

                    <a href="catalog.php"
                       class="block text-center mt-3 text-xs text-siagri-dark hover:underline font-medium">
                        ← Lanjut Belanja
                    </a>
                </div>
            </div>
        </div><!-- /kanan -->

    </div>
    </form>

    <?php endif; ?>
</div>

<?php include 'component/layout/footer.php'; ?>

</body>
</html>