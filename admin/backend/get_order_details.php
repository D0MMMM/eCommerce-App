<?php
session_start();
include '../config/db.php';

if (!isset($_GET['id'])) {
    echo "Order ID is required.";
    exit();
}

$order_id = $_GET['id'];

// Fetch order details
$order_stmt = $conn->prepare("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN user u ON o.user_id = u.id 
    WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "Order not found.";
    exit();
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_stmt = $conn->prepare("
    SELECT c.make, c.model, oi.quantity, oi.price 
    FROM order_items oi 
    JOIN cars c ON oi.car_id = c.id 
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<div class="order-details-container">
    <h2>Order details</h2>
    <div class="order-info">

        <p><strong>Order ID:</strong> <span>#<?= htmlspecialchars($order['order_id']) ?></span></p>
        <p><strong>Customer:</strong> <span><?= htmlspecialchars($order['username']) ?></span></p>
        <p><strong>Contact Name:</strong> <span><?= htmlspecialchars($order['contact_name']) ?></span></p>
        <p><strong>Contact Phone:</strong> <span><?= htmlspecialchars($order['contact_phone']) ?></span></p>
        <p><strong>Contact Email:</strong> <span><?= htmlspecialchars($order['contact_email']) ?></span></p>
        <p><strong>Total Amount:</strong> <span>₱<?= number_format($order['total_amount'], 2) ?></span></p>
        <p><strong>Order Status:</strong> <span><?= htmlspecialchars($order['order_status']) ?></span></p>
        <p><strong>Payment Status:</strong> <span><?= htmlspecialchars($order['payment_status']) ?></span></p>
        <p><strong>Order Date:</strong> <span><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></span></p>
        <p><strong>Delivery Date:</strong> <span><?= date('Y-m-d', strtotime($order['created_at'] . ' + 14 days')) ?></span></p>
    </div>
    <h3>Order Items</h3>
    <div class="order-items">
        <?php while ($item = $items_result->fetch_assoc()): ?>
            <div class="order-item">
                <p><strong>Make:</strong> <span><?= htmlspecialchars($item['make']) ?></span></p>
                <p><strong>Model:</strong> <span><?= htmlspecialchars($item['model']) ?></span></p>
                <p><strong>Quantity:</strong> <span><?= htmlspecialchars($item['quantity']) ?></span></p>
                <p><strong>Price:</strong> <span>₱<?= number_format($item['price'], 2) ?></span></p>
                <p><strong>Total:</strong> <span>₱<?= number_format($item['quantity'] * $item['price'], 2) ?></span></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>