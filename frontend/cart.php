<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontend/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items with car details
$stmt = $conn->prepare("
    SELECT c.id, c.make, c.model, c.price, c.image_path, c.quantity AS car_quantity, ct.quantity, ct.cart_id 
    FROM cart ct 
    JOIN cars c ON ct.car_id = c.id 
    WHERE ct.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_payment = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="../font-awesome/css/all.css">
    <link rel="stylesheet" href="../assets/css/includes-css/cart-footer.css">
    <script src="https://kit.fontawesome.com/bad2460ef5.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../assets/css/includes-css/cart-header.css">
    <title>Shopping Cart</title>
</head>
<body>
    <?php include "../user-includes/header.php" ?>
    
    <main>
        <div class="cart-container">
            <h2>SHOPPING CART <i class="fa-solid fa-cart-shopping"></i></h2>
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Details</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $total_payment += $row['price'] * $row['quantity']; ?>
                            <tr data-cart-id="<?= $row['cart_id'] ?>" data-car-quantity="<?= $row['car_quantity'] ?>">
                                <td>
                                    <img src="../admin/asset/uploaded_img/<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['model']) ?>">
                                </td>
                                <td>
                                    <h3><?= htmlspecialchars($row['make']) ?> <?= htmlspecialchars($row['model']) ?></h3>
                                    <p class="price">₱<?= number_format($row['price'], 2) ?></p>
                                </td>
                                <td>
                                    <div class="quantity-container">
                                        <button 
                                            class="quantity-btn minus-btn" 
                                            data-cart-id="<?= $row['cart_id'] ?>" 
                                            data-car-quantity="<?= $row['car_quantity'] ?>" 
                                            <?= intval($row['quantity']) <= 1 ? 'disabled' : '' ?>
                                        >
                                            -
                                        </button>
                                        <p class="quantity"><?= htmlspecialchars($row['quantity']) ?></p>
                                        <button 
                                            class="quantity-btn plus-btn" 
                                            data-cart-id="<?= $row['cart_id'] ?>" 
                                            data-car-quantity="<?= $row['car_quantity'] ?>" 
                                            <?= intval($row['car_quantity']) <= 0 ? 'disabled' : '' ?>
                                        >
                                            +
                                        </button>
                                    </div>
                                </td>
                                <td>₱<?= number_format($row['price'], 2) ?></td>
                                <td class="item-total">₱<?= number_format($row['price'] * $row['quantity'], 2) ?></td>
                                <td>
                                    <form action="../backend/remove_from_cart.php" method="POST">
                                        <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
                                        <button type="submit" class="remove-btn">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="total-payment">
                    <h4>Total Payment: ₱<?= number_format($total_payment, 2) ?></h4>
                </div>
                <div class="checkout-btn-container">
                    <a href="checkout.php" method="POST">
                        <button type="submit" class="checkout-btn">Proceed to Checkout</button>
                    </a>
                </div>
            <?php else: ?>
                <p class="empty-cart">YOUR CART IS EMPTY
                    <span><i class="fa-solid fa-cart-shopping"></i></span>
                </p>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php' ?>

    <script src="../assets/js/cart.js"></script>
</body>
</html>