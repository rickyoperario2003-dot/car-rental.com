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

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $reservation_id = $_POST['cancel_id'];

    // Get car_id and quantity from reservation
    $res = $conn->prepare("SELECT car_id FROM reservations WHERE id = ? AND customer_id = ? AND status = 'Pending'");
    $res->bind_param("ii", $reservation_id, $customer_id);
    $res->execute();
    $result = $res->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $car_id = $row['car_id'];

        // Cancel reservation
        $conn->query("UPDATE reservations SET status = 'Cancelled' WHERE id = $reservation_id");

        // Return 1 quantity to car
        $conn->query("UPDATE cars SET quantity = quantity + 1 WHERE id = $car_id");

        // If car is now available, update status
        $conn->query("UPDATE cars SET status = 'Available' WHERE id = $car_id AND quantity > 0");
    }

    header("Location: my_reservations.php");
    exit();
}

// Fetch all reservations for current user
$stmt = $conn->prepare("
    SELECT r.id AS reservation_id, c.model, c.year, r.reservation_date, r.return_date, r.status
    FROM reservations r
    JOIN cars c ON r.car_id = c.id
    WHERE r.customer_id = ?
    ORDER BY r.reservation_date DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$reservations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Reservations</title>
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
      background: #fff;
      margin-top: 20px;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: left;
    }
    th {
      background-color: #2c3e50;
      color: white;
    }
    button {
      padding: 6px 12px;
      background-color: #c0392b;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background-color: #962d22;
    }
    .badge {
      padding: 5px 10px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: bold;
    }
    .Pending { background-color: #f39c12; color: white; }
    .Approved { background-color: #3498db; color: white; }
    .Completed { background-color: #2ecc71; color: white; }
    .Cancelled { background-color: #e74c3c; color: white; }
  </style>
</head>
<body>
  <div class="main-container">
    <h1>My Reservations</h1>
    <p>View your current and past car reservations.</p>

    <table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Model</th>
      <th>Year</th>
      <th>Reservation Date</th>
      <th>Return Date</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($reservations->num_rows > 0): ?>
      <?php while ($row = $reservations->fetch_assoc()): ?>
        <tr>
          <td><?= $row['reservation_id'] ?></td>
          <td><?= htmlspecialchars($row['model']) ?></td>
          <td><?= htmlspecialchars($row['year']) ?></td>
          <td><?= $row['reservation_date'] ?></td>
          <td><?= $row['return_date'] ?></td>
          <td><span class="badge <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
          <td>
            <?php if ($row['status'] === 'Pending'): ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="cancel_id" value="<?= $row['reservation_id'] ?>">
                <button type="submit" onclick="return confirm('Are you sure you want to cancel this reservation?')">Cancel</button>
              </form>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="7">No reservations found.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
  </div>
</body>
</html>
