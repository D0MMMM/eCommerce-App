<?php
session_start();
include '../config/db.php';
require '../vendor/autoload.php';
require 'send_order_email.php'; // Include the email sending script

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Symfony\Component\Yaml\Yaml;

if (!isset($_SESSION['paypal_order_id']) || !isset($_GET['token'])) {
    header('Location: ../frontend/cart.php');
    exit();
}

// Load PayPal credentials from the YAML file
$config = Yaml::parseFile(__DIR__ . '/../config/config.yml');

$clientId = $config['paypal']['client_id'];
$clientSecret = $config['paypal']['client_secret'];

// Create PayPal environment
$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

try {
    // Get the PayPal order
    $getRequest = new OrdersGetRequest($_SESSION['paypal_order_id']);
    $getResponse = $client->execute($getRequest);

    if ($getResponse->result->status === 'COMPLETED') {
        // Order already captured
        throw new Exception('Order already captured.');
    }

    // Capture the PayPal order (complete the payment)
    $captureRequest = new OrdersCaptureRequest($_SESSION['paypal_order_id']);
    $captureResponse = $client->execute($captureRequest);

    if ($captureResponse->result->status === 'COMPLETED') {
        // Get user details
        $userId = $_SESSION['user_id'];
        $user_stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
        $user_stmt->bind_param("i", $userId);
        $user_stmt->execute();
        $user = $user_stmt->get_result()->fetch_assoc();

        if (!$user) {
            throw new Exception('User not found');
        }

        // Ensure all required fields are populated
        $required_fields = ['username', 'email', 'contact_number'];
        foreach ($required_fields as $field) {
            if (empty($user[$field])) {
                throw new Exception("User $field is required but is missing.");
            }
        }

        // Ensure address details are available in session
        $address_fields = ['contact_name', 'contact_email', 'contact_phone', 'address', 'city', 'state', 'zip', 'country'];
        foreach ($address_fields as $field) {
            if (empty($_SESSION[$field])) {
                throw new Exception("Address $field is required but is missing.");
            }
        }

        // Get cart items
        $cart_stmt = $conn->prepare("
            SELECT c.*, ct.quantity 
            FROM cart ct 
            JOIN cars c ON ct.car_id = c.id 
            WHERE ct.user_id = ?
        ");
        $cart_stmt->bind_param("i", $userId);
        $cart_stmt->execute();
        $cart_items = $cart_stmt->get_result();

        $total_amount = 0;
        $order_items = [];

        while ($item = $cart_items->fetch_assoc()) {
            $subtotal = $item['price'] * $item['quantity'];
            $total_amount += $subtotal;
            $order_items[] = $item;
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert into orders table
            $order_stmt = $conn->prepare("
                INSERT INTO orders (
                    user_id,
                    contact_name,
                    contact_email,
                    contact_phone,
                    payment_method,
                    address,
                    city,
                    state,
                    zip,
                    country,
                    order_status,
                    total_amount,
                    payment_status,
                    payment_id
                ) VALUES (?, ?, ?, ?, 'paypal', ?, ?, ?, ?, ?, 'processing', ?, 'paid', ?)
            ");
            $paymentId = $captureResponse->result->id;
            $order_stmt->bind_param(
                "ississsisds",
                $userId,
                $_SESSION['contact_name'],
                $_SESSION['contact_email'],
                $_SESSION['contact_phone'],
                $_SESSION['address'],
                $_SESSION['city'],
                $_SESSION['state'],
                $_SESSION['zip'],
                $_SESSION['country'],
                $total_amount,
                $paymentId
            );

            if (!$order_stmt->execute()) {
                throw new Exception('Failed to create order');
            }

            $order_id = $conn->insert_id;

            // Insert order items
            $items_stmt = $conn->prepare("
                INSERT INTO order_items (
                    order_id,
                    car_id,
                    quantity,
                    price,
                    subtotal
                ) VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($order_items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $items_stmt->bind_param(
                    "iiidd",
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                );
                if (!$items_stmt->execute()) {
                    throw new Exception('Failed to create order item');
                }
            }

            // Clear cart
            $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $clear_cart->bind_param("i", $userId);
            $clear_cart->execute();

            // Commit transaction
            $conn->commit();

            // Send order confirmation email
            if (!sendOrderEmail($order_id, $_SESSION['contact_name'], $_SESSION['contact_email'], 'PayPal', $total_amount)) {
                throw new Exception("Failed to send order confirmation email.");
            }

            // Clear PayPal session data
            unset($_SESSION['paypal_order_id']);

            echo "<script>
                alert('Payment successful! Thank you for your order.');
                window.location.href = '../frontend/dashboard.php';
            </script>";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
    }
} catch (Exception $e) {
    $errorMessage = addslashes($e->getMessage());
    echo "<script>
        alert('Error processing payment: $errorMessage');
        window.location.href = '../frontend/cart.php';
    </script>";
}
?>