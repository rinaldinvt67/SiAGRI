<?php
session_start();
require_once 'koneksi.php';

// Only Kiosk can access this
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Kiosk') {
    header("Location: login-page.php");
    exit;
}

// Handle Form Submission
if (isset($_POST['add_product'])) {
    $product_id = $_POST['product_id'];
    $price = $_POST['actual_selling_price'];
    $status = $_POST['stock_status'];
    $kiosk_id = $_SESSION['kiosk_id']; // We need to ensure we have this session

    $query = "INSERT INTO kiosk_catalogs (kiosk_id, product_id, actual_selling_price, stock_status) 
              VALUES ('$kiosk_id', '$product_id', '$price', '$status')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Product added to catalog!'); window.location='kiosk_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch available products for the dropdown
$products = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Product | SiAGRI</title>
    <link rel="stylesheet" href="Assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h1>Add Product to Catalog</h1>

            <div class="input-box">
                <label>Select Product</label>
                <select name="product_id" required>
                    <?php while($row = mysqli_fetch_assoc($products)) { ?>
                        <option value="<?php echo $row['product_id']; ?>"><?php echo $row['product_name']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="input-box">
                <label>Selling Price (Rp)</label>
                <input type="number" name="actual_selling_price" placeholder="e.g. 50000" required>
            </div>

            <div class="input-box">
                <label>Stock Status</label>
                <select name="stock_status">
                    <option value="Available">Available</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>

            <button type="submit" name="add_product" class="btn">Add to Catalog</button>
            <a href="dashboard.php" class="btn"  style="text-decoration: none">Cancel</a>
        </form>
    </div>
</body>
</html>