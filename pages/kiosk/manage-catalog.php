<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Kiosk') {
    header("Location: ../../pages/auth/login.php");
    exit;
}

$kiosk_id = $_SESSION['kiosk_id'];
$success  = "";
$error    = "";

// Cek KYC
$kiosk_info = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT kyc_status FROM kiosk_profiles WHERE kiosk_id = $kiosk_id"
));

// HAPUS PRODUK
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    mysqli_query($conn,
        "DELETE FROM products
         WHERE product_id = $del_id AND kiosk_id = $kiosk_id"
    );
    header("Location: manage-catalog.php?success=deleted");
    exit;
}

// TAMBAH / EDIT PRODUK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($kiosk_info['kyc_status'] !== 'verified') {
        $error = "Akun belum terverifikasi. Upload dokumen KYC terlebih dahulu.";

    } elseif ($_POST['action'] === 'add') {
        $name        = mysqli_real_escape_string($conn, trim($_POST['product_name']));
        $category_id = (int)$_POST['category_id'];
        $price       = (float)$_POST['selling_price'];
        $stock       = (int)$_POST['stock'];
        $subsidized  = $_POST['is_subsidized'];
        $het         = (float)($_POST['het_price'] ?? 0);
        $desc        = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));

        $image_path = null;
        if (!empty($_FILES['product_image']['name'])) {
            $upload_dir = '../../assets/uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext     = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed) && $_FILES['product_image']['size'] <= 2*1024*1024) {
                $filename = 'prod_' . $kiosk_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $filename);
                $image_path = $upload_dir . $filename;
            } else {
                $error = "Format gambar harus JPG/PNG/WEBP, maks 2MB.";
            }
        }

        if (!$error) {
            if (empty($name) || $price <= 0) {
                $error = "Nama produk dan harga wajib diisi!";
            } else {
                $img_val = $image_path ? "'$image_path'" : "NULL";
                mysqli_query($conn,
                    "INSERT INTO products
                        (kiosk_id, category_id, product_name, description,
                         product_image, selling_price, stock, is_subsidized, het_price)
                     VALUES
                        ($kiosk_id, $category_id, '$name', '$desc',
                         $img_val, $price, $stock, '$subsidized', $het)"
                );
                header("Location: manage-catalog.php?success=added");
                exit;
            }
        }

    } elseif ($_POST['action'] === 'edit') {
        $prod_id     = (int)$_POST['product_id'];
        $name        = mysqli_real_escape_string($conn, trim($_POST['product_name']));
        $category_id = (int)$_POST['category_id'];
        $price       = (float)$_POST['selling_price'];
        $stock       = (int)$_POST['stock'];
        $subsidized  = $_POST['is_subsidized'];
        $het         = (float)($_POST['het_price'] ?? 0);
        $desc        = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));

        // Handle image upload for edit
        $image_path = null;
        if (!empty($_FILES['product_image']['name'])) {
            $upload_dir = '../../assets/uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext     = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed) && $_FILES['product_image']['size'] <= 2*1024*1024) {
                $filename = 'prod_' . $kiosk_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $filename);
                $image_path = $upload_dir . $filename;
            } else {
                $error = "Format gambar harus JPG/PNG/WEBP, maks 2MB.";
            }
        }

        if (!$error) {
            if (empty($name) || $price <= 0) {
                $error = "Nama produk dan harga wajib diisi!";
            } else {
                $img_sql = "";
                if ($image_path) {
                    $img_sql = ", product_image = '$image_path'";
                }
                
                mysqli_query($conn,
                    "UPDATE products
                     SET product_name  = '$name',
                         category_id   = $category_id,
                         selling_price = $price,
                         stock         = $stock,
                         is_subsidized = '$subsidized',
                         het_price     = $het,
                         description   = '$desc'
                         $img_sql
                     WHERE product_id = $prod_id AND kiosk_id = $kiosk_id"
                );
                header("Location: manage-catalog.php?success=updated");
                exit;
            }
        }
    }
}

