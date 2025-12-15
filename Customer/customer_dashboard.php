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

// Fetch all cars with images
$carImages = $conn->query("SELECT image, model, price FROM cars WHERE image IS NOT NULL AND image != ''");
$cars = [];
while ($row = $carImages->fetch_assoc()) {
    $cars[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Customer Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      background-color: #f4f4f4;
    }

    .main-content {
      margin-left: 240px;
      padding: 30px;
      width: calc(100% - 240px);
    }

    .slideshow-container {
      position: relative;
      max-width: 100%;
      height: 420px;
      margin-bottom: 30px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .slide {
      display: none;
      width: 100%;
      height: 100%;
      position: absolute;
    }

    .slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .slide-caption {
      position: absolute;
      bottom: 20px;
      left: 30px;
      background-color: rgba(0,0,0,0.6);
      color: white;
      padding: 15px 20px;
      border-radius: 5px;
      font-size: 20px;
    }

    .prev, .next {
      cursor: pointer;
      position: absolute;
      top: 50%;
      padding: 16px;
      color: white;
      font-weight: bold;
      font-size: 24px;
      background-color: rgba(0,0,0,0.4);
      border-radius: 50%;
      user-select: none;
      transform: translateY(-50%);
    }

    .next { right: 10px; }
    .prev { left: 10px; }

    .prev:hover, .next:hover {
      background-color: rgba(0,0,0,0.7);
    }

    .description {
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      max-width: 100%;
    }

    .description h2 {
      color: #2c3e50;
    }

    .description p {
      line-height: 1.6;
      color: #555;
    }

    ul {
      padding-left: 20px;
    }
  </style>
</head>
<body>
  <div class="main-content">
    <!-- Slideshow -->
    <div class="slideshow-container">
      <?php foreach ($cars as $index => $car): ?>
        <?php 
            $carModel = htmlspecialchars($car['model']);
          $carPrice = number_format($car['price'], 2);
        ?>
        <div class="slide" style="<?= $index === 0 ? 'display:block;' : '' ?>">
          <img 
            src="../uploads/<?= htmlspecialchars($car['image']) ?>"
            alt="Car Image"  
            onerror="this.onerror=null; this.src='../uploads/default.jpg';" 
            style="background-color: #ccc;"
          >
          <div class="slide-caption"><?= $carModel ?> — ₱<?= $carPrice ?></div>
        </div>
      <?php endforeach; ?>

      <a class="prev" onclick="plusSlides(-1)">❮</a>
      <a class="next" onclick="plusSlides(1)">❯</a>
    </div>

    <!-- Description -->
    <div class="description">
      <h2>Welcome to the Car Rental Portal</h2>
      <p>
        This dashboard allows you to explore and reserve from our selection of available vehicles. Using the left menu, you can:
      </p>
      <ul>
        <li>View available cars ready for reservation.</li>
        <li>Book a car with your preferred schedule.</li>
        <li>Check your reservation status anytime.</li>
        <li>Cancel or manage your bookings with ease.</li>
      </ul>
      <p>
        Thank you for choosing our car rental service. Enjoy your experience!
      </p>
    </div>
  </div>

  <script>
    let slideIndex = 0;
    let slides = document.getElementsByClassName("slide");

    function showSlide(n) {
      for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
      }
      slides[n].style.display = "block";
    }

    function autoSlide() {
      slideIndex++;
      if (slideIndex >= slides.length) slideIndex = 0;
      showSlide(slideIndex);
      setTimeout(autoSlide, 3000); // Change every 3 seconds
    }

    function plusSlides(n) {
      slideIndex += n;
      if (slideIndex >= slides.length) slideIndex = 0;
      if (slideIndex < 0) slideIndex = slides.length - 1;
      showSlide(slideIndex);
    }

    window.onload = function() {
      showSlide(slideIndex); // Show first slide immediately
      setTimeout(autoSlide, 3000); // Start auto sliding after 3 seconds
    };
  </script>
</body>
</html>
