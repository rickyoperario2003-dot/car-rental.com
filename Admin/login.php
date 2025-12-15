<?php
session_start();
include 'db.php'; // ensure this connects to your database

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, full_name FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashedPassword, $fullName);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_name'] = $fullName;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "Admin not found.";
    }
}
?>

<!-- Your HTML code for login page -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Car Rental Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('2v2.png');
      background-size: cover;
      background-position: center;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-start bg-black/60 pl-10">

  <div class="bg-white/90 rounded-3xl shadow-2xl p-5 w-full max-w-md">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-extrabold text-gray-800">Log In</h1>
    </div>

    <?php if ($login_error): ?>
      <p class="text-sm text-red-600 text-center mb-4"><?= htmlspecialchars($login_error) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
      <div>
        <label class="block text-sm font-semibold text-gray-700">Username</label>
        <input type="text" name="username" required class="w-full mt-1 px-4 py-2 border rounded-lg shadow-sm" />
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700">Password</label>
        <input type="password" name="password" required class="w-full mt-1 px-4 py-2 border rounded-lg shadow-sm" />
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
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
