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

    // Pagination settings
    $perPage = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $perPage;

    // Filter settings
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $today = date('Y-m-d');

    // Base query
    $query = "SELECT * FROM promos WHERE 1=1";

    // Apply filters
    switch ($filter) {
        case 'active':
            $query .= " AND start_date <= '$today' AND end_date >= '$today'";
            break;
        case 'upcoming':
            $query .= " AND start_date > '$today'";
            break;
        case 'expired':
            $query .= " AND end_date < '$today'";
            break;
        default:
            // 'all' - no additional filter
            break;
    }

    // Get total count for pagination
    $countQuery = str_replace('SELECT *', 'SELECT COUNT(*) as total', $query);
    $countResult = $conn->query($countQuery);
    $totalPromos = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalPromos / $perPage);

    // Add pagination and sorting to main query
    $query .= " ORDER BY start_date DESC LIMIT $offset, $perPage";
    $result = $conn->query($query);

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
    <title>Kelola Promo</title>
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
                    <a href="promos.php" class="flex items-center p-3 rounded-lg bg-orange-500 transition-colors">
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

    <div class="flex-1 ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-orange-600">
                <i class="fas fa-tags mr-2"></i> Daftar Promo
            </h2>
            <a href="add_promo.php" class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-3 rounded-lg shadow-md hover:opacity-90 transition flex items-center">
                <i class="fas fa-plus mr-2"></i> Tambah Promo
            </a>
        </div>
        
        <!-- Promo Status Tabs -->
        <div class="flex mb-6 border-b border-gray-200">
            <a href="<?= buildUrl(['filter' => 'all', 'page' => 1]) ?>" 
               class="px-4 py-2 font-medium <?= $filter == 'all' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-orange-500' ?>">
                Semua Promo
            </a>
            <a href="<?= buildUrl(['filter' => 'active', 'page' => 1]) ?>" 
               class="px-4 py-2 font-medium <?= $filter == 'active' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-orange-500' ?>">
                Aktif
            </a>
            <a href="<?= buildUrl(['filter' => 'upcoming', 'page' => 1]) ?>" 
               class="px-4 py-2 font-medium <?= $filter == 'upcoming' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-orange-500' ?>">
                Akan Datang
            </a>
            <a href="<?= buildUrl(['filter' => 'expired', 'page' => 1]) ?>" 
               class="px-4 py-2 font-medium <?= $filter == 'expired' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-orange-500' ?>">
                Kadaluarsa
            </a>
        </div>
        
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-orange-500 to-red-500 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left">Judul</th>
                            <th class="px-6 py-4 text-left">Periode</th>
                            <th class="px-6 py-4 text-left">Jenis</th>
                            <th class="px-6 py-4 text-center">Diskon</th>
                            <th class="px-6 py-4 text-left">Target</th>
                            <th class="px-6 py-4 text-center">Gambar</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($row = $result->fetch_assoc()) { 
                            $category = $row['category_target'] ?? '';
                            $badgeColor = '';
                            
                            // Determine badge color based on promo type
                            switch($row['promo_type']) {
                                case 'discount': $badgeColor = 'bg-blue-100 text-blue-800'; break;
                                case 'buy2get1': $badgeColor = 'bg-purple-100 text-purple-800'; break;
                                case 'bundle': $badgeColor = 'bg-green-100 text-green-800'; break;
                                default: $badgeColor = 'bg-gray-100 text-gray-800';
                            }
                        ?>
                        <tr class="hover:bg-orange-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($row['title']) ?></div>
                                <div class="text-sm text-gray-500 line-clamp-2"><?= htmlspecialchars($row['description']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"><?= date('d M Y', strtotime($row['start_date'])) ?></div>
                                <div class="text-xs text-gray-500">s/d <?= date('d M Y', strtotime($row['end_date'])) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $badgeColor ?>">
                                    <?= htmlspecialchars(ucfirst($row['promo_type'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($row['discount'] > 0): ?>
                                    <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                                        <?= htmlspecialchars($row['discount']) ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($category === 'Semua Kategori'): ?>
                                    <span class="text-gray-500"><?= $category ?></span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                        <?= htmlspecialchars(ucfirst($category)) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center">
                                    <img src="../assets/images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="w-12 h-12 object-cover rounded-lg shadow-sm">
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="edit_promo.php?id=<?= $row['id'] ?>" class="p-2 bg-yellow-100 text-yellow-600 rounded-lg hover:bg-yellow-200 transition-colors" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_promo.php?id=<?= $row['id'] ?>" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" title="Hapus" onclick="return confirm('Hapus promo ini?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Menampilkan <span class="font-medium"><?= $offset + 1 ?></span> sampai 
                    <span class="font-medium"><?= min($offset + $perPage, $totalPromos) ?></span> dari 
                    <span class="font-medium"><?= $totalPromos ?></span> promo
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
        // Simple script to handle tab switching
        document.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.classList.contains('border-orange-600')) {
                    document.querySelector('.border-orange-600').classList.remove('border-orange-600', 'text-orange-600');
                    this.classList.add('border-orange-600', 'text-orange-600');
                }
            });
        });
    </script>
</body>
</html>