<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: login-page.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ─── LAZY CHECK: batalkan pesanan expired ─────────────────────────────────────
$now = date('Y-m-d H:i:s');
$expired = mysqli_query($conn,
    "SELECT o.order_id, oi.product_id, oi.quantity
     FROM orders o
     JOIN order_items oi ON o.order_id = oi.order_id
     WHERE o.user_id = $user_id
     AND o.status = 'pending'
     AND o.expired_at < '$now'"
);
while ($exp = mysqli_fetch_assoc($expired)) {
    mysqli_query($conn,
        "UPDATE products SET stock = stock + {$exp['quantity']}
         WHERE product_id = {$exp['product_id']}"
    );
}
mysqli_query($conn,
    "UPDATE orders SET status = 'cancelled'
     WHERE user_id = $user_id AND status = 'pending' AND expired_at < '$now'"
);

// ─── FILTER STATUS ────────────────────────────────────────────────────────────
$filter = isset($_GET['status']) ? $_GET['status'] : 'active';
if ($filter === 'active') {
    $where_status = "AND o.status IN ('pending','confirmed')";
} elseif ($filter === 'completed') {
    $where_status = "AND o.status = 'completed'";
} elseif ($filter === 'cancelled') {
    $where_status = "AND o.status = 'cancelled'";
} else {
    $where_status = "";
}

// ─── AMBIL PESANAN ────────────────────────────────────────────────────────────
$orders = mysqli_query($conn,
    "SELECT o.*,
            kp.store_name, kp.full_address, kp.whatsapp_number,
            p.product_name, p.product_image,
            oi.quantity, oi.price
     FROM orders o
     JOIN kiosk_profiles kp ON o.kiosk_id    = kp.kiosk_id
     JOIN order_items oi    ON o.order_id    = oi.order_id
     JOIN products p        ON oi.product_id = p.product_id
     WHERE o.user_id = $user_id
     $where_status
     ORDER BY o.created_at DESC"
);

// Hitung pesanan aktif
$count_active = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM orders
     WHERE user_id = $user_id AND status IN ('pending','confirmed')"
))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $page_title = 'Pesanan Saya'; include 'component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'my-orders'; include 'component/layout/navbar.php'; ?>

