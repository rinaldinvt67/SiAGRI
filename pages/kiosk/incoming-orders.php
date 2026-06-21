<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Kiosk') {
    header("Location: ../../pages/auth/login.php");
    exit;
}

$kiosk_id = $_SESSION['kiosk_id'];
$success  = "";

// LAZY CHECK: batalkan pesanan expired
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

// KONFIRMASI PESANAN
if (isset($_GET['confirm'])) {
    $order_id = (int)$_GET['confirm'];
    mysqli_query($conn,
        "UPDATE orders SET status = 'confirmed'
         WHERE order_id = $order_id AND kiosk_id = $kiosk_id AND status = 'pending'"
    );
    header("Location: incoming-orders.php?success=confirmed");
    exit;
}

// TANDAI SELESAI
if (isset($_GET['complete'])) {
    $order_id = (int)$_GET['complete'];
    mysqli_query($conn,
        "UPDATE orders SET status = 'completed'
         WHERE order_id = $order_id AND kiosk_id = $kiosk_id AND status = 'confirmed'"
    );
    header("Location: incoming-orders.php?success=completed");
    exit;
}

// BATALKAN PESANAN (oleh Kiosk)
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
        'confirmed' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Pesanan dikonfirmasi! Siapkan barangnya.',
        'completed' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Pesanan ditandai selesai.',
        'cancelled' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Pesanan dibatalkan dan stok dikembalikan.',
    ];
    $success = $msgs[$_GET['success']] ?? '';
}

// FILTER STATUS 
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

