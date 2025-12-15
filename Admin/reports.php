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

$filterStart = $_GET['startDate'] ?? '';
$filterEnd = $_GET['endDate'] ?? '';
$whereClause = '';

if (!empty($filterStart) && !empty($filterEnd)) {
    $whereClause = "WHERE r.reservation_date BETWEEN '$filterStart' AND '$filterEnd'";
}

$query = "
SELECT r.*, c.name AS customer_name, cars.model, cars.price
FROM reservations r
JOIN customers c ON r.customer_id = c.id
JOIN cars ON r.car_id = cars.id
$whereClause
ORDER BY r.reservation_date DESC
";

$results = $conn->query($query);

// Summary counts
$totalReservations = 0;
$totalRevenue = 0;
$statusCounts = ['Pending' => 0, 'Approved' => 0, 'Completed' => 0, 'Cancelled' => 0];

$reservations = [];
if ($results && $results->num_rows > 0) {
    while ($row = $results->fetch_assoc()) {
        $reservations[] = $row;
        $totalReservations++;
        if ($row['status'] === 'Completed') {
            $totalRevenue += $row['price'];
        }
        $statusCounts[$row['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      background-color: #f4f4f4;
    }

    .main-container {
      margin-left: 240px;
      padding: 30px;
      width: calc(100% - 240px);
    }

    .report-filter, .print-btn {
      margin-bottom: 20px;
    }

    .report-filter label {
      margin-right: 10px;
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
      padding: 10px;
      text-align: left;
    }

    th {
      background-color: #2c3e50;
      color: white;
    }

    .summary-box {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .summary-card {
      background: white;
      padding: 20px;
      border-radius: 6px;
      flex: 1;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .print-btn button {
      padding: 8px 16px;
      background-color: #2c3e50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .print-btn button:hover {
      background-color: #1a252f;
    }

    /* Print Styles */
    @media print {
      body {
        background: white;
      }

      .sidebar,
      .report-filter,
      .print-btn {
        display: none !important;
      }

      .main-container {
        margin: 0;
        width: 100%;
        padding: 0;
      }

      table {
        page-break-inside: auto;
      }

      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }
    }
  </style>
</head>
<body>
<div class="main-container">
  <h1>Rental Reports</h1>

  <form class="report-filter" method="GET">
    <label for="startDate">Date Range:</label>
    <input type="date" id="startDate" name="startDate" value="<?= htmlspecialchars($filterStart) ?>">
    <input type="date" id="endDate" name="endDate" value="<?= htmlspecialchars($filterEnd) ?>">
    <button type="submit">Generate</button>
  </form>
  <div class="print-btn">
    <button onclick="window.print()">🖨️ Print Report</button>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Customer</th>
        <th>Car Model</th>
        <th>Reservation Date</th>
        <th>Return Date</th>
        <th>Status</th>
        <th>Price</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($reservations) === 0): ?>
        <tr><td colspan="7"><i>No report data available for the selected range.</i></td></tr>
      <?php else: ?>
        <?php foreach ($reservations as $row): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= htmlspecialchars($row['model']) ?></td>
            <td><?= $row['reservation_date'] ?></td>
            <td><?= $row['return_date'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>&#8369;<?= number_format($row['price'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
