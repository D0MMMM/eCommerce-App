<?php
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $contact_number = $_POST['contact_number'];
    $status = $_POST['status'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO user (username, email, password, contact_number, status, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password, $contact_number, $status, $role);

    if ($stmt->execute()) {
        header("Location: ../views/profile.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>