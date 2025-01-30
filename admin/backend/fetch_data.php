<?php 
$total_cars_query = "SELECT COUNT(*) AS total_cars FROM cars";
$total_cars_result = $conn->query($total_cars_query);
$total_cars = $total_cars_result->fetch_assoc()['total_cars'];

// Fetch total users
$total_users_query = "SELECT COUNT(*) AS total_users FROM user";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total_users'];

// Fetch total revenue from paid orders
$total_revenue_query = "SELECT SUM(total_amount) AS total_revenue FROM orders WHERE payment_status = 'paid'";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'];

// Fetch total orders
$total_orders_query = "SELECT COUNT(*) AS total_orders FROM orders";
$total_orders_result = $conn->query($total_orders_query);
$total_orders = $total_orders_result->fetch_assoc()['total_orders'];

// Fetch total sales
$total_sales_query = "SELECT SUM(total_amount) AS total_sales FROM orders WHERE payment_status = 'paid'";
$total_sales_result = $conn->query($total_sales_query);
$total_sales = $total_sales_result->fetch_assoc()['total_sales'];

// Fetch average order value
$average_order_value_query = "SELECT AVG(total_amount) AS average_order_value FROM orders WHERE payment_status = 'paid'";
$average_order_value_result = $conn->query($average_order_value_query);
$average_order_value = $average_order_value_result->fetch_assoc()['average_order_value'];

// Fetch sales data over time for the pie chart
$sales_over_time_query = "
    SELECT DATE(created_at) AS date, SUM(total_amount) AS daily_sales 
    FROM orders 
    WHERE payment_status = 'paid' 
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";
$sales_over_time_result = $conn->query($sales_over_time_query);

$dates = [];
$daily_sales = [];
while ($row = $sales_over_time_result->fetch_assoc()) {
    $dates[] = $row['date'];
    $daily_sales[] = $row['daily_sales'];
}

// Fetch data for total users, cars, and orders over time
$users_over_time_query = "
    SELECT DATE(created_at) AS date, COUNT(*) AS daily_users 
    FROM user 
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";
$users_over_time_result = $conn->query($users_over_time_query);

$user_dates = [];
$daily_users = [];
while ($row = $users_over_time_result->fetch_assoc()) {
    $user_dates[] = $row['date'];
    $daily_users[] = $row['daily_users'];
}

$cars_over_time_query = "
    SELECT DATE(created_at) AS date, COUNT(*) AS daily_cars 
    FROM cars 
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";
$cars_over_time_result = $conn->query($cars_over_time_query);

$car_dates = [];
$daily_cars = [];
while ($row = $cars_over_time_result->fetch_assoc()) {
    $car_dates[] = $row['date'];
    $daily_cars[] = $row['daily_cars'];
}

$orders_over_time_query = "
    SELECT DATE(created_at) AS date, COUNT(*) AS daily_orders 
    FROM orders 
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";
$orders_over_time_result = $conn->query($orders_over_time_query);

$order_dates = [];
$daily_orders = [];
while ($row = $orders_over_time_result->fetch_assoc()) {
    $order_dates[] = $row['date'];
    $daily_orders[] = $row['daily_orders'];
}
?>