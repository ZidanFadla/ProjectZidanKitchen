<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Cek apakah username sudah ada
    $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username sudah digunakan!";
        header("Location: register_admin.php");
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert admin baru
    $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Admin berhasil ditambahkan!";
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan admin!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zidan Kitchen - Register Admin</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo_oren.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .floating-label {
            position: relative;
        }

        .floating-label input {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 14px;
            width: 100%;
            outline: none;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.8);
        }

        .floating-label label {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.8);
            padding: 0 5px;
            transition: all 0.3s ease;
            color: #777;
            pointer-events: none;
        }

        .floating-label input:focus+label,
        .floating-label input:not(:placeholder-shown)+label {
            top: 0;
            font-size: 12px;
            color: #d97706;
        }
    </style>
</head>
<body class="relative flex items-center justify-center h-screen bg-cover bg-center bg-no-repeat bg-[url('/ZidanKitchen/assets/images/bg.png')]">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative bg-gradient-to-br from-white/90 to-gray-100/80 p-8 rounded-lg shadow-2xl w-full max-w-md backdrop-blur-lg border border-white/30">
        <h2 class="text-center text-3xl font-bold text-orange-600 mb-2">Zidan Kitchen</h2>
        <p class="text-center text-gray-700 mb-6">Buat Akun Admin Restoran</p>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <form action="register_admin.php" method="POST">
            <div class="floating-label mb-4">
                <input type="text" name="username" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder=" " required>
                <label class="text-gray-600">Username</label>
            </div>
            <div class="floating-label mb-4">
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder=" " required>
                <label class="text-gray-600">Password</label>
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-2 rounded-lg hover:opacity-90 transition">Register</button>
        </form>
        <p class="text-center mt-4">Sudah punya akun? <a href="login.php" class="text-orange-600 hover:underline">Login</a></p>
    </div>
</body>
</html>
