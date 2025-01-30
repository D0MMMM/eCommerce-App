<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cart_id = isset($data['cart_id']) ? intval($data['cart_id']) : 0;
    $new_quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
    $user_id = $_SESSION['user_id'];

    if ($cart_id <= 0 || $new_quantity <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid cart ID or quantity']);
        exit();
    }

    // Fetch current cart details
    $stmt = $conn->prepare("SELECT car_id, quantity FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_row = $result->fetch_assoc();

    if ($cart_row) {
        $car_id = $cart_row['car_id'];
        $current_cart_quantity = $cart_row['quantity'];
        $quantity_difference = $new_quantity - $current_cart_quantity;

        // Fetch current stock of the car
        $stmt = $conn->prepare("SELECT quantity FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $car_row = $result->fetch_assoc();

        if ($car_row) {
            $current_stock = $car_row['quantity'];

            // If increasing quantity, check stock availability
            if ($quantity_difference > 0 && $current_stock < $quantity_difference) {
                echo json_encode(['status' => 'error', 'message' => 'Not enough stock available']);
                exit();
            }

            // Begin transaction for data integrity
            $conn->begin_transaction();

            try {
                // Update cart quantity
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                $stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update cart quantity");
                }

                // Update car stock
                $stmt = $conn->prepare("UPDATE cars SET quantity = quantity - ? WHERE id = ?");
                $stmt->bind_param("ii", $quantity_difference, $car_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update car stock");
                }

                // Fetch updated stock
                $stmt = $conn->prepare("SELECT quantity FROM cars WHERE id = ?");
                $stmt->bind_param("i", $car_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $updated_car_row = $result->fetch_assoc();
                $updated_stock = $updated_car_row['quantity'];

                // Commit transaction
                $conn->commit();

                // Recalculate total payment
                $stmt = $conn->prepare("
                    SELECT SUM(c.price * ct.quantity) AS total_payment
                    FROM cart ct
                    JOIN cars c ON ct.car_id = c.id
                    WHERE ct.user_id = ?
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $payment_row = $result->fetch_assoc();
                $total_payment = $payment_row['total_payment'];

                echo json_encode([
                    'status' => 'success',
                    'total_payment' => (float)$total_payment,
                    'car_quantity' => (int)$updated_stock
                ]);
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                error_log("Update Cart Quantity Error: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Failed to update quantity: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Car not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
    }
}
?>