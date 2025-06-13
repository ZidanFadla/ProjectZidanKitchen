<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($conn)) {
    die("Error: Koneksi database tidak tersedia.");
}

// Check for success/error messages from add/edit/delete operations
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

// Clear the messages after displaying them
unset($_SESSION['success']);
unset($_SESSION['error']);

// Pagination settings
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Filter settings
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Base query
$query = "SELECT * FROM menu WHERE 1=1";

// Apply category filter
if ($category !== 'all') {
    $query .= " AND kategori_menu = '" . $conn->real_escape_string($category) . "'";
}

// Get total count for pagination
$countQuery = str_replace('SELECT *', 'SELECT COUNT(*) as total', $query);
$countResult = $conn->query($countQuery);
$totalMenus = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalMenus / $perPage);

// Add pagination and sorting to main query
$query .= " ORDER BY kategori_menu, nama_menu LIMIT $offset, $perPage";
$result = $conn->query($query);

// Get all categories for filter tabs
$categories = $conn->query("SELECT DISTINCT kategori_menu FROM menu");

// Function to build URL with params
function buildUrl($params) {
    $currentParams = $_GET;
    $mergedParams = array_merge($currentParams, $params);
    return '?' . http_build_query($mergedParams);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo_oren.png">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        model-viewer {
            --progress-bar-color: transparent;
        }
        .category-badge {
            text-transform: capitalize;
        }
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
                    <a href="menu.php" class="flex items-center p-3 rounded-lg bg-orange-500 transition-colors">
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
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-orange-600">
                <i class="fas fa-utensils mr-2"></i> Daftar Menu
            </h1>
            <a href="add_menu.php" class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-2 rounded-lg shadow-md hover:opacity-90 transition flex items-center">
                <i class="fas fa-plus mr-2"></i> Tambah Menu
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Category Filter Tabs -->
        <div class="flex mb-6 overflow-x-auto">
            <div class="flex space-x-2 pb-2">
                <a href="<?= buildUrl(['category' => 'all', 'page' => 1]) ?>" 
                class="px-4 py-2 rounded-lg <?= $category == 'all' ? 'bg-orange-500 text-white' : 'bg-white text-orange-600 border border-orange-200 hover:bg-orange-50' ?> font-medium">
                    Semua
                </a>
                <?php
                $categories = $conn->query("SELECT DISTINCT kategori_menu FROM menu");
                while ($cat = $categories->fetch_assoc()): ?>
                    <a href="<?= buildUrl(['category' => $cat['kategori_menu'], 'page' => 1]) ?>" 
                    class="px-4 py-2 rounded-lg <?= $category == $cat['kategori_menu'] ? 'bg-orange-500 text-white' : 'bg-white text-orange-600 border border-orange-200 hover:bg-orange-50' ?> font-medium category-badge">
                        <?= htmlspecialchars(ucfirst($cat['kategori_menu'])) ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-orange-500 to-red-500 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left">Nama Menu</th>
                            <th class="px-6 py-4 text-right">Harga</th>
                            <th class="px-6 py-4 text-left">Kategori</th>
                            <th class="px-6 py-4 text-center">Gambar</th>
                            <th class="px-6 py-4 text-center">3D Model</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($row = $result->fetch_assoc()): 
                            $categoryColor = match($row['kategori_menu']) {
                                'makanan' => 'bg-amber-100 text-amber-800',
                                'minuman' => 'bg-blue-100 text-blue-800',
                                'dessert' => 'bg-purple-100 text-purple-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        ?>
                        <tr class="hover:bg-orange-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($row['nama_menu']) ?></div>
                                <?php if (!empty($row['deskripsi'])): ?>
                                    <div class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($row['deskripsi']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right font-medium">
                                Rp<?= number_format($row['harga'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $categoryColor ?> category-badge">
                                    <?= htmlspecialchars(ucfirst($row['kategori_menu'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <img src="../assets/images/<?= htmlspecialchars($row['gambar']) ?>" 
                                         alt="<?= htmlspecialchars($row['nama_menu']) ?>" 
                                         class="w-16 h-16 object-cover rounded-lg shadow-sm">
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($row['model_3d'])): ?>
                                    <div class="flex justify-center">
                                        <model-viewer src="../assets/models/<?= htmlspecialchars($row['model_3d']) ?>" 
                                            alt="3D Model <?= htmlspecialchars($row['nama_menu']) ?>" 
                                            camera-controls 
                                            auto-rotate 
                                            style="width: 80px; height: 80px;">
                                        </model-viewer>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-gray-400 text-sm">
                                        <i class="fas fa-cube"></i> Tidak ada model
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center space-x-2">
                                    <a href="edit_menu.php?id=<?= $row['id_menu'] ?>" 
                                       class="p-2 bg-yellow-100 text-yellow-600 rounded-lg hover:bg-yellow-200 transition-colors" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_menu.php?id=<?= $row['id_menu'] ?>" 
                                       class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" 
                                       title="Hapus"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Menampilkan <span class="font-medium"><?= $offset + 1 ?></span> sampai 
                    <span class="font-medium"><?= min($offset + $perPage, $totalMenus) ?></span> dari 
                    <span class="font-medium"><?= $totalMenus ?></span> menu
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= buildUrl(['page' => $page - 1]) ?>" class="px-3 py-1 border rounded-md text-gray-500 hover:bg-gray-50">Sebelumnya</a>
                    <?php else: ?>
                        <span class="px-3 py-1 border rounded-md text-gray-300 cursor-not-allowed">Sebelumnya</span>
                    <?php endif; ?>
                    
                    <?php 
                    // Show pagination numbers
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?= buildUrl(['page' => $i]) ?>" 
                           class="px-3 py-1 border rounded-md <?= $i == $page ? 'bg-orange-500 text-white' : 'text-gray-500 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= buildUrl(['page' => $page + 1]) ?>" class="px-3 py-1 border rounded-md text-gray-500 hover:bg-gray-50">Selanjutnya</a>
                    <?php else: ?>
                        <span class="px-3 py-1 border rounded-md text-gray-300 cursor-not-allowed">Selanjutnya</span>
                    <?php endif; ?>
                </div>
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
    </script>
</body>
</html>