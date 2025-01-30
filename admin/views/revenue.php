<?php 
session_start();
include "../config/db.php";
include "../backend/revenue.php";

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
  <title>Revenue Dashboard</title>
  <link rel="stylesheet" href="../asset/style.css">
  <link rel="stylesheet" href="../../font-awesome/css/all.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include "../include/sidebar.php"?>
  <main class="p-6 md:p-10">
    <h1 class="text-3xl text-red-600 font-bold mb-3"><i class="fa-solid fa-chart-simple"></i> REVENUE DASHBOARD</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-green-300 p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold">TODAY'S REVENUE <i class="fa-solid fa-chart-line"></i></h2>
        <p class="text-3xl mt-4">₱<?= number_format($today_revenue, 2) ?></p>
      </div>
      <div class="bg-yellow-300 p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold">MONTHLY REVENUE <i class="fa fa-bar-chart"></i></h2>
        <p class="text-3xl mt-4">₱<?= number_format($monthly_revenue, 2) ?></p>
      </div>
      <div class="bg-blue-300 p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold">TOTAL REVENUE <i class="fa fa-bar-chart"></i></h2>
        <p class="text-3xl mt-4">₱<?= number_format($total_revenue, 2) ?></p>
      </div>
    </div>
    <div class="mt-10 bg-indigo-300 p-6 rounded-lg shadow-md w-full">
      <h2 class="text-xl flex justify-center font-bold mb-4">REVENUE PIE CHART</h2>
      <div class="mt-10 flex justify-center overflow-hidden">
        <div class="w-full h-96">
          <canvas id="revenuePieChart"></canvas>
        </div>
      </div>
    </div>
  </main>
  <script src="../asset/app.js"></script>
  <script>
    const ctx = document.getElementById('revenuePieChart').getContext('2d');
    const revenuePieChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
          data: <?= json_encode($daily_revenues) ?>,
          backgroundColor: [
            'rgba(54, 162, 235, 0.5)', 
            'rgba(75, 192, 192, 0.5)',
            'rgba(255, 206, 86, 0.5)', 
            'rgba(255, 99, 132, 0.5)',
            'rgba(54, 162, 235, 0.5)',
            'rgba(255, 206, 86, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(153, 102, 255, 0.5)',
            'rgba(255, 159, 64, 0.5)',
            'rgba(201, 203, 207, 0.5)'
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)', 
            'rgba(75, 192, 192, 1)',
            'rgba(255, 206, 86, 1)', 
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(201, 203, 207, 1)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
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
  </script>
</body>
</html>