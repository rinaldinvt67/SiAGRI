<?php
session_start();
require_once 'koneksi.php'; // Ganti jadi config.php kalau kamu sudah me-rename filenya

// 1. Keamanan Lapis Ganda: Hanya Kiosk yang boleh masuk
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Kiosk') {
    header("Location: login-page.php");
    exit;
}

$kiosk_id = $_SESSION['kiosk_id'];

// 2. Fitur DELETE (Hapus Produk)
if (isset($_GET['delete_id'])) {
    $id_to_delete = $_GET['delete_id'];
    // Hapus dari database (Pastikan hanya milik Kios ini yang bisa dihapus)
    $delete_query = "DELETE FROM kiosk_catalogs WHERE catalog_id = '$id_to_delete' AND kiosk_id = '$kiosk_id'";
    mysqli_query($conn, $delete_query);
    
    // Refresh halaman agar data yang dihapus hilang dari tabel
    header("Location: manage_catalog.php");
    exit;
}

// 3. Fitur READ (Mengambil data jualan Kios ini dari Database)
$query = "SELECT kc.catalog_id, p.product_name, p.category, p.retail_ceiling_price, kc.actual_selling_price, kc.stock_status 
          FROM kiosk_catalogs kc
          JOIN products p ON kc.product_id = p.product_id
          WHERE kc.kiosk_id = '$kiosk_id'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Catalog | SiAGRI</title>
    <link rel="stylesheet" href="Assets/css/style.css">
    <style>
        .table-container { padding: 20px; font-family: sans-serif; background: #fff; border-radius: 10px; margin: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2e7d32; color: white; }
        .btn-delete { background: #d32f2f; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .btn-back { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #2e7d32; font-weight: bold; }
        .badge-safe { background: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 10px; font-size: 12px; }
        .badge-warning { background: #ffebee; color: #d32f2f; padding: 3px 8px; border-radius: 10px; font-size: 12px; }
    </style>
</head>
<body>

<div class="table-container">
    <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
    <h2>My Product Catalog</h2>
    
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Category</th>
                <th>My Price (Rp)</th>
                <th>HET (Max Price)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) { 
                    // Logika Validasi HET untuk tampilan Kios
                    $is_violation = ($row['retail_ceiling_price'] > 0 && $row['actual_selling_price'] > $row['retail_ceiling_price']);
            ?>
                <tr>
                    <td><strong><?php echo $row['product_name']; ?></strong></td>
                    <td><?php echo $row['category']; ?></td>
                    <td>Rp <?php echo number_format($row['actual_selling_price'], 0, ',', '.'); ?></td>
                    <td>
                        <?php 
                        if ($row['retail_ceiling_price'] > 0) {
                            echo "Rp " . number_format($row['retail_ceiling_price'], 0, ',', '.');
                            if ($is_violation) echo "<br><span class='badge-warning'>Violation</span>";
                            else echo "<br><span class='badge-safe'>Safe</span>";
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                    <td><?php echo $row['stock_status']; ?></td>
                    <td>
                        <a href="manage_catalog.php?delete_id=<?php echo $row['catalog_id']; ?>" 
                           class="btn-delete" 
                           onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>Your catalog is empty. Add some products!</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>