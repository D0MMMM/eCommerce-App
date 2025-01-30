<?php
session_start();
include '../config/db.php';
require '../vendor/autoload.php'; // Ensure Composer's autoloader is included

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Symfony\Component\Yaml\Yaml;

// Load PayPal credentials from the YAML file
$config = Yaml::parseFile(__DIR__ . '/../config/config.yml');

$clientId = $config['paypal']['client_id'];
$clientSecret = $config['paypal']['client_secret'];

// Create PayPal environment
$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontend/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items for the user
$stmt = $conn->prepare("
    SELECT c.id, c.make, c.model, c.price, ct.quantity 
    FROM cart ct 
    JOIN cars c ON ct.car_id = c.id 
    WHERE ct.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = [
        'name' => $row['make'] . ' ' . $row['model'],
        'quantity' => $row['quantity'],
        'unit_amount' => [
            'currency_code' => 'PHP',
            'value' => number_format($row['price'], 2, '.', '')
        ]
    ];
    $total += $row['price'] * $row['quantity'];
}

// Store address and contact details in session
$_SESSION['contact_name'] = $_POST['contact_name'];
$_SESSION['contact_email'] = $_POST['contact_email'];
$_SESSION['contact_phone'] = $_POST['contact_phone'];
$_SESSION['address'] = $_POST['address'];
$_SESSION['city'] = $_POST['city'];
$_SESSION['state'] = $_POST['state'];
$_SESSION['zip'] = $_POST['zip'];
$_SESSION['country'] = $_POST['country'];

// Create PayPal order
$request = new OrdersCreateRequest();
$request->prefer('return=representation');
$request->body = [
    'intent' => 'CAPTURE',
    'application_context' => [
        'return_url' => 'http://localhost/final-project/backend/process_paypal_success.php',
        'cancel_url' => 'http://localhost/final-project/backend/process_paypal_cancel.php'
    ],
    'purchase_units' => [[
        'amount' => [
            'currency_code' => 'PHP',
            'value' => number_format($total, 2, '.', ''),
            'breakdown' => [
                'item_total' => [
                    'currency_code' => 'PHP',
                    'value' => number_format($total, 2, '.', '')
                ]
            ]
        ],
        'items' => $items
    ]]
];

try {
    // Call PayPal to create the order
    $response = $client->execute($request);
    
    // Store PayPal order ID in session
    $_SESSION['paypal_order_id'] = $response->result->id;
    
    // Redirect to PayPal checkout
    foreach ($response->result->links as $link) {
        if ($link->rel === 'approve') {
            echo json_encode([
                'status' => 'success',
                'redirect_url' => $link->href
            ]);
            exit();
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error creating PayPal order: ' . $e->getMessage()
    ]);
    exit();
}