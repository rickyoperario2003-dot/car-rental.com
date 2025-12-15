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

// Handle Add Car
if (isset($_POST['action']) && $_POST['action'] == 'add') {
  $model = $_POST['model'];
  $year = $_POST['year'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];
  $status = $_POST['status'];

  $imagePath = '';
  if (!empty($_FILES['image']['name'])) {
    $targetDir = "../uploads/";
    $imageName = basename($_FILES["image"]["name"]);
    $imagePath = "uploads/" . $imageName;
    move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $imageName);
  }

  $stmt = $conn->prepare("INSERT INTO cars (model, year, price, quantity, status, image) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssdiss", $model, $year, $price, $quantity, $status, $imagePath);
  $stmt->execute();
  header("Location: manage_cars.php");
  exit();
}

// Handle Edit Car
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
  $id = $_POST['id'];
  $model = $_POST['model'];
  $year = $_POST['year'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];
  $status = $_POST['status'];
  $current_image = $_POST['current_image'];

  $imagePath = $current_image;
  if (!empty($_FILES['image']['name'])) {
    $targetDir = "../uploads/";
    $imageName = basename($_FILES["image"]["name"]);
    $imagePath = "uploads/" . $imageName;
    move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $imageName);
  }

  $stmt = $conn->prepare("UPDATE cars SET model=?, year=?, price=?, quantity=?, status=?, image=? WHERE id=?");
  $stmt->bind_param("ssdissi", $model, $year, $price, $quantity, $status, $imagePath, $id);
  $stmt->execute();
  header("Location: manage_cars.php");
  exit();
}

// Handle Delete Car
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
  $id = $_POST['id'];
  $stmt = $conn->prepare("DELETE FROM cars WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  header("Location: manage_cars.php");
  exit();
}

$cars = $conn->query("SELECT * FROM cars");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Cars</title>
  <style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; }
    .main-container { margin-left: 240px; padding: 20px; background-color: #f8f9fa; min-height: 100vh; }
    .add-btn {
      padding: 10px 16px; background-color: #2c3e50; color: white;
      border: none; border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    .add-btn:hover { background-color: #1a252f; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
    table, th, td { border: 1px solid #ccc; }
    th, td { padding: 12px; text-align: left; }
    th { background-color: #2c3e50; color: white; }
    td img { width: 100px; height: auto; border-radius: 6px; }
    .action-buttons button {
      padding: 6px 12px; margin-right: 5px;
      border: none; background-color: #2c3e50; color: white;
      border-radius: 4px; cursor: pointer;
    }
    .action-buttons button:hover { background-color: #1a252f; }
    .modal {
      display: none; position: fixed; z-index: 999; left: 0; top: 0;
      width: 100%; height: 100%; background-color: rgba(0,0,0,0.6);
      justify-content: center; align-items: center;
    }
    .modal-content {
      background: white; padding: 30px; border-radius: 10px;
      width: 400px; max-width: 90%; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }
    .modal-content h2 { margin-top: 0; text-align: center; margin-bottom: 20px; }
    .modal-content label { display: block; margin: 10px 0 5px; }
    .modal-content input[type="text"],
    .modal-content input[type="number"],
    .modal-content input[type="file"],
    .modal-content select {
      width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px;
      border: 1px solid #ccc;
    }
    .modal-actions {
      display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;
    }
    .cancel-btn { background-color: #bbb; color: black; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; }
    .confirm-btn { background-color: #2c3e50; color: white; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; }
    .confirm-btn:hover { background-color: #1a252f; }
  </style>
</head>
<body>
  <div class="main-container">
    <h1>Manage Cars</h1>
    <button class="add-btn" onclick="document.getElementById('addModal').style.display='flex'">Add New Car</button>
    <table>
      <thead>
        <tr>
          <th>Car ID</th><th>Image</th><th>Model</th><th>Year</th><th>Price</th><th>Quantity</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $cars->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><img src="../<?= htmlspecialchars($row['image']) ?>" alt="Car Image" onerror="this.src='../uploads/default.jpg'"></td>
          <td><?= htmlspecialchars($row['model']) ?></td>
          <td><?= htmlspecialchars($row['year']) ?></td>
          <td>₱<?= number_format($row['price'], 2) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td><?= $row['status'] ?></td>
          <td class="action-buttons">
            <button onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['model'], ENT_QUOTES) ?>', '<?= $row['year'] ?>', '<?= $row['price'] ?>', '<?= $row['quantity'] ?>', '<?= $row['status'] ?>', '<?= htmlspecialchars($row['image'], ENT_QUOTES) ?>')">Edit</button>
            <form method="POST" style="display:inline-block;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Add Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <h2>Add New Car</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <label>Model</label><input type="text" name="model" required>
        <label>Year</label><input type="text" name="year" required>
        <label>Price</label><input type="number" name="price" step="0.01" required>
        <label>Quantity</label><input type="number" name="quantity" min="1" required>
        <label>Status</label><select name="status"><option value="Available">Available</option><option value="Unavailable">Unavailable</option></select>
        <label>Image</label><input type="file" name="image" accept="image/*">
        <div class="modal-actions">
          <button type="button" class="cancel-btn" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
          <button type="submit" class="confirm-btn">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h2>Edit Car</h2>
      <form method="POST" enctype="multipart/form-data" id="editForm">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editId">
        <input type="hidden" name="current_image" id="editCurrentImage">
        <label>Model</label><input type="text" name="model" id="editModel" required>
        <label>Year</label><input type="text" name="year" id="editYear" required>
        <label>Price</label><input type="number" step="0.01" name="price" id="editPrice" required>
        <label>Quantity</label><input type="number" name="quantity" id="editQuantity" required>
        <label>Status</label>
        <select name="status" id="editStatus">
          <option value="Available">Available</option>
          <option value="Unavailable">Unavailable</option>
        </select>
        <label>Change Image</label><input type="file" name="image" accept="image/*">
        <div class="modal-actions">
          <button type="button" class="cancel-btn" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
          <button type="submit" class="confirm-btn">Update</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openEditModal(id, model, year, price, quantity, status, image) {
      document.getElementById('editId').value = id;
      document.getElementById('editModel').value = model;
      document.getElementById('editYear').value = year;
      document.getElementById('editPrice').value = price;
      document.getElementById('editQuantity').value = quantity;
      document.getElementById('editStatus').value = status;
      document.getElementById('editCurrentImage').value = image;
      document.getElementById('editModal').style.display = 'flex';
    }

    window.onclick = function(event) {
      ['addModal', 'editModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
    }
  </script>
</body>
</html>
