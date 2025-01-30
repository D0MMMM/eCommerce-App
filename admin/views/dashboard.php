<?php
session_start();
include "../config/db.php";
include "../backend/fetch_data.php";

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../../font-awesome/css/all.css">
  <link rel="stylesheet" href="../asset/style.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include "../include/sidebar.php"?>
  <main class="p-6 md:p-10">
    <h1 class="text-3xl text-red-600 font-bold mb-3"><i class="fa-solid fa-bars"></i> ADMIN DASHBOARD</h1>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <a href="../views/toyota.php" class="block">
        <div class="bg-red-400 p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-bold">TOTAL CARS <i class="fa-solid fa-car"></i></h2>
          <p class="text-3xl mt-4"><i class="fa-solid fa-car"></i> <?= $total_cars ?></p>
        </div>
      </a>
      <a href="../views/profile.php" class="block">
        <div class="bg-gray-400 p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-bold">TOTAL USERS <i class="fa-solid fa-user"></i></h2>
          <p class="text-3xl mt-4"><i class="fa-regular fa-user"></i> <?= $total_users ?></p>
        </div>
      </a>
      <a href="../views/order.php" class="block">
        <div class="bg-yellow-300 p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-bold">TOTAL ORDERS <i class="fa-solid fa-shopping-cart"></i></h2>
          <p class="text-3xl mt-4"><i class="fa-solid fa-shopping-cart"></i> <?= $total_orders ?></p>
        </div>
      </a>
      <a href="../views/revenue.php" class="block">
        <div class="bg-blue-300 p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-bold">TOTAL REVENUE <i class="fa fa-bar-chart"></i></h2>
          <p class="text-3xl mt-4">₱<?= number_format($total_revenue, 2) ?></p>
        </div>
      </a>
    </div>
    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white mb-0 p-6 rounded-lg shadow-md ">
        <h2 class="text-xl font-bold mb-4">MONTHLY REVENUE</h2>
        <p class="text-lg"><strong>Total Sales:</strong> ₱<?= number_format($total_sales, 2) ?></p>
        <p class="text-lg"><strong>Average Order Value:</strong> ₱<?= number_format($average_order_value, 2) ?></p>
        <canvas id="revenueChart"></canvas>
      </div>
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">SALES ANALYTICS</h2>
        <p class="text-lg"><strong>Total Sales:</strong> ₱<?= number_format($total_sales, 2) ?></p>
        <p class="text-lg"><strong>Average Order Value:</strong> ₱<?= number_format($average_order_value, 2) ?></p>
        <canvas id="salesPieChart" class="mt-6"></canvas>
      </div>
    </div>
    <div class="mt-10 grid grid-cols-1">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <canvas id="userCarsOrdersLineChart"></canvas>
      </div>
    </div>
  </main>
  <script src="../asset/app.js"></script>
  <script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Total Cars', 'Total Users', 'Total Revenue', 'Total Orders'],
        datasets: [{
          label: 'Statistics',
          data: [<?= $total_cars ?>, <?= $total_users ?>, <?= $total_revenue ?>, <?= $total_orders ?>],
          backgroundColor: [
            'rgba(75, 192, 192, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(153, 102, 255, 0.2)'
          ],
          borderColor: [
            'rgba(75, 192, 192, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(153, 102, 255, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Chart.js script to create a pie chart for sales analytics
    const salesCtx = document.getElementById('salesPieChart').getContext('2d');
    const salesPieChart = new Chart(salesCtx, {
      type: 'pie',
      data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
          label: 'Daily Sales',
          data: <?= json_encode($daily_sales) ?>,
          backgroundColor: [
            'rgba(75, 192, 192, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 99, 132, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(255, 205, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(201, 203, 207, 0.2)',
            'rgba(255, 99, 132, 0.2)'
          ],
          borderColor: [
            'rgba(75, 192, 192, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255, 205, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(201, 203, 207, 1)',
            'rgba(255, 99, 132, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: function(tooltipItem) {
                return '₱ ' + tooltipItem.raw.toLocaleString();
              }
            }
          }
        }
      }
    });

    // Chart.js script to create a line chart for total users, cars, and orders
    const userCarsOrdersCtx = document.getElementById('userCarsOrdersLineChart').getContext('2d');
    const userCarsOrdersLineChart = new Chart(userCarsOrdersCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode($user_dates) ?>,
        datasets: [
          {
            label: 'Daily Users',
            data: <?= json_encode($daily_users) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            fill: true
          },
          {
            label: 'Daily Cars',
            data: <?= json_encode($daily_cars) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            fill: true
          },
          {
            label: 'Daily Orders',
            data: <?= json_encode($daily_orders) ?>,
            backgroundColor: 'rgba(255, 206, 86, 0.2)',
            borderColor: 'rgba(255, 206, 86, 1)',
            borderWidth: 1,
            fill: true
          }
        ]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
</body>
</html>