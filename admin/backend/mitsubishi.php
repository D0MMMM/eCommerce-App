<?php
session_start();
include "../config/db.php";

if(isset($_GET['delete'])){
    $car_id = $_GET['delete'];
    $delete_query = mysqli_query($conn, "DELETE FROM `cars` WHERE id = $car_id ") or die('query failed');
    if($delete_query){
       header('location:mitsubishi.php');
       $message[] = 'product has been deleted';
    }else{
       header('location:mitsubishi.php');
       $message[] = 'product could not be deleted';
    }
 };

 if(isset($_GET['edit'])){
    $car_id = $_GET['edit'];
    $edit_query = mysqli_query($conn, "SELECT * FROM `cars` WHERE id = $car_id ") or die('query failed');
    if(mysqli_num_rows($edit_query) > 0){
        while($fetch_edit = mysqli_fetch_assoc($edit_query)){
            $car_id = $fetch_edit['id'];
            $make = $fetch_edit['make'];
            $model = $fetch_edit['model'];
            $year = $fetch_edit['year'];
            $price = $fetch_edit['price'];
            $description = $fetch_edit['description'];
            $car_condition = $fetch_edit['car_condition'];
            $image_path = $fetch_edit['image_path'];

        };
    };
 };

 
?>