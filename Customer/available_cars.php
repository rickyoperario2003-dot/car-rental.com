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

$cars = $conn->query("SELECT * FROM cars WHERE status = 'Available' AND quantity > 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Cars</title>
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

  .search-bar {
    margin-bottom: 20px;
  }

  .search-bar input {
    width: 100%;
    max-width: 400px;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
  }

  .car-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }

  .car-card {
    background: #fff;
    border: 1px solid #ccc;
    padding: 16px;
    border-radius: 6px;
    text-align: center;
    box-sizing: border-box;
    transition: box-shadow 0.2s ease;
  }

  .car-card:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }

  .car-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 6px;
  }

  .car-card .price {
    font-size: 16px;
    font-weight: bold;
    color: #333;
  }

  .car-card .status {
    margin-top: 5px;
    font-size: 14px;
    color: green;
    font-weight: 600;
  }

  .car-card button {
    margin-top: 12px;
    padding: 8px 12px;
    background-color: #2c3e50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s ease;
  }

  .car-card button:hover {
    background-color: #1a252f;
  }

  @media (max-width: 992px) {
    .car-row {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 600px) {
    .car-row {
      grid-template-columns: 1fr;
    }
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
  }

  .modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    width: 400px;
    max-width: 95%;
    text-align: center;
    position: relative;
  }

  .modal-content img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 10px;
  }

  .modal-content input,
  .modal-content select {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
  }

  .modal-content button {
    padding: 10px 16px;
    margin-top: 15px;
    border: none;
    border-radius: 4px;
    background-color: #2c3e50;
    color: white;
    cursor: pointer;
    transition: background-color 0.2s ease;
  }

  .modal-content button:hover {
    background-color: #1a252f;
  }

  .modal-content .warning {
    color: red;
    font-size: 13px;
    margin-top: 4px;
    display: none;
  }

  .modal-actions {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 15px;
  }
</style>

</head>
<body>
  <div class="main-container">
    <h1>Available Cars</h1>
    <div class="search-bar">
      <input type="text" id="searchInput" onkeyup="filterCars()" placeholder="Search by model or year...">
    </div>

    <div class="car-row" id="carContainer">
      <?php while ($car = $cars->fetch_assoc()): ?>
        <?php $today = date('Y-m-d'); $return = date('Y-m-d', strtotime($today . ' +3 days')); ?>
        <div class="car-card" data-model="<?= strtolower($car['model']) ?>" data-year="<?= $car['year'] ?>">
          <img src="../uploads/<?= htmlspecialchars(basename($car['image'])) ?>" alt="<?= htmlspecialchars($car['model']) ?>" onerror="this.src='uploads/default.jpg'">
          <h3><?= htmlspecialchars($car['model']) ?> (<?= $car['year'] ?>)</h3>
          <p class="price">₱<?= number_format($car['price'], 2) ?></p>
          <p class="status">Status: <?= $car['status'] ?></p>
          <button onclick="openModal(<?= $car['id'] ?>)">Reserve</button>
        </div>

        <!-- Modal -->
        <div id="modal<?= $car['id'] ?>" class="modal">
          <div class="modal-content">
            <form method="POST" action="process_reservation.php" onsubmit="return validateQuantity(<?= $car['id'] ?>, <?= $car['quantity'] ?>)">
              <img src="../uploads/<?= htmlspecialchars(basename($car['image'])) ?>" alt="<?= htmlspecialchars($car['model']) ?>">
              <h2><?= htmlspecialchars($car['model']) ?></h2>
              <p><strong>Price:</strong> ₱<?= number_format($car['price'], 2) ?></p>

              <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
              <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
              <input type="hidden" name="reservation_date" value="<?= $today ?>">
              <input type="hidden" name="return_date" value="<?= $return ?>">

              <p><strong>Reserve Date:</strong> <?= $today ?></p>
              <p><strong>Return Date:</strong> <?= $return ?></p>

              <label>Available Quantity : <?= $car['quantity'] ?></label>
              <input type="number" id="quantity<?= $car['id'] ?>" name="quantity" placeholder="Enter quantity" min="1" max="<?= $car['quantity'] ?>" required>
              <div id="warning<?= $car['id'] ?>" class="warning">Quantity exceeds available stock.</div>

              <div class="modal-actions">
                <button type="submit">Confirm</button>
                <button type="button" onclick="closeModal(<?= $car['id'] ?>)">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <script>
    function openModal(id) {
      document.getElementById('modal' + id).style.display = 'flex';
    }

    function closeModal(id) {
      document.getElementById('modal' + id).style.display = 'none';
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
      }
    }

    function validateQuantity(carId, maxQty) {
      const qty = document.getElementById('quantity' + carId).value;
      const warning = document.getElementById('warning' + carId);
      if (parseInt(qty) > maxQty) {
        warning.style.display = 'block';
        return false;
      } else {
        warning.style.display = 'none';
        return true;
      }
    }

    function filterCars() {
      const input = document.getElementById("searchInput").value.toLowerCase();
      const cars = document.querySelectorAll("#carContainer .car-card");

      cars.forEach(car => {
        const model = car.getAttribute('data-model');
        const year = car.getAttribute('data-year');
        if (model.includes(input) || year.includes(input)) {
          car.style.display = "block";
        } else {
          car.style.display = "none";
        }
      });
    }
  </script>
</body>
</html>
