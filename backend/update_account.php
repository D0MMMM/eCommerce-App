<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];
    $contact = $_POST['contact'];

    $query = "UPDATE user SET username = ?, contact_number = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $username, $contact, $user_id);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username; // Update session username
        echo json_encode(['status' => 'success', 'username' => $username]);
    } else {
        echo json_encode(['status' => 'failed']);
    }

    $stmt->close();
    $conn->close();
}
?>