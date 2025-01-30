<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $status = $_POST['status'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE user SET username = ?, email = ?, contact_number = ?, status = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $username, $email, $contact_number, $status, $role, $id);

    if ($stmt->execute()) {
        header("Location: profile.php");
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
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
  <title>Edit User</title>
  <link rel="stylesheet" href="../asset/style.css">
  <link rel="stylesheet" href="../../font-awesome/css/all.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
  <?php include "../include/sidebar.php"?>
  <main class="p-6 md:p-10">
    <!-- <div class="container"> -->
      <h1 class="text-2xl font-bold mb-6"><i class="fa-regular fa-pen-to-square"></i> EDIT USER</h1>
      <div class="bg-white p-6 rounded-lg shadow-md">
        <form action="edit_user.php" method="POST">
          <input type="hidden" name="id" value="<?= $user['id'] ?>">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
              <input type="text" name="username" id="username" class="p-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
              <input readonly type="email" name="email" id="email" class="p-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div>
              <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
              <input type="text" name="contact_number" id="contact_number" class="p-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($user['contact_number']) ?>">
            </div>
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
              <select name="status" id="status" class="p-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="verified" <?= $user['status'] == 'verified' ? 'selected' : '' ?>>verified</option>
                <option value="unverified" <?= $user['status'] == 'unverified' ? 'selected' : '' ?>>unverified</option>
              </select>
            </div>
            <div>
              <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
              <select name="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
              </select>
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Update User</button>
          </div>
        </form>
      </div>
    <!-- </div> -->
  </main>
  <script src="../asset/app.js"></script>
</body>
</html>