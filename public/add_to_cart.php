<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    if (!isset($_POST['id_menu']) || !isset($_POST['nama_menu']) || !isset($_POST['harga'])) {
        $_SESSION['error'] = "Data menu tidak lengkap";
        header("Location: menu.php");
        exit;
    }

    $id_menu = (int)$_POST['id_menu'];
    $nama_menu = trim($_POST['nama_menu']);
    $harga = (float)$_POST['harga'];
    $harga_promo = isset($_POST['harga_promo']) ? (float)$_POST['harga_promo'] : $harga;
    $gambar = isset($_POST['gambar']) ? trim($_POST['gambar']) : 'default.jpg';
    $promo_id = isset($_POST['promo_id']) ? (int)$_POST['promo_id'] : null;
    $promo_type = isset($_POST['promo_type']) ? trim($_POST['promo_type']) : null;

    // Inisialisasi keranjang jika belum ada
    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }

    // Cari item di keranjang
    $item_index = null;
    foreach ($_SESSION['keranjang'] as $index => $item) {
        if ($item['id_menu'] == $id_menu && $item['promo_id'] == $promo_id) {
            $item_index = $index;
            break;
        }
    }

    // Jika item sudah ada, tambahkan jumlah
    if ($item_index !== null) {
        $_SESSION['keranjang'][$item_index]['jumlah'] += 1;
    } else {
        // Tambahkan item baru ke keranjang
        $_SESSION['keranjang'][] = [
            'id_menu' => $id_menu,
            'nama_menu' => $nama_menu,
            'harga' => $harga,
            'harga_promo' => $harga_promo,
            'gambar' => $gambar,
            'jumlah' => 1, // Gunakan 'jumlah' bukan 'qty'
            'promo_id' => $promo_id,
            'promo_type' => $promo_type
        ];
    }

    $_SESSION['success'] = "Item berhasil ditambahkan ke keranjang";
    header("Location: menu.php");
    exit;
}

// Jika akses langsung ke file
header("Location: menu.php");
exit;
?>