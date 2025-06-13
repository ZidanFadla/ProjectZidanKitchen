<?php
include '../config/db.php';
session_start();

// Hitung jumlah item di keranjang
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
}

if (!isset($_SESSION['id_meja']) || !isset($_SESSION['customer_id'])) {
    header("Location: register.php?table=" . ($_SESSION['id_meja'] ?? ''));
    exit;
}

// Ambil daftar kategori
$kategori_result = $conn->query("SELECT DISTINCT kategori_menu FROM menu");
$kategori_list = [];
while ($row = $kategori_result->fetch_assoc()) {
    $kategori_list[] = $row['kategori_menu'];
}

$menu_query = "
SELECT 
    m.*,
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
ORDER BY m.nama_menu
";
$menus = $conn->query($menu_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0 viewport-fit=cover" />
    <title>Menu - ZidanKitchen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module" src="https://unpkg.com/@google/model-viewer"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(180deg, #f0f4ff 0%, #ffffff 100%);
            overscroll-behavior: none;
        }

        /* Smooth Animations */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        /* Glassmorphism Effect */
        .glass {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Custom Scrollbar */
        .category-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .category-scroll::-webkit-scrollbar-track {
            background: #e5e7eb;
            border-radius: 3px;
        }

        .category-scroll::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 3px;
        }

        .category-scroll {
            scroll-behavior: smooth;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        /* Neon Glow Effect */
        .neon-button {
            position: relative;
            overflow: hidden;
        }

        .neon-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
        }

        .neon-button:hover::before {
            left: 100%;
        }
    </style>
</head>

