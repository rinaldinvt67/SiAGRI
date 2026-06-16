// Universal UI Interactions for SiAGRI
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Navbar & Scroll Effects
    const navbar = document.getElementById('main-navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                navbar.classList.add('backdrop-blur-md', 'bg-siagri-dark/95', 'shadow-md');
            } else {
                navbar.classList.remove('backdrop-blur-md', 'bg-siagri-dark/95', 'shadow-md');
            }
        });
    }

    // 2. Scroll to Top Button
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

    // 3. Auto-Dismiss Alert & Notification Boxes
    const alerts = Array.from(document.querySelectorAll(
        '.alert-error, .alert-success, .error-box, .info-box, ' +
        'div[class*="bg-green-100"], div[class*="bg-red-100"], div[class*="bg-green-50"], div[class*="bg-red-50"]'
    )).filter(alert => {
        // Exclude interactive / permanent UI components
        if (alert.classList.contains('permanent') || alert.classList.contains('no-dismiss')) {
            return false;
        }
        if (alert.closest('nav') || alert.closest('.kyc-status') || alert.closest('table') || alert.closest('form')) {
            return false;
        }
        // Exclude KYC persistence and cart indicators (already exists)
        if (alert.closest('[class*="border-l-4"]')) {
            return false;
        }
        // Exclude tags that are not plain content containers
        if (['A', 'BUTTON', 'SPAN', 'LABEL', 'INPUT'].includes(alert.tagName)) {
            return false;
        }
        // Exclude interactive layout items (like category cards, product grids, hover cards)
        if (alert.classList.contains('cursor-pointer') || 
            alert.classList.contains('hover:scale-105') || 
            alert.classList.contains('feature-card') || 
            alert.classList.contains('card-hover') || 
            alert.closest('#kategori') || 
            alert.closest('.grid') || 
            alert.closest('[class*="grid-cols"]')) {
            return false;
        }

        // Only target elements that are styled as block alerts with margin bottom, or have explicit alert class
        const isExplicitAlert = alert.classList.contains('alert-error') || 
                               alert.classList.contains('alert-success') || 
                               alert.classList.contains('error-box') || 
                               alert.classList.contains('info-box');
        const hasAlertMargin = Array.from(alert.classList).some(cls => /^mb-[456]$/.test(cls));
        
        if (!isExplicitAlert && !hasAlertMargin) {
            return false;
        }

        return true;
    });

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.8s ease, transform 0.8s ease, margin-bottom 0.8s ease, padding 0.8s ease, height 0.8s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 800);
        }, 4000);
    });
});

// 4. Mobile Navigation Toggler
window.toggleMobileNav = function() {
    const nav = document.getElementById('mobile-nav');
    if (nav) {
        nav.classList.toggle('hidden');
    }
};

// 5. Profile Tab Switcher
window.showTab = function(tab) {
    const tabs = ['info', 'password', 'kiosk', 'expert'];
    tabs.forEach(t => {
        const el = document.getElementById('tab-' + t + '-content');
        const btn = document.getElementById('tab-' + t);
        if (el) el.classList.add('hidden');
        if (btn) {
            btn.classList.remove('active', 'bg-siagri-dark', 'text-white');
            btn.classList.add('text-gray-500');
        }
    });

    const activeContent = document.getElementById('tab-' + tab + '-content');
    const activeBtn = document.getElementById('tab-' + tab);
    if (activeContent) activeContent.classList.remove('hidden');
    if (activeBtn) {
        activeBtn.classList.add('active', 'bg-siagri-dark', 'text-white');
        activeBtn.classList.remove('text-gray-500');
    }
};
