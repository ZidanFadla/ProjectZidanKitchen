<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';
date_default_timezone_set('Asia/Jakarta');

// Query for current orders (pending/paid)
$current_orders = $conn->query("
    SELECT o.* 
    FROM orders o
    LEFT JOIN meja m ON o.id_meja = m.id_meja
    WHERE o.status IN ('pending', 'paid')
    ORDER BY o.created_at DESC
");

// Query for recently completed orders (done)
$completed_orders = $conn->query("
    SELECT o.* 
    FROM orders o
    LEFT JOIN meja m ON o.id_meja = m.id_meja
    WHERE o.status = 'done'
    ORDER BY o.created_at DESC
    LIMIT 5
");

// Query for failed orders
$failed_orders = $conn->query("
    SELECT o.* 
    FROM orders o
    LEFT JOIN meja m ON o.id_meja = m.id_meja
    WHERE o.status = 'failed'
    ORDER BY o.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - ZidanKitchen</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo_oren.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-badge {
            @apply px-3 py-1 rounded-full text-xs font-medium;
        }

        .status-badge.pending {
            @apply bg-orange-100 text-orange-800;
        }

        .status-badge.paid {
            @apply bg-blue-100 text-blue-800;
        }

        .status-badge.done {
            @apply bg-green-100 text-green-800;
        }

        .status-badge.failed {
            @apply bg-red-100 text-red-800;
        }

        <style>body {
            font-size: 0.95rem;
        }

        .main-content {
            margin-left: 16rem;
            /* ml-64 */
            padding: 2rem;
            max-width: calc(100vw - 16rem);
            /* cegah overflow dari sidebar */
            overflow-x: hidden;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-badge.pending {
            background-color: #fef3c7;
            color: #c2410c;
        }

        .status-badge.paid {
            background-color: #dbeafe;
            color: #1e3a8a;
        }

        .status-badge.done {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-badge.failed {
            background-color: #fee2e2;
            color: #991b1b;
        }

        table {
            table-layout: auto;
        }

        th,
        td {
            white-space: nowrap;
        }
    </style>

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
                    <a href="order.php" class="flex items-center p-3 rounded-lg bg-orange-500 transition-colors">
                        <i class="fas fa-clipboard-list mr-3"></i> Kelola Pesanan
                    </a>
                </li>
            </ul>
        </nav>
        <a href="logout.php" class="flex items-center p-3 rounded-lg bg-red-600 hover:bg-red-700 transition-colors">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </div>

    <div class="main-content">
        <!-- Header Pesanan -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-orange-600">
                <i class="fas fa-clipboard-list mr-2"></i> Kelola Pesanan
            </h1>
            <div class="text-sm text-gray-500">
                <?= date('l, d F Y') ?>
            </div>
        </div>

        <!-- Current Orders -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-clock mr-2 text-orange-500"></i> Pesanan Masuk
                    <span class="ml-auto text-sm font-normal text-gray-500">
                        <?= $current_orders->num_rows ?> pesanan
                    </span>
                </h3>
            </div>
            <div class="overflow-x-auto max-w-full">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meja</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($row = $current_orders->fetch_assoc()): ?>
                            <tr class="hover:bg-orange-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?= $row['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $row['id_meja'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= ucfirst($row['metode_pembayaran']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge <?= $row['status'] ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m H:i', strtotime($row['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="detail_pesanan.php?id=<?= $row['id'] ?>" class="text-orange-600 hover:text-orange-900 mr-3">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="proses_pesanan.php?id=<?= $row['id'] ?>" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-check"></i> Selesai
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Completed Orders -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-check-circle mr-2 text-green-500"></i> Pesanan Selesai Terakhir
                    </h3>
                </div>
                <div class="overflow-x-auto max-w-full">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($row = $completed_orders->fetch_assoc()): ?>
                                <tr class="hover:bg-green-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?= $row['id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $row['id_meja'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d/m H:i', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="detail_pesanan.php?id=<?= $row['id'] ?>" class="text-orange-600 hover:text-orange-900 mr-3">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Failed Orders -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-times-circle mr-2 text-red-500"></i> Pesanan Gagal Terakhir
                    </h3>
                </div>
                <div class="overflow-x-auto max-w-full">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($row = $failed_orders->fetch_assoc()): ?>
                                <tr class="hover:bg-red-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?= $row['id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $row['id_meja'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d/m H:i', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="detail_pesanan.php?id=<?= $row['id'] ?>" class="text-orange-600 hover:text-orange-900 mr-3">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add confirmation for completing orders
        document.querySelectorAll('a[href*="proses_pesanan.php"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin menyelesaikan pesanan ini?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>