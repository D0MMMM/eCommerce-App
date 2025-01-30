<?php
session_start();
include '../config/db.php';

// if (!isset($_SESSION['admin'])) {
//     echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Fetch the current payment method for the order
    $stmt = $conn->prepare("SELECT payment_method FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        if ($order['payment_method'] === 'cod') {
            // Update the order status and payment status if the payment method is 'cod'
            $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = IF(? = 'delivered', 'paid', payment_status) WHERE order_id = ?");
            $stmt->bind_param("ssi", $status, $status, $order_id);
        } elseif ($order['payment_method'] === 'paypal' || $order['payment_method'] === 'gcash') {
            // Update the order status and payment status if the payment method is 'paypal' or 'gcash'
            $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
            $stmt->bind_param("si", $status, $order_id);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unsupported payment method']);
            exit();
        }
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Order status updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update order status']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>