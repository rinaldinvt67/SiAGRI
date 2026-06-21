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

// LAZY CHECK: batalkan pesanan expired
$now = date('Y-m-d H:i:s');
$expired_orders = mysqli_query($conn,
    "SELECT o.order_id, oi.product_id, oi.quantity
     FROM orders o
     JOIN order_items oi ON o.order_id = oi.order_id
     WHERE o.status = 'pending' AND o.expired_at < '$now'"
);
while ($exp = mysqli_fetch_assoc($expired_orders)) {
    mysqli_query($conn,
        "UPDATE products SET stock = stock + {$exp['quantity']}
         WHERE product_id = {$exp['product_id']}"
    );
}
mysqli_query($conn,
    "UPDATE orders SET status='cancelled'
     WHERE status='pending' AND expired_at < '$now'"
);

// KIOSK: TAMBAH PRODUK
if ($role === 'Kiosk' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $kiosk_id = $_SESSION['kiosk_id'];

    // Cek KYC dulu sebelum boleh tambah produk
    $kyc = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT kyc_status FROM kiosk_profiles WHERE kiosk_id = $kiosk_id"
    ));

    if ($kyc['kyc_status'] !== 'verified') {
        $kiosk_error = "Akun kamu belum terverifikasi (KYC). Upload dokumen legalitas terlebih dahulu.";
    } else {

        if ($_POST['action'] === 'add_product') {
            $name        = mysqli_real_escape_string($conn, trim($_POST['product_name']));
            $category_id = (int)$_POST['category_id'];
            $price       = (float)$_POST['selling_price'];
            $stock       = (int)$_POST['stock'];
            $subsidized  = $_POST['is_subsidized'];
            $het         = (float)($_POST['het_price'] ?? 0);
            $desc        = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));

            // Upload gambar produk
            $image_path = null;
            if (!empty($_FILES['product_image']['name'])) {
                $upload_dir = '../../Assets/uploads/products/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext        = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $filename   = 'prod_' . time() . '_' . rand(100,999) . '.' . $ext;
                $allowed    = ['jpg','jpeg','png','webp'];
                if (in_array(strtolower($ext), $allowed)) {
                    move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $filename);
                    $image_path = $upload_dir . $filename;
                }
            }

            if (empty($name) || $price <= 0) {
                $kiosk_error = "Nama produk dan harga wajib diisi!";
            } else {
                $img_val = $image_path ? "'$image_path'" : "NULL";
                mysqli_query($conn,
                    "INSERT INTO products
                        (kiosk_id, category_id, product_name, description,
                         product_image, selling_price, stock, is_subsidized, het_price)
                     VALUES
                        ($kiosk_id, $category_id, '$name', '$desc',
                         $img_val, $price, $stock, '$subsidized', $het)"
                );
                $kiosk_success = "Produk berhasil ditambahkan!";
            }
        }

        if ($_POST['action'] === 'delete_product') {
            $prod_id = (int)$_POST['product_id'];
            mysqli_query($conn,
                "DELETE FROM products
                 WHERE product_id = $prod_id AND kiosk_id = $kiosk_id"
            );
            $kiosk_success = "Produk berhasil dihapus.";
        }

        if ($_POST['action'] === 'edit_product') {
            $prod_id = (int)$_POST['product_id'];
            $price   = (float)$_POST['selling_price'];
            $stock   = (int)$_POST['stock'];
            $het     = (float)($_POST['het_price'] ?? 0);
            mysqli_query($conn,
                "UPDATE products
                 SET selling_price=$price, stock=$stock, het_price=$het
                 WHERE product_id=$prod_id AND kiosk_id=$kiosk_id"
            );
            $kiosk_success = "Produk berhasil diupdate!";
        }
    }
}

