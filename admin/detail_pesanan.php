<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';
date_default_timezone_set('Asia/Jakarta');

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$order_id = intval($_GET['id']);

// Fetch order details
$order_query = $conn->prepare("
    SELECT o.* 
    FROM orders o
    LEFT JOIN meja m ON o.id_meja = m.id_meja
    WHERE o.id = ?
");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$result = $order_query->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found");
}

// Fetch order items
$items_query = $conn->prepare("
    SELECT oi.*, m.gambar 
    FROM order_items oi
    LEFT JOIN menu m ON oi.id_menu = m.id_menu
    WHERE oi.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items_result = $items_query->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - ZidanKitchen</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo_oren.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex">

    <!-- Sidebar -->
    <div class="h-screen w-64 bg-gradient-to-b from-orange-600 to-yellow-900 text-white p-5 shadow-lg fixed flex flex-col">
        <div class="text-center mb-8 pt-4">
            <h2 class="text-2xl font-bold mb-2">Admin Panel</h2>
            <div class="w-16 h-1 bg-orange-300 mx-auto rounded-full"></div>
        </div>
        <nav class="flex-1">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-orange-500 transition-colors">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="menu.php" class="flex items-center p-3 rounded-lg hover:bg-orange-500 transition-colors">
                        <i class="fas fa-utensils mr-3"></i> Kelola Menu
                    </a>
                </li>
                <li>
                    <a href="promos.php" class="flex items-center p-3 rounded-lg hover:bg-orange-500 transition-colors">
                        <i class="fas fa-tags mr-3"></i> Kelola Promo
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="flex items-center p-3 rounded-lg bg-orange-500 transition-colors">
                        <i class="fas fa-clipboard-list mr-3"></i> Kelola Pesanan
                    </a>
                </li>
            </ul>
        </nav>
        <a href="logout.php" class="flex items-center p-3 rounded-lg bg-red-600 hover:bg-red-700 transition-colors">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </div>

    <div class="flex-1 ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-orange-600">
                <i class="fas fa-clipboard-list mr-2"></i> Detail Pesanan #<?= $order['id'] ?>
            </h2>
            
            <div class="flex items-center space-x-4">
                <a href="order.php" class="text-gray-600 hover:text-orange-600 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <?php if ($order['status'] == 'pending' || $order['status'] == 'paid'): ?>
                <a href="proses_pesanan.php?id=<?= $order['id'] ?>" 
                   class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-3 rounded-lg shadow-md hover:opacity-90 transition flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> Proses Pesanan
                </a>
                <?php endif; ?>
                
            </div>
        </div>
        
        <!-- Order Summary Card -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Order Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Informasi Pesanan</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium <?= 
                                    $order['status'] == 'done' ? 'text-green-600' : 
                                    ($order['status'] == 'paid' ? 'text-blue-600' : 
                                    ($order['status'] == 'failed' ? 'text-red-600' : 'text-orange-600')) 
                                ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tanggal:</span>
                                <span class="font-medium"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Meja:</span>
                                <span class="font-medium"><?= $order['id_meja'] ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Pembayaran</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Metode:</span>
                                <span class="font-medium"><?= $order['metode_pembayaran'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total:</span>
                                <span class="font-medium text-orange-600">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Actions -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Aksi</h3>
                        <div class="flex space-x-3">
                            <button class="px-4 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition">
                                <i class="fas fa-print mr-2"></i> Cetak
                            </button>
                            <button class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                                <i class="fas fa-envelope mr-2"></i> Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">Item Pesanan</h3>
            </div>
            
            <div class="divide-y divide-gray-100">
                <?php foreach ($items as $item): ?>
                <div class="p-6 hover:bg-orange-50 transition-colors">
                    <div class="flex items-start">                        
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-800"><?= $item['nama_menu'] ?></h4>
                            <div class="flex justify-between mt-2">
                                <div class="text-gray-600">
                                    <?= $item['jumlah'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                                </div>
                                <div class="font-medium text-orange-600">
                                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Order Total -->
                <div class="p-6 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-800">Total Pesanan</span>
                        <span class="text-2xl font-bold text-orange-600">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple confirmation for processing order
        document.querySelector('a[href*="proses_pesanan.php"]')?.addEventListener('click', function(e) {
            if (!confirm('Konfirmasi selesaikan pesanan ini?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>