<?php
$path_prefix = '';

session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login-page.php");
    exit;
}

$success = "";
$error   = "";

// AKSI KYC
if (isset($_GET['kyc_approve'])) {
    $kiosk_id = (int)$_GET['kyc_approve'];
    $now      = date('Y-m-d H:i:s');
    mysqli_query($conn,
        "UPDATE kiosk_profiles
         SET kyc_status = 'verified', verified_at = '$now', kyc_note = NULL
         WHERE kiosk_id = $kiosk_id"
    );
    header("Location: admin-dashboard.php?tab=kyc&success=approved");
    exit;
}

if (isset($_GET['kyc_reject'])) {
    $kiosk_id = (int)$_GET['kyc_reject'];
    $note     = mysqli_real_escape_string($conn, trim($_POST['kyc_note'] ?? 'Dokumen tidak valid'));
    mysqli_query($conn,
        "UPDATE kiosk_profiles
         SET kyc_status = 'rejected', kyc_note = '$note', verified_at = NULL
         WHERE kiosk_id = $kiosk_id"
    );
    header("Location: admin-dashboard.php?tab=kyc&success=rejected");
    exit;
}

// AKSI KATEGORI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add_category') {
        $name = mysqli_real_escape_string($conn, trim($_POST['category_name']));
        if (empty($name)) {
            $error = "Nama kategori tidak boleh kosong!";
        } else {
            $cek = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT category_id FROM categories WHERE category_name = '$name'"
            ));
            if ($cek) {
                $error = "Kategori '$name' sudah ada!";
            } else {
                mysqli_query($conn,
                    "INSERT INTO categories (category_name) VALUES ('$name')"
                );
                header("Location: admin-dashboard.php?tab=categories&success=cat_added");
                exit;
            }
        }
    }

    if ($_POST['action'] === 'delete_category') {
        $cat_id = (int)$_POST['category_id'];
        // Cek apakah ada produk yang pakai kategori ini
        $used = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) as total FROM products WHERE category_id = $cat_id"
        ));
        if ($used['total'] > 0) {
            $error = "Kategori tidak bisa dihapus karena masih digunakan oleh {$used['total']} produk!";
        } else {
            mysqli_query($conn, "DELETE FROM categories WHERE category_id = $cat_id");
            header("Location: admin-dashboard.php?tab=categories&success=cat_deleted");
            exit;
        }
    }

    if ($_POST['action'] === 'delete_user') {
        $del_user_id = (int)$_POST['user_id'];
        // Jangan hapus diri sendiri
        if ($del_user_id === (int)$_SESSION['user_id']) {
            $error = "Tidak bisa menghapus akun sendiri!";
        } else {
            mysqli_query($conn, "DELETE FROM users WHERE user_id = $del_user_id");
            header("Location: admin-dashboard.php?tab=users&success=user_deleted");
            exit;
        }
    }

    if ($_POST['action'] === 'add_expert') {
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fullname = mysqli_real_escape_string($conn, trim($_POST['full_name']));
        $spec     = mysqli_real_escape_string($conn, trim($_POST['specialization']));
        $wa       = mysqli_real_escape_string($conn, trim($_POST['whatsapp_number'] ?? ''));

        $cek = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT user_id FROM users WHERE username = '$username' OR email = '$email'"
        ));
        if ($cek) {
            $error = "Username atau email sudah digunakan!";
        } else {
            mysqli_query($conn,
                "INSERT INTO users (username, email, password, role)
                 VALUES ('$username', '$email', '$password', 'Expert')"
            );
            $new_id = mysqli_insert_id($conn);
            mysqli_query($conn,
                "INSERT INTO expert_profiles (user_id, full_name, specialization, whatsapp_number)
                 VALUES ($new_id, '$fullname', '$spec', '$wa')"
            );
            header("Location: admin-dashboard.php?tab=users&success=expert_added");
            exit;
        }
    }
}

// Pesan sukses
if (isset($_GET['success'])) {
    $msgs = [
        'approved'     => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> KYC disetujui! Kios sekarang bisa berjualan.',
        'rejected'     => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> KYC ditolak. Kios sudah diberitahu.',
        'cat_added'    => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Kategori berhasil ditambahkan.',
        'cat_deleted'  => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Kategori berhasil dihapus.',
        'user_deleted' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Akun berhasil dihapus.',
        'expert_added' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Akun Expert berhasil dibuat.',
    ];
    $success = $msgs[$_GET['success']] ?? '';
}

