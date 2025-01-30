<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : 0;
    $user_id = $_SESSION['user_id'];

    if ($car_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid car ID']);
        exit();
    }

    // Check stock
    $stmt = $conn->prepare("SELECT quantity FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();

    if ($car['quantity'] <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Car is out of stock']);
        exit();
    }

    // Add to cart
    $stmt = $conn->prepare("INSERT INTO cart (user_id, car_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
    $stmt->bind_param("ii", $user_id, $car_id);
    if ($stmt->execute()) {
        // Update stock
        $stmt = $conn->prepare("UPDATE cars SET quantity = quantity - 1 WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();

        // Get new stock
        $stmt = $conn->prepare("SELECT quantity FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $updated_car = $result->fetch_assoc();
        $new_stock = $updated_car['quantity'];

        echo json_encode(['status' => 'success', 'car_id' => $car_id, 'new_stock' => $new_stock]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add to cart']);
    }
}
?>