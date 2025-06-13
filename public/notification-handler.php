<?php
require_once '../vendor/autoload.php';
include '../config/db.php';

// Get raw POST data
$raw_notification = file_get_contents('php://input');

// Log raw data
file_put_contents('midtrans_notifications.log', 
    date('Y-m-d H:i:s') . " - Raw: " . $raw_notification . "\n", 
    FILE_APPEND
);

try {
    // Parse JSON manually
    $notif = json_decode($raw_notification);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse JSON: " . json_last_error_msg());
    }

    // Validate required fields
    if (!isset($notif->order_id) || !isset($notif->transaction_status)) {
        throw new Exception("Missing required fields in notification");
    }

    // Extract ZidanKitchen order ID from Midtrans order ID
    $parts = explode('-', $notif->order_id);
    if (count($parts) < 3 || $parts[0] !== 'ZK') {
        throw new Exception("Invalid order ID format: {$notif->order_id}");
    }

    $order_id = (int)$parts[1];

    // Verify the order exists
    $stmt = $conn->prepare("SELECT id, midtrans_order_id FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Order not found: $order_id");
    }

    $order = $result->fetch_assoc();

    // Verify the Midtrans order ID matches
    if ($order['midtrans_order_id'] !== $notif->order_id) {
        throw new Exception("Order ID mismatch: DB={$order['midtrans_order_id']}, Midtrans={$notif->order_id}");
    }

    // Map Midtrans status to internal status
    $valid_statuses = ['pending', 'paid', 'failed', 'done'];
    switch ($notif->transaction_status) {
        case 'capture':
        case 'settlement':
            $status = 'paid';
            break;
        case 'pending':
            $status = 'pending';
            break;
        case 'deny':
        case 'cancel':
        case 'expire':
            $status = 'failed';
            break;
        default:
            throw new Exception("Unknown transaction_status: {$notif->transaction_status}");
    }

    // Update order status
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $order_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update order status: " . $conn->error);
    }

    // Log success
    file_put_contents('midtrans_notifications.log', 
        date('Y-m-d H:i:s') . " - Order ID: $order_id, Status: $status\n", 
        FILE_APPEND
    );

    http_response_code(200);
    echo "OK";

} catch (Exception $e) {
    // Log error
    file_put_contents('midtrans_notifications.log', 
        date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", 
        FILE_APPEND
    );
    http_response_code(400);
    echo "Error: " . $e->getMessage();
}