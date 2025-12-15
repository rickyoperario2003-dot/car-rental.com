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

// Handle status update or cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cancel reservation
    if (isset($_POST['cancel_id'])) {
        $id = $_POST['cancel_id'];

        // Get car_id and quantity from reservation
        $res = $conn->query("SELECT car_id, quantity FROM reservations WHERE id = $id");
        $reservation = $res->fetch_assoc();
        $car_id = $reservation['car_id'];
        $qty = $reservation['quantity'] ?? 1;

        // Cancel the reservation
        $stmt = $conn->prepare("UPDATE reservations SET status='Cancelled' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Return quantity to cars
        $conn->query("UPDATE cars SET quantity = quantity + $qty WHERE id = $car_id");
        $conn->query("UPDATE cars SET status = 'Available' WHERE id = $car_id AND quantity > 0");

        header("Location: reservations.php");
        exit();
    }

    // Update reservation status
    if (isset($_POST['update_status'])) {
        $reservation_id = $_POST['reservation_id'];
        $new_status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE reservations SET status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $reservation_id);
        $stmt->execute();

        // If canceled, return car quantity and mark car available if needed
        if ($new_status === 'Cancelled') {
            $res = $conn->query("SELECT car_id, quantity FROM reservations WHERE id = $reservation_id");
            $reservation = $res->fetch_assoc();
            $car_id = $reservation['car_id'];
            $qty = $reservation['quantity'] ?? 1;

            $conn->query("UPDATE cars SET quantity = quantity + $qty WHERE id = $car_id");
            $conn->query("UPDATE cars SET status = 'Available' WHERE id = $car_id AND quantity > 0");
        }

        header("Location: reservations.php");
        exit();
    }
}

// Fetch reservations
$query = "
  SELECT r.id AS reservation_id, r.reservation_date, r.return_date, r.status, r.quantity,
         c.name AS customer_name,
         car.model, car.year, car.image
  FROM reservations r
  JOIN customers c ON r.customer_id = c.id
  JOIN cars car ON r.car_id = car.id
  ORDER BY r.reservation_date DESC
";
$reservations = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reservations</title>
  <style>
    body { margin: 0; }
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
    }
    table, th, td { border: 1px solid #ccc; }
    th, td {
      padding: 12px;
      text-align: left;
    }
    th {
      background-color: #2c3e50;
      color: white;
    }
    button {
      padding: 5px 10px;
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
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      width: 400px;
      text-align: center;
    }
    .modal-content img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 10px;
    }
    select {
      padding: 5px;
    }
  </style>
</head>
<body>
<div class="main-container">
  <h1>Reservations</h1>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Customer</th>
        <th>Model</th>
        <th>Year</th>
        <th>Reservation Date</th>
        <th>Return Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($row = $reservations->fetch_assoc()): ?>
      <tr>
        <td><?= $row['reservation_id'] ?></td>
        <td><?= htmlspecialchars($row['customer_name']) ?></td>
        <td><?= htmlspecialchars($row['model']) ?></td>
        <td><?= htmlspecialchars($row['year']) ?></td>
        <td><?= $row['reservation_date'] ?></td>
        <td><?= $row['return_date'] ?></td>
        <td>
          <form method="POST" style="display: flex; align-items: center;">
            <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
            <select name="status" onchange="this.form.submit()" <?= $row['status'] === 'Cancelled' ? 'disabled' : '' ?>>
              <?php foreach (['Pending','Approved','Completed','Cancelled'] as $status): ?>
                <option value="<?= $status ?>" <?= $status === $row['status'] ? 'selected' : '' ?>><?= $status ?></option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" name="update_status" value="1">
          </form>
        </td>
        <td>
          <button onclick="openModal(<?= $row['reservation_id'] ?>)">View</button>
          <?php if ($row['status'] !== 'Cancelled'): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="cancel_id" value="<?= $row['reservation_id'] ?>">
              <button type="submit" onclick="return confirm('Cancel this reservation?')">Cancel</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>

      <!-- Modal -->
      <div id="modal<?= $row['reservation_id'] ?>" class="modal">
        <div class="modal-content">
          <h2>Reservation Details</h2>
          <img src="<?= !empty($row['image']) ? '../' . htmlspecialchars($row['image']) : '../uploads/default.jpg' ?>" alt="Car Image" onerror="this.src='../uploads/default.jpg'">
          <p><strong>Customer:</strong> <?= htmlspecialchars($row['customer_name']) ?></p>
          <p><strong>Car:</strong> <?= htmlspecialchars($row['model']) ?> (<?= $row['year'] ?>)</p>
          <p><strong>Date:</strong> <?= $row['reservation_date'] ?> to <?= $row['return_date'] ?></p>
          <p><strong>Status:</strong> <?= $row['status'] ?></p>
          <p><strong>Quantity Reserved:</strong> <?= $row['quantity'] ?></p>
          <button onclick="closeModal(<?= $row['reservation_id'] ?>)">Close</button>
        </div>
      </div>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
  function openModal(id) {
    document.getElementById('modal' + id).style.display = 'flex';
  }
  function closeModal(id) {
    document.getElementById('modal' + id).style.display = 'none';
  }
  window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
      e.target.style.display = 'none';
    }
  }
</script>
</body>
</html>
