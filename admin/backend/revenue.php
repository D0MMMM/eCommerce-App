<?php 
// Fetch total revenue for today
$today_revenue_query = "SELECT SUM(total_amount) AS today_revenue FROM orders WHERE payment_status = 'paid' AND DATE(created_at) = CURDATE()";
$today_revenue_result = $conn->query($today_revenue_query);
$today_revenue = $today_revenue_result->fetch_assoc()['today_revenue'];

// Fetch total revenue for the current month
$monthly_revenue_query = "SELECT SUM(total_amount) AS monthly_revenue FROM orders WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(CURDATE())";
$monthly_revenue_result = $conn->query($monthly_revenue_query);
$monthly_revenue = $monthly_revenue_result->fetch_assoc()['monthly_revenue'];

// Fetch total revenue
$total_revenue_query = "SELECT SUM(total_amount) AS total_revenue FROM orders WHERE payment_status = 'paid'";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'];

// Fetch daily revenue for the current month for the pie chart
$daily_revenue_query = "
    SELECT DATE(created_at) AS date, SUM(total_amount) AS daily_revenue 
    FROM orders 
    WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";
$daily_revenue_result = $conn->query($daily_revenue_query);

$dates = [];
$daily_revenues = [];
while ($row = $daily_revenue_result->fetch_assoc()) {
    $dates[] = $row['date'];
    $daily_revenues[] = $row['daily_revenue'];
}

// Add today's revenue and total revenue to the data
$dates[] = "Today's Revenue";
$daily_revenues[] = $today_revenue;

$dates[] = "Total Revenue";
$daily_revenues[] = $total_revenue;
?>