<?php 
// session_start();
include "../config/db.php";
include "../backend/honda.php";

if(isset($_POST['add_car'])){
    $make = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $car_condition = $_POST['car_condition'];
    $car_image = $_FILES['car_img']['name'];
    $image_tmp_name = $_FILES['car_img']['tmp_name'];
    $image_folder = '../asset/uploaded_img/'.$car_image;
    $quantity = $_POST['quantity'];
    
    $insert_query = mysqli_query($conn, "INSERT INTO `cars`(make, model, year, price, description, car_condition, image_path, quantity)
    VALUES('$make', '$model', '$year','$price','$description','$car_condition','$car_image',$quantity)") or die('query failed');

    if($insert_query){
        move_uploaded_file($image_tmp_name, $image_folder);
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Product added successfully!'
                });
            };
        </script>";
    } else {
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not add the product. Please try again.'
                });
            };
        </script>";
    }
};

if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    $delete_query = mysqli_query($conn, "DELETE FROM `cars` WHERE id = '$delete_id'") or die('query failed');
    if($delete_query){
        header('location:honda.php');
    }else{
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not delete the product. Please try again.'
                });
            };
        </script>";
    }
};
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
    <link rel="stylesheet" href="../asset/view-css/modal-style.css">
    <link rel="stylesheet" href="../asset/style.css">
    <link rel="stylesheet" href="../asset/view-css/car.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/bad2460ef5.js" crossorigin="anonymous"></script>
    <title>Document</title>
</head>
<body>
    <?php include "../include/sidebar.php"?>
    <main>
        <div class="toyota-container">
            <span style=" font-size: 1.2em"><i class="fa-solid fa-bars"></i></span> <span style="color: red;">HONDA</span>
            <span style="float: right;">DASHBOARD</span>
        </div>
        <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data" id="carForm">
            <input type="text" name="brand" value="<?php echo 'HONDA' ?>" readonly required>
            <input type="text" name="model" placeholder="Model" required>
            <select id='date-dropdown' name="year" required>
            </select>
            <input type="number" placeholder="Price" min="0" step="1" name="price" id="price" required="required">
            <input type="number" placeholder="Quantity" min="0" step="1" name="quantity" id="quantity" required>
            <select name="car_condition" id="condition-dropdown" required>
            </select>
            <textarea name="description" id="description" placeholder="Description"></textarea>
            <input type="file" name="car_img" accept="image/png, image/jpg, image/jpeg" required>
            <input type="submit" value="Add" name="add_car">
        </form>

        </div>

        <div class="display-car">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search for cars.." title="Type in a name">
                <i class="fa fa-search search-icon"></i>
            </div>
            <table class="display" id="toyota-table" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="text-align:start;">Image</th>
                        <th style="text-align:start;">Brand</th>
                        <th style="text-align:start;">Model</th>
                        <th style="text-align:start;">Year</th>
                        <th style="text-align:start;">Price</th>
                        <th style="text-align:start;">Quantity</th>
                        <th style="text-align:start;">Condition</th>
                        <th style="text-align:start;">Description</th>
                        <th style="text-align:start;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $select_cars = mysqli_query($conn, "SELECT * FROM `cars` WHERE make = 'honda'");
                        if(mysqli_num_rows($select_cars) > 0){
                            while($row = mysqli_fetch_assoc($select_cars)){
                    ?>
                    <tr>
                        <td style="text-align:start;"><img src="../asset/uploaded_img/<?php echo $row['image_path'];?>" height="75" alt=""></td>
                        <td style="text-align:start;"><?php echo $row['make']; ?></td>
                        <td style="text-align:start;"><?php echo $row['model']; ?></td>
                        <td style="text-align:start;"><?php echo $row['year']; ?></td>
                        <td style="text-align:start;">₱<?php echo number_format($row['price'], 2); ?></td>
                        <td style="text-align:start;"><?php echo $row['quantity']; ?></td>
                        <td style="text-align:start;"><?php echo $row['car_condition']; ?></td>
                        <td style="text-align:start; word-wrap: break-word;"><?php echo $row['description']; ?></td>
                        <td class="actions">
                            <a href="javascript:void(0);" class="edit-btn" data-car='<?php echo json_encode($row); ?>'> <i class="fas fa-edit"></i></a>
                            <a href="honda.php?delete=<?php echo $row['id']; ?>" class="delete-btn" name="delete" onclick="return confirm('Are you sure you want to delete the item?');"> <i class="fas fa-trash"></i> </a>
                        </td>
                    </tr>
                    <?php
                            };
                        };
                    ?>
                </tbody>
            </table>

        </div>
    </main>

    <!-- Edit Modal -->
    <?php include "../include/modal.php"; ?>

    <script src="../asset/js/search.js"></script>
    <script src="../asset/app.js"></script>
    <script src="../lib/jquery/jquery.min.js"></script>
    <script>
        let dateDropdown = document.getElementById('date-dropdown'); 
        
        let defaultOption = document.createElement('option');
        defaultOption.text = '--- SELECT YEAR ---';
        defaultOption.value = '';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        dateDropdown.add(defaultOption);

        let currentYear = new Date().getFullYear();    
        let earliestYear = 1970;     
        while (currentYear >= earliestYear) {      
            let dateOption = document.createElement('option');          
            dateOption.text = currentYear;      
            dateOption.value = currentYear;        
            dateDropdown.add(dateOption);      
            currentYear -= 1;    
        }
    </script>
    <script>
        let condition_Dropdown = document.getElementById('condition-dropdown'); 
        
        let DefOption = document.createElement('option');
        DefOption.text = '--- SELECT CONDITION ---';
        DefOption.value = '';
        DefOption.disabled = true;
        DefOption.selected = true;
        condition_Dropdown.add(DefOption);
        condition_Dropdown.add(new Option('BRAND NEW', 'BRAND NEW'));
        condition_Dropdown.add(new Option('USED', 'USED'));
    </script>
    <script src="../asset/js/honda.js"></script>
</body>
</html>