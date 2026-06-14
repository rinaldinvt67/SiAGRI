<?php
$current_page    = $current_page    ?? '';
$cart_count      = $cart_count      ?? 0;
$pesanan_pending = $pesanan_pending ?? 0;
$navbar_extra    = $navbar_extra    ?? '';

$is_logged_in = isset($_SESSION['username']);
$role         = $_SESSION['role'] ?? '';
$username     = $_SESSION['username'] ?? '';

function nav_active($page, $current) {
    if ($page === $current) {
        return 'text-white font-semibold border-b-2 border-siagri-gold pb-0.5';
    }
    return 'text-white/70 hover:text-white';
}

function role_badge($role) {
    $badges = [
        'Farmer' => ['bg' => 'bg-green-500',  'text' => 'text-white',       'label' => '🌾 Petani'],
        'Kiosk'  => ['bg' => 'bg-siagri-gold', 'text' => 'text-siagri-dark', 'label' => 'Mitra Kios'],
        'Expert' => ['bg' => 'bg-blue-500',    'text' => 'text-white',       'label' => '🎓 Pakar'],
        'Admin'  => ['bg' => 'bg-red-500',     'text' => 'text-white',       'label' => 'Admin'],
    ];
    $b = $badges[$role] ?? null;
    if (!$b) return '';
    return "<span class="text-xs {$b['bg']} {$b['text']} px-2 py-0.5 rounded-full font-bold">{$b['label']}</span>";
}
?>

<nav class="bg-siagri-dark text-white sticky top-0 z-40 shadow-lg" id="main-navbar">
    <div class="max-w-7xl mx-auto px-5 py-4 md:py-5 flex items-center justify-between">
        <!-- Logo -->
        <a href="<?= $is_logged_in ? ($role === 'Admin' ? 'admin-dashboard.php' : ($role === 'Kiosk' ? 'dashboard.php' : 'catalog.php')) : 'index.php' ?>"
           class="flex items-center gap-3 shrink-0">
            <img src="Assets/images/LOGO.png" alt="SiAGRI" class="h-10 md:h-11 w-auto"
                 onerror="this.style.display='none'">
        </a>

        <!-- Desktop Nav Links -->
        <div class="hidden md:flex items-center gap-6">

            <?php if (!$is_logged_in): ?>
                <!-- GUEST -->
                <a href="index.php"      class="<?= nav_active('index', $current_page) ?> text-sm transition">Beranda</a>
                <a href="catalog.php"    class="<?= nav_active('catalog', $current_page) ?> text-sm transition">Marketplace</a>
                <a href="forum.php"      class="<?= nav_active('forum', $current_page) ?> text-sm transition">Forum</a>
                <a href="login-page.php"
                   class="border border-white/80 text-white text-sm font-bold px-4 py-2 rounded-full
                          hover:bg-white hover:text-siagri-dark hover:border-white transition shadow-md">
                    Masuk
                </a>
                <a href="register.php"
                   class="bg-siagri-gold text-siagri-dark text-sm font-bold px-4 py-2 rounded-full
                          hover:bg-yellow-400 transition shadow-md">
                    Daftar Gratis
                </a>

            <?php elseif ($role === 'Farmer'): ?>
                <!--  FARMER  -->
                <a href="index.php"     class="<?= nav_active('index', $current_page) ?> text-sm transition">Beranda</a>
                <a href="catalog.php"   class="<?= nav_active('catalog', $current_page) ?> text-sm transition">Katalog</a>
                <a href="forum.php"     class="<?= nav_active('forum', $current_page) ?> text-sm transition">Forum Diskusi</a>
                <a href="my-orders.php" class="<?= nav_active('my-orders', $current_page) ?> text-sm transition">Pesanan Saya</a>
                <a href="profile.php"   class="<?= nav_active('profile', $current_page) ?> text-sm transition">Profil</a>

            <?php elseif ($role === 'Kiosk'): ?>
                <!--  KIOSK  -->
                <a href="dashboard.php"       class="<?= nav_active('dashboard', $current_page) ?> text-sm transition">Dashboard</a>
                <a href="manage-catalog.php"  class="<?= nav_active('manage-catalog', $current_page) ?> text-sm transition">Produk Saya</a>
                <a href="incoming-orders.php" class="<?= nav_active('incoming-orders', $current_page) ?> text-sm transition relative">
                    Pesanan
                    <?php if ($pesanan_pending > 0): ?>
                    <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs
                                 rounded-full w-4 h-4 flex items-center justify-center font-bold">
                        <?= $pesanan_pending ?>
                    </span>
                    <?php endif; ?>
                </a>
                <a href="profile.php" class="<?= nav_active('profile', $current_page) ?> text-sm transition">Profil</a>

            <?php elseif ($role === 'Expert'): ?>
                <!--  EXPERT  -->
                <a href="forum.php"   class="<?= nav_active('forum', $current_page) ?> text-sm transition">Forum</a>
                <a href="profile.php" class="<?= nav_active('profile', $current_page) ?> text-sm transition">Profil</a>

            <?php elseif ($role === 'Admin'): ?>
                <!--  ADMIN  -->
                <a href="admin-dashboard.php" class="<?= nav_active('admin-dashboard', $current_page) ?> text-sm transition">Dashboard</a>
                <a href="profile.php"         class="<?= nav_active('profile', $current_page) ?> text-sm transition">Profil</a>
            <?php endif; ?>
        </div>

    </div>
</nav>
        