// FARMER: TAMBAH KE CART
if ($role === 'Farmer' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add_to_cart') {
        $product_id = (int)$_POST['product_id'];
        $qty        = max(1, (int)($_POST['quantity'] ?? 1));

        // Cek stok
        $prod = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT stock FROM products WHERE product_id = $product_id"
        ));

        if (!$prod || $prod['stock'] < $qty) {
            $cart_error = "Stok tidak mencukupi!";
        } else {
            // Upsert cart — kalau sudah ada tambah qty, kalau belum insert baru
            $existing = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT cart_id, quantity FROM cart
                 WHERE user_id=$user_id AND product_id=$product_id"
            ));
            if ($existing) {
                $new_qty = $existing['quantity'] + $qty;
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
            $cart_success = "Produk ditambahkan ke keranjang!";
        }
    }

    if ($_POST['action'] === 'remove_from_cart') {
        $cart_id = (int)$_POST['cart_id'];
        mysqli_query($conn,
            "DELETE FROM cart WHERE cart_id=$cart_id AND user_id=$user_id"
        );
    }
}

// AMBIL DATA KATALOG
$filter_cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search     = isset($_GET['q'])   ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';

$where = "WHERE p.stock > 0";
if ($filter_cat) $where .= " AND p.category_id = $filter_cat";
if ($search)     $where .= " AND p.product_name LIKE '%$search%'";

// Farmer hanya lihat produk dari kios verified
if ($role === 'Farmer') {
    $where .= " AND kp.kyc_status = 'verified'";
}

$sql = "SELECT p.*, c.category_name, kp.store_name, kp.whatsapp_number,
               kp.kyc_status, kp.kiosk_id
        FROM products p
        JOIN categories c      ON p.category_id = c.category_id
        JOIN kiosk_profiles kp ON p.kiosk_id    = kp.kiosk_id
        $where
        ORDER BY p.created_at DESC";
$products = mysqli_query($conn, $sql);

// Ambil semua kategori untuk filter
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");

// Kiosk: ambil produk milik sendiri
if ($role === 'Kiosk') {
    $kiosk_id    = $_SESSION['kiosk_id'];
    $my_products = mysqli_query($conn,
        "SELECT p.*, c.category_name
         FROM products p
         JOIN categories c ON p.category_id = c.category_id
         WHERE p.kiosk_id = $kiosk_id
         ORDER BY p.created_at DESC"
    );
    $all_categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");

    // Cek KYC status kios ini
    $kiosk_info = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT kyc_status, kyc_note FROM kiosk_profiles WHERE kiosk_id = $kiosk_id"
    ));

    // Hitung pesanan pending
    $pesanan_pending = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM orders
         WHERE kiosk_id = $kiosk_id AND status = 'pending'"
    ))['total'] ?? 0;
}

