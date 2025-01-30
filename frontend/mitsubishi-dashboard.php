<?php 
session_start();
include "../config/db.php";

if(isset($_SESSION['username'])){
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../font-awesome/css/all.css">
    <link rel="stylesheet" href="../assets/css/includes-css/footer.css">
    <script src="https://kit.fontawesome.com/bad2460ef5.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Mitsubishi Dashboard</title>
</head>
<body>
    <?php include "../user-includes/header.php"?>
    <!-- <section class="img-slider"> -->
    <?php include '../includes/slider.php';?>
    <!-- </section> -->
     <div class="brand-choices">
        <a href="dashboard.php">TOYOTA</a>
        <a href="honda-dashboard.php">HONDA</a>
        <a href="mitsubishi-dashboard.php">MITSUBISHI</a>
     </div>
    <section class="toyota">
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Search by car name...">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="toyota-section">
            <?php
            $select_car = mysqli_query($conn, "SELECT * FROM `cars` WHERE make = 'mitsubishi'");
            if(mysqli_num_rows($select_car) > 0){
                while($fetch_car = mysqli_fetch_assoc($select_car)){
            ?>
            <div class="toyota-container" data-car-id="<?= $fetch_car['id'] ?>" data-car-name="<?= htmlspecialchars($fetch_car['model']) ?>">
                <img style="height:15rem; width:23rem" src="../admin/asset/uploaded_img/<?php echo htmlspecialchars($fetch_car['image_path']); ?>" alt="<?= htmlspecialchars($fetch_car['model']) ?>">
                <h3><?= htmlspecialchars($fetch_car['model']); ?></h3>
                <div class="price">₱<?= number_format($fetch_car['price'], 2); ?></div>
                <div class="quantity" id="stock-<?= $fetch_car['id'] ?>">Stock: <?= intval($fetch_car['quantity']); ?></div>
                <button class="view-btn" id="view-detail" data-car='<?php 
                    echo htmlspecialchars(json_encode([
                        'car_id' => $fetch_car['id'],
                        'make' => $fetch_car['make'],
                        'model' => $fetch_car['model'],
                        'year' => $fetch_car['year'],
                        'price' => $fetch_car['price'],
                        'car_condition' => $fetch_car['car_condition'],
                        'description' => $fetch_car['description'],
                        'image_path' => $fetch_car['image_path']
                    ])); 
                ?>' <?= intval($fetch_car['quantity']) === 0 ? 'disabled' : ''; ?>>
                    <?= intval($fetch_car['quantity']) === 0 ? 'OUT OF STOCK' : 'VIEW DETAILS'; ?>
                </button>
            </div>
            <?php
                };
            };
            ?>
         </div>
    </section>
    
    <?php include '../includes/car-modal.php';?>

    <?php include '../includes/footer.php';?>
    <script src="../assets/js/modal.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/mitsubishi_dashboard.js"></script>
</body>
</html>
<?php
}else{
  header("Location: login.php");
  exit();
}
?>