<div class="max-w-4xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-siagri-dark mb-6">Pesanan Saya</h1>

    <!-- Notifikasi sukses -->
    <?php if (isset($_GET['success'])): ?>
    <div class="mb-5 p-4 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        ✅ Pesanan berhasil dibuat! Datang ke kios dalam 24 jam untuk mengambil barang.
    </div>
    <?php endif; ?>

    <!-- Filter Tab -->
    <div class="flex gap-2 mb-6 bg-white rounded-xl p-1.5 shadow-sm border border-gray-100 w-fit">
        <?php
        $tabs = [
            'active'    => 'Aktif',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'all'       => 'Semua',
        ];
        foreach ($tabs as $key => $label):
        ?>
        <a href="?status=<?= $key ?>"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  <?= $filter === $key
                      ? 'bg-siagri-dark text-white'
                      : 'text-gray-500 hover:text-siagri-dark' ?>">
            <?= $label ?>
            <?php if ($key === 'active' && $count_active > 0): ?>
            <span class="ml-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                <?= $count_active ?>
            </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- List Pesanan -->
    <?php if (mysqli_num_rows($orders) === 0): ?>
    <div class="bg-white rounded-2xl shadow-sm p-16 text-center border border-gray-100">
        <p class="text-5xl mb-4">📭</p>
        <p class="font-medium text-gray-600">Belum ada pesanan</p>
        <p class="text-sm text-gray-400 mt-1 mb-6">
            Temukan produk pertanian yang kamu butuhkan di katalog
        </p>
        <a href="catalog.php"
           class="inline-block bg-siagri-dark text-white px-6 py-2.5 rounded-xl
                  font-semibold text-sm hover:bg-siagri-green transition">
            Lihat Katalog
        </a>
    </div>

    <?php else: ?>
    <div class="space-y-4">
    <?php while ($row = mysqli_fetch_assoc($orders)):
        $subtotal = $row['quantity'] * $row['price'];
        $status_cfg = [
            'pending'   => ['label'=>'Menunggu Konfirmasi Kios', 'cls'=>'bg-yellow-100 text-yellow-700'],
            'confirmed' => ['label'=>'Dikonfirmasi — Siap Diambil', 'cls'=>'bg-blue-100 text-blue-700'],
            'completed' => ['label'=>'Selesai',   'cls'=>'bg-green-100 text-green-700'],
            'cancelled' => ['label'=>'Dibatalkan','cls'=>'bg-red-100 text-red-700'],
        ];
        $sc = $status_cfg[$row['status']] ?? $status_cfg['cancelled'];
    ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <span class="text-gray-400 text-sm font-mono">#<?= $row['order_id'] ?></span>
                <span class="<?= $sc['cls'] ?> text-xs px-2.5 py-1 rounded-full font-semibold">
                    <?= $sc['label'] ?>
                </span>
            </div>
            <span class="text-gray-400 text-xs">
                <?= date('d M Y, H:i', strtotime($row['created_at'])) ?>
            </span>
        </div>

        <div class="p-5">
            <div class="flex gap-4">

                <!-- Gambar -->
                <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden">
                    <?php if ($row['product_image']): ?>
                    <img src="<?= htmlspecialchars($row['product_image']) ?>"
                         class="w-full h-full object-cover">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-2xl">📦</div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800">
                        <?= htmlspecialchars($row['product_name']) ?>
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        🏪 <?= htmlspecialchars($row['store_name']) ?>
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        📍 <?= htmlspecialchars($row['full_address']) ?>
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        <?= $row['quantity'] ?> unit ×
                        Rp <?= number_format($row['price'], 0, ',', '.') ?>
                    </p>
                    <p class="font-bold text-siagri-dark mt-1">
                        Total: Rp <?= number_format($subtotal, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-gray-400">💵 Bayar kontan saat ambil</p>
                </div>

                <!-- Timer / info kanan -->
                <div class="text-right flex-shrink-0">
                    <?php if ($row['status'] === 'pending'): ?>
                    <p class="text-xs text-gray-400 mb-1">Sisa waktu:</p>
                    <div class="text-lg font-bold font-mono text-orange-500"
                         id="timer-<?= $row['order_id'] ?>"
                         data-expired="<?= $row['expired_at'] ?>"
                         data-order="<?= $row['order_id'] ?>">
                        --:--:--
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Expired:<br>
                        <?= date('d/m H:i', strtotime($row['expired_at'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Aksi sesuai status -->
            <?php if ($row['status'] === 'pending'): ?>
            <div class="mt-4 pt-4 border-t border-gray-50 flex gap-3">
                <?php if ($row['whatsapp_number'] && $row['whatsapp_number'] !== '-'): ?>
                <a href="https://wa.me/<?= $row['whatsapp_number'] ?>?text=Halo, saya mau konfirmasi pesanan %23<?= $row['order_id'] ?>"
                   target="_blank"
                   class="flex-1 text-center border border-siagri-dark text-siagri-dark
                          text-sm font-semibold py-2.5 rounded-xl hover:bg-siagri-dark
                          hover:text-white transition">
                    💬 Hubungi Kios via WA
                </a>
                <?php endif; ?>
                <div class="flex-1 bg-yellow-50 text-yellow-700 text-xs text-center
                            py-2.5 rounded-xl font-medium flex items-center justify-center">
                    ⏳ Menunggu konfirmasi kios
                </div>
            </div>

            <?php elseif ($row['status'] === 'confirmed'): ?>
            <div class="mt-4 pt-4 border-t border-gray-50">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-blue-800 font-semibold text-sm">
                        ✅ Pesanan dikonfirmasi kios!
                    </p>
                    <p class="text-blue-600 text-xs mt-1">
                        Segera datang ke
                        <strong><?= htmlspecialchars($row['store_name']) ?></strong>
                        dan bayar kontan.
                    </p>
                    <?php if ($row['whatsapp_number'] && $row['whatsapp_number'] !== '-'): ?>
                    <a href="https://wa.me/<?= $row['whatsapp_number'] ?>"
                       target="_blank"
                       class="inline-block mt-2 text-xs text-blue-500 underline">
                        Hubungi kios →
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($row['status'] === 'completed'): ?>
            <div class="mt-4 pt-4 border-t border-gray-50">
                <div class="bg-green-50 rounded-xl p-3 text-center text-green-700 text-sm font-medium">
                    🎉 Pesanan selesai! Terima kasih sudah berbelanja di SiAGRI.
                </div>
            </div>

            <?php elseif ($row['status'] === 'cancelled'): ?>
            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                <p class="text-red-400 text-sm">❌ Pesanan dibatalkan</p>
                <a href="catalog.php"
                   class="text-sm text-siagri-dark underline">Pesan lagi →</a>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endwhile; ?>
    </div>
    <?php endif; ?>

</div>

<script src="assets/js/orders.js"></script>
<?php include 'component/layout/footer.php'; ?>

</body>
</html>
