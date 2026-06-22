<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../../pages/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// LAZY CHECK: batalkan pesanan expired
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
    <?php $page_title = 'Pesanan Saya'; include '../../component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'my-orders'; include '../../component/layout/navbar.php'; ?>

<div class="max-w-4xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-siagri-dark mb-6">Pesanan Saya</h1>

    <!-- Notifikasi sukses -->
    <?php if (isset($_GET['success'])): ?>
    <div class="mb-5 p-4 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Pesanan berhasil dibuat! Datang ke kios dalam 24 jam untuk mengambil barang.
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
        <p class="text-5xl mb-4"><svg class="inline-block w-16 h-16 mx-auto mb-4 text-gray-300 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M9.5 20v2a.75.75 0 0 0 1.5 0v-2zm5.5 0h-1.5v2a.75.75 0 0 0 1.5 0z"/><path fill="currentColor" fill-rule="evenodd" d="m17.385 6.585l.256-.052a2.2 2.2 0 0 1 1.24.115c.69.277 1.446.328 2.165.148l.061-.015c.524-.131.893-.618.893-1.178v-2.13c0-.738-.664-1.282-1.355-1.109c-.396.1-.812.071-1.193-.081l-.073-.03a3.5 3.5 0 0 0-2-.185l-.449.09c-.54.108-.93.6-.93 1.17v6.953c0 .397.31.719.692.719a.706.706 0 0 0 .693-.72z" clip-rule="evenodd"/><path fill="currentColor" d="M14.5 6v4.28c0 1.172.928 2.22 2.192 2.22s2.193-1.048 2.193-2.22V8.229c.76.205 1.56.23 2.335.067c.492.842.78 1.86.78 2.955v6.175C22 18.847 21.012 20 19.793 20H12.5v-8.75c0-2.03-.832-3.974-2.217-5.25z"/><path fill="currentColor" fill-rule="evenodd" d="M2 11.25C2 8.35 4.015 6 6.5 6S11 8.35 11 11.25V20H4.233C3 20 2 18.834 2 17.395zM4.25 16a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75" clip-rule="evenodd"/></svg></p>
        <p class="font-medium text-gray-600">Belum ada pesanan</p>
        <p class="text-sm text-gray-400 mt-1 mb-6">
            Temukan produk pertanian yang kamu butuhkan di katalog
        </p>
        <a href="../../pages/farmer/catalog.php"
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
                    <div class="w-full h-full flex items-center justify-center text-2xl"><svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m17.578 4.432l-2-1.05C13.822 2.461 12.944 2 12 2s-1.822.46-3.578 1.382l-.321.169l8.923 5.099l4.016-2.01c-.646-.732-1.688-1.279-3.462-2.21m4.17 3.534l-3.998 2V13a.75.75 0 0 1-1.5 0v-2.286l-3.5 1.75v9.44c.718-.179 1.535-.607 2.828-1.286l2-1.05c2.151-1.129 3.227-1.693 3.825-2.708c.597-1.014.597-2.277.597-4.8v-.117c0-1.893 0-3.076-.252-3.978M11.25 21.904v-9.44l-8.998-4.5C2 8.866 2 10.05 2 11.941v.117c0 2.525 0 3.788.597 4.802c.598 1.015 1.674 1.58 3.825 2.709l2 1.049c1.293.679 2.11 1.107 2.828 1.286M2.96 6.641l9.04 4.52l3.411-1.705l-8.886-5.078l-.103.054c-1.773.93-2.816 1.477-3.462 2.21"/></svg></div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800">
                        <?= htmlspecialchars($row['product_name']) ?>
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"/></svg> <?= htmlspecialchars($row['store_name']) ?>
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M12 2c-4.418 0-8 4.003-8 8.5c0 4.462 2.553 9.312 6.537 11.174a3.45 3.45 0 0 0 2.926 0C17.447 19.812 20 14.962 20 10.5C20 6.003 16.418 2 12 2m0 10a2 2 0 1 0 0-4a2 2 0 0 0 0 4" clip-rule="evenodd"/></svg> <?= htmlspecialchars($row['full_address']) ?>
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        <?= $row['quantity'] ?> unit ×
                        Rp <?= number_format($row['price'], 0, ',', '.') ?>
                    </p>
                    <p class="font-bold text-siagri-dark mt-1">
                        Total: Rp <?= number_format($subtotal, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-gray-400"><svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M20.41 9.86a3 3 0 0 0-.175-.003H17.8c-1.992 0-3.698 1.581-3.698 3.643s1.706 3.643 3.699 3.643h2.433q.092.001.175-.004a1.7 1.7 0 0 0 1.586-1.581c.004-.059.004-.122.004-.18v-3.756c0-.058 0-.121-.004-.18a1.7 1.7 0 0 0-1.585-1.581m-2.823 4.611c.513 0 .93-.434.93-.971s-.417-.971-.93-.971s-.929.434-.929.971s.416.971.93.971" clip-rule="evenodd"/><path fill="currentColor" fill-rule="evenodd" d="M20.234 18.6a.214.214 0 0 1 .214.27c-.194.692-.501 1.282-.994 1.778c-.721.727-1.636 1.05-2.766 1.203c-1.098.149-2.5.149-4.272.149h-2.037c-1.771 0-3.174 0-4.272-.149c-1.13-.153-2.045-.476-2.766-1.203C2.62 19.923 2.3 19 2.148 17.862C2 16.754 2 15.34 2 13.555v-.11c0-1.785 0-3.2.148-4.306C2.3 8 2.62 7.08 3.34 6.351c.721-.726 1.636-1.05 2.766-1.202C7.205 5 8.608 5 10.379 5h2.037c1.771 0 3.174 0 4.272.149c1.13.153 2.045.476 2.766 1.202c.493.497.8 1.087.994 1.78a.214.214 0 0 1-.214.269h-2.433c-2.734 0-5.143 2.177-5.143 5.1s2.41 5.1 5.144 5.1zM5.614 8.886a.725.725 0 0 0-.722.728c0 .403.323.729.722.729H9.47c.4 0 .723-.326.723-.729a.726.726 0 0 0-.723-.728z" clip-rule="evenodd"/><path fill="currentColor" d="m7.777 4.024l1.958-1.443a2.97 2.97 0 0 1 3.53 0l1.969 1.451C14.41 4 13.49 4 12.483 4h-2.17c-.922 0-1.769 0-2.536.024"/></svg> Bayar kontan saat ambil</p>
                </div>

                <!-- Timer / info kanan -->
                <div class="text-right flex-shrink-0">
                    <?php if ($row['status'] === 'pending'): ?>
                    <p class="text-xs text-gray-400 mb-1">Sisa waktu:</p>
                    <div class="text-lg font-bold font-mono text-orange-500"
                         id="timer-<?= $row['order_id'] ?>"
                         data-expired="<?= strtotime($row['expired_at']) ?>"
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
                    <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m13.629 20.472l-.542.916c-.483.816-1.69.816-2.174 0l-.542-.916c-.42-.71-.63-1.066-.968-1.262c-.338-.197-.763-.204-1.613-.219c-1.256-.021-2.043-.098-2.703-.372a5 5 0 0 1-2.706-2.706C2 14.995 2 13.83 2 11.5v-1c0-3.273 0-4.91.737-6.112a5 5 0 0 1 1.65-1.651C5.59 2 7.228 2 10.5 2h3c3.273 0 4.91 0 6.113.737a5 5 0 0 1 1.65 1.65C22 5.59 22 7.228 22 10.5v1c0 2.33 0 3.495-.38 4.413a5 5 0 0 1-2.707 2.706c-.66.274-1.447.35-2.703.372c-.85.015-1.275.022-1.613.219c-.338.196-.548.551-.968 1.262"/></svg> Hubungi Kios via WA
                </a>
                <?php endif; ?>
                <div class="flex-1 bg-yellow-50 text-yellow-700 text-xs text-center
                            py-2.5 rounded-xl font-medium flex items-center justify-center">
                    <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-yellow-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><defs><mask id="SVGnNgsclOC"><g fill="none"><path fill="#fff" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10"/><path fill="#000" fill-rule="evenodd" d="M12 7.25a.75.75 0 0 1 .75.75v3.69l2.28 2.28a.75.75 0 1 1-1.06 1.06l-2.5-2.5a.75.75 0 0 1-.22-.53V8a.75.75 0 0 1 .75-.75" clip-rule="evenodd"/></g></mask></defs><path fill="currentColor" d="M0 0h24v24H0z" mask="url(#SVGnNgsclOC)"/></svg> Menunggu konfirmasi kios
                </div>
            </div>

            <?php elseif ($row['status'] === 'confirmed'): ?>
            <div class="mt-4 pt-4 border-t border-gray-50">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-blue-800 font-semibold text-sm">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Pesanan dikonfirmasi kios!
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
                    <svg class="inline-block w-5 h-5 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m13.039 18.386l.267-.088c2.298-.767 3.447-1.15 3.66-2.056c.215-.906-.642-1.763-2.355-3.475l-1.689-1.69l-.005.015l-.082.267c-.068.23-.16.55-.251.916c-.187.75-.357 1.622-.357 2.28s.17 1.531.357 2.28a21 21 0 0 0 .356 1.253l.005.017l.002.004zm-5.567 1.855c-2.262.746-3.454 1.058-4.113.399c-.73-.73-.269-2.113.653-4.878l1.69-5.069c.632-1.896 1.003-3.01 1.622-3.461l-.005.025a26 26 0 0 0-.138.73a51 51 0 0 0-.31 1.939c-.216 1.533-.415 3.492-.312 5.057c.062.948.259 2.123.435 3.04a51 51 0 0 0 .312 1.503l.02.093zM8.85 7.259l-.05.245v.003l-.003.009l-.007.037a25 25 0 0 0-.133.7a50 50 0 0 0-.301 1.881c-.213 1.515-.393 3.347-.3 4.751c.055.85.237 1.95.41 2.857a49 49 0 0 0 .303 1.455l.02.088l.005.022l.107.459l2.715-.905l-.103-.309a10 10 0 0 1-.115-.37c-.073-.247-.171-.59-.27-.983c-.192-.77-.401-1.792-.401-2.644s.21-1.874.401-2.643a22 22 0 0 1 .385-1.354l.01-.027l.212-.64l-.503-.503C10.19 8.344 9.463 7.618 8.85 7.259m2.076-4.899a.75.75 0 0 1 .25 1.031a.65.65 0 0 0 .094.8l.098.098c.589.588.806 1.453.565 2.25a.75.75 0 1 1-1.436-.434a.76.76 0 0 0-.19-.756l-.098-.098a2.15 2.15 0 0 1-.314-2.642a.75.75 0 0 1 1.031-.249m2.635 2.037c.201-.201.302-.302.418-.339a.5.5 0 0 1 .302 0c.116.037.217.138.418.339c.2.2.301.301.338.417a.5.5 0 0 1 0 .303c-.037.116-.137.216-.338.417s-.302.302-.418.339a.5.5 0 0 1-.302 0c-.116-.037-.217-.138-.418-.339c-.201-.2-.302-.301-.338-.417a.5.5 0 0 1 0-.303c.036-.116.137-.216.338-.417M6.927 3.94a.536.536 0 1 1 .759.76a.536.536 0 0 1-.759-.76m13.048 3.107c-.139.053-.261.176-.507.421c-.245.246-.368.368-.421.507a.7.7 0 0 0 0 .503c.053.138.176.261.421.507c.246.245.368.368.507.421a.7.7 0 0 0 .503 0c.138-.053.261-.176.507-.421c.245-.246.368-.369.421-.507a.7.7 0 0 0 0-.503c-.053-.139-.176-.261-.421-.507c-.246-.245-.369-.368-.507-.421a.7.7 0 0 0-.503 0m-.917 8.266a.536.536 0 1 1 .759.759a.536.536 0 0 1-.759-.759M17.69 4.722a.75.75 0 0 1 .588.882l-.144.72a2.82 2.82 0 0 1-1.871 2.12a1.31 1.31 0 0 0-.874.99l-.144.72a.75.75 0 0 1-1.471-.295l.144-.72c.198-.99.912-1.8 1.87-2.119c.448-.15.782-.527.875-.99l.144-.72a.75.75 0 0 1 .882-.588m3.719 7.838a1.01 1.01 0 0 0-1.078.17a2.51 2.51 0 0 1-2.923.296l-.213-.123a.75.75 0 0 1 .75-1.299l.213.123c.377.218.852.17 1.178-.12a2.51 2.51 0 0 1 2.674-.422l.292.128a.75.75 0 0 1-.601 1.374zM17.5 9.742a.536.536 0 1 1 .759.758a.536.536 0 0 1-.759-.758"/></svg> Pesanan selesai! Terima kasih sudah berbelanja di SiAGRI.
                </div>
            </div>

            <?php elseif ($row['status'] === 'cancelled'): ?>
            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                <p class="text-red-400 text-sm"><svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> Pesanan dibatalkan</p>
                <a href="../../pages/farmer/catalog.php"
                   class="text-sm text-siagri-dark underline">Pesan lagi →</a>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endwhile; ?>
    </div>
    <?php endif; ?>

</div>

<script src="../../Assets/js/orders.js"></script>
<?php include '../../component/layout/footer.php'; ?>

</body>
</html>
