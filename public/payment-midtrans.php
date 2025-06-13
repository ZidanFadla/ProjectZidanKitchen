<?php
session_start();
require_once '../vendor/autoload.php';
include '../config/db.php';

// Validate session and parameters
if (!isset($_SESSION['customer_id']) || !isset($_GET['order_id']) || !isset($_GET['total'])) {
    header("Location: menu.php");
    exit;
}

$order_id = (int)$_GET['order_id'];
$total_harga = (float)$_GET['total'];

// Verify order belongs to customer
$stmt = $conn->prepare("SELECT id, status, total_harga FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Order $order_id not found for customer {$_SESSION['customer_id']}");
    header("Location: menu.php");
    exit;
}

$order = $result->fetch_assoc();

// If order is already paid, redirect to success
if ($order['status'] === 'paid') {
    header("Location: success.php");
    exit;
}

// Validate total_harga
if (abs($order['total_harga'] - $total_harga) > 0.01) {
    error_log("Total mismatch for order $order_id: DB={$order['total_harga']}, GET=$total_harga");
    header("Location: order-status.php?order_id=$order_id&status=error");
    exit;
}

// Get customer data
$customer_stmt = $conn->prepare("SELECT name, phone FROM customers WHERE id = ?");
$customer_stmt->bind_param("i", $_SESSION['customer_id']);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();

if ($customer_result->num_rows === 0) {
    error_log("Customer {$_SESSION['customer_id']} not found");
    header("Location: order-status.php?order_id=$order_id&status=error");
    exit;
}

$customer = $customer_result->fetch_assoc();

// Setup Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-p_rr6ZhgUcuXXt7ZJaAJsSM2';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Generate unique order ID for Midtrans
$midtrans_order_id = 'ZK-' . $order_id . '-' . time();
// Payment methods to enable
$enabled_payments = ['qris', 'gopay', 'shopeepay', 'bank_transfer', 'credit_card'];

$base_url = \Midtrans\Config::$isProduction 
    ? 'https://zidankitchen.com' 
    : 'http://localhost/zidankitchen/public';
$callback_url = "$base_url/success.php";

$params = [
    'transaction_details' => [
        'order_id' => $midtrans_order_id,
        'gross_amount' => $total_harga
    ],
    'customer_details' => [
        'first_name' => $customer['name'],
        'phone' => $customer['phone'],
        'billing_address' => [
            'address' => 'Meja ' . $_SESSION['id_meja']
        ]
    ],
    'item_details' => getOrderItems($order_id, $conn),
    'enabled_payments' => $enabled_payments,
    'callbacks' => [
        'finish' => $callback_url
    ],
    'expiry' => [
        'unit' => 'minutes',
        'duration' => 30
    ]
];

try {
    // Log params
    error_log("Midtrans params for order $order_id: " . json_encode($params));

    // Update order with Midtrans ID
    $update_stmt = $conn->prepare("UPDATE orders SET midtrans_order_id = ? WHERE id = ?");
    $update_stmt->bind_param("si", $midtrans_order_id, $order_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update midtrans_order_id: " . $conn->error);
    }
    if ($update_stmt->affected_rows === 0) {
        throw new Exception("No rows updated for midtrans_order_id on order $order_id");
    }
    error_log("Midtrans order ID $midtrans_order_id saved for order $order_id");

    // Get Snap Token
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    error_log("Snap token for order $order_id: $snapToken");

    // Store payment token
    $update_stmt = $conn->prepare("UPDATE orders SET snap_token = ? WHERE id = ?");
    $update_stmt->bind_param("si", $snapToken, $order_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update snap_token: " . $conn->error);
    }
    if ($update_stmt->affected_rows === 0) {
        throw new Exception("No rows updated for snap_token on order $order_id");
    }

    // Save order_id to session for success.php
    $_SESSION['order_id'] = $order_id;

} catch (Exception $e) {
    error_log("Midtrans Error for order $order_id: " . $e->getMessage());
    $conn->query("UPDATE orders SET status = 'failed' WHERE id = $order_id");
    header("Location: order-status.php?order_id=$order_id&status=error");
    exit;
}

function getOrderItems($order_id, $conn) {
    $items = [];
    $stmt = $conn->prepare("
        SELECT oi.id_menu, m.nama_menu, oi.jumlah, m.harga 
        FROM order_items oi
        JOIN menu m ON oi.id_menu = m.id_menu
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id_menu'],
            'name' => $row['nama_menu'],
            'price' => $row['harga'],
            'quantity' => $row['jumlah']
        ];
        $total += $row['harga'] * $row['jumlah'];
    }
    error_log("Order $order_id items total: $total");
    return $items;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - ZidanKitchen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-p_rr6ZhgUcuXXt7ZJaAJsSM2"></script>
    <style>
        .payment-container { background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%); }
        .payment-card { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; }
        .payment-card:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.15); }
    </style>
</head>
<body class="payment-container min-h-screen flex items-center justify-center p-4">
    <div class="payment-card bg-white rounded-xl p-6 w-full max-w-md text-center">
        <div class="mb-6">
            <svg class="animate-pulse w-16 h-16 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran</h1>
        <p class="text-gray-600 mb-6">Pilih metode pembayaran yang tersedia</p>
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <span class="font-medium text-gray-700">Total Pembayaran</span>
                <span class="font-bold text-blue-600 text-xl">Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
            </div>
        </div>
        <button id="pay-button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition duration-200 mb-4">
            Pilih Metode Pembayaran
        </button>
        <p class="text-sm text-gray-500">
            Anda akan diarahkan ke halaman pembayaran Midtrans
        </p>
        <script>
            document.getElementById('pay-button').addEventListener('click', function() {
                this.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat...
                `;
                this.disabled = true;
                
                snap.pay('<?= $snapToken ?>', {
                    onSuccess: function(result) { window.location = 'success.php'; },
                    onPending: function(result) { window.location = 'order-status.php?order_id=<?= $order_id ?>&status=pending'; },
                    onError: function(error) { window.location = 'order-status.php?order_id=<?= $order_id ?>&status=error'; },
                    onClose: function() {
                        document.getElementById('pay-button').innerHTML = 'Pilih Metode Pembayaran';
                        document.getElementById('pay-button').disabled = false;
                    }
                });
            });
        </script>
    </div>
</body>
</html>