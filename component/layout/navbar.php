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
    </div>
</nav>