<body class="min-h-screen m-0 p-0">
    <!-- Toast Notification -->
    <script>
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerText = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => document.body.removeChild(toast), 400);
            }, 2500);
        }
    </script>

    <!-- Header -->
    <header class="sticky top-0 z-50 glass shadow-lg px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <div class="flex items-center space-x-3">
                <img src="../assets/images/logo_biru.png" alt="Logo" class="w-16 h-16 object-contain transition-transform hover:scale-105">
                <h1 class="text-3xl font-bold text-gray-800">
                    Zidan<span class="text-blue-600">Kitchen</span>
                </h1>
            </div>
            <a href="keranjang.php" class="relative group">
                <div class="p-3 rounded-full bg-white/50 hover:bg-white transition-all duration-300 shadow-md group-hover:shadow-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path>
                    </svg>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    </header>

    <!-- Category Filter -->
    <div class="fixed top-25 z-40 glass w-full px-6 py-4 shadow-md">
        <div class="category-scroll flex space-x-3 overflow-x-auto max-w-7xl mx-auto">
            <button class="px-6 py-2.5 rounded-full bg-gradient-to-r from-blue-500 to-blue-700 text-white text-sm font-medium transition-all duration-300 hover:from-blue-600 hover:to-blue-800 shadow-md hover:shadow-lg whitespace-nowrap" data-filter="all">Semua Menu</button>
            <?php foreach ($kategori_list as $kategori): ?>
                <button class="px-6 py-2.5 rounded-full bg-white/50 text-gray-700 border border-gray-200 text-sm font-medium transition-all duration-300 hover:bg-white hover:shadow-md whitespace-nowrap" data-filter="<?php echo htmlspecialchars($kategori); ?>">
                    <?php echo htmlspecialchars($kategori); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Menu -->
    <main class="container mx-auto px-6 py-8 max-w-7xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php $index = 0;
            while ($menu = $menus->fetch_assoc()): ?>
                <div class="bg-white/80 rounded-2xl overflow-hidden shadow-lg card-hover animate-in menu-card" style="animation-delay: <?php echo ($index % 10) * 0.1; ?>s;" data-kategori="<?php echo htmlspecialchars($menu['kategori_menu']); ?>">
                    <div class="relative">
                        <div class="h-56 w-full overflow-hidden">
                            <img src="../assets/images/<?php echo htmlspecialchars($menu['gambar']); ?>" alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
                        </div>
                        <?php if (!empty($menu['model_3d'])): ?>
                            <div class="absolute bottom-4 right-4 w-24 h-24 bg-white/90 rounded-xl shadow-xl border border-gray-100 p-2 transform hover:scale-105 transition-transform duration-300">
                                <model-viewer src="../assets/models/<?php echo htmlspecialchars($menu['model_3d']); ?>" alt="3D Model" camera-controls auto-rotate style="width: 100%; height: 100%;"></model-viewer>
                            </div>
                        <?php endif; ?>
                        <?php if ($menu['promo_type'] == 'discount'): ?>
                            <div class="absolute top-4 left-4 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">
                                -<?php echo $menu['discount']; ?>%
                            </div>
                            <div class="absolute top-4 right-4 bg-white/90 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm">
                                <?php echo htmlspecialchars($menu['promo_title']); ?>
                            </div>
                        <?php elseif ($menu['promo_type'] == 'bundle'): ?>
                            <div class="absolute top-4 left-4 bg-gradient-to-r from-green-500 to-green-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">
                                Paket Hemat
                            </div>
                            <div class="absolute top-4 right-4 bg-white/90 text-green-600 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm">
                                <?php echo htmlspecialchars($menu['promo_title']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($menu['nama_menu']); ?></h3>
                            <span class="text-xs text-gray-500 bg-gray-100/50 px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($menu['kategori_menu']); ?></span>
                        </div>
                        <div class="mb-4">
                            <?php if ($menu['promo_type'] == 'discount'): ?>
                                <?php
                                $harga_promo = $menu['harga'] * (1 - ($menu['discount'] / 100));
                                $diskon_value = $menu['harga'] - $harga_promo;
                                ?>
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-bold text-gray-800">Rp <?php echo number_format($harga_promo, 0, ',', '.'); ?></span>
                                        <span class="text-sm line-through text-gray-400">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="text-xs text-green-600 font-medium">
                                        Hemat Rp <?php echo number_format($diskon_value, 0, ',', '.'); ?> (<?php echo $menu['discount']; ?>%)
                                    </div>
                                </div>
                            <?php elseif ($menu['promo_type'] == 'bundle'): ?>
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-bold text-green-600">Rp <?php echo number_format($menu['bundle_price'], 0, ',', '.'); ?></span>
                                        <span class="text-xs text-gray-500">(Paket Hemat)</span>
                                    </div>
                                    <div class="text-xs text-blue-600 font-medium">
                                        <?php echo htmlspecialchars($menu['promo_title']); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-lg font-bold text-gray-800">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></span>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="add_to_cart.php">
                            <input type="hidden" name="id_menu" value="<?php echo $menu['id_menu']; ?>">
                            <input type="hidden" name="nama_menu" value="<?php echo htmlspecialchars($menu['nama_menu']); ?>">
                            <input type="hidden" name="harga" value="<?php echo $menu['harga']; ?>">
                            <input type="hidden" name="harga_promo" value="<?php echo isset($harga_promo) ? $harga_promo : $menu['harga']; ?>">
                            <input type="hidden" name="gambar" value="<?php echo htmlspecialchars($menu['gambar']); ?>">
                            <input type="hidden" name="promo_id" value="<?php echo $menu['promo_id'] ?? ''; ?>">
                            <input type="hidden" name="promo_type" value="<?php echo $menu['promo_type'] ?? ''; ?>">
                            <button type="submit" class="btn-tambah w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 rounded-xl font-medium text-sm transition-all duration-300 shadow-md hover:shadow-xl neon-button">
                                + Tambah ke Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            <?php $index++;
            endwhile; ?>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 px-4 shadow-lg">
        <div class="flex justify-around max-w-md mx-auto">
            <a href="menu.php" class="flex flex-col items-center px-4 py-1 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-xs mt-1">Menu</span>
            </a>
            <a href="success.php" class="flex flex-col items-center px-4 py-1 hover:text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span class="text-xs mt-1">Pesanan</span>
            </a>
        </div>
    </nav>

    <!-- JavaScript -->
    <script>
        // Cart Animation and Toast
        function animateCart() {
            const cartIcon = document.querySelector('.group');
            if (cartIcon) {
                cartIcon.classList.add('animate-pulse');
                setTimeout(() => cartIcon.classList.remove('animate-pulse'), 500);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const addButtons = document.querySelectorAll('.btn-tambah');
            addButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    fetch('add_to_cart.php', {
                        method: 'POST',
                        body: new FormData(form)
                    }).then(() => {
                        animateCart();
                        showToast('Berhasil ditambahkan ke keranjang!');
                        updateCartCount();
                    });
                });
            });
        });

        // Update Cart Count
        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCounts = document.querySelectorAll('.cart-count');
                    cartCounts.forEach(el => {
                        if (data.count > 0) {
                            el.textContent = data.count;
                            el.style.display = 'flex';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                });
        }

        // Category Filter
        document.querySelectorAll('.category-scroll button').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.category-scroll button').forEach(btn => {
                    btn.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-700', 'text-white');
                    btn.classList.add('bg-white/50', 'text-gray-700', 'border', 'border-gray-200');
                });
                this.classList.remove('bg-white/50', 'text-gray-700', 'border', 'border-gray-200');
                this.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-700', 'text-white');

                const filter = this.dataset.filter;
                const cards = document.querySelectorAll('.menu-card');
                cards.forEach(card => {
                    if (filter === 'all' || card.dataset.kategori === filter) {
                        card.style.display = 'block';
                        card.classList.add('animate-in');
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>