$active_tab = $_GET['tab'] ?? 'overview';

// AMBIL DATA
// Statistik overview
$total_users   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users"))['t'];
$total_farmers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='Farmer'"))['t'];
$total_kiosks  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='Kiosk'"))['t'];
$total_orders  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM orders"))['t'];
$total_products= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM products"))['t'];
$kyc_pending   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kiosk_profiles WHERE kyc_status='pending'"))['t'];

// Tambahan statistik role untuk doughnut chart
$total_experts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='Expert'"))['t'] ?? 0;
$total_admins  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='Admin'"))['t'] ?? 0;

// Query transaksi 7 hari terakhir untuk line chart
$days_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $formatted_date = date('d M', strtotime($date));
    $days_data[$date] = [
        'label' => $formatted_date,
        'count' => 0,
        'revenue' => 0
    ];
}

$order_stats_query = mysqli_query($conn, 
    "SELECT DATE(created_at) as order_date, COUNT(*) as order_count, SUM(total_price) as total_revenue 
     FROM orders 
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(created_at)"
);

if ($order_stats_query) {
    while ($row = mysqli_fetch_assoc($order_stats_query)) {
        $od = $row['order_date'];
        if (isset($days_data[$od])) {
            $days_data[$od]['count'] = (int)$row['order_count'];
            $days_data[$od]['revenue'] = (float)$row['total_revenue'];
        }
    }
}

$chart_labels = [];
$chart_counts = [];
$chart_revenues = [];
foreach ($days_data as $day) {
    $chart_labels[] = $day['label'];
    $chart_counts[] = $day['count'];
    $chart_revenues[] = $day['revenue'];
}

// Data tambahan untuk list ringkasan di tab Overview
$overview_orders = mysqli_query($conn,
    "SELECT o.*, u.username, kp.store_name
     FROM orders o
     JOIN users u           ON o.user_id  = u.user_id
     JOIN kiosk_profiles kp ON o.kiosk_id = kp.kiosk_id
     ORDER BY o.created_at DESC
     LIMIT 5"
);

$overview_kyc = mysqli_query($conn,
    "SELECT kp.*, u.username, u.email
     FROM kiosk_profiles kp
     JOIN users u ON kp.user_id = u.user_id
     WHERE kp.kyc_status = 'pending'
     ORDER BY kp.kiosk_id DESC
     LIMIT 5"
);

// KYC list
$kyc_list = mysqli_query($conn,
    "SELECT kp.*, u.username, u.email
     FROM kiosk_profiles kp
     JOIN users u ON kp.user_id = u.user_id
     ORDER BY
         CASE kyc_status
             WHEN 'pending'    THEN 1
             WHEN 'unverified' THEN 2
             WHEN 'rejected'   THEN 3
             WHEN 'verified'   THEN 4
         END,
         kp.kiosk_id DESC"
);

// Users list
$users_list = mysqli_query($conn,
    "SELECT u.*, kp.store_name, kp.kyc_status
     FROM users u
     LEFT JOIN kiosk_profiles kp ON u.user_id = kp.user_id
     ORDER BY u.role, u.user_id DESC"
);

// Categories list
$categories = mysqli_query($conn,
    "SELECT c.*, COUNT(p.product_id) as product_count
     FROM categories c
     LEFT JOIN products p ON c.category_id = p.category_id
     GROUP BY c.category_id
     ORDER BY c.category_name"
);

