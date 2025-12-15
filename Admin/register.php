<?php
include 'db.php';
$smg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  $full_name = trim($_POST['name']);
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $cpassword = $_POST['cpassword'];

  if ($password !== $cpassword) {
    $smg = "Passwords do not match!";
  } else {
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $smg = "Username already exists!";
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $insert = $conn->prepare("INSERT INTO admins (username, password, full_name) VALUES (?, ?, ?)");
      $insert->bind_param("sss", $username, $hashed_password, $full_name);

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

<!-- HTML code -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }

    body {
      background-image: url('2v2.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      min-height: 100vh;
      padding: 2rem;
    }

    .form {
      background: rgba(255, 255, 255, 0.95);
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }

    .form h2 {
      margin-bottom: 20px;
      text-align: center;
      color: #333;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-control {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .btn {
      width: 100%;
      padding: 12px;
      border: none;
      background-color: #4CAF50;
      color: white;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #45a049;
    }

    .form p {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    .form a {
      color: #4CAF50;
      text-decoration: none;
    }

    .msg {
      color: red;
      font-size: 14px;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="form">
    <form method="POST">
      <h2>Registration</h2>
      <p class="msg"><?= htmlspecialchars($smg) ?></p>

      <div class="form-group">
        <input type="text" name="name" placeholder="Full Name" class="form-control" required>
      </div>
      <div class="form-group">
        <input type="text" name="username" placeholder="Username" class="form-control" required>
      </div>
      <div class="form-group">
        <input type="password" name="password" placeholder="Password" class="form-control" required>
      </div>
      <div class="form-group">
        <input type="password" name="cpassword" placeholder="Confirm Password" class="form-control" required>
      </div>

      <button class="btn" name="submit">Register Now</button>
      <p>Already have an account? <a href="login.php">Login Now</a></p>
    </form>
  </div>
</body>
</html>
