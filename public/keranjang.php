<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['id_meja']) || !isset($_SESSION['customer_id'])) {
    header("Location: order.php?table=" . ($_SESSION['id_meja'] ?? ''));
    exit;
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Query untuk mendapatkan data menu dengan promo
$menu_query = "
SELECT 
    m.id_menu, 
    m.nama_menu, 
    m.gambar, 
    m.harga AS harga_asli,
    m.kategori_menu,
    (
        SELECT p.id 
        FROM promos p 
        WHERE 
            p.start_date <= CURDATE() 
            AND p.end_date >= CURDATE()
            AND (
                (p.promo_type = 'discount' AND JSON_CONTAINS(p.menu_target, CAST(m.id_menu AS JSON), '$')) OR
                (p.promo_type = 'bundle' AND JSON_CONTAINS(p.bundle_items, CAST(m.id_menu AS JSON), '$'))
            )
        LIMIT 1
    ) AS promo_id,
    (
        SELECT p.promo_type 
        FROM promos p 
        WHERE 
            p.start_date <= CURDATE() 
            AND p.end_date >= CURDATE()
            AND (
                (p.promo_type = 'discount' AND JSON_CONTAINS(p.menu_target, CAST(m.id_menu AS JSON), '$')) OR
                (p.promo_type = 'bundle' AND JSON_CONTAINS(p.bundle_items, CAST(m.id_menu AS JSON), '$'))
            )
        LIMIT 1
    ) AS promo_type,
    (
        SELECT p.discount 
        FROM promos p 
        WHERE 
            p.start_date <= CURDATE() 
            AND p.end_date >= CURDATE()
            AND p.promo_type = 'discount'
            AND JSON_CONTAINS(p.menu_target, CAST(m.id_menu AS JSON), '$')
        LIMIT 1
    ) AS discount,
    (
        SELECT p.bundle_price 
        FROM promos p 
        WHERE 
            p.start_date <= CURDATE() 
            AND p.end_date >= CURDATE()
            AND p.promo_type = 'bundle'
            AND JSON_CONTAINS(p.bundle_items, CAST(m.id_menu AS JSON), '$')
        LIMIT 1
    ) AS bundle_price,
    (
        SELECT p.title 
        FROM promos p 
        WHERE 
            p.start_date <= CURDATE() 
            AND p.end_date >= CURDATE()
            AND (
                (p.promo_type = 'discount' AND JSON_CONTAINS(p.menu_target, CAST(m.id_menu AS JSON), '$')) OR
                (p.promo_type = 'bundle' AND JSON_CONTAINS(p.bundle_items, CAST(m.id_menu AS JSON), '$'))
            )
        LIMIT 1
    ) AS promo_title
FROM menu m
";

$menu_result = $conn->query($menu_query);
$menu_data = [];

while ($row = $menu_result->fetch_assoc()) {
    // Hitung harga promo
    if ($row['promo_type'] == 'discount') {
        $row['harga_promo'] = $row['harga_asli'] * (1 - ($row['discount'] / 100));
    } elseif ($row['promo_type'] == 'bundle') {
        $row['harga_promo'] = $row['bundle_price'];
    } else {
        $row['harga_promo'] = $row['harga_asli'];
    }
    
    $menu_data[$row['id_menu']] = $row;
}

// Handle tambah/kurang item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_menu']) && isset($_POST['action'])) {
        $id_menu = (int)$_POST['id_menu'];
        $action = $_POST['action'];
        
        // Cari item di keranjang
        $item_index = null;
        foreach ($_SESSION['keranjang'] as $i => $item) {
            if ($item['id_menu'] == $id_menu) {
                $item_index = $i;
                break;
            }
        }
        
        if ($action === 'tambah') {
            if ($item_index !== null) {
                $_SESSION['keranjang'][$item_index]['jumlah']++;
            } else {
                $_SESSION['keranjang'][] = [
                    'id_menu' => $id_menu,
                    'jumlah' => 1
                ];
            }
        } elseif ($action === 'kurang' && $item_index !== null) {
            $_SESSION['keranjang'][$item_index]['jumlah']--;
            if ($_SESSION['keranjang'][$item_index]['jumlah'] <= 0) {
                array_splice($_SESSION['keranjang'], $item_index, 1);
            }
        }
    }
}

// Update data keranjang dengan info terbaru dari database
$keranjang_display = [];
$total = 0;

