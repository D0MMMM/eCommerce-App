<?php
session_start();
include "../config/db.php";
include "../backend/orders.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../asset/style.css">
    <link rel="stylesheet" href="../asset/view-css/orders.css">
    <link rel="stylesheet" href="../lib/datatable/DataTables.css" defer>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/bad2460ef5.js" crossorigin="anonymous"></script>
    <title>Order Management</title>
</head>     
<body>
    <?php include "../include/sidebar.php" ?>
    <main>
        <div class="toyota-container">
            <span style="margin-right: 1em; font-size: 1.2em"><i class="fa-solid fa-bars"></i></span> <span style="color: red;">ORDER MANAGEMENT</span>
            <span style="float: right;">DASHBOARD</span>
        </div>
        <div class="orders-container">
            <div class="filter-section">
                <label for="statusFilter">Filter by status:</label>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <table id="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Products</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders_query = "SELECT o.*, u.username 
                                   FROM orders o 
                                   JOIN user u ON o.user_id = u.id 
                                   ORDER BY o.created_at DESC";
                    $orders_result = mysqli_query($conn, $orders_query);
                    
                    while($order = mysqli_fetch_assoc($orders_result)):
                    ?>
                    <tr>
                        <td>#<?= $order['order_id'] ?></td>
                        <td><?= htmlspecialchars($order['contact_name']) ?></td>
                        <td>
                            <?= htmlspecialchars($order['contact_phone']) ?><br>
                            <?= htmlspecialchars($order['contact_email']) ?>
                        </td>
                        <td>
                            <?php
                            $items_query = "SELECT c.make, c.model, oi.quantity 
                                          FROM order_items oi 
                                          JOIN cars c ON oi.car_id = c.id 
                                          WHERE oi.order_id = ?";
                            $items_stmt = $conn->prepare($items_query);
                            $items_stmt->bind_param("i", $order['order_id']);
                            $items_stmt->execute();
                            $items_result = $items_stmt->get_result();
                            
                            while($item = $items_result->fetch_assoc()) {
                                echo htmlspecialchars($item['make'] . ' ' . $item['model'] . ' x' . $item['quantity']) . "<br>";
                            }
                            ?>
                        </td>
                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                        <td class="order-status"><?= htmlspecialchars($order['order_status']) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
                        <td id="order-actions">
                            <select class="status-select" data-order-id="<?= $order['order_id'] ?>">
                                <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $order['order_status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $order['order_status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $order['order_status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button class="view-btn" data-order-id="<?= $order['order_id'] ?>">
                                <i class="fa-solid fa-folder-open"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <!-- <span class="close">&times;</span> -->
            <div id="orderDetails"></div>
        </div>
    </div>

    <script src="../lib/jquery/jquery.min.js"></script>
    <script src="../lib/datatable/DataTables.js"></script>
    <script src="../asset/js/order.js"></script>
    <script src="../asset/app.js"></script>
</body>
</html>