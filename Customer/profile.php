<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();
include 'db.php';
include 'sidebar_customer.php';

$customer_id = $_SESSION['user_id'] ?? null;

if (!$customer_id) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// Fetch customer data
$stmt = $conn->prepare("SELECT name, phone, location, age, sex, image FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Customer not found.";
    exit();
}

$customer = $result->fetch_assoc();
$profileImg = !empty($customer['image']) ? $customer['image'] : '../assets/default_user.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; }
    .main-container {
      margin-left: 240px; padding: 20px; background-color: #f8f9fa; min-height: 100vh;
    }
    .profile-card {
      background: white; padding: 30px; border-radius: 8px; max-width: 500px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center;
    }
    .profile-img {
      width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
      margin-bottom: 15px; border: 2px solid #2c3e50;
    }
    .profile-card label {
      display: block; margin-top: 10px; font-weight: 600; text-align: left;
    }
    .profile-card input {
      width: 100%; padding: 10px; margin-top: 5px;
      border: 1px solid #ccc; border-radius: 4px; background-color: #e9ecef;
    }
    .profile-card input[readonly] { cursor: not-allowed; }
    .profile-card button {
      margin-top: 20px; padding: 10px 20px;
      background-color: #2c3e50; color: white;
      border: none; border-radius: 4px; cursor: pointer;
    }
    .profile-card button:hover { background-color: #1a252f; }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: white;
      padding: 25px;
      border-radius: 8px;
      width: 400px;
      max-width: 90%;
    }
    .modal-content h2 {
      margin-top: 0;
      margin-bottom: 20px;
      text-align: center;
    }
    .modal-content input, .modal-content select {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .modal-actions {
      text-align: right;
      margin-top: 15px;
    }
    .modal-actions button {
      padding: 8px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .cancel-btn {
      background-color: #ccc;
      color: #333;
    }
    .save-btn {
      background-color: #2c3e50;
      color: white;
    }
    .save-btn:hover {
      background-color: #1a252f;
    }
  </style>
</head>
<body>
<div class="main-container">
  <h1>My Profile</h1>
  <div class="profile-card">
    <img src="<?= htmlspecialchars($profileImg) ?>" alt="Profile Picture" class="profile-img">
    <h2><?= htmlspecialchars($customer['name']) ?></h2>

    <label>Phone:</label>
    <input type="text" value="<?= htmlspecialchars($customer['phone']) ?>" readonly>

    <label>Location:</label>
    <input type="text" value="<?= htmlspecialchars($customer['location']) ?>" readonly>

    <label>Age:</label>
    <input type="number" value="<?= htmlspecialchars($customer['age']) ?>" readonly>

    <label>Sex:</label>
    <input type="text" value="<?= htmlspecialchars($customer['sex']) ?>" readonly>

    <button onclick="openModal()">Edit Profile</button>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
  <div class="modal-content">
    <h2>Edit Profile</h2>
    <form method="POST" action="update_profile.php" enctype="multipart/form-data">
      <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($customer['image']) ?>">

      <input type="text" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required>
      <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" required>
      <input type="text" name="location" value="<?= htmlspecialchars($customer['location']) ?>" required>
      <input type="number" name="age" value="<?= htmlspecialchars($customer['age']) ?>" required>
      <select name="sex" required>
        <option value="Male" <?= $customer['sex'] == 'Male' ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= $customer['sex'] == 'Female' ? 'selected' : '' ?>>Female</option>
      </select>
      <input type="file" name="image" accept="image/*">

      <div class="modal-actions">
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
        <button type="submit" class="save-btn">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal() {
  document.getElementById("editModal").style.display = "flex";
}
function closeModal() {
  document.getElementById("editModal").style.display = "none";
}
window.onclick = function(e) {
  if (e.target.id === "editModal") {
    closeModal();
  }
}
</script>
</body>
</html>