foreach ($_SESSION['keranjang'] as $item) {
    $id_menu = $item['id_menu'];
    if (isset($menu_data[$id_menu])) {
        $menu_item = $menu_data[$id_menu];
        $subtotal = $menu_item['harga_promo'] * $item['jumlah'];
        
        $keranjang_display[] = [
            'id_menu' => $id_menu,
            'nama_menu' => $menu_item['nama_menu'],
            'gambar' => $menu_item['gambar'],
            'harga_asli' => $menu_item['harga_asli'],
            'harga_promo' => $menu_item['harga_promo'],
            'promo_type' => $menu_item['promo_type'],
            'promo_title' => $menu_item['promo_title'],
            'jumlah' => $item['jumlah'],
            'subtotal' => $subtotal
        ];
        
        $total += $subtotal;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Keranjang - ZidanKitchen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding-bottom: 100px; /* Space for fixed footer */
        }

        .promo-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            transform: rotate(15deg);
            font-size: 0.65rem;
            z-index: 10;
        }

        .cart-item {
            position: relative;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Fixed footer styling */
        .checkout-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-blue-50 to-white min-h-screen">
    <!-- Sticky Header -->
    <header class="sticky top-0 z-50 bg-blue-600 shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <h1 class="text-xl font-bold text-white">Keranjang Belanja</h1>
            <a href="menu.php" class="text-blue-100 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6 max-w-2xl mb-20">
        <?php if (!empty($keranjang_display)): ?>
            <!-- Daftar Item -->
            <div class="space-y-4">
                <?php foreach ($keranjang_display as $item): ?>
                    <div class="cart-item bg-white rounded-lg shadow p-4 relative">
                        <?php if ($item['promo_type']): ?>
                            <div class="promo-badge bg-yellow-400 text-yellow-800 font-bold px-2 py-1 rounded-full">
                                <?= strtoupper($item['promo_type']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex gap-4 items-center">
                            <!-- Gambar Menu -->
                            <div class="flex-shrink-0">
                                <img src="../assets/images/<?= htmlspecialchars($item['gambar']); ?>"
                                    alt="<?= htmlspecialchars($item['nama_menu']); ?>"
                                    class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                            </div>

                            <!-- Detail Menu -->
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800"><?= htmlspecialchars($item['nama_menu']); ?></h3>

                                <!-- Harga -->
                                <div class="mt-1">
                                    <?php if ($item['promo_type'] == 'discount'): ?>
                                        <span class="text-green-600 font-bold">Rp <?= number_format($item['harga_promo'], 0, ',', '.') ?></span>
                                        <span class="ml-2 text-sm text-gray-400 line-through">Rp <?= number_format($item['harga_asli'], 0, ',', '.') ?></span>
                                    <?php elseif ($item['promo_type'] == 'bundle'): ?>
                                        <span class="text-green-600 font-bold">Rp <?= number_format($item['harga_promo'], 0, ',', '.') ?></span>
                                        <span class="ml-2 text-xs text-gray-500">(Harga Paket)</span>
                                    <?php else: ?>
                                        <span class="text-gray-800 font-bold">Rp <?= number_format($item['harga_asli'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Quantity Control -->
                                <div class="flex items-center mt-3">
                                    <!-- Form Kurang -->
                                    <form method="POST" action="keranjang.php">
                                        <input type="hidden" name="id_menu" value="<?= $item['id_menu'] ?>">
                                        <input type="hidden" name="action" value="kurang">
                                        <button type="submit" class="quantity-btn bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                    </form>
                                    
                                    <span class="mx-3 font-medium text-gray-700 min-w-[20px] text-center"><?= $item['jumlah'] ?></span>

                                    <!-- Form Tambah -->
                                    <form method="POST" action="keranjang.php">
                                        <input type="hidden" name="id_menu" value="<?= $item['id_menu']; ?>">
                                        <input type="hidden" name="action" value="tambah">
                                        <button type="submit" class="quantity-btn bg-green-500 text-white rounded-full hover:bg-green-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Subtotal -->
                            <div class="text-right">
                                <p class="font-medium text-gray-800">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Floating Action Button -->
            <div class="fixed bottom-24 right-4 z-20 md:bottom-28 md:right-1/2 md:transform md:translate-x-1/2">
                <a href="menu.php" class="bg-blue-600 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 transition flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </a>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="mx-auto w-48 h-48 bg-blue-100 rounded-full flex items-center justify-center mb-8">
                    <svg class="w-24 h-24 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z">
                        </path>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-700 mb-2">Keranjang Kosong</h2>
                <p class="text-gray-500 mb-6">Belum ada item di keranjang belanja Anda</p>
                
                <a href="menu.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg shadow-md transition duration-300">
                    Lihat Menu
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Checkout Footer -->
    <?php if (!empty($keranjang_display)): ?>
    <div class="checkout-footer bg-white shadow-lg border-t border-gray-200 px-4 py-3">
        <div class="max-w-2xl mx-auto flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-600">Total Pembayaran</p>
                <p class="text-lg font-bold text-green-600">Rp <?= number_format($total, 0, ',', '.'); ?></p>
            </div>
            <form method="POST" action="checkout.php">
                <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-6 py-2 rounded-lg shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Checkout
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>