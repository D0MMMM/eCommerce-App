<?php
include "../config/db.php";

// Fetch orders
$orders_query = "
    SELECT o.*, u.username 
    FROM orders o 
    JOIN user u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
";
$orders_result = mysqli_query($conn, $orders_query);
?>