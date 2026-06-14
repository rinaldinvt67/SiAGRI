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