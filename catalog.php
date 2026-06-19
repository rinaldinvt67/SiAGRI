<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit;
}

$role    = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// ─── LAZY CHECK: batalkan pesanan expired ────────────────────────────────────
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

// ─── KIOSK: TAMBAH PRODUK ────────────────────────────────────────────────────
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
                $upload_dir = 'Assets/uploads/products/';
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

// ─── FARMER: TAMBAH KE CART ──────────────────────────────────────────────────
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

// ─── AMBIL DATA KATALOG ───────────────────────────────────────────────────────
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
    $extra_head = '<style>
        .cart-panel { transform: translateX(100%); transition: transform 0.3s ease; }
        .cart-panel.open { transform: translateX(0); }
        .overlay { opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .overlay.open { opacity: 1; pointer-events: all; }
    </style>';
    include 'component/layout/head.php';
    ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'catalog'; include 'component/layout/navbar.php'; ?>


<div class="md:hidden mx-4 -mt-0 bg-white rounded-xl shadow-lg px-4 py-3 z-30 relative">
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
        <a href="catalog.php<?= $search ? '?q='.$search : '' ?>"
           class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition
                  <?= !$filter_cat ? 'bg-siagri-dark text-white' : 'bg-gray-100 text-gray-600' ?>">
            Semua
        </a>
        <?php
        $cat_tmp = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
        while ($c = mysqli_fetch_assoc($cat_tmp)):
        ?>
        <a href="?cat=<?= $c['category_id'] ?><?= $search ? '&q='.$search : '' ?>"
           class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition
                  <?= $filter_cat == $c['category_id'] ? 'bg-siagri-dark text-white' : 'bg-gray-100 text-gray-600' ?>">
            <?= htmlspecialchars($c['category_name']) ?>
        </a>
        <?php endwhile; ?>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-6">

    <?php if ($role === 'Kiosk'): ?>
    <div class="mb-6 p-4 rounded-xl border-l-4 <?=
        $kiosk_info['kyc_status'] === 'verified'   ? 'bg-green-50 border-green-500 text-green-800' :
        ($kiosk_info['kyc_status'] === 'pending'   ? 'bg-yellow-50 border-yellow-500 text-yellow-800' :
        ($kiosk_info['kyc_status'] === 'rejected'  ? 'bg-red-50 border-red-500 text-red-800' :
                                                      'bg-blue-50 border-blue-500 text-blue-800')) ?>">
        <div class="flex items-start gap-3">
            <span class="text-2xl">
                <?= $kiosk_info['kyc_status'] === 'verified' ? '✅' :
                   ($kiosk_info['kyc_status'] === 'pending'  ? '⏳' :
                   ($kiosk_info['kyc_status'] === 'rejected' ? '❌' : 'ℹ️')) ?>
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
                <a href="kyc-upload.php"
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
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">✅ <?= $kiosk_success ?></div>
    <?php endif; ?>
    <?php if (isset($kiosk_error)): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm">❌ <?= $kiosk_error ?></div>
    <?php endif; ?>
    <?php if (isset($cart_success)): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">🛒 <?= $cart_success ?></div>
    <?php endif; ?>
    <?php if (isset($cart_error)): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm">❌ <?= $cart_error ?></div>
    <?php endif; ?>

    <div class="flex gap-6">
        <aside class="hidden md:block w-56 flex-shrink-0">
            <div class="bg-white rounded-xl shadow-sm p-4 sticky top-24">
                <h3 class="font-semibold text-siagri-dark mb-3">Kategori</h3>
                <a href="catalog.php<?= $search ? '?q='.$search : '' ?>"
                   class="block px-3 py-2 rounded-lg text-sm mb-1 transition
                          <?= !$filter_cat ? 'bg-siagri-dark text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
                    Semua Produk
                </a>
                <?php
                mysqli_data_seek($categories, 0);
                while ($c = mysqli_fetch_assoc($categories)):
                ?>
                <a href="?cat=<?= $c['category_id'] ?><?= $search ? '&q='.$search : '' ?>"
                   class="block px-3 py-2 rounded-lg text-sm mb-1 transition
                          <?= $filter_cat == $c['category_id'] ? 'bg-siagri-dark text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
                    <?= htmlspecialchars($c['category_name']) ?>
                </a>
                <?php endwhile; ?>
            </div>
        </aside>

        <main class="flex-1 min-w-0">
            <!-- ══ FORM TAMBAH PRODUK (Kiosk Only) ══════════ -->
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

            <!-- ══ TABEL PRODUK MILIK KIOSK ══════════════════ -->
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

            <!-- ══ KATALOG PRODUK (semua role lihat) ════════ -->
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
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">

                <!-- Gambar produk -->
                <div class="relative h-36 bg-gray-100 overflow-hidden">
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
                </div>

                <div class="p-3">
                    <!-- Kategori -->
                    <span class="text-xs text-siagri-green font-medium">
                        <?= htmlspecialchars($p['category_name']) ?>
                    </span>

                    <!-- Nama produk -->
                    <h3 class="font-semibold text-gray-800 text-sm mt-0.5 line-clamp-2 leading-tight">
                        <?= htmlspecialchars($p['product_name']) ?>
                    </h3>

                    <!-- Nama kios -->
                    <p class="text-xs text-gray-400 mt-1 truncate">
                        🏪 <?= htmlspecialchars($p['store_name']) ?>
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

                    <!-- Stok -->
                    <p class="text-xs text-gray-400 mt-1">
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
                    <?php elseif ($role === 'Kiosk'): ?>
                    <a href="https://wa.me/<?= $p['whatsapp_number'] ?>" target="_blank"
                       class="mt-3 block text-center text-xs text-siagri-dark border border-siagri-dark
                              rounded-lg py-2 hover:bg-siagri-dark hover:text-white transition">
                        Lihat Kios
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
            </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- CART PANEL -->
<?php if ($role === 'Farmer'): ?>

<!-- Overlay -->
<div id="overlay" class="overlay fixed inset-0 bg-black/40 z-40"
     onclick="toggleCart()"></div>

<!-- Panel -->
<div id="cart-panel" class="cart-panel fixed top-0 right-0 h-full w-full max-w-sm
                             bg-white shadow-2xl z-50 flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b bg-siagri-dark text-white">
        <h2 class="font-bold text-lg">🛒 Keranjang Saya</h2>
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
                    🏪 <?= htmlspecialchars($ci['store_name']) ?>
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
            <p class="text-4xl mb-3">🛒</p>
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
        <a href="checkout.php"
           class="block w-full text-center bg-siagri-gold text-siagri-dark font-bold py-3
                  rounded-xl hover:bg-yellow-400 transition text-sm">
            Lanjut ke Checkout →
        </a>
        <p class="text-xs text-gray-400 text-center mt-2">
            Bayar kontan saat ambil di kios
        </p>
    </div>
    <?php endif; ?>
</div>

<script src="assets/js/catalog.js"></script>
<?php include 'component/layout/footer.php'; ?>

</body>
</html>
