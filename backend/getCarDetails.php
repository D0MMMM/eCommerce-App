<?php
include "../config/db.php";

if (isset($_GET['id'])) {
    $car_id = intval($_GET['id']); // Convert to integer to avoid SQL injection

    if ($car_id > 0) {
        // Query the database for the specific car
        $query = "SELECT * FROM `cars` WHERE id = $car_id";
        $result = mysqli_query($conn, $query);

        // Check if any result was returned
        if (mysqli_num_rows($result) > 0) {
            $car = mysqli_fetch_assoc($result);
            echo json_encode($car); // Return car details as JSON
        } else {
            // Log or send error if no result found
            echo json_encode(["error" => "Car not found for ID: " . $car_id]);
        }
    } else {
        echo json_encode(["error" => "Invalid ID"]);
    }
} else {
    echo json_encode(["error" => "ID parameter is missing"]);
}
?>
