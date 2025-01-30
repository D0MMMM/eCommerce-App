<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
session_start();
include "../config/db.php";

// if (!isset($_SESSION['user_id'])) {
//     header('Location: ../frontend/login.php');
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $make = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $car_condition = $_POST['car_condition'];
    $description = $_POST['description'];
    $car_image = $_FILES['car_img']['name'];
    $image_tmp_name = $_FILES['car_img']['tmp_name'];
    $image_folder = '../asset/uploaded_img/'.$car_image;

    if (!empty($car_image)) {
        // Update with new image
        $update_query = $conn->prepare("UPDATE cars SET make = ?, model = ?, year = ?, price = ?, quantity = ?, car_condition = ?, description = ?, image_path = ? WHERE id = ?");
        $update_query->bind_param("ssisdsssi", $make, $model, $year, $price, $quantity, $car_condition, $description, $car_image, $car_id);
        move_uploaded_file($image_tmp_name, $image_folder);
    } else {
        // Update without new image
        $update_query = $conn->prepare("UPDATE cars SET make = ?, model = ?, year = ?, price = ?, quantity = ?, car_condition = ?, description = ? WHERE id = ?");
        $update_query->bind_param("ssisdssi", $make, $model, $year, $price, $quantity, $car_condition, $description, $car_id);
    }

    if ($update_query->execute()) {
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Car details updated successfully!'
                }).then(function() {
                    window.location = '../views/toyota.php';
                });
            };
        </script>";
    } else {
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not update the car details. Please try again.'
                }).then(function() {
                    window.location = '../views/toyota.php';
                });
            };
        </script>";
    }
}
?>