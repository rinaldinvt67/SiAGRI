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

                <?php if ($current_page === 'catalog'): ?>
        <!-- Search bar (desktop) -->
        <form method="GET" class="hidden md:flex items-center bg-white/10 rounded-full px-4 py-2 w-48 lg:w-72 xl:w-96 gap-2">
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
                   placeholder="Cari produk..."
                   class="bg-transparent text-white placeholder-white/60 outline-none text-sm flex-1">
            <?php if (isset($filter_cat) && $filter_cat): ?>
                <input type="hidden" name="cat" value="<?= $filter_cat ?>">
            <?php endif; ?>
        </form>
        <?php endif; ?>

        <!-- Right Section: User + Cart + Logout -->
        <div class="flex items-center gap-4">

            <?php if ($is_logged_in): ?>

                <?php if ($role === 'Farmer' && $current_page === 'catalog' && $cart_count > 0): ?>
                <!-- Cart Button (only on catalog for Farmer) -->
                <button onclick="toggleCart()"
                        class="relative bg-siagri-gold text-siagri-dark p-2 rounded-full
                               hover:bg-yellow-400 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M8.75 13a.75.75 0 0 0-1.5 0v4a.75.75 0 0 0 1.5 0zm7.25-.75a.75.75 0 0 1 .75.75v4a.75.75 0 0 1-1.5 0v-4a.75.75 0 0 1 .75-.75m-3.25.75a.75.75 0 0 0-1.5 0v4a.75.75 0 0 0 1.5 0z"/><path fill="currentColor" fill-rule="evenodd" d="M17.274 3.473c-.476-.186-1.009-.217-1.692-.222A1.75 1.75 0 0 0 14 2.25h-4a1.75 1.75 0 0 0-1.582 1c-.684.006-1.216.037-1.692.223A3.25 3.25 0 0 0 5.3 4.563c-.367.493-.54 1.127-.776 1.998l-.628 2.303a3 3 0 0 0-1.01.828c-.622.797-.732 1.746-.621 2.834c.107 1.056.44 2.386.856 4.05l.026.107c.264 1.052.477 1.907.731 2.574c.265.696.602 1.266 1.156 1.699c.555.433 1.19.62 1.929.71c.708.084 1.59.084 2.675.084h4.724c1.085 0 1.966 0 2.675-.085c.74-.088 1.374-.276 1.928-.71c.555-.432.891-1.002 1.156-1.698c.255-.667.468-1.522.731-2.575l.027-.105c.416-1.665.748-2.995.856-4.05c.11-1.09 0-2.038-.622-2.835a3 3 0 0 0-1.009-.828l-.628-2.303c-.237-.871-.41-1.505-.776-1.999a3.25 3.25 0 0 0-1.426-1.089M7.272 4.87c.22-.086.486-.111 1.147-.118c.282.59.884.998 1.58.998h4c.698 0 1.3-.408 1.582-.998c.661.007.927.032 1.147.118c.306.12.572.323.768.587c.176.237.279.568.57 1.635l.354 1.297c-1.038-.139-2.378-.139-4.043-.139H9.622c-1.664 0-3.004 0-4.042.139l.354-1.297c.29-1.067.394-1.398.57-1.635a1.75 1.75 0 0 1 .768-.587M10 3.75a.25.25 0 0 0 0 .5h4a.25.25 0 1 0 0-.5zm-5.931 6.865c.279-.357.72-.597 1.63-.729c.931-.134 2.193-.136 3.986-.136h4.63c1.793 0 3.054.002 3.985.136c.911.132 1.352.372 1.631.73c.279.357.405.842.311 1.758c-.095.936-.399 2.16-.834 3.9c-.277 1.108-.47 1.876-.688 2.45c-.212.554-.419.847-.678 1.05c-.259.202-.594.331-1.183.402c-.61.073-1.4.074-2.544.074h-4.63c-1.144 0-1.935-.001-2.544-.074c-.59-.07-.924-.2-1.183-.402c-.26-.203-.467-.496-.678-1.05c-.218-.574-.411-1.342-.689-2.45c-.434-1.74-.739-2.964-.834-3.9c-.093-.916.033-1.402.312-1.759" clip-rule="evenodd"/></svg>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full
                                 w-5 h-5 flex items-center justify-center font-bold">
                        <?= $cart_count ?>
                    </span>
                </button>
                <?php endif; ?>

                <?= role_badge($role) ?>
                <a href="logout.php"
                   class="text-white/70 hover:text-white text-sm transition hidden md:block">Logout</a>

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
                    <a href="index.php"     class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Beranda
                    </a>
                    <a href="catalog.php"   class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Katalog
                    </a>
                    <a href="forum.php"     class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Forum Diskusi
                    </a>
                    <a href="my-orders.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Pesanan Saya
                    </a>
                    <a href="profile.php"   class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                <?php elseif ($role === 'Kiosk'): ?>
                    <a href="dashboard.php"       class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        Dashboard
                    </a>
                    <a href="manage-catalog.php"  class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Produk Saya
                    </a>
                    <a href="incoming-orders.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Pesanan
                    </a>
                    <a href="profile.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                <?php elseif ($role === 'Expert'): ?>
                    <a href="forum.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Forum
                    </a>
                    <a href="profile.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                <?php elseif ($role === 'Admin'): ?>
                    <a href="admin-dashboard.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        Dashboard
                    </a>
                    <a href="profile.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                <?php endif; ?>
                <hr class="border-white/10 my-1">
                <a href="logout.php" class="text-red-400 hover:text-red-300 text-sm py-2.5 transition flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M10 8V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2"/><path d="M15 12H3l3-3m0 6l-3-3"/></g></svg>
                    Logout
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col gap-2 pt-3">
                <a href="index.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Beranda
                </a>
                <a href="catalog.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Marketplace
                </a>
                <a href="forum.php" class="text-white/80 hover:text-white text-sm py-2.5 transition flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Forum
                </a>
                <a href="login-page.php" class="border border-white/60 text-white text-sm font-bold px-4 py-2 rounded-full hover:bg-white hover:text-siagri-dark hover:border-white transition shadow-md text-center mt-2">
                    Masuk
                </a>
                <a href="register.php" class="bg-siagri-gold text-siagri-dark text-sm font-bold px-4 py-2 rounded-full hover:bg-yellow-400 transition shadow-md text-center">
                    Daftar Gratis
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($current_page === 'catalog'): ?>
    <!-- Search bar (mobile) -->
    <div class="md:hidden px-5 pb-3">
        <form method="GET" class="flex items-center bg-white/10 rounded-full px-4 py-2 gap-2">
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
                   placeholder="Cari produk..."
                   class="bg-transparent text-white placeholder-white/60 outline-none text-sm flex-1">
        </form>
    </div>
    <?php endif; ?>

    <?php if (!empty($navbar_extra)) echo $navbar_extra; ?>
</nav>
        