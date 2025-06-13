<?php
include '../config/db.php';

// Ambil promo yang masih berlaku
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');
$promo_query = "SELECT * FROM promos WHERE CURDATE() BETWEEN start_date AND end_date";
$promos = $conn->query($promo_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo - ZidanKitchen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Promo Spesial ZidanKitchen</h1>
        
        <div class="grid md:grid-cols-2 grid-cols-1 gap-6">
            <?php while ($promo = $promos->fetch_assoc()): ?>
                <div class="bg-white p-4 rounded-xl shadow-lg hover:shadow-xl transition-all relative">
                    <img src="../assets/images/<?= $promo['image']; ?>" alt="<?= $promo['title']; ?>" class="w-full h-48 object-cover rounded-lg">
                    <h2 class="text-xl font-semibold mt-4"> <?= $promo['title']; ?> </h2>
                    <p class="text-gray-500 text-sm mt-1">Berlaku dari: <?= date('d M Y', strtotime($promo['start_date'])); ?></p>
                    <p class="text-gray-500 text-sm mt-1">Berlaku hingga: <?= date('d M Y', strtotime($promo['end_date'])); ?></p>
                    <p class="mt-2 text-gray-700"> <?= $promo['description']; ?> </p>
                    
                    <?php if ($promo['promo_type'] == 'discount'): ?>
                        <span class="absolute top-2 left-2 bg-red-500 text-white text-xs px-3 py-1 rounded-full">
                            Diskon <?= $promo['discount']; ?>%
                        </span>
                    <?php elseif ($promo['promo_type'] == 'buy2get1'): ?>
                        <span class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-3 py-1 rounded-full">
                            Buy 2 Get 1
                        </span>
                    <?php elseif ($promo['promo_type'] == 'bundle'): ?>
                        <span class="absolute top-2 left-2 bg-green-500 text-white text-xs px-3 py-1 rounded-full">
                            Paket Spesial: Rp <?= number_format($promo['bundle_price'], 0, ',', '.'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
