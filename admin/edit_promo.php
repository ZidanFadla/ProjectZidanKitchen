<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_GET['id'])) {
    die("ID Promo tidak ditemukan!");
}

$id = $_GET['id'];
$query = $conn->prepare("SELECT * FROM promos WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$promo = $result->fetch_assoc();

if (!$promo) {
    die("Promo tidak ditemukan!");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $discount = $_POST['discount'];
    $promo_type = $_POST['promo_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $category_target = $_POST['category_target'] ?? null;
    $bundle_price = $_POST['bundle_price'] ?? null;
    
    // Validations
    if (strtotime($start_date) > strtotime($end_date)) {
        $error = 'Tanggal mulai tidak boleh setelah tanggal berakhir';
    } 
    
    // Validate discount percentage
    if ($discount > 100) {
        $error = 'Diskon tidak boleh melebihi 100%';
    }
    
    // Berjalan jika tidak ada error
    if (empty($error)) {
        // Handle image upload
        $image = $promo['image']; // Default to existing image
        
        if ($_FILES['image']['name']) {
            $new_image = $_FILES['image']['name'];
            $target_file = "../assets/images/" . basename($new_image);
            
            // Check if image file is valid
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check === false) {
                $error = 'File yang diupload bukan gambar yang valid';
            } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $new_image;
            } else {
                $error = 'Gagal mengupload gambar';
            }
        }
        
        if (empty($error)) {
            $updateQuery = $conn->prepare("UPDATE promos SET title = ?, description = ?, start_date = ?, end_date = ?, discount = ?, promo_type = ?, category_target = ?, bundle_price = ?, image = ? WHERE id = ?");
            $updateQuery->bind_param("ssssissisi", $title, $description, $start_date, $end_date, $discount, $promo_type, $category_target, $bundle_price, $image, $id);

            if ($updateQuery->execute()) {
                $success = 'Promo berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui promo: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Promo</title>
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

    <!-- Content -->
    <div class="flex-1 ml-64 p-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-orange-600">
                    <i class="fas fa-edit mr-2"></i> Edit Promo
                </h1>
                <a href="promos.php" class="text-orange-500 hover:text-orange-700">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p><?= htmlspecialchars($success) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Nama Promo <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="<?= htmlspecialchars($promo['title']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Jenis Promo <span class="text-red-500">*</span></label>
                            <select name="promo_type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                                <option value="discount" <?= $promo['promo_type'] == 'discount' ? 'selected' : '' ?>>Diskon</option>
                                <option value="buy2get1" <?= $promo['promo_type'] == 'buy2get1' ? 'selected' : '' ?>>Beli 2 Gratis 1</option>
                                <option value="bundle" <?= $promo['promo_type'] == 'bundle' ? 'selected' : '' ?>>Bundle</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-medium">Deskripsi <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required><?= htmlspecialchars($promo['description']) ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" value="<?= $promo['start_date'] ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Tanggal Berakhir <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" value="<?= $promo['end_date'] ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Diskon (%)</label>
                            <div class="relative">
                                <input type="number" name="discount" min="0" max="100" value="<?= $promo['discount'] ?>" 
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" placeholder="0-100">
                                <span class="absolute right-3 top-2 text-gray-400">%</span>
                            </div>
                            <p class="text-sm text-gray-500">Masukkan nilai antara 0-100</p>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Kategori Target</label>
                            <select name="category_target" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                                <option value="">Pilih Kategori</option>
                                <option value="makanan" <?= $promo['category_target'] == 'makanan' ? 'selected' : '' ?>>Makanan</option>
                                <option value="minuman" <?= $promo['category_target'] == 'minuman' ? 'selected' : '' ?>>Minuman</option>
                                <option value="dessert" <?= $promo['category_target'] == 'dessert' ? 'selected' : '' ?>>Dessert</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-medium">Harga Bundle (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="bundle_price" min="0" value="<?= $promo['bundle_price'] ?>" 
                                   class="w-full px-10 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <label class="block text-gray-700 font-medium">Gambar Promo</label>
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg hover:bg-gray-50 hover:border-orange-300 transition-all">
                                        <div class="flex flex-col items-center justify-center pt-7">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                            <p class="pt-1 text-sm text-gray-600">Upload gambar baru</p>
                                        </div>
                                        <input type="file" name="image" class="opacity-0 absolute">
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">Format: JPG, PNG (Maksimal 2MB)</p>
                            </div>
                            
                            <div class="flex-1">
                                <div class="border rounded-lg p-4 bg-gray-50">
                                    <p class="text-sm text-gray-600 mb-2">Gambar saat ini:</p>
                                    <?php if (!empty($promo['image'])): ?>
                                        <img src="../assets/promo_images/<?= htmlspecialchars($promo['image']) ?>" class="max-h-24 mx-auto rounded-lg shadow-sm">
                                        <p class="text-xs text-center text-gray-500 mt-2 truncate"><?= htmlspecialchars($promo['image']) ?></p>
                                    <?php else: ?>
                                        <div class="text-center text-gray-400 text-sm">
                                            <i class="fas fa-image text-3xl mb-2"></i>
                                            <p>Tidak ada gambar</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="promos.php" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-times mr-2"></i> Batal
                        </a>
                        <button type="submit" name="update" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        // client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const discount = document.querySelector('input[name="discount"]');
            if (discount.value > 100) {
                alert('Diskon tidak boleh melebihi 100%');
                e.preventDefault();
                discount.focus();
                return false;
            }

            const select = document.querySelector('select[name="category_target"]');
            if(select.value === "") {
                select.value = null;
            }
            
            const startDate = new Date(document.querySelector('input[name="start_date"]').value);
            const endDate = new Date(document.querySelector('input[name="end_date"]').value);
            
            if (startDate > endDate) {
                alert('Tanggal mulai tidak boleh setelah tanggal berakhir');
                e.preventDefault();
                return false;
            }
        });
        
        // Show selected file name
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Belum ada file dipilih';
            const uploadText = e.target.parentElement.querySelector('p');
            if (uploadText) {
                uploadText.textContent = fileName;
                uploadText.className = 'pt-1 text-sm text-orange-600 font-medium';
            }
        });
    </script>
</body>
</html>