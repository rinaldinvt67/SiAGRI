<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Kiosk') {
    header("Location: login-page.php");
    exit;
}

$kiosk_id = $_SESSION['kiosk_id'];
$success  = "";

// ─── LAZY CHECK: batalkan pesanan expired ─────────────────────────────────────
$now = date('Y-m-d H:i:s');
$expired = mysqli_query($conn,
    "SELECT o.order_id, oi.product_id, oi.quantity
     FROM orders o
     JOIN order_items oi ON o.order_id = oi.order_id
     WHERE o.status = 'pending'
     AND o.expired_at < '$now'
     AND o.kiosk_id = $kiosk_id"
);
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

// ─── KONFIRMASI PESANAN ───────────────────────────────────────────────────────
if (isset($_GET['confirm'])) {
    $order_id = (int)$_GET['confirm'];
    mysqli_query($conn,
        "UPDATE orders SET status = 'confirmed'
         WHERE order_id = $order_id AND kiosk_id = $kiosk_id AND status = 'pending'"
    );
    header("Location: incoming-orders.php?success=confirmed");
    exit;
}

// ─── TANDAI SELESAI ───────────────────────────────────────────────────────────
if (isset($_GET['complete'])) {
    $order_id = (int)$_GET['complete'];
    mysqli_query($conn,
        "UPDATE orders SET status = 'completed'
         WHERE order_id = $order_id AND kiosk_id = $kiosk_id AND status = 'confirmed'"
    );
    header("Location: incoming-orders.php?success=completed");
    exit;
}

// ─── BATALKAN PESANAN (oleh Kiosk) ───────────────────────────────────────────
if (isset($_GET['cancel'])) {
    $order_id = (int)$_GET['cancel'];

    // Kembalikan stok dulu
    $items = mysqli_query($conn,
        "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id"
    );
    while ($item = mysqli_fetch_assoc($items)) {
        mysqli_query($conn,
            "UPDATE products SET stock = stock + {$item['quantity']}
             WHERE product_id = {$item['product_id']}"
        );
    }
    mysqli_query($conn,
        "UPDATE orders SET status = 'cancelled'
         WHERE order_id = $order_id AND kiosk_id = $kiosk_id
         AND status IN ('pending','confirmed')"
    );
    header("Location: incoming-orders.php?success=cancelled");
    exit;
}

// Pesan sukses
if (isset($_GET['success'])) {
    $msgs = [
        'confirmed' => '✅ Pesanan dikonfirmasi! Siapkan barangnya.',
        'completed' => '✅ Pesanan ditandai selesai.',
        'cancelled' => '✅ Pesanan dibatalkan dan stok dikembalikan.',
    ];
    $success = $msgs[$_GET['success']] ?? '';
}

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
            u.username,
            p.product_name,
            p.product_image,
            oi.quantity,
            oi.price
     FROM orders o
     JOIN users u           ON o.user_id     = u.user_id
     JOIN order_items oi    ON o.order_id    = oi.order_id
     JOIN products p        ON oi.product_id = p.product_id
     WHERE o.kiosk_id = $kiosk_id
     $where_status
     ORDER BY o.created_at DESC"
);

// Hitung badge
$count_pending = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM orders
     WHERE kiosk_id=$kiosk_id AND status='pending'"
))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $page_title = 'Pesanan Masuk'; include 'component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'incoming-orders'; $pesanan_pending = $count_pending; include 'component/layout/navbar.php'; ?>

