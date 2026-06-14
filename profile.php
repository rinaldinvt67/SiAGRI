<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit;
}

$role    = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$error   = "";
$success = "";

// ─── AMBIL DATA USER ─────────────────────────────────────────
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE user_id = $user_id"
));

// ─── AMBIL DATA TAMBAHAN SESUAI ROLE ─────────────────────────
$extra = null;
if ($role === 'Kiosk') {
    $extra = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM kiosk_profiles WHERE user_id = $user_id"
    ));
} elseif ($role === 'Expert') {
    $extra = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM expert_profiles WHERE user_id = $user_id"
    ));
}

// ─── UPDATE PROFIL ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_user') {
        $email    = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
        $username = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));

        // Cek username duplikat
        $cek = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT user_id FROM users WHERE username='$username' AND user_id != $user_id"
        ));
        if ($cek) {
            $error = "Username sudah digunakan orang lain!";
        } else {
            mysqli_query($conn,
                "UPDATE users SET username='$username', email='$email'
                 WHERE user_id = $user_id"
            );
            $_SESSION['username'] = $username;
            $success = "Profil berhasil diperbarui!";
            // Refresh data
            $user = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT * FROM users WHERE user_id = $user_id"
            ));
        }
    }

    if ($_POST['action'] === 'update_password') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $cfm = $_POST['confirm_password'] ?? '';

        if (!password_verify($old, $user['password'])) {
            $error = "Password lama tidak tepat!";
        } elseif (strlen($new) < 6) {
            $error = "Password baru minimal 6 karakter!";
        } elseif ($new !== $cfm) {
            $error = "Konfirmasi password tidak cocok!";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE user_id=$user_id");
            $success = "Password berhasil diubah!";
        }
    }

    if ($_POST['action'] === 'update_kiosk' && $role === 'Kiosk') {
        $store   = mysqli_real_escape_string($conn, trim($_POST['store_name'] ?? ''));
        $address = mysqli_real_escape_string($conn, trim($_POST['full_address'] ?? ''));
        $wa      = mysqli_real_escape_string($conn, trim($_POST['whatsapp_number'] ?? ''));

        mysqli_query($conn,
            "UPDATE kiosk_profiles
             SET store_name='$store', full_address='$address', whatsapp_number='$wa'
             WHERE user_id=$user_id"
        );
        $success = "Profil kios berhasil diperbarui!";
        $extra = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT * FROM kiosk_profiles WHERE user_id = $user_id"
        ));
    }

    if ($_POST['action'] === 'update_expert' && $role === 'Expert') {
        $fullname = mysqli_real_escape_string($conn, trim($_POST['full_name'] ?? ''));
        $spec     = mysqli_real_escape_string($conn, trim($_POST['specialization'] ?? ''));
        $wa       = mysqli_real_escape_string($conn, trim($_POST['whatsapp_number'] ?? ''));

        $existing = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT expert_id FROM expert_profiles WHERE user_id=$user_id"
        ));
        if ($existing) {
            mysqli_query($conn,
                "UPDATE expert_profiles
                 SET full_name='$fullname', specialization='$spec', whatsapp_number='$wa'
                 WHERE user_id=$user_id"
            );
        } else {
            mysqli_query($conn,
                "INSERT INTO expert_profiles (user_id, full_name, specialization, whatsapp_number)
                 VALUES ($user_id, '$fullname', '$spec', '$wa')"
            );
        }
        $success = "Profil pakar berhasil diperbarui!";
        $extra = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT * FROM expert_profiles WHERE user_id = $user_id"
        ));
    }
}

// ─── STATISTIK PER ROLE ───────────────────────────────────────
$stats = [];
if ($role === 'Farmer') {
    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM orders WHERE user_id=$user_id"
    ));
    $stats['total_orders'] = $r['total'] ?? 0;

    $r2 = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM orders WHERE user_id=$user_id AND status='completed'"
    ));
    $stats['completed'] = $r2['total'] ?? 0;

    $r3 = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM orders WHERE user_id=$user_id AND status='pending'"
    ));
    $stats['pending'] = $r3['total'] ?? 0;

} elseif ($role === 'Kiosk') {
    $kiosk_id = $_SESSION['kiosk_id'] ?? 0;

    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM products WHERE kiosk_id=$kiosk_id"
    ));
    $stats['total_products'] = $r['total'] ?? 0;

    $r2 = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM orders WHERE kiosk_id=$kiosk_id AND status='completed'"
    ));
    $stats['completed_orders'] = $r2['total'] ?? 0;

    $r3 = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM orders WHERE kiosk_id=$kiosk_id AND status='pending'"
    ));
    $stats['pending_orders'] = $r3['total'] ?? 0;

} elseif ($role === 'Admin') {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"));
    $stats['total_users'] = $r['total'] ?? 0;

    $r2 = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM kiosk_profiles WHERE kyc_status='pending'"
    ));
    $stats['kyc_pending'] = $r2['total'] ?? 0;

    $r3 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"));
    $stats['total_orders'] = $r3['total'] ?? 0;
}

// ─── CONFIG ROLE ──────────────────────────────────────────────
$role_config = [
    'Farmer' => ['icon'=>'🌾', 'color'=>'siagri-dark',  'label'=>'Petani',    'back'=>'catalog.php'],
    'Kiosk'  => ['icon'=>'🏪', 'color'=>'siagri-green', 'label'=>'Mitra Kios','back'=>'dashboard.php'],
    'Expert' => ['icon'=>'👨‍🔬', 'color'=>'blue-700',   'label'=>'Pakar',     'back'=>'catalog.php'],
    'Admin'  => ['icon'=>'⚙️', 'color'=>'gray-800',     'label'=>'Admin',     'back'=>'admin-dashboard.php'],
];
$rc = $role_config[$role] ?? $role_config['Farmer'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php
    $page_title = 'Profil Saya';
    $extra_head = '<style>.tab-btn { transition: all 0.2s; } .tab-btn.active { background: #164a41; color: white; }</style>';
    include 'komponen/layout/head.php';
    ?>
</head>