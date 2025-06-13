<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Password salah!";
        }
    } else {
        $_SESSION['login_error'] = "Username tidak ditemukan!";
    }

    // Redirect balik ke login.php kalau ada error
    header("Location: login.php");
    exit;
}
?>
