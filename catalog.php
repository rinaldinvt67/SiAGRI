<?php
session_start();
require_once 'koneksi.php';

// 1. KEAMANAN: Cek apakah user sudah login. Kalau belum, tendang balik ke login!
if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit;
}

// 2. AMBIL DATA DARI DATABASE (Menggabungkan 3 tabel sekaligus!)
$sql_catalog = "SELECT 
                    kc.actual_selling_price, kc.stock_status,
                    p.product_name, p.category, p.retail_ceiling_price, p.product_image,
                    kp.store_name, kp.whatsapp_number 
                FROM kiosk_catalogs kc
                JOIN products p ON kc.product_id = p.product_id
                JOIN kiosk_profiles kp ON kc.kiosk_id = kp.kiosk_id";

$result = mysqli_query($conn, $sql_catalog);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Saprotan | SiAGRI</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #2E7D32; color: white; padding: 15px 30px; border-radius: 8px; margin-bottom: 20px; }
        .header a { color: white; text-decoration: none; font-weight: bold; background: #1b5e20; padding: 8px 15px; border-radius: 5px; }
        .grid-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .card { background: white; border-radius: 10px; padding: 20px; width: 250px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .badge-kategori { background: #e8f5e9; color: #2e7d32; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .harga { font-size: 20px; font-weight: bold; color: #333; margin: 10px 0; }
        .het-aman { background: #4CAF50; color: white; padding: 5px; text-align: center; border-radius: 5px; font-size: 13px; margin-bottom: 10px;}
        .het-bahaya { background: #F44336; color: white; padding: 5px; text-align: center; border-radius: 5px; font-size: 13px; margin-bottom: 10px;}
        .btn-wa { display: block; background: #25D366; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 15px;}
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h2 style="margin: 0;">🌾 SiAGRI Katalog</h2>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Selamat datang, <b><?php echo $_SESSION['username']; ?></b> (<?php echo $_SESSION['role']; ?>)</p>
        </div>
        <a href="logout.php">Keluar (Logout)</a>
    </div>

    <h3 style="text-align: center; color: #333;">Pupuk & Alat Tani Tersedia</h3>

    <div class="grid-container">
        <?php 
        // Mengecek apakah ada barang yang dijual
        if (mysqli_num_rows($result) > 0) {
            // Jika ada barang, tampilkan dalam bentuk kartu (Card)
            while ($row = mysqli_fetch_assoc($result)) { 
        ?>
                <div class="card">
                    <span class="badge-kategori"><?php echo $row['category']; ?></span>
                    <h3 style="margin-top: 15px; margin-bottom: 5px;"><?php echo $row['product_name']; ?></h3>
                    <p style="margin: 0; color: #666; font-size: 14px;">Toko: <b><?php echo $row['store_name']; ?></b></p>
                    
                    <p class="harga">Rp <?php echo number_format($row['actual_selling_price'], 0, ',', '.'); ?></p>
                    
                    <?php if ($row['retail_ceiling_price'] != null && $row['retail_ceiling_price'] > 0) { 
                        if ($row['actual_selling_price'] > $row['retail_ceiling_price']) {
                            echo "<div class='het-bahaya'>🔴 Melanggar HET (Maks. Rp ".number_format($row['retail_ceiling_price'], 0, ',', '.').")</div>";
                        } else {
                            echo "<div class='het-aman'>🟢 Harga Aman sesuai HET</div>";
                        }
                    } ?>

                    <p style="margin: 0; font-size: 14px;">Status: 
                        <b style="color: <?php echo ($row['stock_status'] == 'Available') ? '#2e7d32' : '#d32f2f'; ?>;">
                            <?php echo ($row['stock_status'] == 'Available') ? 'Tersedia' : 'Stok Habis'; ?>
                        </b>
                    </p>
                    
                    <?php 
                        // Menyusun kata-kata otomatis untuk WhatsApp
                        $wa_message = "Halo " . $row['store_name'] . ", saya ingin membeli " . $row['product_name'] . " dari aplikasi SiAGRI. Apakah stoknya masih ada?";
                        // Membuat link WhatsApp
                        $wa_link = "https://wa.me/" . $row['whatsapp_number'] . "?text=" . urlencode($wa_message);
                    ?>
                    <a href="<?php echo $wa_link; ?>" target="_blank" class="btn-wa">💬 Chat Penjual</a>
                </div>
        <?php 
            } // Penutup While
        } else {
            // Jika database kosong
            echo "<p style='text-align: center; color: #666; width: 100%;'>Belum ada produk yang dijual oleh Mitra Kios saat ini.</p>";
        }
        ?>
    </div>

</body>
</html>