<?php
session_start();
include '../config/db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Pastikan ada ID pesanan yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ID pesanan tidak ditemukan.");
}

$order_id = intval($_GET['id']); // Pastikan ID valid

// Update status pesanan menjadi selesai
$sql = "UPDATE orders SET status = 'done' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Pesanan #$order_id berhasil diselesaikan.";
} else {
    $_SESSION['error_message'] = "Gagal menyelesaikan pesanan.";
}

// Kembali ke dashboard admin
header("Location: order.php");
exit;
?>
