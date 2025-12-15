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

// Sample stats
$totalCars = $conn->query("SELECT COUNT(*) AS total FROM cars")->fetch_assoc()['total'];
$totalCustomers = $conn->query("SELECT COUNT(*) AS total FROM customers")->fetch_assoc()['total'];
$totalReservations = $conn->query("SELECT COUNT(*) AS total FROM reservations")->fetch_assoc()['total'];
$totalRevenue = $conn->query("SELECT SUM(c.price) AS total FROM reservations r JOIN cars c ON r.car_id = c.id WHERE r.status = 'Completed'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      background-image: url('2v2.png');
      background-size: cover;
      background-position: center;
    }

    .main-content {
      margin-left: 240px;
      padding: 30px;
      width: calc(100% - 240px);
    }

    h1 {
      margin-bottom: 10px;
      color: #2c3e50;
    }

    .card-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
    }

    .card {
      flex: 1 1 220px;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .card .info {
      display: flex;
      flex-direction: column;
    }

    .card .info h3 {
      margin: 0;
      font-size: 20px;
      color: #333;
    }

    .card .info span {
      font-size: 28px;
      font-weight: bold;
      color: #2c3e50;
    }

    .card .icon {
      font-size: 36px;
      color: #2c3e50;
    }

    .quick-actions {
      margin-top: 40px;
    }

    .quick-actions h2 {
      margin-bottom: 10px;
      color: #2c3e50;
    }

    .quick-actions .btn {
      padding: 12px 20px;
      margin: 10px 10px 0 0;
      background-color: #2c3e50;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      display: inline-block;
    }

    .quick-actions .btn:hover {
      background-color: #34495e;
    }
  </style>
</head>
<body>

<div class="main-content">
  <h1>Admin Dashboard</h1>
  <div class="card-container">
    <div class="card">
      <div class="info">
        <h3>Total Cars</h3>
        <span><?= $totalCars ?></span>
      </div>
      <div class="icon"><i class="fas fa-car"></i></div>
    </div>
    <div class="card">
      <div class="info">
        <h3>Total Reservations</h3>
        <span><?= $totalReservations ?></span>
      </div>
      <div class="icon"><i class="fas fa-calendar-check"></i></div>
    </div>
    <div class="card">
      <div class="info">
        <h3>Revenue</h3>
        <span>₱<?= number_format($totalRevenue, 2) ?></span>
      </div>
      <div class="icon"><i class="fas fa-coins"></i></div>
    </div>
    <div class="card">
      <div class="info">
        <h3>Customers</h3>
        <span><?= $totalCustomers ?></span>
      </div>
      <div class="icon"><i class="fas fa-users"></i></div>
    </div>
  </div>


</body>
</html>
