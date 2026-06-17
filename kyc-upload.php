<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Kiosk') {
    header("Location: login-page.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$kiosk_id = $_SESSION['kiosk_id'];
$success  = "";
$error    = "";

// Ambil info kiosk
$kiosk = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM kiosk_profiles WHERE kiosk_id = $kiosk_id"
));
$kyc_status = $kiosk['kyc_status'] ?? 'unverified';

// ─── UPLOAD DOKUMEN KYC ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['kyc_file'])) {
    $file = $_FILES['kyc_file'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Gagal upload file. Silakan coba lagi.";
    } elseif (!in_array($file['type'], $allowed)) {
        $error = "Format file tidak didukung! Gunakan JPG, PNG, WebP, atau PDF.";
    } elseif ($file['size'] > $max_size) {
        $error = "Ukuran file terlalu besar! Maksimal 5MB.";
    } else {
        // Buat folder uploads jika belum ada
        $upload_dir = 'Assets/uploads/kyc/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'kyc_' . $kiosk_id . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            mysqli_query($conn,
                "UPDATE kiosk_profiles
                 SET kyc_doc_path = '$filepath', kyc_status = 'pending'
                 WHERE kiosk_id = $kiosk_id"
            );
            $success = "Dokumen berhasil diupload! Menunggu verifikasi admin.";
            $kyc_status = 'pending';
            $kiosk['kyc_doc_path'] = $filepath;
        } else {
            $error = "Gagal menyimpan file. Silakan coba lagi.";
        }
    }
}

// Status config
$status_config = [
    'unverified' => ['color' => 'blue',   'icon' => 'ℹ️',  'label' => 'Belum Upload', 'desc' => 'Upload dokumen legalitas untuk mulai berjualan.'],
    'pending'    => ['color' => 'yellow', 'icon' => '⏳', 'label' => 'Menunggu Verifikasi', 'desc' => 'Dokumen sedang ditinjau admin. Harap tunggu 1×24 jam.'],
    'verified'   => ['color' => 'green',  'icon' => '✅', 'label' => 'Terverifikasi', 'desc' => 'Kios kamu sudah resmi terverifikasi!'],
    'rejected'   => ['color' => 'red',    'icon' => '❌', 'label' => 'Ditolak', 'desc' => 'Alasan: ' . htmlspecialchars($kiosk['kyc_note'] ?? '-') . '. Silakan upload ulang.'],
];
$sc = $status_config[$kyc_status] ?? $status_config['unverified'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $page_title = 'Upload KYC'; include 'komponen/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'kyc-upload'; include 'komponen/layout/navbar.php'; ?>

<div class="max-w-2xl mx-auto px-4 py-10">

    <h1 class="text-2xl font-bold text-siagri-dark mb-2">Verifikasi Kios (KYC)</h1>
    <p class="text-gray-400 text-sm mb-8">Upload dokumen legalitas untuk verifikasi kios kamu</p>

    <!-- Notifikasi -->
    <?php if ($success): ?>
    <div class="mb-5 p-4 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-5 p-4 bg-red-50 text-red-800 rounded-xl border border-red-200 text-sm">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Status Card -->
    <div class="bg-<?= $sc['color'] ?>-50 border border-<?= $sc['color'] ?>-200 rounded-2xl p-5 mb-8">
        <div class="flex items-center gap-3 mb-2">
            <span class="text-2xl"><?= $sc['icon'] ?></span>
            <h3 class="font-bold text-<?= $sc['color'] ?>-800"><?= $sc['label'] ?></h3>
        </div>
        <p class="text-<?= $sc['color'] ?>-700 text-sm ml-9"><?= $sc['desc'] ?></p>
    </div>

    <!-- Upload Form -->
    <?php if ($kyc_status === 'unverified' || $kyc_status === 'rejected'): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-siagri-dark mb-4">Upload Dokumen</h3>
        <p class="text-sm text-gray-500 mb-5">
            Upload foto/scan dokumen legalitas usaha (SIUP, NIB, atau KTP pemilik).
            Format: JPG, PNG, WebP, atau PDF. Maks 5MB.
        </p>

        <form method="POST" enctype="multipart/form-data">
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center
                        hover:border-siagri-green transition cursor-pointer mb-5"
                 onclick="document.getElementById('kyc_file').click()"
                 id="drop-zone">
                <div class="text-4xl mb-3">📄</div>
                <p class="text-sm text-gray-500 mb-1" id="file-label">
                    Klik untuk pilih file atau drag & drop di sini
                </p>
                <p class="text-xs text-gray-400">JPG, PNG, WebP, PDF — Maks 5MB</p>
                <input type="file" name="kyc_file" id="kyc_file" accept=".jpg,.jpeg,.png,.webp,.pdf"
                       required class="hidden"
                       onchange="document.getElementById('file-label').textContent = this.files[0]?.name || 'Pilih file...'">
            </div>

            <button type="submit"
                    class="w-full bg-siagri-dark text-white py-3 rounded-xl font-semibold
                           hover:bg-siagri-green transition btn-lift">
                Upload & Kirim untuk Verifikasi
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Dokumen yang sudah diupload -->
    <?php if (!empty($kiosk['kyc_doc_path'])): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
        <h3 class="text-sm font-bold text-gray-700 mb-3">Dokumen Terakhir</h3>
        <?php
        $ext = strtolower(pathinfo($kiosk['kyc_doc_path'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])): ?>
        <img src="<?= htmlspecialchars($kiosk['kyc_doc_path']) ?>"
             alt="Dokumen KYC"
             class="rounded-xl max-w-full h-auto border border-gray-200">
        <?php else: ?>
        <a href="<?= htmlspecialchars($kiosk['kyc_doc_path']) ?>" target="_blank"
           class="inline-flex items-center gap-2 text-siagri-dark font-medium text-sm
                  hover:text-siagri-green transition">
            Lihat Dokumen PDF
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php include 'component/layout/footer.php'; ?>

</body>
</html>
