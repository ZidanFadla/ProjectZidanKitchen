<?php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

$order_id = (int)($_GET['order_id'] ?? 0);
if ($order_id === 0 || !isset($_SESSION['customer_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order_id or session']);
    exit;
}

$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit;
}

$order = $result->fetch_assoc();
echo json_encode(['status' => $order['status'] ?? 'pending']);
$stmt->close();
?>