<?php
session_start();
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zidan Kitchen - Login Admin</title>
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

<div class="absolute inset-0 bg-black/60">
    
</div>
    <div class="relative bg-gradient-to-br from-white/90 to-gray-100/80 p-8 rounded-lg shadow-2xl w-full max-w-md backdrop-blur-lg border border-white/30">
        <h2 class="text-center text-3xl font-bold text-orange-600 mb-2">Zidan Kitchen</h2>
        <p class="text-center text-gray-700 mb-6">Sistem Login Admin Restoran</p>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4"> <?= $error ?> </div>
        <?php endif; ?>
        <form action="login_process.php" method="POST">
            <div class="floating-label mb-4">
                <input type="text" name="username" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder=" " required>
                <label class="text-gray-600">Username</label>
            </div>
            <div class="floating-label mb-4">
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder=" " required>
                <label class="text-gray-600">Password</label>
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-2 rounded-lg hover:opacity-90 transition">Login</button>
        </form>
        <p class="text-center mt-4">Belum punya akun? <a href="register_admin.php" class="text-orange-600 hover:underline">Daftar</a></p>
    </div>

</body>

</html> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>