// Farmer: hitung item di cart
$cart_count = 0;
if ($role === 'Farmer') {
    $cc = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT SUM(quantity) as total FROM cart WHERE user_id=$user_id"
    ));
    $cart_count = $cc['total'] ?? 0;

    // Ambil isi cart
    $cart_items = mysqli_query($conn,
        "SELECT c.*, p.product_name, p.selling_price, p.stock,
                kp.store_name, p.product_image
         FROM cart c
         JOIN products p        ON c.product_id = p.product_id
         JOIN kiosk_profiles kp ON p.kiosk_id   = kp.kiosk_id
         WHERE c.user_id = $user_id"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <?php
    $page_title = 'Katalog';
    $extra_head = '';
    include '../../component/layout/head.php';
    ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'catalog'; include '../../component/layout/navbar.php'; ?>


<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- ALERT KYC untuk Kiosk-->
    <?php if ($role === 'Kiosk'): ?>
    <div class="mb-6 p-4 rounded-xl border-l-4 <?=
        $kiosk_info['kyc_status'] === 'verified'   ? 'bg-green-50 border-green-500 text-green-800' :
        ($kiosk_info['kyc_status'] === 'pending'   ? 'bg-yellow-50 border-yellow-500 text-yellow-800' :
        ($kiosk_info['kyc_status'] === 'rejected'  ? 'bg-red-50 border-red-500 text-red-800' :
                                                      'bg-blue-50 border-blue-500 text-blue-800')) ?>">
        <div class="flex items-start gap-3">
            <span class="text-2xl">
                <?= $kiosk_info['kyc_status'] === 'verified' ? '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg>' :
                   ($kiosk_info['kyc_status'] === 'pending'  ? '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-yellow-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><defs><mask id="SVGnNgsclOC"><g fill="none"><path fill="#fff" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10"/><path fill="#000" fill-rule="evenodd" d="M12 7.25a.75.75 0 0 1 .75.75v3.69l2.28 2.28a.75.75 0 1 1-1.06 1.06l-2.5-2.5a.75.75 0 0 1-.22-.53V8a.75.75 0 0 1 .75-.75" clip-rule="evenodd"/></g></mask></defs><path fill="currentColor" d="M0 0h24v24H0z" mask="url(#SVGnNgsclOC)"/></svg>' :
                   ($kiosk_info['kyc_status'] === 'rejected' ? '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg>' : '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-blue-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-10 5.75a.75.75 0 0 0 .75-.75v-6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75M12 7a1 1 0 1 1 0 2a1 1 0 0 1 0-2" clip-rule="evenodd"/></svg>')) ?>
            </span>
            <div>
                <p class="font-semibold">
                    Status Verifikasi Kios:
                    <span class="uppercase"><?= $kiosk_info['kyc_status'] ?></span>
                </p>
                <?php if ($kiosk_info['kyc_status'] === 'unverified' || $kiosk_info['kyc_status'] === 'rejected'): ?>
                <p class="text-sm mt-1">
                    <?= $kiosk_info['kyc_status'] === 'rejected'
                        ? 'Dokumenmu ditolak. Alasan: <strong>'.htmlspecialchars($kiosk_info['kyc_note'] ?? '-').'</strong>. Silakan upload ulang.'
                        : 'Upload dokumen legalitas (NIB/SIUP/SPJB) untuk mulai berjualan.' ?>
                </p>
                <a href="../../pages/kiosk/kyc-upload.php"
                   class="inline-block mt-2 bg-siagri-dark text-white text-sm px-4 py-1.5 rounded-lg hover:bg-siagri-green transition">
                    Upload Dokumen KYC →
                </a>
                <?php elseif ($kiosk_info['kyc_status'] === 'pending'): ?>
                <p class="text-sm mt-1">Dokumenmu sedang ditinjau oleh Admin. Harap tunggu 1x24 jam.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notifikasi sukses/error -->
    <?php if (isset($kiosk_success)): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm"><svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> <?= $kiosk_success ?></div>
    <?php endif; ?>
    <?php if (isset($kiosk_error)): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm"><svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> <?= $kiosk_error ?></div>
    <?php endif; ?>
    <?php if (isset($cart_success)): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm"><svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M2.237 2.288a.75.75 0 1 0-.474 1.423l.265.089c.676.225 1.124.376 1.453.529c.312.145.447.262.533.382s.155.284.194.626c.041.361.042.833.042 1.546v2.672c0 1.367 0 2.47.117 3.337c.12.9.38 1.658.982 2.26c.601.602 1.36.86 2.26.981c.866.117 1.969.117 3.336.117H18a.75.75 0 0 0 0-1.5h-7c-1.435 0-2.436-.002-3.192-.103c-.733-.099-1.122-.28-1.399-.556c-.235-.235-.4-.551-.506-1.091h10.12c.959 0 1.438 0 1.814-.248s.565-.688.943-1.57l.428-1c.81-1.89 1.215-2.834.77-3.508S18.506 6 16.45 6H5.745a9 9 0 0 0-.047-.833c-.055-.485-.176-.93-.467-1.333c-.291-.404-.675-.66-1.117-.865c-.417-.194-.946-.37-1.572-.58zM7.5 18a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3m9 0a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3"/></svg> <?= $cart_success ?></div>
    <?php endif; ?>
    <?php if (isset($cart_error)): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm"><svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> <?= $cart_error ?></div>
    <?php endif; ?>

    <!-- CATEGORY & SEARCH SECTION -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 text-center w-full">
        <!-- Search bar -->
        <form method="GET" class="flex items-center bg-gray-50 border border-gray-200 rounded-full px-5 py-3 max-w-2xl mx-auto mb-6 gap-3 focus-within:border-siagri-green focus-within:bg-white transition">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
                   placeholder="Cari pupuk, benih, atau alat tani..."
                   class="bg-transparent text-gray-800 placeholder-gray-400 outline-none text-sm flex-1">
            <?php if (isset($filter_cat) && $filter_cat): ?>
                <input type="hidden" name="cat" value="<?= $filter_cat ?>">
            <?php endif; ?>
            <?php if ($search): ?>
                <a href="catalog.php<?= $filter_cat ? '?cat='.$filter_cat : '' ?>" class="text-xs text-gray-400 hover:text-gray-600 transition font-medium">Batal</a>
            <?php endif; ?>
        </form>

        <!-- Category Pills -->
        <div class="flex overflow-x-auto gap-2 justify-start md:justify-center pb-2 whitespace-nowrap scrollbar-hide">
            <a href="catalog.php<?= $search ? '?q='.$search : '' ?>"
               class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-medium transition
                      <?= !$filter_cat ? 'bg-siagri-dark text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                Semua Produk
            </a>
            <?php
            mysqli_data_seek($categories, 0);
            while ($c = mysqli_fetch_assoc($categories)):
            ?>
            <a href="?cat=<?= $c['category_id'] ?><?= $search ? '&q='.$search : '' ?>"
               class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-medium transition
                      <?= $filter_cat == $c['category_id'] ? 'bg-siagri-dark text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                <?= htmlspecialchars($c['category_name']) ?>
            </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="w-full">
            <!-- FORM TAMBAH PRODUK (Kiosk Only) -->
            <?php if ($role === 'Kiosk' && $kiosk_info['kyc_status'] === 'verified'): ?>
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-siagri-dark text-lg">Tambah Produk Baru</h2>
                    <button onclick="toggleCatalogAddForm()"
                            class="text-sm text-siagri-dark underline" id="toggle-btn">
                        + Tampilkan Form
                    </button>
                </div>

                <form id="add-form" method="POST" enctype="multipart/form-data"
                      class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="add_product">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk*</label>
                        <input type="text" name="product_name" required
                               placeholder="contoh: Urea 50kg"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:border-siagri-dark">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori*</label>
                        <select name="category_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                       focus:outline-none focus:border-siagri-dark">
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $all_categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
                            while ($c = mysqli_fetch_assoc($all_categories)):
                            ?>
                            <option value="<?= $c['category_id'] ?>">
                                <?= htmlspecialchars($c['category_name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (Rp)*</label>
                        <input type="number" name="selling_price" min="0" step="500" required
                               placeholder="contoh: 87000"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:border-siagri-dark">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok*</label>
                        <input type="number" name="stock" min="0" required
                               placeholder="contoh: 20"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:border-siagri-dark">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produk Bersubsidi?</label>
                        <select name="is_subsidized"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                       focus:outline-none focus:border-siagri-dark">
                            <option value="No">Tidak</option>
                            <option value="Yes">Ya (ada HET)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            HET (Harga Eceran Tertinggi)
                        </label>
                        <input type="number" name="het_price" min="0" step="500" value="0"
                               placeholder="Isi 0 jika tidak ada HET"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:border-siagri-dark">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2"
                                  placeholder="Jelaskan produkmu..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                         focus:outline-none focus:border-siagri-dark resize-none"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                        <input type="file" name="product_image" accept="image/*"
                               class="w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                      file:bg-siagri-dark file:text-white file:cursor-pointer">
                    </div>

                    <div class="md:col-span-2 flex gap-3">
                        <button type="submit"
                                class="bg-siagri-dark text-white px-6 py-2 rounded-lg text-sm
                                       hover:bg-siagri-green transition font-medium">
                            + Tambah Produk
                        </button>
                        <button type="button" onclick="toggleCatalogAddForm()"
                                class="border border-gray-300 text-gray-600 px-6 py-2 rounded-lg
                                       text-sm hover:bg-gray-50 transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABEL PRODUK MILIK KIOSK -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="font-bold text-siagri-dark text-lg mb-4">Produk Saya</h2>
                <?php if (mysqli_num_rows($my_products) === 0): ?>
                <p class="text-gray-500 text-sm text-center py-6">
                    Belum ada produk. Tambah produk pertamamu di atas!
                </p>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-siagri-dark text-white">
                                <th class="px-4 py-3 text-left rounded-tl-lg">Produk</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-right">Harga</th>
                                <th class="px-4 py-3 text-right">HET</th>
                                <th class="px-4 py-3 text-center">Stok</th>
                                <th class="px-4 py-3 text-center rounded-tr-lg">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($p = mysqli_fetch_assoc($my_products)): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">
                                <?= htmlspecialchars($p['product_name']) ?>
                                <?php if ($p['is_subsidized'] === 'Yes'): ?>
                                <span class="ml-1 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                    Subsidi
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500"><?= $p['category_name'] ?></td>
                            <td class="px-4 py-3 text-right font-semibold">
                                Rp <?= number_format($p['selling_price'], 0, ',', '.') ?>
                                <?php if ($p['het_price'] > 0 && $p['selling_price'] > $p['het_price']): ?>
                                <br><span class="text-xs text-red-500 font-normal">⚠️ Melanggar HET!</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500 text-xs">
                                <?= $p['het_price'] > 0 ? 'Rp '.number_format($p['het_price'],0,',','.') : '-' ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-semibold <?= $p['stock'] <= 5 ? 'text-red-500' : 'text-gray-700' ?>">
                                    <?= $p['stock'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" class="inline"
                                      onsubmit="return confirm('Hapus produk ini?')">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                    <button type="submit"
                                            class="text-red-500 hover:text-red-700 text-xs font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- KATALOG PRODUK (semua role lihat) -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-siagri-dark text-lg">
                    <?= $role === 'Kiosk' ? 'Semua Produk di Marketplace' : 'Katalog Produk' ?>
                    <?php if ($search): ?>
                    <span class="text-sm font-normal text-gray-500">
                        — hasil pencarian "<em><?= htmlspecialchars($search) ?></em>"
                    </span>
                    <?php endif; ?>
                </h2>
                <span class="text-sm text-gray-500">
                    <?= mysqli_num_rows($products) ?> produk ditemukan
                </span>
            </div>

            <?php if (mysqli_num_rows($products) === 0): ?>
            <div class="text-center py-16 text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="font-medium">Belum ada produk tersedia</p>
                <p class="text-sm">Coba ubah filter atau kata kunci pencarianmu</p>
            </div>
            <?php else: ?>

            <!-- Grid produk: 2 kolom mobile, 3 tablet, 4 desktop -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group flex flex-col h-full">
                <!-- Gambar produk -->
                <a href="../../pages/farmer/product-detail.php?id=<?= $p['product_id'] ?>" class="block relative h-36 bg-gray-100 overflow-hidden">
                    <?php if ($p['product_image']): ?>
                    <img src="<?= htmlspecialchars($p['product_image']) ?>"
                         alt="<?= htmlspecialchars($p['product_name']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <?php endif; ?>

                    <!-- Badge subsidi + badge kios verified -->
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <?php if ($p['is_subsidized'] === 'Yes'): ?>
                        <span class="bg-green-500 text-white text-xs px-2 py-0.5 rounded-full font-semibold">
                            Subsidi
                        </span>
                        <?php endif; ?>
                        <?php if ($p['kyc_status'] === 'verified'): ?>
                        <span class="bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full font-semibold">
                            Kios Resmi
                        </span>
                        <?php endif; ?>
                    </div>
                </a>

                <div class="p-3 flex flex-col flex-grow">
                    <!-- Kategori -->
                    <span class="text-xs text-siagri-green font-medium">
                        <?= htmlspecialchars($p['category_name']) ?>
                    </span>

                    <!-- Nama produk -->
                    <h3 class="font-semibold text-gray-800 text-sm mt-0.5 line-clamp-2 leading-tight">
                        <a href="../../pages/farmer/product-detail.php?id=<?= $p['product_id'] ?>" class="hover:text-siagri-green transition">
                            <?= htmlspecialchars($p['product_name']) ?>
                        </a>
                    </h3>

                    <!-- Nama kios -->
                    <p class="text-xs text-gray-400 mt-1 truncate">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"/></svg> <?= htmlspecialchars($p['store_name']) ?>
                    </p>

                    <!-- Harga -->
                    <p class="text-siagri-dark font-bold text-base mt-2">
                        Rp <?= number_format($p['selling_price'], 0, ',', '.') ?>
                    </p>

                    <!-- HET Warning -->
                    <?php if ($p['het_price'] > 0): ?>
                    <?php if ($p['selling_price'] > $p['het_price']): ?>
                    <p class="text-xs text-red-500 font-medium">
                        Melebihi HET (maks Rp <?= number_format($p['het_price'],0,',','.') ?>)
                    </p>
                    <?php else: ?>
                    <p class="text-xs text-green-600">
                        Sesuai HET
                    </p>
                    <?php endif; ?>
                    <?php else: ?>
                    <p class="text-xs invisible">
                        Sesuai HET
                    </p>
                    <?php endif; ?>

                    <!-- Stok & Tombol aksi Container -->
                    <div class="mt-auto pt-2">
                        <!-- Stok -->
                        <p class="text-xs text-gray-400">
                            Stok: <span class="font-medium <?= $p['stock'] <= 5 ? 'text-red-500' : 'text-gray-600' ?>">
                                <?= $p['stock'] ?>
                            </span>
                        </p>

                        <!-- Tombol aksi -->
                        <?php if ($role === 'Farmer'): ?>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit"
                                    class="w-full bg-siagri-dark text-white text-sm py-2 rounded-lg
                                           hover:bg-siagri-green transition font-medium">
                                + Keranjang
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </main>
</div>

<!-- CART PANEL (Farmer Only) -->
<?php if ($role === 'Farmer'): ?>
<!-- Overlay -->
<div id="overlay" class="overlay fixed inset-0 bg-black/40 z-40"
     onclick="toggleCart()"></div>
<!-- Panel -->
<div id="cart-panel" class="cart-panel fixed top-0 right-0 h-full w-full max-w-sm
                             bg-white shadow-2xl z-50 flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b bg-siagri-dark text-white">
        <h2 class="font-bold text-lg">Keranjang Saya</h2>
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
           class="group flex items-center justify-center gap-2 w-full bg-siagri-gold text-siagri-dark font-bold py-3
                  rounded-xl hover:bg-yellow-400 transition text-sm shadow-md">
            <span>Lanjut ke Checkout</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current transform group-hover:translate-x-1 transition-transform duration-200">
                <path d="M4 11h12.17l-5.59-5.59L12 4l8 8l-8 8l-1.41-1.41L16.17 13H4z"></path>
            </svg>
        </a>
        <p class="text-xs text-gray-400 text-center mt-2">
            <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M20.41 9.86a3 3 0 0 0-.175-.003H17.8c-1.992 0-3.698 1.581-3.698 3.643s1.706 3.643 3.699 3.643h2.433q.092.001.175-.004a1.7 1.7 0 0 0 1.586-1.581c.004-.059.004-.122.004-.18v-3.756c0-.058 0-.121-.004-.18a1.7 1.7 0 0 0-1.585-1.581m-2.823 4.611c.513 0 .93-.434.93-.971s-.417-.971-.93-.971s-.929.434-.929.971s.416.971.93.971" clip-rule="evenodd"/><path fill="currentColor" fill-rule="evenodd" d="M20.234 18.6a.214.214 0 0 1 .214.27c-.194.692-.501 1.282-.994 1.778c-.721.727-1.636 1.05-2.766 1.203c-1.098.149-2.5.149-4.272.149h-2.037c-1.771 0-3.174 0-4.272-.149c-1.13-.153-2.045-.476-2.766-1.203C2.62 19.923 2.3 19 2.148 17.862C2 16.754 2 15.34 2 13.555v-.11c0-1.785 0-3.2.148-4.306C2.3 8 2.62 7.08 3.34 6.351c.721-.726 1.636-1.05 2.766-1.202C7.205 5 8.608 5 10.379 5h2.037c1.771 0 3.174 0 4.272.149c1.13.153 2.045.476 2.766 1.202c.493.497.8 1.087.994 1.78a.214.214 0 0 1-.214.269h-2.433c-2.734 0-5.143 2.177-5.143 5.1s2.41 5.1 5.144 5.1zM5.614 8.886a.725.725 0 0 0-.722.728c0 .403.323.729.722.729H9.47c.4 0 .723-.326.723-.729a.726.726 0 0 0-.723-.728z" clip-rule="evenodd"/><path fill="currentColor" d="m7.777 4.024l1.958-1.443a2.97 2.97 0 0 1 3.53 0l1.969 1.451C14.41 4 13.49 4 12.483 4h-2.17c-.922 0-1.769 0-2.536.024"/></svg> Bayar kontan saat ambil di kios
        </p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script src="../../assets/js/catalog.js"></script>
<?php include '../../component/layout/footer.php'; ?>

</body>
</html>
