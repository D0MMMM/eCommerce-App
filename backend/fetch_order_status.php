<?php
session_start();
include '../config/db.php';

$user_id = $_SESSION['user_id'];

$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_status
    FROM orders o
    WHERE o.user_id = ?
");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

$orders = [];
while ($order = $order_result->fetch_assoc()) {
    $orders[] = $order;
}

echo json_encode($orders);

$order_stmt->close();
$conn->close();
?>