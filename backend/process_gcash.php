<?php
session_start();
include '../config/db.php';
require '../vendor/autoload.php'; // Ensure Composer's autoloader is included

use Symfony\Component\Yaml\Yaml;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontend/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Store address and contact details in session
$_SESSION['contact_name'] = $_POST['contact_name'];
$_SESSION['contact_email'] = $_POST['contact_email'];
$_SESSION['contact_phone'] = $_POST['contact_phone'];
$_SESSION['address'] = $_POST['address'];
$_SESSION['city'] = $_POST['city'];
$_SESSION['state'] = $_POST['state'];
$_SESSION['zip'] = $_POST['zip'];
$_SESSION['country'] = $_POST['country'];

// Fetch user and cart details
$user_stmt = $conn->prepare("SELECT id, username, email, contact_number FROM user WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$cart_stmt = $conn->prepare("
    SELECT c.id, c.make, c.model, c.price, ct.quantity 
    FROM cart ct 
    JOIN cars c ON ct.car_id = c.id 
    WHERE ct.user_id = ?
");
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_items = $cart_stmt->get_result();

$total_amount = 0;
$order_items = [];

while ($item = $cart_items->fetch_assoc()) {
    $subtotal = $item['price'] * $item['quantity'];
    $total_amount += $subtotal;
    $order_items[] = $item;
}

// Load PayMongo secret key from the YAML file
$config = Yaml::parseFile(__DIR__ . '/../config/config.yml');
$secretKey = $config['paymongo']['secret_key'];

// Create GCash payment via PayMongo
$gcashPayment = createGcashPayment($user_id, $total_amount, $secretKey);

if ($gcashPayment['status'] === 'success') {
    echo json_encode([
        'status' => 'success',
        'redirect_url' => $gcashPayment['redirect_url']
    ]);
    exit;
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $gcashPayment['message']
    ]);
    exit;
}

// Function to create GCash payment via PayMongo
function createGcashPayment($userId, $amount, $secretKey) {
    // Format amount in centavos
    $amountInCentavos = $amount * 100;

    // Create a Payment Intent
    $paymentIntentData = [
        'data' => [
            'attributes' => [
                'amount' => $amountInCentavos,
                'payment_method_allowed' => ['gcash'],
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'any'
                    ]
                ],
                'currency' => 'PHP',
            ]
        ]
    ];

    $paymentIntent = paymongoRequest('/payment_intents', $paymentIntentData, $secretKey);

    if (!isset($paymentIntent['data']['id'])) {
        return [
            'status' => 'error',
            'message' => 'Network error, please try again later. ' . json_encode($paymentIntent)
        ];
    }

    $paymentIntentId = $paymentIntent['data']['id'];
    $clientKey = $paymentIntent['data']['attributes']['client_key'];

    // Create a Payment Method for GCash
    $paymentMethodData = [
        'data' => [
            'attributes' => [
                'type' => 'gcash',
                'billing' => [
                    'name' => $_SESSION['contact_name'],
                    'email' => $_SESSION['contact_email'],
                    'phone' => $_SESSION['contact_phone']
                ]
            ]
        ]
    ];

    $paymentMethod = paymongoRequest('/payment_methods', $paymentMethodData, $secretKey);

    if (!isset($paymentMethod['data']['id'])) {
        return [
            'status' => 'error',
            'message' => 'Failed to create Payment Method. ' . json_encode($paymentMethod)
        ];
    }

    $paymentMethodId = $paymentMethod['data']['id'];

    // Attach Payment Method to Payment Intent
    $attachData = [
        'data' => [
            'attributes' => [
                'payment_method' => $paymentMethodId,
                'client_key' => $clientKey,
                'return_url' => 'http://localhost/final-project/backend/verify_gcash_payment.php' // Update this URL
            ]
        ]
    ];

    $attachedIntent = paymongoRequest("/payment_intents/{$paymentIntentId}/attach", $attachData, $secretKey);

    if (isset($attachedIntent['errors'])) {
        return [
            'status' => 'error',
            'message' => 'Failed to attach Payment Method. ' . json_encode($attachedIntent)
        ];
    }

    $redirectUrl = $attachedIntent['data']['attributes']['next_action']['redirect']['url'];

    return [
        'status' => 'success',
        'redirect_url' => $redirectUrl
    ];
}

// Helper function to make requests to PayMongo API
function paymongoRequest($url, $data, $secretKey) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_POST, 1);

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCash Payment</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .gcash-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            text-align: center;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .qr-code {
            margin: 2rem 0;
            padding: 1rem;
            border: 2px dashed #ddd;
        }
        .amount {
            font-size: 2rem;
            color: #007AFF;
            margin: 1rem 0;
        }
        .reference {
            background: #f4f4f4;
            padding: 0.5rem;
            border-radius: 4px;
            font-family: monospace;
        }
        .timer {
            font-size: 1.2rem;
            color: #e32636;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="gcash-container">
        <h2>GCash Payment</h2>
        <div class="amount">₱<?= number_format($total, 2) ?></div>
        <p>Reference Number:</p>
        <div class="reference"><?= $reference ?></div>
        
        <div class="qr-code">
            <!-- Replace with your GCash QR code -->
            <img src="../assets/img/qr.png" alt="GCash QR Code" style="max-width: 300px;">
        </div>
        
        <div class="timer">Payment expires in: <span id="countdown">15:00</span></div>
        
        <p>Steps:</p>
        <ol style="text-align: left">
            <li>Open your GCash app</li>
            <li>Scan the QR code</li>
            <li>Enter the exact amount</li>
            <li>Use the reference number in the payment details</li>
            <li>Complete the payment</li>
        </ol>
        
        <button onclick="confirmPayment()" style="
            background: #007AFF;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1rem;
        ">I've completed the payment</button>
    </div>

    <script>
        // Countdown timer
        function startTimer(duration, display) {
            let timer = duration, minutes, seconds;
            let countdown = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(countdown);
                    window.location.href = '../frontend/cart.php?expired=true';
                }
            }, 1000);
        }

        window.onload = function () {
            let fifteenMinutes = 60 * 15,
                display = document.querySelector('#countdown');
            startTimer(fifteenMinutes, display);
        };

        function confirmPayment() {
            Swal.fire({
                title: 'Verifying Payment',
                text: 'Please wait while we verify your payment...',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            const formData = new FormData();
            formData.append('reference', document.querySelector('.reference').textContent);

            fetch('../backend/verify_gcash_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const text = await response.text();
                console.log('Raw response:', text); // Log response for debugging
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    throw new Error(`Invalid JSON response: ${text}`);
                }
            })
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Payment Successful!',
                        text: 'Thank you for your order.',
                        icon: 'success',
                        confirmButtonColor: '#007AFF'
                    }).then(() => {
                        window.location.href = '../frontend/dashboard.php';
                    });
                } else {
                    throw new Error(data.message || 'Payment verification failed');
                }
            })
            .catch(error => {
                console.error('Payment Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Failed to verify payment. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#e32636'
                });
            });
        }
    </script>
</body>
</html>