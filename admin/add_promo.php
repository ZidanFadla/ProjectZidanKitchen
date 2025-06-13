<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';
date_default_timezone_set('Asia/Jakarta');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $discount = $_POST['discount'];
    $promo_type = $_POST['promo_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $menu_target = isset($_POST['menu_target']) ? json_encode($_POST['menu_target']) : null;
    // Handle bundle 
    $bundle_price = $_POST['bundle_price'] ?? null;
    $bundle_items = isset($_POST['bundle_items']) ? json_encode($_POST['bundle_items']) : null;
    $bundle_discount_type = $_POST['bundle_discount_type'] ?? null;
    $bundle_discount_value = !empty($_POST['bundle_discount_value']) ? $_POST['bundle_discount_value'] : null;
    
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
        if ($_FILES['image']['name']) {
            $image = $_FILES['image']['name'];
            $target_file = "../assets/images/" . basename($image);
            
            // Cek apakah image file valid
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check === false) {
                $error = 'File yang diupload bukan gambar yang valid';
            } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $error = 'Gagal mengupload gambar';
            }
        } else {
            $image = "default.png";
        }
        
        
        if (empty($error)) {
            $query = $conn->prepare("INSERT INTO promos 
                (title, description, start_date, end_date, discount, promo_type, 
                menu_target, bundle_price, image, bundle_items, bundle_discount_type, bundle_discount_value) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $query->bind_param("ssssississss", 
                $title, $description, $start_date, $end_date, $discount, $promo_type,
                $menu_target, $bundle_price, $image, $bundle_items, $bundle_discount_type, $bundle_discount_value);

            if ($query->execute()) {
                $success = 'Promo berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan promo: ' . $conn->error;
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
    <title>Tambah Promo</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo_oren.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-orange-600">
                    <i class="fas fa-tags mr-2"></i> Tambah Promo Baru
                </h1>
                <a href="promos.php" class="text-orange-500 hover:text-orange-700 flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>

            <!-- Alert -->
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
            <?php elseif ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p><?php echo $success; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-md space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium">Nama Promo <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium">Jenis Promo <span class="text-red-500">*</span></label>
                        <select name="promo_type" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                            <option value="">Pilih Jenis Promo</option>
                            <option value="discount">Diskon</option>
                            <option value="bundle">Bundle</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="3" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium">Tanggal Berakhir <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="regular-discount-field">
                        <label class="block text-gray-700 font-medium">Diskon (%)</label>
                        <div class="relative">
                            <input type="number" name="discount" min="0" max="100" placeholder="0-100" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                            <span class="absolute right-3 top-2 text-gray-400">%</span>
                        </div>
                        <p class="text-sm text-gray-500">Masukkan nilai antara 0-100</p>
                    </div>
                    <div class="mb-4" id="menu-target-field" style="display: none;">
                        <label class="block text-gray-700 font-medium mb-2">Menu yang Di-diskon <span class="text-red-500">*</span></label>
                        <select name="menu_target[]" multiple="multiple" class="menu-select w-full px-4 py-2 border rounded-lg">
                            <?php foreach($menu_items as $item): ?>
                                <option value="<?= $item['id_menu'] ?>"><?= $item['nama_menu'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Pilih menu yang akan mendapatkan diskon</p>
                    </div>
                </div>

                <!-- Bundle Fields (hidden by default) -->
                <div id="bundle-fields" style="display: none;" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium">Item Bundle <span class="text-red-500">*</span></label>
                        <select name="bundle_items[]" multiple class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                            <?php
                            $menu_items = $conn->query("SELECT id_menu, nama_menu FROM menu");
                            while ($item = $menu_items->fetch_assoc()): ?>
                                <option value="<?= $item['id_menu'] ?>"><?= $item['nama_menu'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <p class="text-sm text-gray-500">Pilih beberapa item (gunakan Ctrl/Cmd + klik untuk memilih multiple)</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium">Jenis Diskon Bundle <span class="text-red-500">*</span></label>
                            <select name="bundle_discount_type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                                <option value="percentage">Persentase</option>
                                <option value="fixed">Nominal Tetap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium">Nilai Diskon <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="bundle_discount_value" min="0" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300">
                                <span id="discount-suffix" class="absolute right-3 top-2 text-gray-400">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium">Gambar Promo</label>
                    <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed rounded-lg hover:bg-gray-50 hover:border-orange-300 cursor-pointer transition">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-1"></i>
                        <p class="text-sm text-gray-600">Upload gambar promo</p>
                        <input type="file" name="image" class="hidden">
                    </label>
                    <p class="text-sm text-gray-500">Format: JPG, PNG (maks. 2MB)</p>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="promos.php" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        <i class="fas fa-times mr-2"></i> Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-save mr-2"></i> Simpan Promo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elemen utama
            const form = document.querySelector('form');
            const promoTypeSelect = document.querySelector('select[name="promo_type"]');
            const bundleFields = document.getElementById('bundle-fields');
            const regularDiscountField = document.querySelector('.regular-discount-field');
            const menuTargetField = document.getElementById('menu-target-field');

            // Elemen file upload
            const fileInput = document.querySelector('input[type="file"]');
            const fileLabel = fileInput?.closest('label');
            const fileText = fileLabel?.querySelector('p');
            
            // Inisialisasi Select2 untuk bundle items
            function initSelect2(selector, placeholder) {
                $(selector).select2({
                    placeholder: placeholder,
                    width: '100%',
                    closeOnSelect: false
                });
            }

            // Toggle visibility field berdasarkan jenis promo
            function togglePromoFields() {
                if (promoTypeSelect.value === 'bundle') {
                    bundleFields.style.display = 'block';
                    regularDiscountField.style.display = 'none';
                    
                    // Inisialisasi Select2 hanya sekali
                    if (!select2Initialized && $('select[name="bundle_items[]"]').length) {
                        $('select[name="bundle_items[]"]').select2({
                            placeholder: "Pilih item bundle (minimal 2)",
                            width: '100%',
                            closeOnSelect: false
                        });
                        select2Initialized = true;
                    }
                } else if (promoTypeSelect.value === 'discount') {
                    bundleFields.style.display = 'none';
                    regularDiscountField.style.display = 'block';
                } else {
                    // Default state (belum memilih jenis promo)
                    bundleFields.style.display = 'none';
                    regularDiscountField.style.display = 'none';
                }
            }

            // Handle perubahan jenis diskon bundle
            function handleBundleDiscountTypeChange() {
                const bundleDiscountType = document.querySelector('select[name="bundle_discount_type"]');
                const discountSuffix = document.getElementById('discount-suffix');
                const bundleDiscountValue = document.querySelector('input[name="bundle_discount_value"]');
                
                if (bundleDiscountType && discountSuffix) {
                    const isPercentage = bundleDiscountType.value === 'percentage';
                    discountSuffix.textContent = isPercentage ? '%' : 'Rp';
                    
                    // Update placeholder dan validasi
                    if (bundleDiscountValue) {
                        bundleDiscountValue.placeholder = isPercentage ? '0-100' : 'Jumlah diskon';
                        bundleDiscountValue.min = isPercentage ? '0' : '1';
                        bundleDiscountValue.max = isPercentage ? '100' : '';
                    }
                }
            }

            // Validasi nilai diskon bundle
            function validateBundleDiscount() {
                const bundleDiscountType = document.querySelector('select[name="bundle_discount_type"]');
                const bundleDiscountValue = document.querySelector('input[name="bundle_discount_value"]');
                
                if (!bundleDiscountType || !bundleDiscountValue) return true;
                
                const value = parseFloat(bundleDiscountValue.value);
                const isPercentage = bundleDiscountType.value === 'percentage';
                
                if (isNaN(value)) {
                    bundleDiscountValue.setCustomValidity('Masukkan nilai diskon yang valid');
                    return false;
                }
                
                if (isPercentage) {
                    if (value < 0 || value > 100) {
                        bundleDiscountValue.setCustomValidity('Diskon harus antara 0-100%');
                        return false;
                    }
                } else {
                    if (value <= 0) {
                        bundleDiscountValue.setCustomValidity('Diskon harus lebih dari 0');
                        return false;
                    }
                }
                
                bundleDiscountValue.setCustomValidity('');
                return true;
            }

            // Validasi form sebelum submit
            function validateForm(e) {
                let isValid = true;
                const errorMessages = [];
                
                // Validasi dasar
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }
                
                // Validasi tanggal
                const startDate = new Date(document.querySelector('input[name="start_date"]').value);
                const endDate = new Date(document.querySelector('input[name="end_date"]').value);
                
                if (startDate > endDate) {
                    errorMessages.push('Tanggal mulai tidak boleh setelah tanggal berakhir');
                    isValid = false;
                }

                // Validasi berdasarkan jenis promo
                if (promoTypeSelect.value === 'discount') {
                    // Validasi diskon reguler
                    const discount = document.querySelector('input[name="discount"]');
                    if (discount.value && (discount.value < 0 || discount.value > 100)) {
                        errorMessages.push('Diskon harus antara 0-100%');
                        isValid = false;
                    }
                    const selectedMenus = $('.menu-select').val();
                    if (!selectedMenus || selectedMenus.length === 0) {
                        errorMessages.push('Pilih minimal 1 menu untuk diskon');
                        isValid = false;
                    }
                } 
                else if (promoTypeSelect.value === 'bundle') {
                    // Validasi bundle items
                    const bundleItems = document.querySelector('select[name="bundle_items[]"]');
                    if (!bundleItems || bundleItems.selectedOptions.length < 2) {
                        errorMessages.push('Pilih minimal 2 item untuk promo bundle');
                        isValid = false;
                    }
                    
                    // Validasi diskon bundle
                    if (!validateBundleDiscount()) {
                        errorMessages.push('Masukkan nilai diskon bundle yang valid');
                        isValid = false;
                    }
                }

                // Validasi file upload
                if (fileInput && fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    
                    if (!validTypes.includes(file.type)) {
                        errorMessages.push('Format file harus JPG atau PNG');
                        isValid = false;
                    }
                    
                    if (file.size > maxSize) {
                        errorMessages.push('Ukuran file terlalu besar (maksimal 2MB)');
                        isValid = false;
                    }
                }

                // Tampilkan error jika ada
                if (!isValid) {
                    e.preventDefault();
                    showErrorMessages(errorMessages);
                    return false;
                }
                
                return true;
            }

            // Tampilkan pesan error
            function showErrorMessages(messages) {
                // Buat atau update error container
                let errorContainer = document.getElementById('client-side-errors');
                if (!errorContainer) {
                    errorContainer = document.createElement('div');
                    errorContainer.id = 'client-side-errors';
                    errorContainer.className = 'bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded';
                    form.parentNode.insertBefore(errorContainer, form);
                }
                
                // Isi pesan error
                errorContainer.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <div>
                            ${messages.map(msg => `<p class="mb-1">${msg}</p>`).join('')}
                        </div>
                    </div>
                `;
                
                // Scroll ke error
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            // Update nama file yang diupload
            function updateFileName() {
                if (fileInput && fileText) {
                    const fileName = fileInput.files[0]?.name || 'Upload gambar promo';
                    fileText.textContent = fileName;
                    fileText.className = 'text-sm text-orange-600 font-medium';
                }
            }

            // Inisialisasi event listeners
            function initEventListeners() {
                // Toggle fields berdasarkan jenis promo
                if (promoTypeSelect) {
                    promoTypeSelect.addEventListener('change', togglePromoFields);
                    togglePromoFields(); // Jalankan sekali saat load
                }

                // Handle perubahan jenis diskon bundle
                const bundleDiscountType = document.querySelector('select[name="bundle_discount_type"]');
                if (bundleDiscountType) {
                    bundleDiscountType.addEventListener('change', handleBundleDiscountTypeChange);
                    handleBundleDiscountTypeChange(); // Jalankan sekali saat load
                }

                // Validasi real-time untuk diskon bundle
                const bundleDiscountValue = document.querySelector('input[name="bundle_discount_value"]');
                if (bundleDiscountValue) {
                    bundleDiscountValue.addEventListener('input', validateBundleDiscount);
                }

                // Validasi real-time untuk diskon reguler
                const discountInput = document.querySelector('input[name="discount"]');
                if (discountInput) {
                    discountInput.addEventListener('input', function() {
                        if (this.value > 100) {
                            this.setCustomValidity('Diskon tidak boleh melebihi 100%');
                        } else {
                            this.setCustomValidity('');
                        }
                    });
                }

                // Form submission
                if (form) {
                    form.addEventListener('submit', validateForm);
                }

                // File upload
                if (fileInput) {
                    fileInput.addEventListener('change', updateFileName);
                }

                // Inisialisasi Select2 untuk menu target
                $('.menu-select').select2({
                    placeholder: "Pilih menu target",
                    width: '100%',
                    closeOnSelect: false
                });
            }

            togglePromoFields();    
            // Jalankan inisialisasi
            initEventListeners();
        });
    </script>
</body>

</html>