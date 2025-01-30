<?php
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Fetch all orders related to the user
    $orders_stmt = $conn->prepare("SELECT order_id FROM orders WHERE user_id = ?");
    $orders_stmt->bind_param("i", $id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();

    // Delete related order items first
    while ($order = $orders_result->fetch_assoc()) {
        $order_id = $order['order_id'];
        $delete_order_items_stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $delete_order_items_stmt->bind_param("i", $order_id);
        $delete_order_items_stmt->execute();
    }

    // Delete related orders
    $delete_orders_stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
    $delete_orders_stmt->bind_param("i", $id);
    $delete_orders_stmt->execute();

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ../views/profile.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>