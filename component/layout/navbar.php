<?php
/**
 * navbar.php — Komponen Navbar modular SiAGRI
 * 
 * Cara pakai:
 * <?php $current_page = 'catalog'; include 'component/layout/navbar.php'; ?>
 * Session yang dibutuhkan: $_SESSION['username'], $_SESSION['role']
 */

$current_page    = $current_page    ?? '';
$cart_count      = $cart_count      ?? 0;
$pesanan_pending = $pesanan_pending ?? 0;
$navbar_extra    = $navbar_extra    ?? '';

$is_logged_in = isset($_SESSION['username']);
$role         = $_SESSION['role'] ?? '';
$username     = $_SESSION['username'] ?? '';

// Helper: class aktif
function nav_active($page, $current) {
    if ($page === $current) {
        return 'text-white font-semibold border-b-2 border-siagri-gold pb-0.5';
    }
    return 'text-white/70 hover:text-white';
}

// Badge role
function role_badge($role) {
    $badges = [
        'Farmer' => ['bg' => 'bg-green-500',  'text' => 'text-white',       'label' => 'Petani'],
        'Kiosk'  => ['bg' => 'bg-siagri-gold', 'text' => 'text-siagri-dark', 'label' => 'Mitra Kios'],
        'Expert' => ['bg' => 'bg-blue-500',    'text' => 'text-white',       'label' => 'Pakar'],
        'Admin'  => ['bg' => 'bg-red-500',     'text' => 'text-white',       'label' => 'Admin'],
    ];
    $b = $badges[$role] ?? null;
    if (!$b) return '';
    return "<span class=\"text-xs {$b['bg']} {$b['text']} px-2 py-0.5 rounded-full font-bold\">{$b['label']}</span>";
}
?>

