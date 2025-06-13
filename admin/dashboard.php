<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';
date_default_timezone_set('Asia/Jakarta');

// Get orders data
$result = $conn->query("
    SELECT * FROM orders 
    WHERE status IN ('paid', 'pending') 
    ORDER BY created_at ASC
");

$completed_orders = $conn->query("SELECT * FROM orders WHERE status='done' ORDER BY created_at DESC LIMIT 10");
$failed_orders = $conn->query("SELECT * FROM orders WHERE status='failed' ORDER BY created_at DESC LIMIT 10");

// Statistics
$total_pendapatan = $conn->query("SELECT IFNULL(SUM(total_harga), 0) AS total FROM orders WHERE status='done'")->fetch_assoc()['total'];
$total_pesanan = $conn->query("SELECT COUNT(id) AS total FROM orders")->fetch_assoc()['total'];
$pesanan_hari_ini = $conn->query("SELECT COUNT(id) AS total FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'];
$pendapatan_hari_ini = $conn->query("SELECT IFNULL(SUM(total_harga), 0) AS total FROM orders WHERE status='done' AND DATE(created_at) = CURDATE()")->fetch_assoc()['total'];

// Get recent 5 menu items - FIXED QUERY
$popular_menu = $conn->query("
    SELECT m.nama_menu, COUNT(oi.id_menu) as jumlah 
    FROM order_items oi 
    JOIN menu m ON oi.id_menu = m.id_menu 
    GROUP BY oi.id_menu 
    ORDER BY jumlah DESC 
    LIMIT 5
");

// Get data for charts
$weekly_orders = $conn->query("
    SELECT 
        DAYNAME(created_at) AS day, 
        COUNT(id) AS count,
        SUM(CASE WHEN status='done' THEN total_harga ELSE 0 END) AS revenue
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY day
    ORDER BY FIELD(day, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ZidanKitchen</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo_oren.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .pending { background-color: #fef3c7; color: #92400e; }
        .paid { background-color: #d1fae5; color: #065f46; }
        .done { background-color: #dbeafe; color: #1e40af; }
        .failed { background-color: #fee2e2; color: #991b1b; }
    </style>
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
                    <a href="dashboard.php" class="flex items-center p-3 rounded-lg bg-orange-500 transition-colors">
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
                    <a href="order.php" class="flex items-center p-3 rounded-lg hover:bg-orange-500 transition-colors">
                        <i class="fas fa-receipt mr-3"></i> Kelola Pesanan
                    </a>
                </li>
            </ul>
        </nav>
        <a href="logout.php" class="flex items-center p-3 rounded-lg bg-red-600 hover:bg-red-700 transition-colors">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </div>

    <!-- Content -->
    <div class="flex-1 ml-64 p-8">
        <!-- Notification Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <p><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Header Dashboard-->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-orange-600">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard Admin
            </h1>
            <div class="text-sm text-gray-500">
                <?= date('l, d F Y') ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 transition-all duration-300 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Total Pendapatan</p>
                        <h3 class="text-2xl font-bold text-orange-600">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                    </div>
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500 flex items-center">
                        <span class="text-green-500 mr-1"><i class="fas fa-arrow-up"></i> Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></span>
                        <span>hari ini</span>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 transition-all duration-300 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Total Pesanan</p>
                        <h3 class="text-2xl font-bold text-orange-600"><?= number_format($total_pesanan, 0, ',', '.') ?></h3>
                    </div>
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-receipt text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500 flex items-center">
                        <span class="text-green-500 mr-1"><i class="fas fa-arrow-up"></i> <?= $pesanan_hari_ini ?></span>
                        <span>hari ini</span>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 transition-all duration-300 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Pesanan Masuk</p>
                        <h3 class="text-2xl font-bold text-orange-600"><?= $result->num_rows ?></h3>
                    </div>
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500">
                        <?= $conn->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetch_row()[0] ?> pending
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 transition-all duration-300 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Menu Terpopuler</p>
                        <h3 class="text-2xl font-bold text-orange-600 truncate">
                            <?= $popular_menu->num_rows > 0 ? htmlspecialchars($popular_menu->fetch_assoc()['nama_menu']) : '-' ?>
                        </h3>
                    </div>
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-utensils text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500">
                        Lihat semua di <a href="menu.php" class="text-orange-500 hover:underline">Kelola Menu</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Orders Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Pesanan 7 Hari Terakhir</h3>
                <canvas id="ordersChart" height="250"></canvas>
            </div>
            
            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Pendapatan 7 Hari Terakhir</h3>
                <canvas id="revenueChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 1s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 1000);
            });
        }, 5000);

        // Prepare data for charts from PHP query
        const weeklyData = [
            <?php 
            $weekly_orders->data_seek(0);
            while ($row = $weekly_orders->fetch_assoc()): 
                echo "{day: '".substr($row['day'], 0, 3)."', orders: ".$row['count'].", revenue: ".$row['revenue']."},";
            endwhile; 
            ?>
        ];

        // Orders Chart
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ordersCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(item => item.day),
                datasets: [{
                    label: 'Pesanan',
                    data: weeklyData.map(item => item.orders),
                    backgroundColor: 'rgba(249, 115, 22, 0.2)',
                    borderColor: 'rgba(249, 115, 22, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(item => item.day),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: weeklyData.map(item => item.revenue),
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>