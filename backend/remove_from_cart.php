<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontend/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = $_POST['cart_id'];

// Fetch the car ID and quantity from the cart
$cart_stmt = $conn->prepare("SELECT car_id, quantity FROM cart WHERE cart_id = ? AND user_id = ?");
$cart_stmt->bind_param("ii", $cart_id, $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

if ($cart_result->num_rows > 0) {
    $cart_row = $cart_result->fetch_assoc();
    $car_id = $cart_row['car_id'];
    $quantity = $cart_row['quantity'];

    // Remove the car from the cart
    $remove_cart = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $remove_cart->bind_param("ii", $cart_id, $user_id);
    $remove_cart->execute();

    // Update the quantity in the cars table
    $update_car_quantity = $conn->prepare("UPDATE cars SET quantity = quantity + ? WHERE id = ?");
    $update_car_quantity->bind_param("ii", $quantity, $car_id);
    $update_car_quantity->execute();
}

header('Location: ../frontend/cart.php');
exit();
?>