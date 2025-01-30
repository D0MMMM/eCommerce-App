<?php
session_start();
include '../config/db.php';
require '../vendor/autoload.php'; // Ensure Composer's autoloader is included

use Symfony\Component\Yaml\Yaml;

// Suppress error reporting
error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_GET['payment_intent_id'])) {
    echo "<script>
        alert('Payment not completed.');
        window.location.href = '../frontend/cart.php';
    </script>";
    exit;
}

$paymentIntentId = $_GET['payment_intent_id'];

// Load PayMongo secret key from the YAML file
$config = Yaml::parseFile(__DIR__ . '/../config/config.yml');
$secretKey = $config['paymongo']['secret_key'];

// Retrieve the Payment Intent
$paymentIntent = paymongoGetRequest("/payment_intents/{$paymentIntentId}", $secretKey);

if (isset($paymentIntent['errors']) || !$paymentIntent['data']) {
    echo "<script>
        alert('Failed to retrieve payment status.');
        window.location.href = '../frontend/cart.php';
    </script>";
    exit;
}

$status = $paymentIntent['data']['attributes']['status'];

if ($status !== 'succeeded') {
    echo "<script>
        alert('Payment not successful.');
        window.location.href = '../frontend/cart.php';
    </script>";
    exit;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['contact_name'])) {
    header('Location: ../frontend/cart.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $conn->begin_transaction();

    // Get user details
    $user_stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    if (!$user_stmt) {
        throw new Exception('Failed to prepare user statement');
    }
    $user_stmt->bind_param("i", $user_id);
    if (!$user_stmt->execute()) {
        throw new Exception('Failed to execute user statement');
    }
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
    if (!$cart_stmt) {
        throw new Exception('Failed to prepare cart statement');
    }
    $cart_stmt->bind_param("i", $user_id);
    if (!$cart_stmt->execute()) {
        throw new Exception('Failed to execute cart statement');
    }
    $cart_items = $cart_stmt->get_result();

    $total_amount = 0;
    $order_items = [];

    while ($item = $cart_items->fetch_assoc()) {
        $subtotal = $item['price'] * $item['quantity'];
        $total_amount += $subtotal;
        $order_items[] = $item;
    }

    // Create order
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
        ) VALUES (?, ?, ?, ?, 'gcash', ?, ?, ?, ?, ?, 'processing', ?, 'paid', ?)
    ");
    if (!$order_stmt) {
        throw new Exception('Failed to prepare order statement');
    }
    $paymentId = $paymentIntent['data']['id'];
    $order_stmt->bind_param(
        "isssssssssdss",
        $user_id,
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
    if (!$items_stmt) {
        throw new Exception('Failed to prepare order items statement');
    }
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
    if (!$clear_cart) {
        throw new Exception('Failed to prepare clear cart statement');
    }
    $clear_cart->bind_param("i", $user_id);
    if (!$clear_cart->execute()) {
        throw new Exception('Failed to clear cart');
    }

    $conn->commit();

    // Send success response
    echo "<script>
        alert('Payment successful! Thank you for your order.');
        window.location.href = '../frontend/dashboard.php';
    </script>";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    $errorMessage = addslashes($e->getMessage());
    echo "<script>
        alert('Error processing payment: $errorMessage');
        window.location.href = '../frontend/cart.php';
    </script>";
}

// Helper function to make GET requests to PayMongo API
function paymongoGetRequest($url, $secretKey) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($secretKey . ':')
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return [
            'errors' => [
                'message' => curl_error($ch)
            ]
        ];
    }
    curl_close($ch);

    return json_decode($result, true);
}