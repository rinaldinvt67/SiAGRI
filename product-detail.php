<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../pages/auth/login.php");
    exit;
}

$role    = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Ambil product_id dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../../pages/farmer/catalog.php");
    exit;
}
$product_id = (int)$_GET['id'];

// Handler Aksi Post Farmer (Keranjang)
if ($role === 'Farmer' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_to_cart') {
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        // Cek stok produk
        $prod = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT stock FROM products WHERE product_id = $product_id"
        ));

        if (!$prod || $prod['stock'] < $qty) {
            $cart_error = "Stok tidak mencukupi!";
        } else {
            // Cek apakah item sudah ada di keranjang
            $existing = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT cart_id, quantity FROM cart
                 WHERE user_id=$user_id AND product_id=$product_id"
            ));
            if ($existing) {
                $new_qty = $existing['quantity'] + $qty;
                if ($new_qty > $prod['stock']) {
                    $new_qty = $prod['stock'];
                }
                mysqli_query($conn,
                    "UPDATE cart SET quantity=$new_qty
                     WHERE cart_id={$existing['cart_id']}"
                );
            } else {
                mysqli_query($conn,
                    "INSERT INTO cart (user_id, product_id, quantity)
                     VALUES ($user_id, $product_id, $qty)"
                );
            }
            $cart_success = "Produk berhasil ditambahkan ke keranjang!";
        }
    }

    if ($_POST['action'] === 'remove_from_cart') {
        $cart_id = (int)$_POST['cart_id'];
        mysqli_query($conn,
            "DELETE FROM cart WHERE cart_id=$cart_id AND user_id=$user_id"
        );
        $cart_success = "Produk dihapus dari keranjang.";
    }
}

// Ambil data detail produk
$product_query = mysqli_query($conn,
    "SELECT p.*, c.category_name, kp.store_name, kp.whatsapp_number, kp.full_address, kp.kyc_status
     FROM products p
     JOIN categories c ON p.category_id = c.category_id
     JOIN kiosk_profiles kp ON p.kiosk_id = kp.kiosk_id
     WHERE p.product_id = $product_id"
);
$p = mysqli_fetch_assoc($product_query);

// Jika produk tidak ditemukan
if (!$p) {
    header("Location: ../../pages/farmer/catalog.php");
    exit;
}

// Untuk Farmer, hanya boleh melihat produk dari Kios yang status KYC-nya verified
if ($role === 'Farmer' && $p['kyc_status'] !== 'verified') {
    header("Location: ../../pages/farmer/catalog.php");
    exit;
}

// Hitung total produk terjual (berdasarkan pesanan yang statusnya 'completed')
$sold_query = mysqli_query($conn,
    "SELECT COALESCE(SUM(oi.quantity), 0) as total_sold
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.order_id
     WHERE oi.product_id = $product_id AND o.status = 'completed'"
);
$sold_data = mysqli_fetch_assoc($sold_query);
$total_sold = $sold_data['total_sold'];

// Ambil cart count & items untuk Navbar (Farmer only)
$cart_count = 0;
$cart_items = [];
if ($role === 'Farmer') {
    $cc = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT SUM(quantity) as total FROM cart WHERE user_id=$user_id"
    ));
    $cart_count = $cc['total'] ?? 0;

    $cart_items = mysqli_query($conn,
        "SELECT c.*, p.product_name, p.selling_price, p.stock,
                kp.store_name, p.product_image
         FROM cart c
         JOIN products p        ON c.product_id = p.product_id
         JOIN kiosk_profiles kp ON p.kiosk_id   = kp.kiosk_id
         WHERE c.user_id = $user_id"
    );
}

// Hitung pesanan pending (Kiosk only)
$pesanan_pending = 0;
if ($role === 'Kiosk') {
    $kiosk_id = $_SESSION['kiosk_id'] ?? 0;
    $pesanan_pending = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM orders
         WHERE kiosk_id = $kiosk_id AND status = 'pending'"
    ))['total'] ?? 0;
}

