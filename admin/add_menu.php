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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = $conn->real_escape_string($_POST['name']);
        $harga = (int)$_POST['price'];
        $kategori_menu = $conn->real_escape_string($_POST['category']);
        $image = null;
        $modelPath = null;

        // Validate required fields
        if (empty($name)) {
            throw new Exception("Nama menu harus diisi");
        }
        if ($harga <= 0) {
            throw new Exception("Harga harus lebih dari 0");
        }

        $targetDir = "../assets/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Upload image Menu
        if (!empty($_FILES['image']['name'])) {
            $image = basename($_FILES['image']['name']);
            $targetFile = $targetDir . "images/" . $image;
            
            // Check if image file is valid
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check === false) {
                throw new Exception("File yang diupload bukan gambar yang valid");
            }
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                throw new Exception("Gagal mengunggah gambar");
            }
        } else {
            throw new Exception("Gambar menu harus diupload");
        }

        // Upload & Ekstrak Model 3D (ZIP)
        if (!empty($_FILES['model_zip']['name'])) {
            $zipFile = $_FILES['model_zip']['tmp_name'];
            $folderName = strtolower(str_replace(" ", "_", $name));
            $modelDir = $targetDir . "models/" . $folderName . "/";

            if (!file_exists($modelDir)) {
                mkdir($modelDir, 0777, true);
            }

            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $zip->extractTo($modelDir);
                $zip->close();
                $modelPath = "$folderName/scene.gltf";
            } else {
                throw new Exception("Gagal mengekstrak file ZIP");
            }
        }

        // Simpan ke Database
        $stmt = $conn->prepare("INSERT INTO menu (nama_menu, harga, kategori_menu, gambar, model_3d) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsss", $name, $harga, $kategori_menu, $image, $modelPath);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Menu berhasil ditambahkan!";
            header("Location: menu.php");
            exit;
        } else {
            throw new Exception("Gagal menyimpan menu: " . $conn->error);
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
    <title>Tambah Menu - Zidan Kitchen</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo_oren.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .file-upload {
            transition: all 0.3s ease;
        }
        .file-upload:hover {
            border-color: #f97316;
            background-color: #fff7ed;
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
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-orange-600">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Menu Baru
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
                <form action="add_menu.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-medium">Nama Menu <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Harga <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                <input type="number" name="price" min="1" class="w-full px-10 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-medium">Kategori <span class="text-red-500">*</span></label>
                            <select name="category" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300" required>
                                <option value="makanan">Makanan</option>
                                <option value="minuman">Minuman</option>
                                <option value="dessert">Dessert</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <label class="block text-gray-700 font-medium">Gambar Menu <span class="text-red-500">*</span></label>
                            <div class="flex items-center justify-center w-full">
                                <label class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg hover:bg-gray-50 hover:border-orange-300 transition-all file-upload">
                                    <div class="flex flex-col items-center justify-center pt-7">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                        <p class="pt-1 text-sm text-gray-600">Upload gambar menu</p>
                                    </div>
                                    <input type="file" name="image" class="opacity-0 absolute" required>
                                </label>
                            </div>
                            <p class="text-sm text-gray-500">Format: JPG, PNG (Maksimal 2MB)</p>
                        </div>
                        
                        <div class="space-y-4">
                            <label class="block text-gray-700 font-medium">Model 3D (ZIP)</label>
                            <div class="flex items-center justify-center w-full">
                                <label class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg hover:bg-gray-50 hover:border-orange-300 transition-all file-upload">
                                    <div class="flex flex-col items-center justify-center pt-7">
                                        <i class="fas fa-file-archive text-3xl text-gray-400"></i>
                                        <p class="pt-1 text-sm text-gray-600">Upload model 3D (ZIP)</p>
                                    </div>
                                    <input type="file" name="model_zip" accept=".zip" class="opacity-0 absolute">
                                </label>
                            </div>
                            <p class="text-sm text-gray-500">Format: ZIP (Maksimal 5MB)</p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="menu.php" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-times mr-2"></i> Batal
                        </a>
                        <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="fas fa-save mr-2"></i> Simpan Menu
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