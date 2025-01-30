<?php 
session_start();
include "../config/db.php";

$users_query = "SELECT * FROM user";
$users_result = $conn->query($users_query);

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
  <title>Profile</title>
  <link rel="stylesheet" href="../asset/style.css">
  <link rel="stylesheet" href="../../font-awesome/css/all.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <?php include "../include/sidebar.php"?>
  <main class="p-6 md:p-10">
    <h1 class="text-red-500 text-2xl font-bold mb-6">
      <i class="fa-solid fa-user"></i> USER MANAGEMENT
    </h1>
    
    <!-- Add User Form -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <h2 class="text-xl font-bold mb-4"><i class="fa-regular fa-address-book"></i> ADD USER</h2>
      <form id="addUserForm" action="../backend/add_user.php" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" id="username" class="p-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" class="p-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" class="p-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
          </div>
          <div>
            <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
            <input type="text" name="contact_number" id="contact_number" class="p-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
          </div>
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
              <option value="verified">verified</option>
              <option value="unverified">unverified</option>
            </select>
          </div>
          <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
            <select name="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md" id="addUserButton">Add User</button>
        </div>
      </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-bold mb-4"><i class="fa-regular fa-user"></i> USERS</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Number</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php while ($user = $users_result->fetch_assoc()): ?>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['username']) ?></td>
              <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']) ?></td>
              <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['contact_number']) ?></td>
              <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['status']) ?></td>
              <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['role']) ?></td>
              <!-- <td class="px-6 py-4 whitespace-nowrap">
                <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                <form action="../backend/delete_user.php" method="POST" class="inline deleteUserForm">
                  <input type="hidden" name="id" value="<?= $user['id'] ?>">
                  <button type="submit" class="text-red-600 hover:text-red-900 ml-4 deleteUserButton">Delete</button>
                </form>
              </td> -->
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <!-- </div> -->
  </main>
  <script src="../asset/app.js"></script>
  <script src="../asset/js/profile.js"></script>
</body>
</html>