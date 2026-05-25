<?php
/**
 * footer.php — Komponen Footer SiAGRI
 * 
 * Cara pakai:
 * <?php include 'component/layout/footer.php'; ?>
 * 
 * Catatan: TIDAK termasuk </body></html> — halaman pemanggil harus menutup sendiri.
 */
?>

<footer class="bg-siagri-dark text-white pt-16 pb-8 relative mt-16">
    <!-- Aksen garis emas di atas -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-siagri-gold/0 via-siagri-gold to-siagri-gold/0"></div>

    <div class="max-w-7xl mx-auto px-6 md:px-12 lg:px-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

            <!-- Brand -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <img src="Assets/images/LOGO.png" alt="SiAGRI" class="h-8 w-auto"
                         onerror="this.style.display='none'"
                         style="filter: brightness(0) invert(1); opacity: 0.85;">
                </div>
                <p class="text-white/50 text-sm leading-relaxed">
                    Platform digital untuk petani modern.<br>
                    Menghubungkan petani dengan supplier<br>
                    dalam satu ekosistem.
                </p>
            </div>

            <!-- Navigasi -->
            <div>
                <h3 class="text-sm font-semibold mb-5 uppercase tracking-wider text-siagri-gold">Navigasi</h3>
                <ul class="flex flex-col space-y-3">
                    <li><a href="index.php" class="text-white/50 hover:text-white transition duration-300 text-sm">Beranda</a></li>
                    <li><a href="catalog.php" class="text-white/50 hover:text-white transition duration-300 text-sm">Katalog Produk</a></li>
                    <li><a href="forum.php" class="text-white/50 hover:text-white transition duration-300 text-sm">Forum Diskusi</a></li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-sm font-semibold mb-5 uppercase tracking-wider text-siagri-gold">Tentang</h3>
                <ul class="flex flex-col space-y-3">
                    <li><a href="#" class="text-white/50 hover:text-white transition duration-300 text-sm">Tentang Kami</a></li>
                    <li><a href="#" class="text-white/50 hover:text-white transition duration-300 text-sm">Pusat Bantuan</a></li>
                    <li><a href="#" class="text-white/50 hover:text-white transition duration-300 text-sm">Kontak</a></li>
                </ul>
            </div>

            <!-- Kebijakan -->
            <div>
                <h3 class="text-sm font-semibold mb-5 uppercase tracking-wider text-siagri-gold">Kebijakan</h3>
                <ul class="flex flex-col space-y-3">
                    <li><a href="terms-of-service.php" class="text-white/50 hover:text-white transition duration-300 text-sm">Syarat Layanan</a></li>
                    <li><a href="privacy-policy.php" class="text-white/50 hover:text-white transition duration-300 text-sm">Kebijakan Privasi</a></li>
                    <li><a href="#" class="text-white/50 hover:text-white transition duration-300 text-sm">Pemberitahuan</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="border-t border-white/10 mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-white/30 text-sm">
                &copy; 2026 SiAGRI — All rights reserved
            </p>
            <div class="flex items-center gap-4">
                <span class="text-white/30 text-xs">Kelompok 11</span>
            </div>
        </div>
    </div>

    <!-- Scroll to top -->
    <button onclick="window.scrollTo({top:0, behavior:'smooth'})"
            class="fixed bottom-6 right-6 bg-siagri-dark border border-white/20 text-white/60
                   hover:text-white hover:bg-siagri-green rounded-full w-10 h-10
                   flex items-center justify-center shadow-lg transition-all duration-300 z-50
                   opacity-0 translate-y-4 pointer-events-none"
            id="scroll-top-btn">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
        </svg>
    </button>

    <script>
    // Show/hide scroll-to-top button
    const scrollBtn = document.getElementById('scroll-top-btn');
    if (scrollBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                scrollBtn.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
                scrollBtn.classList.add('opacity-100', 'translate-y-0');
            } else {
                scrollBtn.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
                scrollBtn.classList.remove('opacity-100', 'translate-y-0');
            }
        });
    }
    </script>
</footer>
