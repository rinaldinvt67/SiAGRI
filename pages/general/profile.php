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
$error   = "";
$success = "";

// AMBIL DATA USER
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE user_id = $user_id"
));

// AMBIL DATA TAMBAHAN SESUAI ROLE
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

// UPDATE PROFIL 
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

// STATISTIK PER ROLE
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
}

// CONFIG ROLE
$role_config = [
    'Farmer' => ['color'=>'siagri-dark',  'label'=>'Petani',    'back'=>'catalog.php'],
    'Kiosk'  => ['color'=>'siagri-green', 'label'=>'Mitra Kios','back'=>'dashboard.php'],
    'Expert' => ['color'=>'blue-700',   'label'=>'Pakar',     'back'=>'catalog.php'],
    'Admin'  => ['color'=>'gray-800',     'label'=>'Admin',     'back'=>'admin-dashboard.php'],
];
$rc = $role_config[$role] ?? $role_config['Farmer'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php
    $page_title = 'Profil Saya';
    $extra_head = '';
    include '../../component/layout/head.php';
    ?>
</head>
<body class="bg-gray-50 min-h-screen">

<?php $current_page = 'profile'; include '../../component/layout/navbar.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-8">

    <!-- Notifikasi -->
    <?php if ($success): ?>
    <div class="mb-5 p-3 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-5 p-3 bg-red-50 text-red-800 rounded-xl border border-red-200 text-sm">
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- SIDEBAR PROFIL -->
        <div class="lg:col-span-1 space-y-5">

            <!-- Avatar card -->
            <div class="bg-white rounded-2xl shadow-sm p-6 text-center border border-gray-100">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center
                             mx-auto mb-4 shadow-lg overflow-hidden border border-gray-200">
                    <img src="../../Assets/images/Placeholder-photo.png" alt="Profile Photo" class="w-full h-full object-cover rounded-full">
                </div>
                <h2 class="font-bold text-siagri-dark text-xl">
                    <?= htmlspecialchars($user['username']) ?>
                </h2>
                <p class="text-gray-400 text-sm mt-1">
                    <?= htmlspecialchars($user['email']) ?>
                </p>
                <span class="inline-block mt-3 bg-siagri-dark text-white text-xs px-3 py-1 rounded-full">
                    <?= $rc['label'] ?>
                </span>

                <?php if ($role === 'Kiosk' && $extra): ?>
                <div class="mt-4 pt-4 border-t border-gray-100 text-left">
                    <p class="text-xs text-gray-400 mb-1">Status KYC</p>
                    <?php
                    $kyc_cfg = [
                        'unverified' => ['text'=>'Belum Upload Dokumen', 'cls'=>'bg-gray-100 text-gray-600'],
                        'pending'    => ['text'=>'Sedang Ditinjau',      'cls'=>'bg-yellow-100 text-yellow-700'],
                        'verified'   => ['text'=>'✓ Kios Resmi',         'cls'=>'bg-green-100 text-green-700'],
                        'rejected'   => ['text'=>'Ditolak',              'cls'=>'bg-red-100 text-red-700'],
                    ];
                    $ks = $extra['kyc_status'] ?? 'unverified';
                    $kc = $kyc_cfg[$ks] ?? $kyc_cfg['unverified'];
                    ?>
                    <span class="<?= $kc['cls'] ?> text-xs px-3 py-1 rounded-full font-medium">
                        <?= $kc['text'] ?>
                    </span>
                    <?php if ($ks !== 'verified'): ?>
                    <div class="mt-3">
                        <a href="../../pages/kiosk/kyc-upload.php"
                           class="inline-flex items-center gap-2 bg-siagri-dark text-white font-semibold px-4 py-2 rounded-xl
                                  hover:bg-siagri-green transition text-sm">
                            Upload dokumen
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4 fill-current">
                                <path d="M4 11h12.17l-5.59-5.59L12 4l8 8l-8 8l-1.41-1.41L16.17 13H4z"></path>
                            </svg>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Statistik -->
            <?php if (!empty($stats)): ?>
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold text-siagri-dark mb-4">Statistik Akun</h3>
                <div class="space-y-3">
                    <?php foreach ($stats as $key => $val):
                        $labels = [
                            'total_orders'    => 'Total Pesanan',
                            'completed'       => 'Selesai',
                            'pending'         => 'Menunggu',
                            'total_products'  => 'Produk Dijual',
                            'completed_orders'=> 'Pesanan Selesai',
                            'pending_orders'  => 'Pesanan Masuk',
                            'total_users'     => 'Total Pengguna',
                            'kyc_pending'     => 'KYC Menunggu',
                        ];
                    ?>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500"><?= $labels[$key] ?? $key ?></span>
                        <span class="font-bold text-siagri-dark"><?= $val ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- MAIN CONTENT -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Tabs -->
            <div class="flex gap-2 bg-white rounded-xl p-1 shadow-sm border border-gray-100">
                <button onclick="showTab('info')"    id="tab-info"    class="tab-btn active flex-1 text-sm py-2 rounded-lg font-medium">Informasi Akun</button>
                <button onclick="showTab('password')" id="tab-password" class="tab-btn flex-1 text-sm py-2 rounded-lg font-medium text-gray-500">Ubah Password</button>
                <?php if ($role === 'Kiosk'): ?>
                <button onclick="showTab('kiosk')"   id="tab-kiosk"   class="tab-btn flex-1 text-sm py-2 rounded-lg font-medium text-gray-500">Profil Kios</button>
                <?php elseif ($role === 'Expert'): ?>
                <button onclick="showTab('expert')"  id="tab-expert"  class="tab-btn flex-1 text-sm py-2 rounded-lg font-medium text-gray-500">Profil Pakar</button>
                <?php endif; ?>
            </div>

            <!-- Tab: Informasi Akun -->
            <div id="tab-info-content" class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-bold text-siagri-dark text-lg mb-5">Informasi Akun</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_user">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username*</label>
                            <input type="text" name="username"
                                   value="<?= htmlspecialchars($user['username']) ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email*</label>
                            <input type="email" name="email"
                                   value="<?= htmlspecialchars($user['email']) ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <input type="text" value="<?= $rc['label'] ?>" disabled
                                   class="w-full border border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm text-gray-400">
                        </div>

                    </div>
                    <button type="submit"
                            class="mt-5 bg-siagri-dark text-white font-semibold px-6 py-2.5 rounded-xl
                                   hover:bg-siagri-green transition text-sm">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- Tab: Ubah Password -->
            <div id="tab-password-content" class="hidden bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-bold text-siagri-dark text-lg mb-5">Ubah Password</h3>
                <form method="POST" class="max-w-md">
                    <input type="hidden" name="action" value="update_password">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama*</label>
                            <input type="password" name="old_password" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru* (min. 6 karakter)</label>
                            <input type="password" name="new_password" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru*</label>
                            <input type="password" name="confirm_password" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark">
                        </div>
                    </div>
                    <button type="submit"
                            class="mt-5 bg-siagri-dark text-white font-semibold px-6 py-2.5 rounded-xl
                                   hover:bg-siagri-green transition text-sm">
                        Ubah Password
                    </button>
                </form>
            </div>

            <!-- Tab: Profil Kios (Kiosk only) -->
            <?php if ($role === 'Kiosk'): ?>
            <div id="tab-kiosk-content" class="hidden bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-bold text-siagri-dark text-lg mb-5">Profil Toko</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_kiosk">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko*</label>
                            <input type="text" name="store_name"
                                   value="<?= htmlspecialchars($extra['store_name'] ?? '') ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap*</label>
                            <textarea name="full_address" rows="3"
                                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                             focus:outline-none focus:border-siagri-dark resize-none"
                                      required><?= htmlspecialchars($extra['full_address'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp*</label>
                            <input type="text" name="whatsapp_number"
                                   value="<?= htmlspecialchars($extra['whatsapp_number'] ?? '') ?>"
                                   placeholder="628xxxxxxxxxx"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark">
                        </div>
                    </div>
                    <button type="submit"
                            class="mt-5 bg-siagri-dark text-white font-semibold px-6 py-2.5 rounded-xl
                                   hover:bg-siagri-green transition text-sm">
                        Simpan Profil Toko
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Tab: Profil Pakar (Expert only) -->
            <?php if ($role === 'Expert'): ?>
            <div id="tab-expert-content" class="hidden bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-bold text-siagri-dark text-lg mb-5">Profil Pakar</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_expert">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap*</label>
                            <input type="text" name="full_name"
                                   value="<?= htmlspecialchars($extra['full_name'] ?? '') ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bidang Keahlian*</label>
                            <input type="text" name="specialization"
                                   value="<?= htmlspecialchars($extra['specialization'] ?? '') ?>"
                                   placeholder="contoh: Agronomi, Hama Tanaman"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                            <input type="text" name="whatsapp_number"
                                   value="<?= htmlspecialchars($extra['whatsapp_number'] ?? '') ?>"
                                   placeholder="628xxxxxxxxxx"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-siagri-dark">
                        </div>
                    </div>
                    <button type="submit"
                            class="mt-5 bg-siagri-dark text-white font-semibold px-6 py-2.5 rounded-xl
                                   hover:bg-siagri-green transition text-sm">
                        Simpan Profil Pakar
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Quick links -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold text-siagri-dark mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php if ($role === 'Farmer'): ?>
                    <a href="../../pages/farmer/catalog.php"   class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Katalog
                    </a>
                    <a href="../../pages/farmer/my-orders.php" class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Pesanan Saya
                    </a>
                    <?php elseif ($role === 'Kiosk'): ?>
                    <a href="../../pages/farmer/catalog.php"         class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg class="inline-block w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M3.778 3.655c-.181.36-.27.806-.448 1.696l-.598 2.99a3.06 3.06 0 1 0 6.043.904l.07-.69a3.167 3.167 0 1 0 6.307-.038l.073.728a3.06 3.06 0 1 0 6.043-.904l-.598-2.99c-.178-.89-.267-1.335-.448-1.696a3 3 0 0 0-1.888-1.548C17.944 2 17.49 2 16.582 2H7.418c-.908 0-1.362 0-1.752.107a3 3 0 0 0-1.888 1.548M18.269 13.5a4.53 4.53 0 0 0 2.231-.581V14c0 3.771 0 5.657-1.172 6.828c-.943.944-2.348 1.127-4.828 1.163V18.5c0-.935 0-1.402-.201-1.75a1.5 1.5 0 0 0-.549-.549C13.402 16 12.935 16 12 16s-1.402 0-1.75.201a1.5 1.5 0 0 0-.549.549c-.201.348-.201.815-.201 1.75v3.491c-2.48-.036-3.885-.22-4.828-1.163C3.5 19.657 3.5 17.771 3.5 14v-1.081a4.53 4.53 0 0 0 2.232.581a4.55 4.55 0 0 0 3.112-1.228A4.64 4.64 0 0 0 12 13.5a4.64 4.64 0 0 0 3.156-1.228a4.55 4.55 0 0 0 3.112 1.228"></path></svg>
                        Marketplace
                    </a>
                    <a href="../../pages/kiosk/dashboard.php"       class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg class="inline-block w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M17.293 2.293C17 2.586 17 3.057 17 4v13c0 .943 0 1.414.293 1.707S18.057 19 19 19s1.414 0 1.707-.293S21 17.943 21 17V4c0-.943 0-1.414-.293-1.707S19.943 2 19 2s-1.414 0-1.707.293M10 7c0-.943 0-1.414.293-1.707S11.057 5 12 5s1.414 0 1.707.293S14 6.057 14 7v10c0 .943 0 1.414-.293 1.707S12.943 19 12 19s-1.414 0-1.707-.293S10 17.943 10 17zM3.293 9.293C3 9.586 3 10.057 3 11v6c0 .943 0 1.414.293 1.707S4.057 19 5 19s1.414 0 1.707-.293S7 17.943 7 17v-6c0-.943 0-1.414-.293-1.707S5.943 9 5 9s-1.414 0-1.707.293M3 21.25a.75.75 0 0 0 0 1.5h18a.75.75 0 0 0 0-1.5z"/></svg>
                        Dashboard
                    </a>
                    <a href="../../pages/kiosk/incoming-orders.php" class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Pesanan
                    </a>
                    <?php elseif ($role === 'Admin'): ?>
                    <a href="../../pages/admin/dashboard.php" class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg class="inline-block w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M17.293 2.293C17 2.586 17 3.057 17 4v13c0 .943 0 1.414.293 1.707S18.057 19 19 19s1.414 0 1.707-.293S21 17.943 21 17V4c0-.943 0-1.414-.293-1.707S19.943 2 19 2s-1.414 0-1.707.293M10 7c0-.943 0-1.414.293-1.707S11.057 5 12 5s1.414 0 1.707.293S14 6.057 14 7v10c0 .943 0 1.414-.293 1.707S12.943 19 12 19s-1.414 0-1.707-.293S10 17.943 10 17zM3.293 9.293C3 9.586 3 10.057 3 11v6c0 .943 0 1.414.293 1.707S4.057 19 5 19s1.414 0 1.707-.293S7 17.943 7 17v-6c0-.943 0-1.414-.293-1.707S5.943 9 5 9s-1.414 0-1.707.293M3 21.25a.75.75 0 0 0 0 1.5h18a.75.75 0 0 0 0-1.5z"></path></svg>
                        Dashboard Admin
                    </a>
                    <?php elseif ($role === 'Expert'): ?>
                    <a href="../../pages/farmer/forum.php" class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Forum
                    </a>
                    <?php endif; ?>
                    <a href="../../proses/logout.php" class="text-center bg-red-50 p-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-500 hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M10 8V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2"/><path d="M15 12H3l3-3m0 6l-3-3"/></g></svg>
                        Logout
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="../../Assets/js/auth.js"></script>
<?php include '../../component/layout/footer.php'; ?>

</body>
</html>
