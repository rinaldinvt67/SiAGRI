<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Privacy Policy'; include '../../component/layout/head.php'; ?>
</head>
<body class="bg-siagri-light min-h-screen flex flex-col">

<?php $current_page = ''; include '../../component/layout/navbar.php'; ?>

<main class="flex-1 max-w-4xl mx-auto px-6 py-12 w-full">
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 md:p-12 fade-in">
        <!-- Header -->
        <div class="border-b border-gray-100 pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-left">
                <span class="text-xs bg-siagri-gold/20 text-siagri-dark font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                    Legal Document
                </span>
                <h1 class="text-3xl font-extrabold text-siagri-dark mt-3">Privacy Policy</h1>
                <p class="text-siagri-muted text-sm mt-1">Last Updated: May 26, 2026</p>
            </div>
            <!-- Language Switcher -->
            <div class="flex justify-center sm:justify-end shrink-0">
                <a href="../../pages/general/privacy-policy.php" 
                   class="flex items-center gap-1.5 border border-siagri-dark/20 text-siagri-dark hover:bg-siagri-dark hover:text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-300">
                    🇮🇩 Versi Indonesia
                </a>
            </div>
        </div>

        <!-- Contents -->
        <div class="prose prose-sm max-w-none text-siagri-slate space-y-6 leading-relaxed">
            <p>
                At <strong>SiAGRI</strong>, we highly value and are fully committed to protecting your personal data privacy as a Farmer, Kiosk owner, or Agricultural Expert. This Privacy Policy describes how we collect, use, store, and protect your personal information when you interact with our platform.
            </p>

            <hr class="border-gray-100 my-6">

            <!-- Section 1 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">1. Information We Collect</h2>
                <p>
                    We collect essential information to run platform operations optimally, safely, and securely:
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-siagri-muted">
                    <li><strong>Registration Information:</strong> Username, email address, password, and role selection when you sign up.</li>
                    <li><strong>Profile Information (Optional):</strong> Kiosk's full address, WhatsApp number, Expert's specialization, and profile photos.</li>
                    <li><strong>KYC Legal Documents (Kiosk):</strong> Business legal documents (NIB, SIUP, SPJB) in image or PDF format for manual verification.</li>
                    <li><strong>Order Information:</strong> Details of agricultural products ordered, transaction history, and fulfillment status.</li>
                </ul>
            </div>

            <!-- Section 2 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">2. How We Use Your Information</h2>
                <p>
                    The collected personal data is used wisely for the following key purposes:
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-siagri-muted">
                    <li>Operating, maintaining, and improving SiAGRI transaction features.</li>
                    <li>Executing manual KYC verification for Kiosks to minimize unauthorized selling of subsidized products.</li>
                    <li>Facilitating direct communication via WhatsApp API between Farmers and Kiosks for physical order confirmations.</li>
                    <li>Maintaining system security and verifying user identity during access recovery.</li>
                </ul>
            </div>

            <!-- Section 3 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">3. Data Security & Protection</h2>
                <p>
                    We implement strong data protection standards to safeguard the confidentiality of your information:
                </p>
                <p class="mt-2 text-sm text-siagri-muted">
                    All user passwords are encrypted one-way on our servers using the industry-standard <strong>Bcrypt</strong> cryptographic algorithm. Database access is strictly controlled, and KYC legal documents are stored in designated secure directories protected from unauthorized direct access.
                </p>
            </div>

            <!-- Section 4 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">4. Information Sharing with Third Parties</h2>
                <p>
                    SiAGRI **never** sells, rents, trades, or shares your personal information with third parties for commercial or promotional purposes without your explicit prior written consent.
                </p>
            </div>

            <!-- Section 5 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">5. Data Access & Deletion Rights</h2>
                <p>
                    Every user has full rights to update their personal profile data at any time via the **Profile** page. If you wish to submit a permanent account deletion request from our servers, you may contact the official SiAGRI Admin.
                </p>
            </div>
        </div>

        <!-- Back Button -->
        <div class="border-t border-gray-100 mt-10 pt-6 flex justify-center">
            <a href="javascript:history.back()" 
               class="bg-siagri-dark text-white px-6 py-2.5 rounded-xl font-semibold text-sm hover:bg-siagri-green btn-lift flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8l8 8l1.41-1.41L7.83 13H20z"/>
                </svg>
                <span>Back to Previous Page</span>
            </a>
        </div>
    </div>
</main>

<?php include '../../component/layout/footer.php'; ?>

</body>
</html>