// Recent orders
$recent_orders = mysqli_query($conn,
    "SELECT o.*, u.username, kp.store_name
     FROM orders o
     JOIN users u           ON o.user_id  = u.user_id
     JOIN kiosk_profiles kp ON o.kiosk_id = kp.kiosk_id
     ORDER BY o.created_at DESC
     LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php
    $page_title = 'Admin Dashboard';
    $extra_head = '';
    include 'component/layout/head.php';
    ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'admin-dashboard'; include 'component/layout/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-5 py-8">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-siagri-dark">Pusat Kendali Admin</h1>
        <p class="text-gray-400 text-sm mt-0.5">Kelola platform SiAGRI</p>
    </div>

    <!-- Notifikasi -->
    <?php if ($success): ?>
    <div class="mb-5 p-3 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        <?= $success ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-5 p-3 bg-red-50 text-red-800 rounded-xl border border-red-200 text-sm">
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="flex gap-1 mb-8 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100 w-fit">
        <?php
        $tabs = [
            'overview'   => ['icon'=>'<svg class="inline-block w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M17.293 2.293C17 2.586 17 3.057 17 4v13c0 .943 0 1.414.293 1.707S18.057 19 19 19s1.414 0 1.707-.293S21 17.943 21 17V4c0-.943 0-1.414-.293-1.707S19.943 2 19 2s-1.414 0-1.707.293M10 7c0-.943 0-1.414.293-1.707S11.057 5 12 5s1.414 0 1.707.293S14 6.057 14 7v10c0 .943 0 1.414-.293 1.707S12.943 19 12 19s-1.414 0-1.707-.293S10 17.943 10 17zM3.293 9.293C3 9.586 3 10.057 3 11v6c0 .943 0 1.414.293 1.707S4.057 19 5 19s1.414 0 1.707-.293S7 17.943 7 17v-6c0-.943 0-1.414-.293-1.707S5.943 9 5 9s-1.414 0-1.707.293M3 21.25a.75.75 0 0 0 0 1.5h18a.75.75 0 0 0 0-1.5z"/></svg>', 'label'=>'Overview'],
            'kyc'        => ['icon'=>'<svg class="inline-block w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14"/></svg>', 'label'=>'Verifikasi KYC', 'badge'=>$kyc_pending],
            'users'      => ['icon'=>'<svg class="inline-block w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="9.001" cy="6" r="4" fill="currentColor"/><ellipse cx="9.001" cy="17.001" fill="currentColor" rx="7" ry="4"/><path fill="currentColor" d="M21 17c0 1.657-2.036 3-4.521 3c.732-.8 1.236-1.805 1.236-2.998c0-1.195-.505-2.2-1.239-3.001C18.962 14 21 15.344 21 17M18 6a3 3 0 0 1-4.029 2.82A5.7 5.7 0 0 0 14.714 6c0-1.025-.27-1.987-.742-2.819A3 3 0 0 1 18 6.001"/></svg>', 'label'=>'Pengguna'],
            'categories' => ['icon'=>'<svg class="inline-block w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M2.123 12.816c.287 1.003 1.06 1.775 2.605 3.32l1.83 1.83C9.248 20.657 10.592 22 12.262 22c1.671 0 3.015-1.344 5.704-4.033c2.69-2.69 4.034-4.034 4.034-5.705c0-1.67-1.344-3.015-4.033-5.704l-1.83-1.83c-1.546-1.545-2.318-2.318-3.321-2.605c-1.003-.288-2.068-.042-4.197.45l-1.228.283c-1.792.413-2.688.62-3.302 1.233S3.27 5.6 2.856 7.391l-.284 1.228c-.491 2.13-.737 3.194-.45 4.197m8-5.545a2.017 2.017 0 1 1-2.852 2.852a2.017 2.017 0 0 1 2.852-2.852m8.928 4.78l-6.979 6.98a.75.75 0 0 1-1.06-1.061l6.978-6.98a.75.75 0 0 1 1.061 1.061" clip-rule="evenodd"/></svg>', 'label'=>'Kategori'],
            'orders'     => ['icon'=>'<svg class="inline-block w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M9.5 2A1.5 1.5 0 0 0 8 3.5v1A1.5 1.5 0 0 0 9.5 6h5A1.5 1.5 0 0 0 16 4.5v-1A1.5 1.5 0 0 0 14.5 2z"/><path fill="currentColor" fill-rule="evenodd" d="M6.5 4.037c-1.258.07-2.052.27-2.621.84C3 5.756 3 7.17 3 9.998v6c0 2.829 0 4.243.879 5.122c.878.878 2.293.878 5.121.878h6c2.828 0 4.243 0 5.121-.878c.879-.88.879-2.293.879-5.122v-6c0-2.828 0-4.242-.879-5.121c-.569-.57-1.363-.77-2.621-.84V4.5a3 3 0 0 1-3 3h-5a3 3 0 0 1-3-3zM7 9.75a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5zm3.5 0a.75.75 0 0 0 0 1.5H17a.75.75 0 0 0 0-1.5zM7 13.25a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5zm3.5 0a.75.75 0 0 0 0 1.5H17a.75.75 0 0 0 0-1.5zM7 16.75a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5zm3.5 0a.75.75 0 0 0 0 1.5H17a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/></svg>', 'label'=>'Pesanan'],
        ];
        foreach ($tabs as $key => $tab):
        ?>
        <a href="?tab=<?= $key ?>"
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium
                  transition relative
                  <?= $active_tab === $key
                      ? 'bg-siagri-dark text-white'
                      : 'text-gray-500 hover:text-siagri-dark' ?>">
            <span><?= $tab['icon'] ?></span>
            <span><?= $tab['label'] ?></span>
            <?php if (!empty($tab['badge']) && $tab['badge'] > 0): ?>
            <span class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                <?= $tab['badge'] ?>
            </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- TAB: OVERVIEW -->
    <?php if ($active_tab === 'overview'): ?>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        <?php
        $stats = [
            ['val'=>$total_users,    'label'=>'Total Pengguna',   'color'=>'text-siagri-dark'],
            ['val'=>$total_farmers,  'label'=>'Petani',           'color'=>'text-green-600'],
            ['val'=>$total_kiosks,   'label'=>'Mitra Kios',       'color'=>'text-blue-600'],
            ['val'=>$total_products, 'label'=>'Total Produk',     'color'=>'text-purple-600'],
            ['val'=>$total_orders,   'label'=>'Total Pesanan',    'color'=>'text-orange-600'],
        ];
        foreach ($stats as $s):
        ?>
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 text-center">
            <p class="text-3xl font-bold <?= $s['color'] ?>"><?= $s['val'] ?></p>
            <p class="text-gray-400 text-xs mt-1"><?= $s['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- KYC Alert -->
    <?php if ($kyc_pending > 0): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 mb-8 flex items-center gap-4">
        <span class="text-3.5xl"><svg class="inline-block w-8 h-8 text-yellow-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><defs><mask id="SVGnNgsclOC"><g fill="none"><path fill="#fff" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10"/><path fill="#000" fill-rule="evenodd" d="M12 7.25a.75.75 0 0 1 .75.75v3.69l2.28 2.28a.75.75 0 1 1-1.06 1.06l-2.5-2.5a.75.75 0 0 1-.22-.53V8a.75.75 0 0 1 .75-.75" clip-rule="evenodd"/></g></mask></defs><path fill="currentColor" d="M0 0h24v24H0z" mask="url(#SVGnNgsclOC)"/></svg></span>
        <div class="flex-1">
            <p class="font-semibold text-yellow-800">
                Ada <?= $kyc_pending ?> pengajuan KYC menunggu ditinjau
            </p>
            <p class="text-sm text-yellow-600 mt-0.5">
                Kios tidak bisa berjualan sampai dokumennya diverifikasi oleh Admin.
            </p>
        </div>
        <a href="?tab=kyc"
           class="bg-siagri-dark text-white px-4 py-2 rounded-xl text-sm font-semibold
                  hover:bg-siagri-green transition flex-shrink-0 inline-flex items-center gap-2">
            Tinjau Sekarang
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current">
                <path d="M4 11h12.17l-5.59-5.59L12 4l8 8l-8 8l-1.41-1.41L16.17 13H4z"></path>
            </svg>
        </a>
    </div>
    <?php endif; ?>

    <!-- Section Grafik Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Grafik Tren Pesanan -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-siagri-dark text-base">Tren Pesanan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Jumlah pesanan masuk dalam 7 hari terakhir</p>
                </div>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>

        <!-- Grafik Komposisi Pengguna -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h3 class="font-bold text-siagri-dark text-base mb-1">Komposisi Pengguna</h3>
            <p class="text-xs text-gray-400 mb-4">Persentase berdasarkan role pengguna</p>
            <div class="relative h-56 flex items-center justify-center">
                <canvas id="usersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Section Ringkasan Aktivitas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Ringkasan Pesanan Terbaru -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-siagri-dark text-sm">Pesanan Terbaru</h3>
                    <a href="?tab=orders" class="text-xs font-semibold text-siagri-green hover:underline flex items-center gap-1">
                        Lihat Semua
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-3.5 h-3.5 fill-current">
                            <path d="M4 11h12.17l-5.59-5.59L12 4l8 8l-8 8l-1.41-1.41L16.17 13H4z"></path>
                        </svg>
                    </a>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php if (mysqli_num_rows($overview_orders) === 0): ?>
                        <div class="p-8 text-center text-gray-400 text-sm">Belum ada pesanan terbaru.</div>
                    <?php else: ?>
                        <?php while ($o = mysqli_fetch_assoc($overview_orders)): 
                            $sc = [
                                'pending'   => 'bg-yellow-100 text-yellow-700',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ][$o['status']] ?? 'bg-gray-100 text-gray-600';
                        ?>
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition">
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">#<?= $o['order_id'] ?> - <?= htmlspecialchars($o['username']) ?></p>
                                <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($o['store_name']) ?> &nbsp;·&nbsp; <?= date('d M Y, H:i', strtotime($o['created_at'])) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800 text-sm">Rp <?= number_format($o['total_price'], 0, ',', '.') ?></p>
                                <span class="inline-block <?= $sc ?> text-[10px] px-2 py-0.5 rounded-full font-semibold mt-1">
                                    <?= ucfirst($o['status']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ringkasan Pengajuan KYC -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-siagri-dark text-sm">Pengajuan KYC Tertunda</h3>
                    <a href="?tab=kyc" class="text-xs font-semibold text-siagri-green hover:underline flex items-center gap-1">
                        Lihat Semua
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-3.5 h-3.5 fill-current">
                            <path d="M4 11h12.17l-5.59-5.59L12 4l8 8l-8 8l-1.41-1.41L16.17 13H4z"></path>
                        </svg>
                    </a>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php if (mysqli_num_rows($overview_kyc) === 0): ?>
                        <div class="p-8 text-center text-gray-400 text-sm">Tidak ada pengajuan KYC tertunda.</div>
                    <?php else: ?>
                        <?php while ($k = mysqli_fetch_assoc($overview_kyc)): ?>
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition">
                            <div>
                                <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($k['store_name']) ?></p>
                                <p class="text-xs text-gray-400 mt-0.5">Pemilik: <?= htmlspecialchars($k['username']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($k['email']) ?></p>
                            </div>
                            <div class="pr-5">
                                <a href="?tab=kyc" class="bg-siagri-dark text-white text-[11px] px-3 py-1.5 rounded-lg hover:bg-siagri-green font-semibold transition">
                                    Tinjau
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <!-- TAB: KYC -->
    <?php if ($active_tab === 'kyc'): ?>

    <div class="space-y-4">
        <?php if (mysqli_num_rows($kyc_list) === 0): ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-gray-100">
            <p class="text-4xl mb-3"><svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg></p>
            <p class="font-medium text-gray-600">Tidak ada pengajuan KYC</p>
        </div>
        <?php else: ?>
        <?php while ($kiosk = mysqli_fetch_assoc($kyc_list)):
            $status_cfg = [
                'unverified' => ['cls'=>'bg-gray-100 text-gray-600',    'label'=>'Belum Upload'],
                'pending'    => ['cls'=>'bg-yellow-100 text-yellow-700', 'label'=>'Menunggu Tinjauan'],
                'verified'   => ['cls'=>'bg-green-100 text-green-700',  'label'=>'Terverifikasi'],
                'rejected'   => ['cls'=>'bg-red-100 text-red-700',      'label'=>'Ditolak'],
            ];
            $sc = $status_cfg[$kiosk['kyc_status']];
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="font-bold text-siagri-dark">
                            <?= htmlspecialchars($kiosk['store_name']) ?>
                        </h3>
                        <span class="<?= $sc['cls'] ?> text-xs px-2.5 py-1 rounded-full font-semibold">
                            <?= $sc['label'] ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><circle cx="12" cy="6" r="4" fill="currentColor"/><path fill="currentColor" d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5"/></svg> <?= htmlspecialchars($kiosk['username']) ?>
                        &nbsp;·&nbsp;
                        ✉️ <?= htmlspecialchars($kiosk['email']) ?>
                    </p>
                    <p class="text-sm text-gray-500 mt-0.5">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M12 2c-4.418 0-8 4.003-8 8.5c0 4.462 2.553 9.312 6.537 11.174a3.45 3.45 0 0 0 2.926 0C17.447 19.812 20 14.962 20 10.5C20 6.003 16.418 2 12 2m0 10a2 2 0 1 0 0-4a2 2 0 0 0 0 4" clip-rule="evenodd"/></svg> <?= htmlspecialchars($kiosk['full_address']) ?>
                    </p>
                    <?php if ($kiosk['kyc_note']): ?>
                    <p class="text-sm text-red-500 mt-1">
                        Catatan: <?= htmlspecialchars($kiosk['kyc_note']) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($kiosk['verified_at']): ?>
                    <p class="text-xs text-gray-400 mt-1">
                        Diverifikasi: <?= date('d M Y H:i', strtotime($kiosk['verified_at'])) ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Dokumen + aksi -->
                <div class="flex-shrink-0 text-right space-y-2">
                    <?php if ($kiosk['kyc_doc_path']): ?>
                    <a href="<?= htmlspecialchars($kiosk['kyc_doc_path']) ?>"
                       target="_blank"
                       class="inline-block bg-blue-50 text-blue-600 text-xs px-3 py-1.5
                              rounded-lg hover:bg-blue-100 transition font-medium">
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M4.172 3.172C3 4.343 3 6.229 3 10v4c0 3.771 0 5.657 1.172 6.828S7.229 22 11 22h2c3.771 0 5.657 0 6.828-1.172S21 17.771 21 14v-4c0-3.771 0-5.657-1.172-6.828S16.771 2 13 2h-2C7.229 2 5.343 2 4.172 3.172M8 9.25a.75.75 0 0 0 0 1.5h8a.75.75 0 0 0 0-1.5zm0 4a.75.75 0 0 0 0 1.5h5a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/></svg> Lihat Dokumen
                    </a>
                    <?php else: ?>
                    <span class="text-xs text-gray-400">Belum ada dokumen</span>
                    <?php endif; ?>

                    <?php if ($kiosk['kyc_status'] === 'pending'): ?>
                    <div class="flex gap-2 justify-end">
                        <a href="?kyc_approve=<?= $kiosk['kiosk_id'] ?>"
                           onclick="return confirm('Setujui KYC kios ini?')"
                           class="bg-green-500 text-white text-xs px-4 py-1.5 rounded-lg
                                  hover:bg-green-600 transition font-semibold inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 fill-current text-white" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Setujui
                        </a>
                        <button onclick="openReject(<?= $kiosk['kiosk_id'] ?>)"
                                class="bg-red-50 text-red-600 text-xs px-4 py-1.5 rounded-lg
                                       hover:bg-red-100 transition font-semibold border border-red-200 inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 fill-current text-red-500" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> Tolak
                        </button>
                    </div>
                    <?php elseif ($kiosk['kyc_status'] === 'verified'): ?>
                    <a href="?kyc_reject=<?= $kiosk['kiosk_id'] ?>"
                       onclick="return confirm('Cabut verifikasi kios ini?')"
                       class="inline-block text-xs text-red-400 hover:text-red-600 transition">
                        Cabut Verifikasi
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <?php endif; ?>

    <!-- TAB: PENGGUNA -->
    <?php if ($active_tab === 'users'): ?>

    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-siagri-dark text-lg">Daftar Pengguna</h2>
        <button onclick="toggleModal('modal-add-expert')"
                class="bg-siagri-dark text-white px-4 py-2 rounded-xl text-sm font-semibold
                       hover:bg-siagri-green transition">
            + Tambah Akun Expert
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-siagri-dark text-white">
                        <th class="px-5 py-3 text-left">Username</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-center">Role</th>
                        <th class="px-5 py-3 text-center">Info Tambahan</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($u = mysqli_fetch_assoc($users_list)):
                    $role_cfg = [
                        'Farmer' => 'bg-green-100 text-green-700',
                        'Kiosk'  => 'bg-blue-100 text-blue-700',
                        'Expert' => 'bg-purple-100 text-purple-700',
                        'Admin'  => 'bg-red-100 text-red-700',
                    ];
                    $rc = $role_cfg[$u['role']] ?? 'bg-gray-100 text-gray-600';
                ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium">
                        <?= htmlspecialchars($u['username']) ?>
                        <?php if ($u['user_id'] == $_SESSION['user_id']): ?>
                        <span class="text-xs text-gray-400">(kamu)</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-gray-500">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="<?= $rc ?> text-xs px-2.5 py-1 rounded-full font-semibold">
                            <?= $u['role'] ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center text-xs text-gray-400">
                        <?php if ($u['role'] === 'Kiosk' && $u['store_name']): ?>
                        <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"/></svg> <?= htmlspecialchars($u['store_name']) ?>
                        <?php if ($u['kyc_status']): ?>
                        <br>
                        <span class="<?=
                            $u['kyc_status'] === 'verified' ? 'text-green-500' :
                            ($u['kyc_status'] === 'pending' ? 'text-yellow-500' : 'text-red-400')
                        ?>">
                            <?= ucfirst($u['kyc_status']) ?>
                        </span>
                        <?php endif; ?>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <?php if ($u['user_id'] != $_SESSION['user_id'] && $u['role'] !== 'Admin'): ?>
                        <form method="POST" class="inline"
                              onsubmit="return confirm('Hapus akun <?= addslashes($u['username']) ?>?')">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                            <button type="submit"
                                    class="text-red-400 hover:text-red-600 text-xs font-medium
                                           transition">
                                Hapus
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-gray-200 text-xs">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>

    <!-- TAB: KATEGORI -->
    <?php if ($active_tab === 'categories'): ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Form tambah kategori -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-siagri-dark text-lg mb-4">Tambah Kategori Baru</h2>
            <form method="POST" class="flex gap-3">
                <input type="hidden" name="action" value="add_category">
                <input type="text" name="category_name" required
                       placeholder="contoh: Vitamin Tanaman"
                       class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-siagri-dark">
                <button type="submit"
                        class="bg-siagri-dark text-white px-5 py-2.5 rounded-xl text-sm
                               font-semibold hover:bg-siagri-green transition">
                    + Tambah
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                <svg class="w-4 h-4 shrink-0 text-yellow-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span>Kategori yang sudah digunakan produk tidak bisa dihapus.</span>
            </p>
        </div>

        <!-- List kategori -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-bold text-siagri-dark">Kategori yang Ada</h2>
            </div>
            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50
                        hover:bg-gray-50">
                <div>
                    <p class="font-medium text-gray-800">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </p>
                    <p class="text-xs text-gray-400">
                        <?= $cat['product_count'] ?> produk
                    </p>
                </div>
                <?php if ($cat['product_count'] == 0): ?>
                <form method="POST"
                      onsubmit="return confirm('Hapus kategori ini?')">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                    <button type="submit"
                            class="text-red-400 hover:text-red-600 text-xs font-medium transition">
                        Hapus
                    </button>
                </form>
                <?php else: ?>
                <span class="text-xs text-gray-300">Tidak bisa dihapus</span>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php endif; ?>

    <!-- TAB: PESANAN -->
    <?php if ($active_tab === 'orders'): ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-siagri-dark">10 Pesanan Terbaru</h2>
        </div>
        <?php if (mysqli_num_rows($recent_orders) === 0): ?>
        <div class="text-center py-12 text-gray-400">
            <p class="text-4xl mb-3"><svg class="inline-block w-12 h-12 mx-auto mb-3 text-gray-300 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M9.5 20v2a.75.75 0 0 0 1.5 0v-2zm5.5 0h-1.5v2a.75.75 0 0 0 1.5 0z"/><path fill="currentColor" fill-rule="evenodd" d="m17.385 6.585l.256-.052a2.2 2.2 0 0 1 1.24.115c.69.277 1.446.328 2.165.148l.061-.015c.524-.131.893-.618.893-1.178v-2.13c0-.738-.664-1.282-1.355-1.109c-.396.1-.812.071-1.193-.081l-.073-.03a3.5 3.5 0 0 0-2-.185l-.449.09c-.54.108-.93.6-.93 1.17v6.953c0 .397.31.719.692.719a.706.706 0 0 0 .693-.72z" clip-rule="evenodd"/><path fill="currentColor" d="M14.5 6v4.28c0 1.172.928 2.22 2.192 2.22s2.193-1.048 2.193-2.22V8.229c.76.205 1.56.23 2.335.067c.492.842.78 1.86.78 2.955v6.175C22 18.847 21.012 20 19.793 20H12.5v-8.75c0-2.03-.832-3.974-2.217-5.25z"/><path fill="currentColor" fill-rule="evenodd" d="M2 11.25C2 8.35 4.015 6 6.5 6S11 8.35 11 11.25V20H4.233C3 20 2 18.834 2 17.395zM4.25 16a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75" clip-rule="evenodd"/></svg></p>
            <p>Belum ada pesanan</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-siagri-dark text-white">
                        <th class="px-5 py-3 text-left">#</th>
                        <th class="px-5 py-3 text-left">Petani</th>
                        <th class="px-5 py-3 text-left">Kios</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-center">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($o = mysqli_fetch_assoc($recent_orders)):
                    $sc = [
                        'pending'   => 'bg-yellow-100 text-yellow-700',
                        'confirmed' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ][$o['status']] ?? 'bg-gray-100 text-gray-600';
                ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-gray-400">#<?= $o['order_id'] ?></td>
                    <td class="px-5 py-3 font-medium"><?= htmlspecialchars($o['username']) ?></td>
                    <td class="px-5 py-3 text-gray-500"><?= htmlspecialchars($o['store_name']) ?></td>
                    <td class="px-5 py-3 text-right font-semibold">
                        Rp <?= number_format($o['total_price'], 0, ',', '.') ?>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="<?= $sc ?> text-xs px-2.5 py-1 rounded-full font-semibold">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center text-xs text-gray-400">
                        <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php endif; ?>

</div>

<!-- MODAL: TOLAK KYC -->
<div id="modal-reject"
     class="modal fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h2 class="font-bold text-siagri-dark text-lg mb-4">Tolak Pengajuan KYC</h2>
        <form method="POST" id="reject-form" action="">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Penolakan*
                </label>
                <textarea name="kyc_note" rows="3" required
                          placeholder="Jelaskan alasan penolakan agar Kios bisa memperbaiki..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                 focus:outline-none focus:border-siagri-dark resize-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-red-500 text-white font-semibold py-2.5 rounded-xl
                               hover:bg-red-600 transition text-sm">
                    <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> Tolak KYC
                </button>
                <button type="button" onclick="toggleModal('modal-reject')"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl
                               hover:bg-gray-50 transition text-sm">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: TAMBAH EXPERT -->
<div id="modal-add-expert"
     class="modal fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-siagri-dark text-lg">Tambah Akun Expert</h2>
            <button onclick="toggleModal('modal-add-expert')"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="action" value="add_expert">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Username*</label>
                    <input type="text" name="username" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                                  focus:outline-none focus:border-siagri-dark">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email*</label>
                    <input type="email" name="email" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                                  focus:outline-none focus:border-siagri-dark">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Password*</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                              focus:outline-none focus:border-siagri-dark">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Lengkap*</label>
                <input type="text" name="full_name" required
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                              focus:outline-none focus:border-siagri-dark">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Bidang Keahlian*</label>
                <input type="text" name="specialization" required
                       placeholder="contoh: Agronomi, Hama Tanaman"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                              focus:outline-none focus:border-siagri-dark">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">WhatsApp</label>
                <input type="text" name="whatsapp_number" placeholder="628xxxxxxxxxx"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                              focus:outline-none focus:border-siagri-dark">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-siagri-dark text-white font-semibold py-2.5
                               rounded-xl hover:bg-siagri-green transition text-sm">
                    Buat Akun Expert
                </button>
                <button type="button" onclick="toggleModal('modal-add-expert')"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5
                               rounded-xl hover:bg-gray-50 transition text-sm">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/catalog.js"></script>
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Chart 1: ordersChart
    const ctxOrders = document.getElementById('ordersChart');
    if (ctxOrders) {
        const ctx = ctxOrders.getContext('2d');
        window.ordersChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [
                    {
                        label: 'Jumlah Pesanan',
                        data: <?= json_encode($chart_counts) ?>,
                        borderColor: '#4d774e',
                        backgroundColor: 'rgba(77, 119, 78, 0.08)',
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        titleFont: { family: 'Poppins', weight: 'bold' },
                        bodyFont: { family: 'Poppins' },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + ' pesanan';
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Poppins', size: 10 } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: { font: { family: 'Poppins', size: 10 }, stepSize: 1 },
                        grid: { color: 'rgba(0, 0, 0, 0.03)' }
                    }
                }
            }
        });
    }

    // Chart 2: usersChart (Bar Chart)
    const ctxUsers = document.getElementById('usersChart');
    if (ctxUsers) {
        const ctx = ctxUsers.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Petani', 'Mitra Kios', 'Pakar', 'Admin'],
                datasets: [{
                    label: 'Pengguna',
                    data: [
                        <?= $total_farmers ?>,
                        <?= $total_kiosks ?>,
                        <?= $total_experts ?>,
                        <?= $total_admins ?>
                    ],
                    backgroundColor: [
                        'rgba(77, 119, 78, 0.75)',
                        'rgba(59, 130, 246, 0.75)',
                        'rgba(168, 85, 247, 0.75)',
                        'rgba(239, 68, 68, 0.75)'
                    ],
                    borderColor: ['#4d774e', '#3b82f6', '#a855f7', '#ef4444'],
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
<?php include 'component/layout/footer.php'; ?>

</body>
</html>