// AMBIL PESANAN
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
    <?php $page_title = 'Pesanan Masuk'; include '../../component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'incoming-orders'; $pesanan_pending = $count_pending; include '../../component/layout/navbar.php'; ?>

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
        <p class="text-5xl mb-4"><svg class="inline-block w-16 h-16 mx-auto mb-4 text-gray-300 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M9.5 20v2a.75.75 0 0 0 1.5 0v-2zm5.5 0h-1.5v2a.75.75 0 0 0 1.5 0z"/><path fill="currentColor" fill-rule="evenodd" d="m17.385 6.585l.256-.052a2.2 2.2 0 0 1 1.24.115c.69.277 1.446.328 2.165.148l.061-.015c.524-.131.893-.618.893-1.178v-2.13c0-.738-.664-1.282-1.355-1.109c-.396.1-.812.071-1.193-.081l-.073-.03a3.5 3.5 0 0 0-2-.185l-.449.09c-.54.108-.93.6-.93 1.17v6.953c0 .397.31.719.692.719a.706.706 0 0 0 .693-.72z" clip-rule="evenodd"/><path fill="currentColor" d="M14.5 6v4.28c0 1.172.928 2.22 2.192 2.22s2.193-1.048 2.193-2.22V8.229c.76.205 1.56.23 2.335.067c.492.842.78 1.86.78 2.955v6.175C22 18.847 21.012 20 19.793 20H12.5v-8.75c0-2.03-.832-3.974-2.217-5.25z"/><path fill="currentColor" fill-rule="evenodd" d="M2 11.25C2 8.35 4.015 6 6.5 6S11 8.35 11 11.25V20H4.233C3 20 2 18.834 2 17.395zM4.25 16a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75" clip-rule="evenodd"/></svg></p>
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
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m17.578 4.432l-2-1.05C13.822 2.461 12.944 2 12 2s-1.822.46-3.578 1.382l-.321.169l8.923 5.099l4.016-2.01c-.646-.732-1.688-1.279-3.462-2.21m4.17 3.534l-3.998 2V13a.75.75 0 0 1-1.5 0v-2.286l-3.5 1.75v9.44c.718-.179 1.535-.607 2.828-1.286l2-1.05c2.151-1.129 3.227-1.693 3.825-2.708c.597-1.014.597-2.277.597-4.8v-.117c0-1.893 0-3.076-.252-3.978M11.25 21.904v-9.44l-8.998-4.5C2 8.866 2 10.05 2 11.941v.117c0 2.525 0 3.788.597 4.802c.598 1.015 1.674 1.58 3.825 2.709l2 1.049c1.293.679 2.11 1.107 2.828 1.286M2.96 6.641l9.04 4.52l3.411-1.705l-8.886-5.078l-.103.054c-1.773.93-2.816 1.477-3.462 2.21"/></svg>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info pesanan -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800">
                        <?= htmlspecialchars($row['product_name']) ?>
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="12" cy="6" r="4" fill="currentColor"/><path fill="currentColor" d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5"/></svg> Petani: <span class="text-gray-600 font-medium">
                            <?= htmlspecialchars($row['username']) ?>
                        </span>
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M10.723 3.2a.75.75 0 1 0-1.446-.4L7.763 8.25H4a.75.75 0 1 0 0 1.5h3.347l-1.528 5.5H2a.75.75 0 0 0 0 1.5h3.402L4.277 20.8a.75.75 0 0 0 1.446.4l1.236-4.45h7.443l-1.125 4.05a.75.75 0 0 0 1.446.4l1.236-4.45H20a.75.75 0 1 0 0-1.5h-3.624l1.527-5.5H22a.75.75 0 0 0 0-1.5h-3.68l1.403-5.05a.75.75 0 1 0-1.446-.4l-1.514 5.45H9.32zm4.096 12.05l1.528-5.5H8.903l-1.527 5.5z" clip-rule="evenodd"/></svg> <?= $row['quantity'] ?> unit ×
                        Rp <?= number_format($row['price'], 0, ',', '.') ?>
                    </p>
                    <p class="font-bold text-siagri-dark mt-1">
                        Total: Rp <?= number_format($subtotal, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M20.41 9.86a3 3 0 0 0-.175-.003H17.8c-1.992 0-3.698 1.581-3.698 3.643s1.706 3.643 3.699 3.643h2.433q.092.001.175-.004a1.7 1.7 0 0 0 1.586-1.581c.004-.059.004-.122.004-.18v-3.756c0-.058 0-.121-.004-.18a1.7 1.7 0 0 0-1.585-1.581m-2.823 4.611c.513 0 .93-.434.93-.971s-.417-.971-.93-.971s-.929.434-.929.971s.416.971.93.971" clip-rule="evenodd"/><path fill="currentColor" fill-rule="evenodd" d="M20.234 18.6a.214.214 0 0 1 .214.27c-.194.692-.501 1.282-.994 1.778c-.721.727-1.636 1.05-2.766 1.203c-1.098.149-2.5.149-4.272.149h-2.037c-1.771 0-3.174 0-4.272-.149c-1.13-.153-2.045-.476-2.766-1.203C2.62 19.923 2.3 19 2.148 17.862C2 16.754 2 15.34 2 13.555v-.11c0-1.785 0-3.2.148-4.306C2.3 8 2.62 7.08 3.34 6.351c.721-.726 1.636-1.05 2.766-1.202C7.205 5 8.608 5 10.379 5h2.037c1.771 0 3.174 0 4.272.149c1.13.153 2.045.476 2.766 1.202c.493.497.8 1.087.994 1.78a.214.214 0 0 1-.214.269h-2.433c-2.734 0-5.143 2.177-5.143 5.1s2.41 5.1 5.144 5.1zM5.614 8.886a.725.725 0 0 0-.722.728c0 .403.323.729.722.729H9.47c.4 0 .723-.326.723-.729a.726.726 0 0 0-.723-.728z" clip-rule="evenodd"/><path fill="currentColor" d="m7.777 4.024l1.958-1.443a2.97 2.97 0 0 1 3.53 0l1.969 1.451C14.41 4 13.49 4 12.483 4h-2.17c-.922 0-1.769 0-2.536.024"/></svg> Pembayaran kontan saat pengambilan
                    </p>
                </div>

                <!-- Info waktu & expired -->
                <div class="text-right flex-shrink-0">
                    <?php if ($row['status'] === 'pending'): ?>
                    <p class="text-xs text-gray-400 mb-1">Batas waktu:</p>
                    <p class="text-sm font-semibold <?= $is_expired ? 'text-red-500' : 'text-orange-500' ?>
                               font-mono" id="timer-<?= $row['order_id'] ?>"
                       data-expired="<?= strtotime($row['expired_at']) ?>">
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
                    <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> 
                    Konfirmasi, Siapkan Barang
                </a>
                <a href="?cancel=<?= $row['order_id'] ?>"
                   onclick="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')"
                   class="px-4 text-center border border-red-200 text-red-500 text-sm
                          font-semibold py-2.5 rounded-xl hover:bg-red-500 hover:text-white transition">
                    Batalkan
                </a>
            </div>

            <?php elseif ($row['status'] === 'confirmed'): ?>
            <div class="flex gap-3 mt-4 pt-4 border-t border-gray-50">
                <div class="flex-1 bg-blue-50 text-blue-700 text-sm text-center
                            py-2.5 rounded-xl font-medium">
                    <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m17.578 4.432l-2-1.05C13.822 2.461 12.944 2 12 2s-1.822.46-3.578 1.382l-.321.169l8.923 5.099l4.016-2.01c-.646-.732-1.688-1.279-3.462-2.21m4.17 3.534l-3.998 2V13a.75.75 0 0 1-1.5 0v-2.286l-3.5 1.75v9.44c.718-.179 1.535-.607 2.828-1.286l2-1.05c2.151-1.129 3.227-1.693 3.825-2.708c.597-1.014.597-2.277.597-4.8v-.117c0-1.893 0-3.076-.252-3.978M11.25 21.904v-9.44l-8.998-4.5C2 8.866 2 10.05 2 11.941v.117c0 2.525 0 3.788.597 4.802c.598 1.015 1.674 1.58 3.825 2.709l2 1.049c1.293.679 2.11 1.107 2.828 1.286M2.96 6.641l9.04 4.52l3.411-1.705l-8.886-5.078l-.103.054c-1.773.93-2.816 1.477-3.462 2.21"/></svg> Barang sudah disiapkan, menunggu Petani datang
                </div>
                <a href="?complete=<?= $row['order_id'] ?>"
                   onclick="return confirm('Tandai pesanan ini selesai? Petani sudah bayar dan ambil barang.')"
                   class="px-4 text-center bg-green-500 text-white text-sm font-semibold
                          py-2.5 rounded-xl hover:bg-green-600 transition">
                    <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M5.75 1a.75.75 0 0 1 .75.75V3.6l1.72-.344a8.7 8.7 0 0 1 4.925.452l.204.081a8 8 0 0 0 4.91.334a1.2 1.2 0 0 1 1.491 1.164v7.367c0 .644-.439 1.206-1.064 1.362l-.214.053a8.68 8.68 0 0 1-5.327-.361a8.7 8.7 0 0 0-4.924-.452L6.5 13.6v8.15a.75.75 0 0 1-1.5 0v-20A.75.75 0 0 1 5.75 1"/></svg> Selesai
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endwhile; ?>
    </div>
    <?php endif; ?>

</div>

<script src="../../assets/js/orders.js"></script>
<?php include '../../component/layout/footer.php'; ?>

</body>
</html>