<?php
include 'db.php';
$smg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  $name = trim($_POST['name']);
  $user_name = trim($_POST['user_name']);
  $phone = trim($_POST['phone']);
  $location = trim($_POST['location']);
  $age = intval($_POST['age']);
  $sex = $_POST['sex'];
  $password = $_POST['password'];
  $cpassword = $_POST['cpassword'];
  $imagePath = null;

  if ($password !== $cpassword) {
    $smg = "Passwords do not match!";
  } else {
    $stmt = $conn->prepare("SELECT id FROM customers WHERE user_name = ? OR phone = ?");
    $stmt->bind_param("ss", $user_name, $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $smg = "Username or phone already exists!";
    } else {
      // Handle image upload if any
      if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $fileName;
        move_uploaded_file($fileTmp, $imagePath);
      }

      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $insert = $conn->prepare("INSERT INTO customers (name, user_name, phone, location, age, sex, password, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $insert->bind_param("ssssisss", $name, $user_name, $phone, $location, $age, $sex, $hashed_password, $imagePath);

      if ($insert->execute()) {
        header("Location: login.php?registered=1");
        exit();
      } else {
        $smg = "Registration failed. Please try again.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('2v2.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-end bg-black/60 pr-10">



  <div class="bg-white/90 rounded-3xl shadow-2xl p-6 w-full max-w-md">
    <div class="text-center mb-6">
      <h2 class="text-3xl font-bold text-gray-800">Register</h2>
    </div>

    <?php if (!empty($smg)): ?>
      <p class="text-red-600 text-sm mb-4 text-center"><?= $smg ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="text" name="name" placeholder="Full Name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm" />
      <input type="text" name="user_name" placeholder="Username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm" />
      <input type="text" name="phone" placeholder="Phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm" />
      <input type="text" name="location" placeholder="Location" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm" />
      <input type="number" name="age" placeholder="Age" required min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm" />
      
      <select name="sex" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm">
        <option value="">Select Sex</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>

      <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm" />
      <input type="password" name="cpassword" placeholder="Confirm Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm" />

      <!-- Optional Image Upload -->
      <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm" />

      <button type="submit" name="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
        Register Now
      </button>
    </form>

    <p class="text-sm text-center text-gray-600 mt-6">
      Already have an account?
      <a href="login.php" class="text-blue-600 hover:underline">Login Now</a>
    </p>
  </div>

</body>
</html>
