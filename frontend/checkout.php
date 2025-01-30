<?php
session_start();
include '../config/db.php';
require '../backend/send_order_email.php'; // Include the email sending script

if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontend/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_name = $_POST['contact_name'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $payment_method = $_POST['payment_method'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $country = $_POST['country'];
    $total_amount = floatval($_POST['total_amount']); // Ensure total_amount is a float

    // Store address and contact details in session
    $_SESSION['contact_name'] = $contact_name;
    $_SESSION['contact_email'] = $contact_email;
    $_SESSION['contact_phone'] = $contact_phone;
    $_SESSION['address'] = $address;
    $_SESSION['city'] = $city;
    $_SESSION['state'] = $state;
    $_SESSION['zip'] = $zip;
    $_SESSION['country'] = $country;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert order into database
        $stmt = $conn->prepare("INSERT INTO orders (user_id, contact_name, contact_email, contact_phone, payment_method, address, city, state, zip, country, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssssd", $user_id, $contact_name, $contact_email, $contact_phone, $payment_method, $address, $city, $state, $zip, $country, $total_amount);

        if (!$stmt->execute()) {
            throw new Exception("Order insertion failed: " . $stmt->error);
        }

        $order_id = $conn->insert_id;

        // Fetch cart items
        $cart_query = $conn->prepare("
            SELECT c.id, c.make, c.model, c.price, c.image_path, ct.quantity 
            FROM cart ct 
            JOIN cars c ON ct.car_id = c.id 
            WHERE ct.user_id = ?
        ");
        $cart_query->bind_param("i", $user_id);
        $cart_query->execute();
        $cart_result = $cart_query->get_result();

        // Insert order items into database
        $items_stmt = $conn->prepare("INSERT INTO order_items (order_id, car_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        while ($item = $cart_result->fetch_assoc()) {
            $subtotal = $item['price'] * $item['quantity'];
            $items_stmt->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item['price'], $subtotal);
            if (!$items_stmt->execute()) {
                throw new Exception("Order item insertion failed: " . $items_stmt->error);
            }
        }

        // Clear cart
        $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_cart->bind_param("i", $user_id);
        $clear_cart->execute();

        // Commit transaction
        $conn->commit();

        // Send order confirmation email
        if (!sendOrderEmail($order_id, $contact_name, $contact_email, $payment_method, $total_amount)) {
            throw new Exception("Failed to send order confirmation email.");
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Order placed successfully'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log($e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to place order. Please try again.'
        ]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/checkout.css">
    <link rel="stylesheet" href="../font-awesome/css/all.css">
    <link rel="stylesheet" href="../assets/css/includes-css/cart-header.css">
    <link rel="stylesheet" href="../assets/css/includes-css/cart-footer.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Checkout</title>
</head>
<body>
    <?php include "../user-includes/header.php" ?>
    
    <main>
        <div class="checkout-wrapper">
            <!-- Checkout Form -->
            <div class="checkout-container">
                <h2>Checkout</h2>
                <form action="checkout.php" method="POST">
                    <!-- Contact Information -->
                    <div class="form-section">
                        <h3>Contact Information</h3>
                        <div class="form-group">
                            <label for="contact_name">Full Name</label>
                            <input type="text" id="contact_name" name="contact_name" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_email">Email</label>
                            <input type="email" id="contact_email" name="contact_email" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_phone">Phone Number</label>
                            <input type="tel" id="contact_phone" name="contact_phone" required>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="form-section">
                        <h3>Delivery Address</h3>
                        <div class="form-group">
                            <label for="address">Street Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country" required>
                                <option value="Philippines">Philippines</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="state">Province/Region</label>
                            <select id="state" name="state" required></select>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <select id="city" name="city" required></select>
                        </div>
                        <div class="form-group">
                            <label for="zip">ZIP Code</label>
                            <input type="text" id="zip" name="zip" required>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h3>Payment Method</h3>
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                <label for="cod">
                                    <i class="fas fa-truck"></i>
                                    Cash on Delivery
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="paypal" name="payment_method" value="paypal">
                                <label for="paypal">
                                    <i class="fab fa-paypal"></i>
                                    PayPal
                                </label>
                            </div>
                            <!-- <div class="payment-option">
                                <input type="radio" id="gcash" name="payment_method" value="gcash">
                                <label for="gcash">
                                    <i class="fas fa-wallet"></i>
                                    GCash
                                </label>
                            </div> -->
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" id="placeOrderBtn">
                        <span class="btn-text">Place Order</span>
                        <span class="btn-loader" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </form>
            </div>
            <!-- Product Summary -->
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="cart-items">
                    <?php
                    // Fetch cart items
                    $cart_query = $conn->prepare("
                        SELECT c.id, c.make, c.model, c.price, c.image_path, ct.quantity 
                        FROM cart ct 
                        JOIN cars c ON ct.car_id = c.id 
                        WHERE ct.user_id = ?
                    ");
                    $cart_query->bind_param("i", $user_id);
                    $cart_query->execute();
                    $cart_result = $cart_query->get_result();
                    $total = 0;

                    while ($item = $cart_result->fetch_assoc()):
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <div class="cart-item">
                            <img src="../admin/asset/uploaded_img/<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['model']) ?>">
                            <div class="item-details">
                                <h4><?= htmlspecialchars($item['make']) ?> <?= htmlspecialchars($item['model']) ?></h4>
                                <p>Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                                <p class="price">₱<?= number_format($item['price'], 2) ?></p>
                            </div>
                            <div class="item-total">
                                ₱<?= number_format($subtotal, 2) ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="order-total">
                    <h3>Total Payment</h3>
                    <p class="total-amount">₱<?= number_format($total, 2) ?></p>
                    <input type="hidden" name="total_amount" value="<?= $total ?>">
                </div>
            </div>
        </div>
    </main>

    <?php include "../includes/footer.php" ?>

    <script src="../assets/js/checkout.js"></script>
</body>
</html>