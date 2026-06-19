<?php
session_start();
require_once 'koneksi.php';

// Hanya bisa diakses via POST (dari fetch() JS)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Harus sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data dari body JSON
$data     = json_decode(file_get_contents('php://input'), true);
$order_id = (int)($data['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order_id']);
    exit;
}

// Pastikan pesanan milik user ini dan masih pending
$order = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT order_id FROM orders
     WHERE order_id = $order_id
     AND user_id = $user_id
     AND status = 'pending'"
));

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found or already processed']);
    exit;
}

// Kembalikan stok
$items = mysqli_query($conn,
    "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id"
);
while ($item = mysqli_fetch_assoc($items)) {
    mysqli_query($conn,
        "UPDATE products SET stock = stock + {$item['quantity']}
         WHERE product_id = {$item['product_id']}"
    );
}

// Batalkan pesanan
mysqli_query($conn,
    "UPDATE orders SET status = 'cancelled'
     WHERE order_id = $order_id AND user_id = $user_id"
);

echo json_encode(['status' => 'ok', 'message' => 'Order cancelled']);
