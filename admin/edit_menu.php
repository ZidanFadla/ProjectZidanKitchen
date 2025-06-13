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

// Initialize error/success messages
$error = '';
$success = '';

// Ambil data menu berdasarkan ID
if (isset($_GET['id'])) {
    $id_menu = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM menu WHERE id_menu = ?");
    $stmt->bind_param("i", $id_menu);
    $stmt->execute();
    $result = $stmt->get_result();
    $menu = $result->fetch_assoc();
    
    if (!$menu) {
        $_SESSION['error'] = "Menu tidak ditemukan!";
        header("Location: menu.php");
        exit;
    }
}

// Proses update menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id_menu = (int)$_POST['id_menu'];
    $nama_menu = $conn->real_escape_string($_POST['nama_menu']);
    $harga = (int)$_POST['harga'];
    $kategori = $conn->real_escape_string($_POST['kategori_menu']);

    try {
        // Handle upload gambar jika ada
        if (!empty($_FILES['gambar']['name'])) {
            $gambar_name = $_FILES['gambar']['name'];
            $gambar_tmp = $_FILES['gambar']['tmp_name'];
            $target_dir = "../assets/images/";
            $target_file = $target_dir . basename($gambar_name);
            
            // Check if image file is valid
            $check = getimagesize($gambar_tmp);
            if ($check === false) {
                throw new Exception("File yang diupload bukan gambar yang valid");
            }
            
            if (!move_uploaded_file($gambar_tmp, $target_file)) {
                throw new Exception("Gagal mengunggah gambar");
            }
            $update_gambar = ", gambar='$gambar_name'";
        } else {
            $update_gambar = "";
        }

        // Handle upload model 3D jika ada
        if (!empty($_FILES['model_3d']['name'])) {
            $zip_name = $_FILES['model_3d']['name'];
            $zip_tmp = $_FILES['model_3d']['tmp_name'];
            $target_dir = "../assets/models/";
            $target_file = $target_dir . basename($zip_name);
            
            if (!move_uploaded_file($zip_tmp, $target_file)) {
                throw new Exception("Gagal mengunggah model 3D");
            }
            $update_model = ", model_3d='$zip_name'";
        } else {
            $update_model = "";
        }

        $query = "UPDATE menu SET 
                 nama_menu='$nama_menu', 
                 harga='$harga', 
                 kategori_menu='$kategori'
                 $update_gambar $update_model 
                 WHERE id_menu=$id_menu";

        if ($conn->query($query)) {
            $_SESSION['success'] = "Menu berhasil diperbarui!";
            header("Location: menu.php");
            exit;
        } else {
            throw new Exception("Gagal memperbarui menu: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu</title>
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
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-orange-600">
                    <i class="fas fa-edit mr-2"></i> Edit Menu
                </h1>
                <a href="menu.php" class="text-orange-500 hover:text-orange-700">
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

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    <input type="hidden" name="id_menu" value="<?= $menu['id_menu'] ?>">
                    
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-medium">Nama Menu <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_menu" value="<?= htmlspecialchars($menu['nama_menu']) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Harga <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                <input type="number" name="harga" value="<?= $menu['harga'] ?>" 
                                       class="w-full px-10 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Kategori <span class="text-red-500">*</span></label>
                            <select name="kategori_menu" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                                <option value="makanan" <?= $menu['kategori_menu'] == 'makanan' ? 'selected' : '' ?>>Makanan</option>
                                <option value="minuman" <?= $menu['kategori_menu'] == 'minuman' ? 'selected' : '' ?>>Minuman</option>
                                <option value="dessert" <?= $menu['kategori_menu'] == 'dessert' ? 'selected' : '' ?>>Dessert</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <label class="block text-gray-700 font-medium">Gambar Menu</label>
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center justify-center w-full">
                                        <label class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg hover:bg-gray-50 hover:border-orange-300 transition-all">
                                            <div class="flex flex-col items-center justify-center pt-7">
                                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                                <p class="pt-1 text-sm text-gray-600">Upload gambar baru</p>
                                            </div>
                                            <input type="file" name="gambar" class="opacity-0 absolute">
                                        </label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">Format: JPG, PNG (Maksimal 2MB)</p>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="border rounded-lg p-4 bg-gray-50">
                                        <p class="text-sm text-gray-600 mb-2">Gambar saat ini:</p>
                                        <img src="../assets/images/<?= htmlspecialchars($menu['gambar']) ?>" class="max-h-24 mx-auto rounded-lg shadow-sm">
                                        <p class="text-xs text-center text-gray-500 mt-2 truncate"><?= htmlspecialchars($menu['gambar']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <label class="block text-gray-700 font-medium">Model 3D (ZIP)</label>
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center justify-center w-full">
                                        <label class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg hover:bg-gray-50 hover:border-orange-300 transition-all">
                                            <div class="flex flex-col items-center justify-center pt-7">
                                                <i class="fas fa-file-archive text-3xl text-gray-400"></i>
                                                <p class="pt-1 text-sm text-gray-600">Upload model baru</p>
                                            </div>
                                            <input type="file" name="model_3d" accept=".zip,.glb" class="opacity-0 absolute">
                                        </label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">Format: ZIP (Maksimal 5MB)</p>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="border rounded-lg p-4 bg-gray-50">
                                        <p class="text-sm text-gray-600 mb-2">Model saat ini:</p>
                                        <?php if (!empty($menu['model_3d'])): ?>
                                            <div class="text-center">
                                                <i class="fas fa-cube text-3xl text-orange-500"></i>
                                                <p class="text-xs text-center text-gray-500 mt-2 truncate"><?= htmlspecialchars($menu['model_3d']) ?></p>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center text-gray-400 text-sm">
                                                <i class="fas fa-cube"></i> Tidak ada model
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="menu.php" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
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
        // Show selected file names
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'Belum ada file dipilih';
                const uploadText = e.target.parentElement.querySelector('p');
                if (uploadText) {
                    uploadText.textContent = fileName;
                    uploadText.className = 'pt-1 text-sm text-orange-600 font-medium';
                }
            });
        });
    </script>
</body>
</html>