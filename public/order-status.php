<?php
session_start();
include '../config/db.php';
require_once '../vendor/autoload.php';

if (!isset($_GET['order_id'])) {
    header("Location: menu.php");
    exit;
}

$order_id = (int)$_GET['order_id'];
$status = $_GET['status'] ?? '';

// Verify order belongs to customer
$stmt = $conn->prepare("SELECT o.id, o.total_harga, o.status, o.midtrans_order_id, c.name 
                       FROM orders o
                       JOIN customers c ON o.customer_id = c.id
                       WHERE o.id = ? AND o.customer_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: menu.php");
    exit;
}

$order = $result->fetch_assoc();

// Re-check status with Midtrans if pending
if ($order['status'] === 'pending' && !empty($order['midtrans_order_id'])) {
    try {
        \Midtrans\Config::$serverKey = 'SB-Mid-server-p_rr6ZhgUcuXXt7ZJaAJsSM2';
        \Midtrans\Config::$isProduction = false;

        /** @var \stdClass $status_response */
        $status_response = \Midtrans\Transaction::status($order['midtrans_order_id']);
        $new_status = 'pending';
        switch ($status_response->transaction_status) {
            case 'capture':
            case 'settlement':
                $new_status = 'paid';
                break;
            case 'deny':
            case 'cancel':
            case 'expire':
                $new_status = 'failed';
                break;
        }

        if ($new_status !== $order['status']) {
            $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_status, $order_id);
            $update_stmt->execute();
            $order['status'] = $new_status;
        }
    } catch (Exception $e) {
        error_log("Midtrans status check failed for order $order_id: " . $e->getMessage());
    }
}

// Determine status details
switch ($status) {
    case 'success':
        $title = "Pembayaran Berhasil";
        $message = "Terima kasih telah memesan di ZidanKitchen";
        $icon = "✅";
        $color = "bg-green-100 text-green-800";
        break;
    case 'pending':
        $title = "Pembayaran Tertunda";
        $message = "Silakan selesaikan pembayaran Anda";
        $icon = "🕒";
        $color = "bg-yellow-100 text-yellow-800";
        break;
    case 'error':
        $title = "Pembayaran Gagal";
        $message = "Silakan coba lagi atau pilih metode pembayaran lain";
        $icon = "❌";
        $color = "bg-red-100 text-red-800";
        break;
    default:
        // Check actual status from database
        switch ($order['status']) {
            case 'paid':
                $title = "Pembayaran Berhasil";
                $message = "Terima kasih telah memesan di ZidanKitchen";
                $icon = "✅";
                $color = "bg-green-100 text-green-800";
                break;
            case 'pending':
                $title = "Pembayaran Tertunda";
                $message = "Silakan selesaikan pembayaran Anda";
                $icon = "🕒";
                $color = "bg-yellow-100 text-yellow-800";
                break;
            case 'failed':
            case 'done':
            default:
                $title = "Pembayaran Gagal";
                $message = "Silakan coba lagi atau pilih metode pembayaran lain";
                $icon = "❌";
                $color = "bg-red-100 text-red-800";
        }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran - ZidanKitchen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md text-center">
        <div class="text-6xl mb-4"><?= $icon ?></div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= $title ?></h1>
        <p class="text-gray-600 mb-6"><?= $message ?></p>
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Nomor Pesanan</span>
                <span class="font-medium">ZK-<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Total Pembayaran</span>
                <span class="font-bold text-blue-600">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
            </div>
        </div>
        <div class="space-y-3">
            <a href="menu.php" class="block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                Kembali ke Menu
            </a>
            <?php if ($status === 'error' || $order['status'] === 'failed'): ?>
            <a href="keranjang.php" class="block bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                Coba Lagi
            </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>