<nav class="bg-siagri-dark text-white sticky top-0 z-40 shadow-lg" id="main-navbar">
    <div class="max-w-7xl mx-auto px-5 py-4 md:py-5 flex items-center justify-between">

        <!-- Logo -->
        <a href="<?= $is_logged_in ? ($role === 'Admin' ? $path_prefix . 'pages/admin/dashboard.php' : ($role === 'Kiosk' ? $path_prefix . 'pages/kiosk/dashboard.php' : $path_prefix . 'pages/farmer/catalog.php')) : $path_prefix . 'index.php' ?>"
           class="flex items-center gap-3 shrink-0">
            <img src="<?= $path_prefix ?>Assets/images/LOGO.png" alt="SiAGRI" class="h-10 md:h-11 w-auto"
                 onerror="this.style.display='none'">
        </a>

        <!-- Desktop Nav Links -->
        <div class="hidden md:flex items-center gap-6">

            <?php if ($role === 'Farmer'): ?>
                <a href="<?= $path_prefix ?>pages/farmer/catalog.php"   class="<?= nav_active('catalog', $current_page) ?> text-sm transition">Katalog</a>
                <a href="<?= $path_prefix ?>pages/farmer/forum.php"     class="<?= nav_active('forum', $current_page) ?> text-sm transition">Forum Diskusi</a>
                <a href="<?= $path_prefix ?>pages/farmer/my-orders.php" class="<?= nav_active('my-orders', $current_page) ?> text-sm transition">Pesanan Saya</a>
                <a href="<?= $path_prefix ?>pages/general/profile.php"   class="<?= nav_active('profile', $current_page) ?> text-sm transition">Profil</a>

            <?php elseif ($role === 'Kiosk'): ?>
                <a href="<?= $path_prefix ?>pages/kiosk/dashboard.php"       class="<?= nav_active('dashboard', $current_page) ?> text-sm transition">Dashboard</a>
                <a href="<?= $path_prefix ?>pages/kiosk/manage-catalog.php"  class="<?= nav_active('manage-catalog', $current_page) ?> text-sm transition">Produk Saya</a>
                <a href="<?= $path_prefix ?>pages/kiosk/incoming-orders.php" class="<?= nav_active('incoming-orders', $current_page) ?> text-sm transition relative">
                    Pesanan
                    <?php if ($pesanan_pending > 0): ?>
                    <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs
                                 rounded-full w-4 h-4 flex items-center justify-center font-bold">
                        <?= $pesanan_pending ?>
                    </span>
                    <?php endif; ?>
                </a>
                <a href="<?= $path_prefix ?>pages/general/profile.php" class="<?= nav_active('profile', $current_page) ?> text-sm transition">Profil</a>

            <?php elseif ($role === 'Expert'): ?>
                <a href="<?= $path_prefix ?>pages/farmer/forum.php"   class="<?= nav_active('forum', $current_page) ?> text-sm transition">Forum</a>
                <a href="<?= $path_prefix ?>pages/general/profile.php" class="<?= nav_active('profile', $current_page) ?> text-sm transition">Profil</a>

            <?php elseif ($role === 'Admin'): ?>
                <a href="<?= $path_prefix ?>pages/admin/dashboard.php" class="<?= nav_active('admin-dashboard', $current_page) ?> text-sm transition">Dashboard</a>
                <a href="<?= $path_prefix ?>pages/general/profile.php"         class="<?= nav_active('profile', $current_page) ?> text-sm transition">Profil</a>
            <?php endif; ?>
        </div>



        <!-- Right Section: User + Cart + Logout -->
        <div class="flex items-center gap-4">

            <?php if ($is_logged_in): ?>

                <?php if ($role === 'Farmer' && $current_page === 'catalog'): ?>
                <!-- Cart Button (only on catalog for Farmer) -->
                <button onclick="toggleCart()"
                        class="relative bg-siagri-gold text-siagri-dark p-2 rounded-full
                               hover:bg-yellow-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2s-.9-2-2-2M1 2v2h2l3.6 7.59l-1.35 2.45c-.16.28-.25.61-.25.96c0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12l.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48a1 1 0 0 0-1-1H5.21l-.94-2zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2s2-.9 2-2s-.9-2-2-2"/></svg>
                    <?php if ($cart_count > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full
                                 w-5 h-5 flex items-center justify-center font-bold">
                        <?= $cart_count ?>
                    </span>
                    <?php endif; ?>
                </button>
                <?php endif; ?>

                <?= role_badge($role) ?>
                <a href="<?= $path_prefix ?>proses/logout.php"
                   class="text-white/70 hover:text-white text-sm transition hidden md:block">Logout</a>

            <?php else: ?>
                <!-- Desktop Guest Buttons -->
                <div class="hidden md:flex items-center gap-3">
                    <a href="<?= $path_prefix ?>pages/auth/login.php"
                       class="border border-white/80 text-white text-sm font-bold px-4 py-2 rounded-full
                              hover:bg-white hover:text-siagri-dark hover:border-white transition shadow-md">
                        Masuk
                    </a>
                    <a href="<?= $path_prefix ?>pages/auth/register.php"
                       class="bg-siagri-gold text-siagri-dark text-sm font-bold px-4 py-2 rounded-full
                              hover:bg-yellow-400 transition shadow-md">
                        Daftar Gratis
                    </a>
                </div>
            <?php endif; ?>

            <!-- Mobile Hamburger -->
            <button onclick="toggleMobileNav()"
                    class="md:hidden text-white p-1.5 rounded-lg hover:bg-white/10 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Nav Menu -->
    <div id="mobile-nav" class="hidden md:hidden bg-siagri-dark border-t border-white/10 px-5 pb-4">
        <?php if ($is_logged_in): ?>
            <div class="flex flex-col gap-2 pt-3">
                <?php if ($role === 'Farmer'): ?>
                    <a href="<?= $path_prefix ?>pages/farmer/catalog.php"   class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Katalog
                    </a>
                    <a href="<?= $path_prefix ?>pages/farmer/forum.php"     class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Forum Diskusi
                    </a>
                    <a href="<?= $path_prefix ?>pages/farmer/my-orders.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Pesanan Saya
                    </a>
                    <a href="<?= $path_prefix ?>pages/general/profile.php"   class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                <?php elseif ($role === 'Kiosk'): ?>
                    <a href="<?= $path_prefix ?>pages/kiosk/dashboard.php"       class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        Dashboard
                    </a>
                    <a href="<?= $path_prefix ?>pages/kiosk/manage-catalog.php"  class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Produk Saya
                    </a>
                    <a href="<?= $path_prefix ?>pages/kiosk/incoming-orders.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Pesanan
                    </a>
                    <a href="<?= $path_prefix ?>pages/general/profile.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                <?php elseif ($role === 'Expert'): ?>
                    <a href="<?= $path_prefix ?>pages/farmer/forum.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Forum
                    </a>
                    <a href="<?= $path_prefix ?>pages/general/profile.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                <?php elseif ($role === 'Admin'): ?>
                    <a href="<?= $path_prefix ?>pages/admin/dashboard.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        Dashboard
                    </a>
                    <a href="<?= $path_prefix ?>pages/general/profile.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                <?php endif; ?>
                <hr class="border-white/10 my-1">
                <a href="<?= $path_prefix ?>proses/logout.php" class="text-red-400 hover:text-red-300 text-sm py-2.5 transition flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M10 8V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2"/><path d="M15 12H3l3-3m0 6l-3-3"/></g></svg>
                    Logout
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col gap-2 pt-3">
                <a href="<?= $path_prefix ?>index.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Beranda
                </a>
                <a href="<?= $path_prefix ?>pages/farmer/catalog.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Marketplace
                </a>
                <a href="<?= $path_prefix ?>pages/farmer/forum.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Forum
                </a>
                <a href="<?= $path_prefix ?>pages/auth/login.php" class="border border-white/60 text-white text-sm font-bold px-4 py-2 rounded-full hover:bg-white hover:text-siagri-dark hover:border-white transition shadow-md text-center mt-2">
                    Masuk
                </a>
                <a href="<?= $path_prefix ?>pages/auth/register.php" class="bg-siagri-gold text-siagri-dark text-sm font-bold px-4 py-2 rounded-full hover:bg-yellow-400 transition shadow-md text-center">
                    Daftar Gratis
                </a>
            </div>
        <?php endif; ?>
    </div>



    <?php if (!empty($navbar_extra)) echo $navbar_extra; ?>
</nav>