// Pesan sukses
if (isset($_GET['success'])) {
    $msgs = [
        'added'   => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Produk berhasil ditambahkan!',
        'updated' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Produk berhasil diperbarui!',
        'deleted' => '<svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Produk berhasil dihapus.',
    ];
    $success = $msgs[$_GET['success']] ?? '';
}

// Ambil data
$products = mysqli_query($conn,
    "SELECT p.*, c.category_name
     FROM products p
     JOIN categories c ON p.category_id = c.category_id
     WHERE p.kiosk_id = $kiosk_id
     ORDER BY p.created_at DESC"
);

$categories = mysqli_query($conn,
    "SELECT * FROM categories ORDER BY category_name"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php
    $page_title = 'Kelola Katalog';
    $extra_head = '';
    include '../../component/layout/head.php';
    ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'manage-catalog'; include '../../component/layout/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-5 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-siagri-dark">Kelola Katalog Produk</h1>
            <p class="text-gray-400 text-sm mt-0.5">
                Tambah, edit, atau hapus produk yang kamu jual
            </p>
        </div>
        <?php if ($kiosk_info['kyc_status'] === 'verified'): ?>
        <button onclick="toggleModal('modal-add')"
                class="bg-siagri-dark text-white px-5 py-2.5 rounded-xl text-sm
                       font-semibold hover:bg-siagri-green transition">
            + Tambah Produk
        </button>
        <?php endif; ?>
    </div>

    <!-- Notifikasi -->
    <?php if ($success): ?>
    <div class="mb-5 p-3 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        <?= $success ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-5 p-3 bg-red-50 text-red-800 rounded-xl border border-red-200 text-sm">
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Banner KYC -->
    <?php if ($kiosk_info['kyc_status'] !== 'verified'): ?>
    <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-xl text-yellow-800 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0 text-yellow-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <span>Akun kamu belum terverifikasi. Kamu tidak bisa menambah produk sampai KYC disetujui Admin.</span>
    </div>
    <?php endif; ?>

    <!-- Tabel Produk -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <?php if (mysqli_num_rows($products) === 0): ?>
        <div class="text-center py-16 text-gray-400">
            <p class="text-5xl mb-4"><svg class="inline-block w-10 h-10 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m17.578 4.432l-2-1.05C13.822 2.461 12.944 2 12 2s-1.822.46-3.578 1.382l-.321.169l8.923 5.099l4.016-2.01c-.646-.732-1.688-1.279-3.462-2.21m4.17 3.534l-3.998 2V13a.75.75 0 0 1-1.5 0v-2.286l-3.5 1.75v9.44c.718-.179 1.535-.607 2.828-1.286l2-1.05c2.151-1.129 3.227-1.693 3.825-2.708c.597-1.014.597-2.277.597-4.8v-.117c0-1.893 0-3.076-.252-3.978M11.25 21.904v-9.44l-8.998-4.5C2 8.866 2 10.05 2 11.941v.117c0 2.525 0 3.788.597 4.802c.598 1.015 1.674 1.58 3.825 2.709l2 1.049c1.293.679 2.11 1.107 2.828 1.286M2.96 6.641l9.04 4.52l3.411-1.705l-8.886-5.078l-.103.054c-1.773.93-2.816 1.477-3.462 2.21"/></svg></p>
            <p class="font-medium text-gray-600">Belum ada produk</p>
            <p class="text-sm mt-1">
                <?= $kiosk_info['kyc_status'] === 'verified'
                    ? 'Klik "Tambah Produk" untuk mulai berjualan'
                    : 'Selesaikan verifikasi KYC terlebih dahulu' ?>
            </p>
        </div>

        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-siagri-dark text-white">
                        <th class="px-5 py-3 text-left">Produk</th>
                        <th class="px-5 py-3 text-left">Kategori</th>
                        <th class="px-5 py-3 text-right">Harga Jual</th>
                        <th class="px-5 py-3 text-right">HET</th>
                        <th class="px-5 py-3 text-center">Stok</th>
                        <th class="px-5 py-3 text-center">Subsidi</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($products)):
                    $violasi = $row['het_price'] > 0 && $row['selling_price'] > $row['het_price'];
                ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <?php if ($row['product_image']): ?>
                            <img src="<?= htmlspecialchars($row['product_image']) ?>"
                                 class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                            <?php else: ?>
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center
                                        justify-center text-xl flex-shrink-0"><svg class="inline-block w-4 h-4 mr-1.5 align-middle fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m17.578 4.432l-2-1.05C13.822 2.461 12.944 2 12 2s-1.822.46-3.578 1.382l-.321.169l8.923 5.099l4.016-2.01c-.646-.732-1.688-1.279-3.462-2.21m4.17 3.534l-3.998 2V13a.75.75 0 0 1-1.5 0v-2.286l-3.5 1.75v9.44c.718-.179 1.535-.607 2.828-1.286l2-1.05c2.151-1.129 3.227-1.693 3.825-2.708c.597-1.014.597-2.277.597-4.8v-.117c0-1.893 0-3.076-.252-3.978M11.25 21.904v-9.44l-8.998-4.5C2 8.866 2 10.05 2 11.941v.117c0 2.525 0 3.788.597 4.802c.598 1.015 1.674 1.58 3.825 2.709l2 1.049c1.293.679 2.11 1.107 2.828 1.286M2.96 6.641l9.04 4.52l3.411-1.705l-8.886-5.078l-.103.054c-1.773.93-2.816 1.477-3.462 2.21"/></svg></div>
                            <?php endif; ?>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    <?= htmlspecialchars($row['product_name']) ?>
                                </p>
                                <?php if ($row['description']): ?>
                                <p class="text-xs text-gray-400 truncate max-w-xs">
                                    <?= htmlspecialchars($row['description']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-gray-500">
                        <?= htmlspecialchars($row['category_name']) ?>
                    </td>
                    <td class="px-5 py-4 text-right font-semibold">
                        Rp <?= number_format($row['selling_price'], 0, ',', '.') ?>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <?php if ($row['het_price'] > 0): ?>
                        <p class="text-gray-500">
                            Rp <?= number_format($row['het_price'], 0, ',', '.') ?>
                        </p>
                        <span class="text-xs px-2 py-0.5 rounded-full
                            <?= $violasi ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' ?>">
                            <?= $violasi ? '<svg class="inline-block w-2.5 h-2.5 mr-1 align-middle fill-current text-red-500" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" /></svg> Melebihi HET' : '<svg class="inline-block w-2.5 h-2.5 mr-1 align-middle fill-current text-green-500" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" /></svg> Aman' ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-300">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-bold <?= $row['stock'] <= 5 ? 'text-red-500' : 'text-gray-700' ?>">
                            <?= $row['stock'] ?>
                        </span>
                        <?php if ($row['stock'] == 0): ?>
                        <p class="text-xs text-red-500">Habis</p>
                        <?php elseif ($row['stock'] <= 5): ?>
                        <p class="text-xs text-yellow-500">Hampir habis</p>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <?php if ($row['is_subsidized'] === 'Yes'): ?>
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">
                            <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Ya
                        </span>
                        <?php else: ?>
                        <span class="text-gray-300 text-xs">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='openEdit(
                                        <?= $row["product_id"] ?>,
                                        <?= htmlspecialchars(json_encode($row["product_name"]), ENT_QUOTES) ?>,
                                        <?= $row["category_id"] ?>,
                                        <?= $row["selling_price"] ?>,
                                        <?= $row["stock"] ?>,
                                        "<?= $row["is_subsidized"] ?>",
                                        <?= $row["het_price"] ?>,
                                        <?= htmlspecialchars(json_encode($row["description"] ?? ""), ENT_QUOTES) ?>
                                    )'
                                    class="bg-blue-50 text-blue-600 hover:bg-blue-100
                                           text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                Edit
                            </button>
                            <a href="?delete_id=<?= $row['product_id'] ?>"
                               onclick="return confirm('Hapus produk ini?')"
                               class="bg-red-50 text-red-600 hover:bg-red-100
                                      text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modal-add"
     class="modal fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white">
            <h2 class="font-bold text-siagri-dark text-lg">Tambah Produk Baru</h2>
            <button onclick="toggleModal('modal-add')"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk*</label>
                <input type="text" name="product_name" required placeholder="contoh: Urea 50kg"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-siagri-dark">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori*</label>
                <select name="category_id" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                               focus:outline-none focus:border-siagri-dark">
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    mysqli_data_seek($categories, 0);
                    while ($c = mysqli_fetch_assoc($categories)):
                    ?>
                    <option value="<?= $c['category_id'] ?>">
                        <?= htmlspecialchars($c['category_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (Rp)*</label>
                    <input type="number" name="selling_price" required min="0" step="500"
                           placeholder="87000"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-siagri-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok*</label>
                    <input type="number" name="stock" required min="0" placeholder="20"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-siagri-dark">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bersubsidi?</label>
                    <select name="is_subsidized"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                   focus:outline-none focus:border-siagri-dark">
                        <option value="No">Tidak</option>
                        <option value="Yes">Ya (ada HET)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HET (Rp)</label>
                    <input type="number" name="het_price" min="0" step="500" value="0"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-siagri-dark">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Jelaskan produkmu..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                 focus:outline-none focus:border-siagri-dark resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Foto Produk (JPG/PNG, maks 2MB)
                </label>
                <input type="file" name="product_image" accept="image/*"
                       class="w-full text-sm text-gray-500
                              file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:bg-siagri-dark file:text-white file:cursor-pointer
                              hover:file:bg-siagri-green">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-siagri-dark text-white font-semibold py-2.5
                               rounded-xl hover:bg-siagri-green transition text-sm">
                    + Tambah Produk
                </button>
                <button type="button" onclick="toggleModal('modal-add')"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5
                               rounded-xl hover:bg-gray-50 transition text-sm">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit"
     class="modal fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white">
            <h2 class="font-bold text-siagri-dark text-lg">Edit Produk</h2>
            <button onclick="toggleModal('modal-edit')"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" id="edit_product_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk*</label>
                <input type="text" name="product_name" id="edit_product_name" required placeholder="contoh: Urea 50kg"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-siagri-dark">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori*</label>
                <select name="category_id" id="edit_category_id" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                               focus:outline-none focus:border-siagri-dark">
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    mysqli_data_seek($categories, 0);
                    while ($c = mysqli_fetch_assoc($categories)):
                    ?>
                    <option value="<?= $c['category_id'] ?>">
                        <?= htmlspecialchars($c['category_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (Rp)*</label>
                    <input type="number" name="selling_price" id="edit_price" required min="0" step="500"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-siagri-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok*</label>
                    <input type="number" name="stock" id="edit_stock" required min="0"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-siagri-dark">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bersubsidi?</label>
                    <select name="is_subsidized" id="edit_is_subsidized"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                   focus:outline-none focus:border-siagri-dark">
                        <option value="No">Tidak</option>
                        <option value="Yes">Ya (ada HET)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HET (Rp)</label>
                    <input type="number" name="het_price" id="edit_het" min="0" step="500"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-siagri-dark">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" id="edit_desc" rows="2" placeholder="Jelaskan produkmu..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                 focus:outline-none focus:border-siagri-dark resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ganti Foto Produk (Biarkan kosong jika tidak ingin diubah)
                </label>
                <input type="file" name="product_image" accept="image/*"
                       class="w-full text-sm text-gray-500
                              file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:bg-siagri-dark file:text-white file:cursor-pointer
                              hover:file:bg-siagri-green">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-siagri-dark text-white font-semibold py-2.5
                               rounded-xl hover:bg-siagri-green transition text-sm">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="toggleModal('modal-edit')"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5
                               rounded-xl hover:bg-gray-50 transition text-sm">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/catalog.js"></script>
<?php include '../../component/layout/footer.php'; ?>

</body>
</html>