<?php
session_start();
require_once 'koneksi.php';

// === AUTH GUARD ===
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: login-page.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Petani';
$message  = '';

// === AMBIL DATA CART ===
$sql_cart = "
    SELECT c.cart_id, c.quantity,
           p.product_id, p.product_name, p.selling_price, p.het_price,
           p.is_subsidized, p.stock, p.product_image, p.kiosk_id,
           k.store_name, k.full_address, k.whatsapp_number, k.kyc_status
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    JOIN kiosk_profiles k ON p.kiosk_id = k.kiosk_id
    WHERE c.user_id = ?
    ORDER BY k.kiosk_id, p.product_name
";
$stmt = mysqli_prepare($conn, $sql_cart);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result_cart = mysqli_stmt_get_result($stmt);

$cart_items   = [];
$kiosk_groups = [];
$grand_total  = 0;

while ($row = mysqli_fetch_assoc($result_cart)) {
    $cart_items[] = $row;
    $kid = $row['kiosk_id'];
    if (!isset($kiosk_groups[$kid])) {
        $kiosk_groups[$kid] = [
            'store_name'      => $row['store_name'],
            'full_address'    => $row['full_address'],
            'whatsapp_number' => $row['whatsapp_number'],
            'kyc_status'      => $row['kyc_status'],
            'items'           => [],
            'subtotal'        => 0,
        ];
    }
    $sub = $row['selling_price'] * $row['quantity'];
    $kiosk_groups[$kid]['items'][]  = $row;
    $kiosk_groups[$kid]['subtotal'] += $sub;
    $grand_total += $sub;
}

// === HANDLE POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    if (empty($cart_items)) {
        $message = "Keranjang belanja kamu kosong.";

    } else {
        // Cek KYC
        $has_unverified = false;
        foreach ($kiosk_groups as $kg) {
            if ($kg['kyc_status'] !== 'verified') { $has_unverified = true; break; }
        }

        if ($has_unverified) {
            $message = "Beberapa kios belum terverifikasi KYC. Pesanan tidak dapat diproses.";
        }
?>