<div class="max-w-5xl mx-auto px-5 py-8">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-siagri-dark">Pesanan Masuk</h1>
        <p class="text-gray-400 text-sm mt-0.5">
            Kelola pesanan dari Petani, konfirmasi dan tandai selesai
        </p>
    </div>

    <!-- Notifikasi sukses -->
    <?php if ($success): ?>
    <div class="mb-5 p-3 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        <?= $success ?>
    </div>
    <?php endif; ?>

    <!-- Filter Tab -->
    <div class="flex gap-2 mb-6 bg-white rounded-xl p-1.5 shadow-sm border
                border-gray-100 w-fit">
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
            <?php if ($key === 'active' && $count_pending > 0): ?>
            <span class="ml-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                <?= $count_pending ?>
            </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- List Pesanan -->
    <?php if (mysqli_num_rows($orders) === 0): ?>
    <div class="bg-white rounded-2xl shadow-sm p-16 text-center border border-gray-100">
        <p class="text-5xl mb-4">📭</p>
        <p class="font-medium text-gray-600">Tidak ada pesanan</p>
        <p class="text-sm text-gray-400 mt-1">
            <?= $filter === 'active' ? 'Belum ada pesanan masuk saat ini' : 'Tidak ada riwayat pesanan' ?>
        </p>
    </div>

    <?php else: ?>
    <div class="space-y-4">
    <?php while ($row = mysqli_fetch_assoc($orders)):
        $subtotal  = $row['quantity'] * $row['price'];
        $is_expired = strtotime($row['expired_at']) < time();

        $status_cfg = [
            'pending'   => ['label'=>'Menunggu Konfirmasi', 'cls'=>'bg-yellow-100 text-yellow-700'],
            'confirmed' => ['label'=>'Dikonfirmasi',        'cls'=>'bg-blue-100 text-blue-700'],
            'completed' => ['label'=>'Selesai',             'cls'=>'bg-green-100 text-green-700'],
            'cancelled' => ['label'=>'Dibatalkan',          'cls'=>'bg-red-100 text-red-700'],
        ];
        $sc = $status_cfg[$row['status']] ?? $status_cfg['cancelled'];
    ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Header card -->
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

                <!-- Gambar produk -->
                <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden">
                    <?php if ($row['product_image']): ?>
                    <img src="<?= htmlspecialchars($row['product_image']) ?>"
                         class="w-full h-full object-cover">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-2xl">
                        📦
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info pesanan -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800">
                        <?= htmlspecialchars($row['product_name']) ?>
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        👤 Petani: <span class="text-gray-600 font-medium">
                            <?= htmlspecialchars($row['username']) ?>
                        </span>
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        🔢 <?= $row['quantity'] ?> unit ×
                        Rp <?= number_format($row['price'], 0, ',', '.') ?>
                    </p>
                    <p class="font-bold text-siagri-dark mt-1">
                        Total: Rp <?= number_format($subtotal, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        💵 Pembayaran kontan saat pengambilan
                    </p>
                </div>

                <!-- Info waktu & expired -->
                <div class="text-right flex-shrink-0">
                    <?php if ($row['status'] === 'pending'): ?>
                    <p class="text-xs text-gray-400 mb-1">Batas waktu:</p>
                    <p class="text-sm font-semibold <?= $is_expired ? 'text-red-500' : 'text-orange-500' ?>
                               font-mono" id="timer-<?= $row['order_id'] ?>"
                       data-expired="<?= $row['expired_at'] ?>">
                        <?= date('d/m H:i', strtotime($row['expired_at'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tombol aksi -->
            <?php if ($row['status'] === 'pending' && !$is_expired): ?>
            <div class="flex gap-3 mt-4 pt-4 border-t border-gray-50">
                <a href="?confirm=<?= $row['order_id'] ?>"
                   onclick="return confirm('Konfirmasi pesanan ini? Pastikan stok tersedia.')"
                   class="flex-1 text-center bg-siagri-dark text-white text-sm font-semibold
                          py-2.5 rounded-xl hover:bg-siagri-green transition">
                    ✅ Konfirmasi, Siapkan Barang
                </a>
                <a href="?cancel=<?= $row['order_id'] ?>"
                   onclick="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')"
                   class="px-4 text-center border border-red-200 text-red-500 text-sm
                          font-semibold py-2.5 rounded-xl hover:bg-red-50 transition">
                    Batalkan
                </a>
            </div>

            <?php elseif ($row['status'] === 'confirmed'): ?>
            <div class="flex gap-3 mt-4 pt-4 border-t border-gray-50">
                <div class="flex-1 bg-blue-50 text-blue-700 text-sm text-center
                            py-2.5 rounded-xl font-medium">
                    📦 Barang sudah disiapkan, menunggu Petani datang
                </div>
                <a href="?complete=<?= $row['order_id'] ?>"
                   onclick="return confirm('Tandai pesanan ini selesai? Petani sudah bayar dan ambil barang.')"
                   class="px-4 text-center bg-green-500 text-white text-sm font-semibold
                          py-2.5 rounded-xl hover:bg-green-600 transition">
                    🏁 Selesai
                </a>
            </div>

            <?php elseif ($row['status'] === 'pending' && $is_expired): ?>
            <div class="mt-4 pt-4 border-t border-gray-50">
                <p class="text-sm text-red-400 text-center">
                    ⏰ Pesanan ini sudah melewati batas waktu dan akan otomatis dibatalkan.
                </p>
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