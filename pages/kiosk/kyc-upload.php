<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Kiosk') {
    header("Location: ../../pages/auth/login.php");
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

// UPLOAD DOKUMEN KYC
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
        $upload_dir = '../../Assets/uploads/kyc/';
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
    'unverified' => ['color' => 'blue',   'icon' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-blue-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-10 5.75a.75.75 0 0 0 .75-.75v-6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75M12 7a1 1 0 1 1 0 2a1 1 0 0 1 0-2" clip-rule="evenodd"/></svg>',  'label' => 'Belum Upload', 'desc' => 'Upload dokumen legalitas untuk mulai berjualan.'],
    'pending'    => ['color' => 'yellow', 'icon' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-yellow-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><defs><mask id="SVGnNgsclOC"><g fill="none"><path fill="#fff" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10"/><path fill="#000" fill-rule="evenodd" d="M12 7.25a.75.75 0 0 1 .75.75v3.69l2.28 2.28a.75.75 0 1 1-1.06 1.06l-2.5-2.5a.75.75 0 0 1-.22-.53V8a.75.75 0 0 1 .75-.75" clip-rule="evenodd"/></g></mask></defs><path fill="currentColor" d="M0 0h24v24H0z" mask="url(#SVGnNgsclOC)"/></svg>', 'label' => 'Menunggu Verifikasi', 'desc' => 'Dokumen sedang ditinjau admin. Harap tunggu 1×24 jam.'],
    'verified'   => ['color' => 'green',  'icon' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg>', 'label' => 'Terverifikasi', 'desc' => 'Kios kamu sudah resmi terverifikasi!'],
    'rejected'   => ['color' => 'red',    'icon' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg>', 'label' => 'Ditolak', 'desc' => 'Alasan: ' . htmlspecialchars($kiosk['kyc_note'] ?? '-') . '. Silakan upload ulang.'],
];
$sc = $status_config[$kyc_status] ?? $status_config['unverified'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="../../assets/images/ICON.png">
    <?php $page_title = 'Upload KYC'; include '../../component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'kyc-upload'; include '../../component/layout/navbar.php'; ?>

<div class="max-w-2xl mx-auto px-4 py-10">

    <!-- Breadcrumbs -->
    <nav class="flex mb-5" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs md:text-sm text-gray-500">
            <li class="inline-flex items-center">
                <a href="../../pages/kiosk/dashboard.php" class="inline-flex items-center hover:text-siagri-green font-medium text-gray-400 hover:scale-[1.02] transition">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 md:ml-2 font-semibold text-siagri-dark">
                        Verifikasi Kios (KYC)
                    </span>
                </div>
            </li>
        </ol>
    </nav>

    <h1 class="text-2xl font-bold text-siagri-dark mb-2">Verifikasi Kios (KYC)</h1>
    <p class="text-gray-400 text-sm mb-8">Upload dokumen legalitas untuk verifikasi kios kamu</p>

    <!-- Notifikasi -->
    <?php if ($success): ?>
    <div class="mb-5 p-4 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-5 p-4 bg-red-50 text-red-800 rounded-xl border border-red-200 text-sm">
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> <?= htmlspecialchars($error) ?>
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
                <div class="text-4xl mb-3"><svg class="inline-block w-12 h-12 mx-auto mb-3 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M4.172 3.172C3 4.343 3 6.229 3 10v4c0 3.771 0 5.657 1.172 6.828S7.229 22 11 22h2c3.771 0 5.657 0 6.828-1.172S21 17.771 21 14v-4c0-3.771 0-5.657-1.172-6.828S16.771 2 13 2h-2C7.229 2 5.343 2 4.172 3.172M8 9.25a.75.75 0 0 0 0 1.5h8a.75.75 0 0 0 0-1.5zm0 4a.75.75 0 0 0 0 1.5h5a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/></svg></div>
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
            <svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M15.729 3.884c1.434-1.44 3.532-1.47 4.693-.304c1.164 1.168 1.133 3.28-.303 4.72l-2.423 2.433a.75.75 0 0 0 1.062 1.059l2.424-2.433c1.911-1.919 2.151-4.982.303-6.838c-1.85-1.857-4.907-1.615-6.82.304L9.819 7.692c-1.911 1.919-2.151 4.982-.303 6.837a.75.75 0 1 0 1.063-1.058c-1.164-1.168-1.132-3.28.303-4.72z"/><path fill="currentColor" d="M14.485 9.47a.75.75 0 0 0-1.063 1.06c1.164 1.168 1.133 3.279-.303 4.72l-4.847 4.866c-1.435 1.44-3.533 1.47-4.694.304c-1.164-1.168-1.132-3.28.303-4.72l2.424-2.433a.75.75 0 0 0-1.063-1.059l-2.424 2.433c-1.911 1.92-2.151 4.982-.303 6.838c1.85 1.858 4.907 1.615 6.82-.304l4.847-4.867c1.911-1.918 2.151-4.982.303-6.837"/></svg> Lihat Dokumen PDF
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php include '../../component/layout/footer.php'; ?>

</body>
</html>
