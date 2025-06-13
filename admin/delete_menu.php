<?php
include('../config/db.php');

if (!isset($conn)) {
    die("Error: Koneksi database tidak tersedia.");
}

if (isset($_GET['id'])) {
    $id_menu = $_GET['id'];

    // Hapus data dari database
    $query = "DELETE FROM menu WHERE id_menu = $id_menu";

    if ($conn->query($query)) {
        echo "<script>alert('Menu berhasil dihapus!'); window.location='menu.php';</script>";
    } else {
        echo "Gagal menghapus menu: " . $conn->error;
    }
} else {
    echo "<script>alert('ID menu tidak ditemukan!'); window.location='menu.php';</script>";
}
?>
