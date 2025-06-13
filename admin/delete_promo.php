<?php
include('../config/db.php');

if (!isset($conn)) {
    die("Error: Koneksi database tidak tersedia.");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus data dari database
    $query = "DELETE FROM promos WHERE id = $id";

    if ($conn->query($query)) {
        echo "<script>alert('promo berhasil dihapus!'); window.location='promos.php';</script>";
    } else {
        echo "Gagal menghapus promo: " . $conn->error;
    }
} else {
    echo "<script>alert('ID promo tidak ditemukan!'); window.location='promos.php';</script>";
}
?>