<?php
$host = "localhost";
$user = "root"; 
$pass = "";
$dbname = "zidankitchen";

$conn = new mysqli($host, $user, $pass, $dbname);

date_default_timezone_set('Asia/Jakarta'); // Set ke WIB
$conn->query("SET time_zone = '+07:00'"); // Set MySQL ke WIB juga

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
