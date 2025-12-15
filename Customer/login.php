<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user_name = trim($_POST['user_name']);
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT id, name, user_name, password FROM customers WHERE user_name = ?");
  $stmt->bind_param("s", $user_name);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      // Success: set session and redirect
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['name'] = $user['name'];
      $_SESSION['user_name'] = $user['user_name'];
      header("Location: customer_dashboard.php");
      exit();
    } else {
      $error = "Incorrect password.";
    }
  } else {
    $error = "User not found.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Car Rental Login</title>
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
<body class="min-h-screen flex items-center justify-start bg-black/60 pl-10">


  <div class="bg-white/90 rounded-3xl shadow-2xl p-5 w-full max-w-md">
    <div class="text-center mb-6">
      <h1 class="text-3xl font-extrabold text-gray-800">Log In</h1>
    </div>

    <?php if (!empty($error)): ?>
      <p class="text-red-600 text-sm mb-4 text-center"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <div>
        <label for="user_name" class="block text-sm font-semibold text-gray-700">Username</label>
        <input type="text" id="user_name" name="user_name" required
               class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
        <input type="password" id="password" name="password" required
               class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div class="flex justify-between items-center text-sm">
        <label class="flex items-center">
          <input type="checkbox" class="form-checkbox text-blue-600 mr-2">
          Remember me
        </label>
      </div>

      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
        Log In
      </button>
    </form>

    <p class="text-sm text-center text-gray-600 mt-6">
      Don’t have an account?
      <a href="register.php" class="text-blue-600 hover:underline">Register Now</a>
    </p>
  </div>

</body>
</html>
