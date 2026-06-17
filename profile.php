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
<body class="bg-gray-50 min-h-screen">

<?php $current_page = 'profile'; include 'component/layout/navbar.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-8">

    <!-- Notifikasi -->
    <?php if ($success): ?>
    <div class="mb-5 p-3 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-5 p-3 bg-red-50 text-red-800 rounded-xl border border-red-200 text-sm">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- SIDEBAR PROFIL -->
        <div class="lg:col-span-1 space-y-5">

            <!-- Avatar card -->
            <div class="bg-white rounded-2xl shadow-sm p-6 text-center border border-gray-100">
                <div class="w-24 h-24 bg-siagri-dark rounded-full flex items-center justify-center
                             text-4xl mx-auto mb-4 shadow-lg">
                    <?= $rc['icon'] ?>
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
                    <a href="kyc-upload.php"
                       class="block mt-2 text-center text-xs text-siagri-dark underline">
                        Upload dokumen →
                    </a>
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
                    <a href="catalog.php"   class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Katalog
                    </a>
                    <a href="my-orders.php" class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Pesanan Saya
                    </a>
                    <?php elseif ($role === 'Kiosk'): ?>
                    <a href="catalog.php"         class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M22 22H2m18 0V11M4 22V11"/><path stroke-linejoin="round" d="M16.528 2H7.472c-1.203 0-1.804 0-2.287.299c-.484.298-.753.836-1.29 1.912L2.49 7.76c-.324.82-.608 1.786-.062 2.479A2 2 0 0 0 6 9a2 2 0 1 0 4 0a2 2 0 1 0 4 0a2 2 0 1 0 4 0a2 2 0 0 0 3.571 1.238c.546-.693.262-1.659-.062-2.479l-1.404-3.548c-.537-1.076-.806-1.614-1.29-1.912C18.332 2 17.731 2 16.528 2Z"/>
                        <path stroke-linecap="round" d="M9.5 21.5v-3c0-.935 0-1.402.201-1.75a1.5 1.5 0 0 1 .549-.549C10.598 16 11.065 16 12 16s1.402 0 1.75.201a1.5 1.5 0 0 1 .549.549c.201.348.201.815.201 1.75v3"/></g></svg>
                        Marketplace
                    </a>
                    <a href="dashboard.php"       class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        Dashboard
                    </a>
                    <a href="incoming-orders.php" class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Pesanan
                    </a>
                    <?php elseif ($role === 'Admin'): ?>
                    <a href="admin-dashboard.php" class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Dashboard Admin
                    </a>
                    <?php elseif ($role === 'Expert'): ?>
                    <a href="forum.php" class="text-center bg-siagri-light p-3 rounded-xl text-sm font-medium text-siagri-dark hover:bg-siagri-dark hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Forum
                    </a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-center bg-red-50 p-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-500 hover:text-white transition flex items-center justify-center gap-2">
                        <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M10 8V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2"/><path d="M15 12H3l3-3m0 6l-3-3"/></g></svg>
                        Logout
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="assets/js/auth.js"></script>
<?php include 'component/layout/footer.php'; ?>

</body>
</html>
