<?php
session_start();
include '../config/db.php';

// Pastikan ada keranjang & ID Meja
if (!isset($_SESSION['keranjang']) || count($_SESSION['keranjang']) === 0 || !isset($_SESSION['id_meja'])) {
    header("Location: menu.php");
    exit;
}

$id_meja = $_SESSION['id_meja'] ?? null;
if (!$id_meja) {
    die("Error: ID meja tidak ditemukan. Silakan ulangi proses pemesanan.");
}

// PROSES FORM HANYA JIKA ADA REQUEST POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['metode_pembayaran']) || empty($_POST['metode_pembayaran'])) {
        die("Error: Metode pembayaran belum dipilih.");
    }
    
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $total_harga = 0;

    // Simpan order ke database
    $sql_order = "INSERT INTO orders (id_meja, total_harga, metode_pembayaran, status, created_at) VALUES (?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($sql_order);
    $stmt->bind_param("ids", $id_meja, $total_harga, $metode_pembayaran);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Simpan detail order (produk di keranjang)
    foreach ($_SESSION['keranjang'] as $item) {
        // Gunakan harga promo jika tersedia
        $harga_satuan = isset($item['harga_promo']) && $item['harga_promo'] > 0 ? $item['harga_promo'] : $item['harga'];

        $subtotal = $harga_satuan * $item['jumlah'];
        $total_harga += $subtotal;

        $sql_detail = "INSERT INTO order_items (order_id, id_menu, nama_menu, jumlah, harga, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_detail);
        $stmt->bind_param("iisidd", $order_id, $item['id_menu'], $item['nama_menu'], $item['jumlah'], $harga_satuan, $subtotal);
        $stmt->execute();
    }


    // Update total harga di order
    $sql_update = "UPDATE orders SET total_harga = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("di", $total_harga, $order_id);
    $stmt->execute();

    // Hapus keranjang setelah checkout
    unset($_SESSION['keranjang']);

    // Redirect ke pembayaran (kalau pakai payment gateway)
    if ($metode_pembayaran === 'QRIS') {
        header("Location: payment.php?order_id=" . $order_id);
        exit;
    } else {
        header("Location: success.php");
        exit;
    }
}
?>