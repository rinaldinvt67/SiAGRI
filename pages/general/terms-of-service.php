<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Terms & Conditions of Service'; include '../../component/layout/head.php'; ?>
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
                <h1 class="text-3xl font-extrabold text-siagri-dark mt-3">Terms of Service</h1>
                <p class="text-siagri-muted text-sm mt-1">Last Updated: May 26, 2026</p>
            </div>
            <!-- Language Switcher -->
            <div class="flex justify-center sm:justify-end shrink-0">
                <a href="../../pages/general/terms-of-service-id.php" 
                   class="flex items-center gap-1.5 border border-siagri-dark/20 text-siagri-dark hover:bg-siagri-dark hover:text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-300">
                    🇮🇩 Versi Indonesia
                </a>
            </div>
        </div>

        <!-- Contents -->
        <div class="prose prose-sm max-w-none text-siagri-slate space-y-6 leading-relaxed">
            <p>
                Welcome to <strong>SiAGRI</strong> (Agricultural Information System). By accessing or using our platform, you agree to be bound by the following Terms and Conditions of Service. Please read this document carefully before using our services.
            </p>

            <hr class="border-gray-100 my-6">

            <!-- Section 1 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">1. General Provisions & Membership</h2>
                <p>
                    SiAGRI provides a localized agricultural e-marketplace platform and discussion board that connects Farmers, registered Kiosks, and Agricultural Experts. Users are required to register honestly using valid personal information.
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-siagri-muted">
                    <li><strong>Farmer:</strong> Entitled to place orders for agricultural products from official registered Kiosks.</li>
                    <li><strong>Kiosk:</strong> Mandatory compliance with kiosk verification rules and government-regulated Maximum Retail Price (HET) for certain products.</li>
                    <li><strong>Expert:</strong> Handpicked and registered exclusively by the Admin to provide trusted consultation on the discussion board.</li>
                </ul>
            </div>

            <!-- Section 2 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">2. Kiosk Verification Process (KYC)</h2>
                <p>
                    To ensure transaction safety and platform integrity, every registered Kiosk is required to upload valid business legal documents (such as NIB, SIUP, or official SPJB documents).
                </p>
                <p class="mt-2 text-sm text-siagri-muted">
                    SiAGRI's Admin reserves the right to review, approve, or reject these documents to filter out unofficial agents and protect Farmers from counterfeit agricultural products.
                </p>
            </div>

            <!-- Section 3 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">3. Click & Collect (Self-Pickup) Transaction System</h2>
                <p>
                    SiAGRI facilitates the <em>Click & Collect</em> purchasing method to overcome logistical constraints of heavy agricultural goods (such as heavy bags of fertilizer or large farming equipment).
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-siagri-muted">
                    <li>Product reservations are binding within a 24-hour window.</li>
                    <li>Buyers (Farmers) must personally visit the Kiosk's physical location to pick up their orders.</li>
                    <li>Payments are processed in cash directly at the Kiosk counter during product handover.</li>
                    <li>If orders are not picked up within 24 hours, they will be automatically cancelled to maintain Kiosk inventory availability.</li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">4. Maximum Retail Price (HET) Policy</h2>
                <p>
                    For government-subsidized fertilizers, Kiosks are strictly prohibited from selling products above the official government-established HET. Violations of this policy will result in the immediate and unilateral revocation of the "Verified Kiosk" badge by the Admin.
                </p>
            </div>

            <!-- Section 5 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">5. Limitation of Liability</h2>
                <p>
                    SiAGRI acts as a facilitator bridging agricultural transactions and education. We are not directly liable for the physical quality of the purchased agricultural goods or personal disputes between Farmers and Kiosks outside of our direct platform mechanism.
                </p>
            </div>

            <!-- Section 6 -->
            <div>
                <h2 class="text-lg font-bold text-siagri-dark mb-2">6. Changes to Terms & Conditions</h2>
                <p>
                    SiAGRI reserves the right to change, modify, or update these Terms and Conditions at any time without prior written notice to align with agricultural regulations and legal frameworks in Indonesia.
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
