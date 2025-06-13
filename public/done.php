<?php
session_start();

// Simpen id_meja sebelum hapus session, dengan fallback
$id_meja = isset($_SESSION['id_meja']) ? (int)$_SESSION['id_meja'] : 1; // Default ke 1 kalo gak ada

// Log buat debug
error_log("done.php - id_meja: " . $id_meja);

// Clear all session data
$_SESSION = [];
session_destroy();

// Pastiin id_meja valid
if ($id_meja <= 0) {
    $id_meja = 1; // Fallback ke meja 1 kalo invalid
}

// Redirect to register after 20 seconds
header("Refresh: 20; url=register.php?table=$id_meja");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Selesai - ZidanKitchen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 1s ease-out forwards; }
    </style>
</head>
<body class="bg-gray-50 font-['Inter'] min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl p-6 max-w-md w-full text-center shadow-md border border-blue-100 animate-fade-in">
        <div class="mb-6">
            <svg class="w-20 h-20 mx-auto text-green-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Pesanan Selesai!</h1>
        <p class="text-gray-600 mb-4">Terima kasih sudah memesan di ZidanKitchen. Kami tunggu kunjungan Anda lagi!</p>
        <p class="text-sm text-gray-500">Anda akan diarahkan ke halaman registrasi dalam <span id="countdown">20</span> detik...</p>
    </div>
    <script>
        // Trigger confetti on load
        confetti({
            particleCount: 150,
            spread: 90,
            origin: { y: 0.6 }
        });

        // Countdown timer
        let countdown = 20;
        const countdownElement = document.getElementById('countdown');
        setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            if (countdown <= 0) {
                window.location = 'register.php';
            }
        }, 1000);
    </script>
</body>
</html>