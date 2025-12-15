<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'sidebar_admin.php';
include 'db.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer_id'])) {
    $id = $_POST['delete_customer_id'];

    // Optional: Delete customer reservations first to avoid foreign key issues
    $conn->query("DELETE FROM reservations WHERE customer_id = $id");

    // Then delete customer
    $conn->query("DELETE FROM customers WHERE id = $id");

    header("Location: admin_customers.php");
    exit();
}

// Fetch all customers
$customers = $conn->query("SELECT * FROM customers ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customers</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
    }
    .main-container {
      margin-left: 240px;
      padding: 20px;
      background-color: #f8f9fa;
      min-height: 100vh;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background-color: white;
    }

    table, th, td {
      border: 1px solid #ccc;
    }

    th, td {
      padding: 12px;
      text-align: left;
    }

    th {
      background-color: #2c3e50;
      color: white;
    }

    button {
      padding: 6px 12px;
      margin: 2px;
      border: none;
      background-color: #2c3e50;
      color: white;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background-color: #1a252f;
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 8px;
      width: 400px;
      max-width: 90%;
      text-align: center;
    }

    .modal-content img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      margin-bottom: 10px;
      object-fit: cover;
    }

    .modal-content h2 {
      margin-top: 0;
    }

    .modal-content p {
      margin: 6px 0;
      font-size: 15px;
    }

    .modal-actions {
      margin-top: 20px;
      text-align: right;
    }

    .modal-actions button {
      padding: 8px 12px;
      margin-left: 10px;
    }

    .cancel-btn {
      background-color: #ccc;
      color: #333;
    }

    .confirm-btn {
      background-color: #2c3e50;
      color: white;
    }

    .confirm-btn:hover {
      background-color: #1a252f;
    }
  </style>
</head>
<body>
<div class="main-container">
  <h1>Customers</h1>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Location</th>
        <th>Age</th>
        <th>Sex</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $customers->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['location']) ?></td>
        <td><?= htmlspecialchars($row['age']) ?></td>
        <td><?= htmlspecialchars($row['sex']) ?></td>
        <td>
          <button onclick="openModal('viewModal<?= $row['id'] ?>')">View</button>
          <button onclick="openModal('deleteModal<?= $row['id'] ?>')">Delete</button>
        </td>
      </tr>

      <!-- View Modal -->
<?php
  $profileImage = !empty($row['image']) ? $row['image'] : '../assets/default_user.png';
?>
<div id="viewModal<?= $row['id'] ?>" class="modal">
  <div class="modal-content">
    <img src="<?= htmlspecialchars($profileImage) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
    <h2><?= htmlspecialchars($row['name']) ?></h2>
    <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
    <p><strong>Age:</strong> <?= htmlspecialchars($row['age']) ?></p>
    <p><strong>Sex:</strong> <?= htmlspecialchars($row['sex']) ?></p>
    <div class="modal-actions">
      <button class="cancel-btn" onclick="closeModal('viewModal<?= $row['id'] ?>')">Close</button>
    </div>
  </div>
</div>


      <!-- Delete Modal -->
      <div id="deleteModal<?= $row['id'] ?>" class="modal">
        <div class="modal-content">
          <h2>Confirm Deletion</h2>
          <p>Are you sure you want to delete <strong><?= htmlspecialchars($row['name']) ?></strong>?</p>
          <div class="modal-actions">
            <button class="cancel-btn" onclick="closeModal('deleteModal<?= $row['id'] ?>')">Cancel</button>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="delete_customer_id" value="<?= $row['id'] ?>">
              <button class="confirm-btn" type="submit">Delete</button>
            </form>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
  function openModal(id) {
    document.getElementById(id).style.display = 'flex';
  }
  function closeModal(id) {
    document.getElementById(id).style.display = 'none';
  }

  window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
      e.target.style.display = 'none';
    }
  }
</script>
</body>
</html>