// Format link WhatsApp helper
function format_whatsapp($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strpos($phone, '0') === 0) {
        $phone = '62' . substr($phone, 1);
    }
    return $phone;
}

$wa_message = rawurlencode("Halo " . $p['store_name'] . ", saya tertarik dengan produk \"" . $p['product_name'] . "\" di SiAGRI. Apakah produk ini tersedia?");
$wa_url = "https://wa.me/" . format_whatsapp($p['whatsapp_number']) . "?text=" . $wa_message;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <?php
    $page_title = 'Detail Produk - ' . htmlspecialchars($p['product_name']);
    $extra_head = '';
    include 'component/layout/head.php';
    ?>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col justify-between">

<div>
    <!-- Navbar -->
    <?php $current_page = 'catalog'; include 'component/layout/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <!-- Breadcrumbs & Tombol Kembali -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <nav class="flex text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="../../pages/farmer/catalog.php" class="hover:text-siagri-green transition font-medium flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Katalog
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="ml-1 md:ml-2 font-semibold text-siagri-dark truncate max-w-xs md:max-w-md">
                                <?= htmlspecialchars($p['product_name']) ?>
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <a href="../../pages/farmer/catalog.php" class="inline-flex items-center text-xs font-semibold text-siagri-dark bg-white border border-gray-200 rounded-xl px-4 py-2 hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Katalog
            </a>
        </div>

        <!-- Alert Notification -->
        <?php if (isset($cart_success)): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-xl flex items-center gap-3 shadow-sm text-sm">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd"/>
                </svg>
                <?= $cart_success ?>
            </div>
        <?php endif; ?>

        <?php if (isset($cart_error)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-xl flex items-center gap-3 shadow-sm text-sm">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd"/>
                </svg>
                <?= $cart_error ?>
            </div>
        <?php endif; ?>

        <!-- Layout Utama Detail Produk -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Kiri: Gambar Produk -->
            <div class="lg:col-span-5 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                <div class="relative w-full aspect-square bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center">
                    <?php if ($p['product_image']): ?>
                        <img src="<?= htmlspecialchars($p['product_image']) ?>" 
                             alt="<?= htmlspecialchars($p['product_name']) ?>" 
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    <?php endif; ?>

                    <!-- Badges -->
                    <div class="absolute top-4 left-4 flex flex-col gap-2">
                        <?php if ($p['is_subsidized'] === 'Yes'): ?>
                            <span class="bg-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow">
                                Subsidi
                            </span>
                        <?php endif; ?>
                        <?php if ($p['kyc_status'] === 'verified'): ?>
                            <span class="bg-blue-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow">
                                Kios Resmi
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Kanan: Info & Aksi -->
            <div class="lg:col-span-7 bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <!-- Kategori -->
                <span class="text-xs uppercase tracking-wider text-siagri-green font-bold bg-green-50 px-2.5 py-1 rounded">
                    <?= htmlspecialchars($p['category_name']) ?>
                </span>

                <!-- Nama Produk -->
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mt-3 leading-tight">
                    <?= htmlspecialchars($p['product_name']) ?>
                </h1>

                <!-- Harga -->
                <div class="mt-4 flex flex-wrap items-baseline gap-2">
                    <span class="text-3xl font-black text-siagri-dark">
                        Rp <?= number_format($p['selling_price'], 0, ',', '.') ?>
                    </span>
                </div>

                <!-- Warning HET jika ada -->
                <?php if ($p['is_subsidized'] === 'Yes' && $p['het_price'] > 0): ?>
                    <div class="mt-3 p-3 rounded-xl flex items-center gap-2.5 text-xs font-semibold <?= $p['selling_price'] > $p['het_price'] ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' ?>">
                        <?php if ($p['selling_price'] > $p['het_price']): ?>
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Peringatan: Harga melebihi HET (Maksimal Rp <?= number_format($p['het_price'], 0, ',', '.') ?>)
                        <?php else: ?>
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Sesuai HET (Harga Eceran Tertinggi Rp <?= number_format($p['het_price'], 0, ',', '.') ?>)
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <hr class="my-6 border-gray-100">

                <!-- Stok & Terjual -->
                <div class="grid grid-cols-2 gap-4 text-center md:text-left bg-gray-50 p-4 rounded-xl">
                    <div>
                        <span class="block text-xs text-gray-400 font-medium">Stok Tersedia</span>
                        <span class="text-base font-bold <?= $p['stock'] <= 5 ? 'text-red-500' : 'text-gray-700' ?>">
                            <?= $p['stock'] ?> unit
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-medium">Total Terjual</span>
                        <span class="text-base font-bold text-gray-700">
                            <?= $total_sold ?> unit
                        </span>
                    </div>
                </div>

                <!-- Form / WhatsApp Aksi -->
                <div class="mt-6">
                    <?php if ($role === 'Farmer'): ?>
                        <?php if ($p['stock'] > 0): ?>
                            <!-- Form Atur Quantity -->
                            <div class="flex flex-col sm:flex-row items-center gap-4 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-600 mr-2">Jumlah:</span>
                                    <button type="button" onclick="adjustQty(-1)" class="w-10 h-10 border border-gray-200 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-50 active:bg-gray-100 transition font-bold shadow-sm">
                                        −
                                    </button>
                                    <input type="number" id="qty-input" value="1" min="1" max="<?= $p['stock'] ?>" class="w-16 h-10 border border-gray-200 rounded-xl text-center font-bold text-gray-800 focus:outline-none focus:border-siagri-dark shadow-sm bg-gray-50" readonly>
                                    <button type="button" onclick="adjustQty(1)" class="w-10 h-10 border border-gray-200 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-50 active:bg-gray-100 transition font-bold shadow-sm">
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- Tombol Aksi Farmer -->
                            <div class="flex flex-col md:flex-row gap-3">
                                <form method="POST" class="flex-1">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="quantity" id="hidden-qty-input" value="1">
                                    <button type="submit" class="w-full bg-siagri-dark text-white py-3.5 px-6 rounded-xl hover:bg-siagri-green transition font-bold shadow-md flex items-center justify-center gap-2.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Masukkan Keranjang
                                    </button>
                                </form>
                                <a href="<?= $wa_url ?>" target="_blank" class="flex-1 bg-green-500 text-white py-3.5 px-6 rounded-xl hover:bg-green-600 transition font-bold shadow-md flex items-center justify-center gap-2.5">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.488 1.459 5.407 1.46h.006c5.566 0 10.096-4.526 10.1-10.096.002-2.7-.1.047-5.247-2.1-7.382-2.1-2.13-1.047-4.134-2.266-5.734-1.6-1.6-1.459-3.75-2.213-5.752-2.213-.679-.001-1.348.113-1.986.34L6.647 19.154zM16.92 14.81c-.267-.134-1.58-.78-1.823-.867-.243-.088-.42-.132-.596.134-.176.265-.682.868-.836 1.046-.154.177-.308.2-.575.066-.267-.134-1.127-.416-2.146-1.326-.793-.707-1.328-1.58-1.484-1.847-.156-.267-.017-.411.117-.544.12-.12.267-.31.4-.464.135-.155.18-.266.27-.443.09-.177.045-.333-.023-.466-.068-.134-.596-1.436-.816-1.968-.214-.516-.45-.445-.6-.453l-.513-.007c-.177 0-.464.066-.707.332-.243.266-.93.91-.93 2.222 0 1.31.954 2.576 1.087 2.753.133.177 1.88 2.87 4.555 4.024.636.274 1.13.438 1.517.561.64.203 1.222.174 1.682.105.514-.077 1.58-.646 1.802-1.238.222-.593.222-1.102.155-1.21-.067-.105-.244-.17-.512-.303z"/>
                                    </svg>
                                    Tanya Kios via WA
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="p-4 bg-yellow-50 text-yellow-800 rounded-xl text-center font-medium border border-yellow-200">
                                Stok produk habis. Hubungi kios via WhatsApp untuk menanyakan ketersediaan kembali.
                            </div>
                            <a href="<?= $wa_url ?>" target="_blank" class="w-full bg-green-500 text-white py-3.5 px-6 rounded-xl hover:bg-green-600 transition font-bold shadow-md flex items-center justify-center gap-2.5 mt-3">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.488 1.459 5.407 1.46h.006c5.566 0 10.096-4.526 10.1-10.096.002-2.7-.1.047-5.247-2.1-7.382-2.1-2.13-1.047-4.134-2.266-5.734-1.6-1.6-1.459-3.75-2.213-5.752-2.213-.679-.001-1.348.113-1.986.34L6.647 19.154zM16.92 14.81c-.267-.134-1.58-.78-1.823-.867-.243-.088-.42-.132-.596.134-.176.265-.682.868-.836 1.046-.154.177-.308.2-.575.066-.267-.134-1.127-.416-2.146-1.326-.793-.707-1.328-1.58-1.484-1.847-.156-.267-.017-.411.117-.544.12-.12.267-.31.4-.464.135-.155.18-.266.27-.443.09-.177.045-.333-.023-.466-.068-.134-.596-1.436-.816-1.968-.214-.516-.45-.445-.6-.453l-.513-.007c-.177 0-.464.066-.707.332-.243.266-.93.91-.93 2.222 0 1.31.954 2.576 1.087 2.753.133.177 1.88 2.87 4.555 4.024.636.274 1.13.438 1.517.561.64.203 1.222.174 1.682.105.514-.077 1.58-.646 1.802-1.238.222-.593.222-1.102.155-1.21-.067-.105-.244-.17-.512-.303z"/>
                                </svg>
                                Tanya Kios via WA
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- User non-Farmer (Kiosk / Admin / Expert) -->
                        <div class="p-4 bg-gray-50 border border-gray-200 text-gray-500 rounded-xl text-xs md:text-sm font-medium">
                            <span class="font-bold text-gray-700">Catatan:</span> Tombol pembelian, keranjang, dan hubungi via WhatsApp dinonaktifkan untuk role <strong><?= htmlspecialchars($role) ?></strong>. Fitur ini hanya tersedia untuk akun Petani (Farmer).
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Section Informasi Pendukung: Deskripsi & Toko -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-8">
            <!-- Deskripsi Produk -->
            <div class="lg:col-span-8 bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-lg font-bold text-siagri-dark border-b border-gray-100 pb-3">Deskripsi Produk</h2>
                <div class="text-sm text-gray-600 leading-relaxed mt-4">
                    <?= !empty($p['description']) ? nl2br(htmlspecialchars($p['description'])) : '<em class="text-gray-400">Tidak ada deskripsi produk.</em>' ?>
                </div>
            </div>

            <!-- Detail Toko/Kios -->
            <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-siagri-dark border-b border-gray-100 pb-3">Mitra Kios Penjual</h2>
                    <div class="mt-4 flex items-center gap-3">
                        <!-- Icon Kios -->
                        <div class="w-12 h-12 bg-siagri-dark/10 rounded-xl flex items-center justify-center text-siagri-dark shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"/>
                            </svg>
                        </div>
                        <div>
                            <span class="block font-bold text-gray-800 text-sm leading-tight">
                                <?= htmlspecialchars($p['store_name']) ?>
                            </span>
                            <?php if ($p['kyc_status'] === 'verified'): ?>
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full mt-1.5">
                                    <svg class="w-3 h-3 text-blue-500 fill-current" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/>
                                    </svg>
                                    Terverifikasi
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full mt-1.5">
                                    Belum Terverifikasi
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span class="block text-xs font-semibold text-gray-400">Alamat Kios</span>
                        <p class="text-sm text-gray-600 mt-1 font-medium leading-relaxed">
                            <?= htmlspecialchars($p['full_address']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- CART PANEL (Farmer Only — Slide dari kanan) -->
<?php if ($role === 'Farmer'): ?>

<!-- Overlay -->
<div id="overlay" class="overlay fixed inset-0 bg-black/40 z-40"
     onclick="toggleCart()"></div>

<!-- Panel -->
<div id="cart-panel" class="cart-panel fixed top-0 right-0 h-full w-full max-w-sm
                             bg-white shadow-2xl z-50 flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b bg-siagri-dark text-white">
        <h2 class="font-bold text-lg"><svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M2.237 2.288a.75.75 0 1 0-.474 1.423l.265.089c.676.225 1.124.376 1.453.529c.312.145.447.262.533.382s.155.284.194.626c.041.361.042.833.042 1.546v2.672c0 1.367 0 2.47.117 3.337c.12.9.38 1.658.982 2.26c.601.602 1.36.86 2.26.981c.866.117 1.969.117 3.336.117H18a.75.75 0 0 0 0-1.5h-7c-1.435 0-2.436-.002-3.192-.103c-.733-.099-1.122-.28-1.399-.556c-.235-.235-.4-.551-.506-1.091h10.12c.959 0 1.438 0 1.814-.248s.565-.688.943-1.57l.428-1c.81-1.89 1.215-2.834.77-3.508S18.506 6 16.45 6H5.745a9 9 0 0 0-.047-.833c-.055-.485-.176-.93-.467-1.333c-.291-.404-.675-.66-1.117-.865c-.417-.194-.946-.37-1.572-.58zM7.5 18a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3m9 0a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3"/></svg> Keranjang Saya</h2>
        <button onclick="toggleCart()" class="text-white/70 hover:text-white text-2xl leading-none">
            ×
        </button>
    </div>

    <div class="flex-1 overflow-y-auto p-4">
        <?php
        mysqli_data_seek($cart_items, 0);
        $cart_total = 0;
        $has_items  = false;
        while ($ci = mysqli_fetch_assoc($cart_items)):
            $has_items  = true;
            $subtotal   = $ci['quantity'] * $ci['selling_price'];
            $cart_total += $subtotal;
        ?>
        <div class="flex gap-3 mb-4 pb-4 border-b border-gray-100">
            <!-- Gambar -->
            <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                <?php if ($ci['product_image']): ?>
                <img src="<?= htmlspecialchars($ci['product_image']) ?>"
                     class="w-full h-full object-cover">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-gray-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                    </svg>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm text-gray-800 truncate">
                    <?= htmlspecialchars($ci['product_name']) ?>
                </p>
                <p class="text-xs text-gray-400 truncate">
                    <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"/></svg> <?= htmlspecialchars($ci['store_name']) ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    <?= $ci['quantity'] ?> × Rp <?= number_format($ci['selling_price'],0,',','.') ?>
                </p>
                <p class="text-sm font-bold text-siagri-dark">
                    Rp <?= number_format($subtotal, 0, ',', '.') ?>
                </p>
            </div>

            <!-- Hapus dari cart -->
            <form method="POST" class="self-start">
                <input type="hidden" name="action" value="remove_from_cart">
                <input type="hidden" name="cart_id" value="<?= $ci['cart_id'] ?>">
                <button type="submit"
                        class="text-red-400 hover:text-red-600 text-lg leading-none mt-1">×</button>
            </form>
        </div>
        <?php endwhile; ?>

        <?php if (!$has_items): ?>
        <div class="text-center py-12 text-gray-400">
            <p class="text-4xl mb-3"><svg class="inline-block w-5 h-5 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M2.237 2.288a.75.75 0 1 0-.474 1.423l.265.089c.676.225 1.124.376 1.453.529c.312.145.447.262.533.382s.155.284.194.626c.041.361.042.833.042 1.546v2.672c0 1.367 0 2.47.117 3.337c.12.9.38 1.658.982 2.26c.601.602 1.36.86 2.26.981c.866.117 1.969.117 3.336.117H18a.75.75 0 0 0 0-1.5h-7c-1.435 0-2.436-.002-3.192-.103c-.733-.099-1.122-.28-1.399-.556c-.235-.235-.4-.551-.506-1.091h10.12c.959 0 1.438 0 1.814-.248s.565-.688.943-1.57l.428-1c.81-1.89 1.215-2.834.77-3.508S18.506 6 16.45 6H5.745a9 9 0 0 0-.047-.833c-.055-.485-.176-.93-.467-1.333c-.291-.404-.675-.66-1.117-.865c-.417-.194-.946-.37-1.572-.58zM7.5 18a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3m9 0a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3"/></svg></p>
            <p class="font-medium">Keranjang masih kosong</p>
            <p class="text-sm">Tambah produk dari katalog</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($has_items): ?>
    <div class="border-t p-4 bg-white">
        <div class="flex justify-between items-center mb-4">
            <span class="font-medium text-gray-700">Total</span>
            <span class="font-bold text-siagri-dark text-lg">
                Rp <?= number_format($cart_total, 0, ',', '.') ?>
            </span>
        </div>
        <a href="../../pages/farmer/checkout.php"
           class="block w-full text-center bg-siagri-gold text-siagri-dark font-bold py-3
                  rounded-xl hover:bg-yellow-400 transition text-sm shadow-md">
            Lanjut ke Checkout →
        </a>
        <p class="text-xs text-gray-400 text-center mt-2">
            <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M20.41 9.86a3 3 0 0 0-.175-.003H17.8c-1.992 0-3.698 1.581-3.698 3.643s1.706 3.643 3.699 3.643h2.433q.092.001.175-.004a1.7 1.7 0 0 0 1.586-1.581c.004-.059.004-.122.004-.18v-3.756c0-.058 0-.121-.004-.18a1.7 1.7 0 0 0-1.585-1.581m-2.823 4.611c.513 0 .93-.434.93-.971s-.417-.971-.93-.971s-.929.434-.929.971s.416.971.93.971" clip-rule="evenodd"/><path fill="currentColor" fill-rule="evenodd" d="M20.234 18.6a.214.214 0 0 1 .214.27c-.194.692-.501 1.282-.994 1.778c-.721.727-1.636 1.05-2.766 1.203c-1.098.149-2.5.149-4.272.149h-2.037c-1.771 0-3.174 0-4.272-.149c-1.13-.153-2.045-.476-2.766-1.203C2.62 19.923 2.3 19 2.148 17.862C2 16.754 2 15.34 2 13.555v-.11c0-1.785 0-3.2.148-4.306C2.3 8 2.62 7.08 3.34 6.351c.721-.726 1.636-1.05 2.766-1.202C7.205 5 8.608 5 10.379 5h2.037c1.771 0 3.174 0 4.272.149c1.13.153 2.045.476 2.766 1.202c.493.497.8 1.087.994 1.78a.214.214 0 0 1-.214.269h-2.433c-2.734 0-5.143 2.177-5.143 5.1s2.41 5.1 5.144 5.1zM5.614 8.886a.725.725 0 0 0-.722.728c0 .403.323.729.722.729H9.47c.4 0 .723-.326.723-.729a.726.726 0 0 0-.723-.728z" clip-rule="evenodd"/><path fill="currentColor" d="m7.777 4.024l1.958-1.443a2.97 2.97 0 0 1 3.53 0l1.969 1.451C14.41 4 13.49 4 12.483 4h-2.17c-.922 0-1.769 0-2.536.024"/></svg> Bayar kontan saat ambil di kios
        </p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Footer -->
<?php include 'component/layout/footer.php'; ?>

<!-- Script -->
<script src="assets/js/catalog.js"></script>
<script>
function adjustQty(amount) {
    const qtyInput = document.getElementById('qty-input');
    const hiddenQtyInput = document.getElementById('hidden-qty-input');
    const maxStock = parseInt(qtyInput.max) || 1;
    let currentQty = parseInt(qtyInput.value) || 1;

    currentQty += amount;
    if (currentQty < 1) currentQty = 1;
    if (currentQty > maxStock) currentQty = maxStock;

    qtyInput.value = currentQty;
    if (hiddenQtyInput) {
        hiddenQtyInput.value = currentQty;
    }
}
</script>
</body>
</html>
