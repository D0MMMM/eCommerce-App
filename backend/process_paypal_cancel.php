<?php
session_start();

// Clear PayPal session data
unset($_SESSION['paypal_order_id']);

echo "<script>
    alert('Payment cancelled. Your order has not been processed.');
    window.location.href = '../frontend/cart.php';
</script>";