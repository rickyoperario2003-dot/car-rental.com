<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['customer_id'];
  $name = trim($_POST['name']);
  $phone = trim($_POST['phone']);
  $location = trim($_POST['location']);
  $age = intval($_POST['age']);
  $sex = $_POST['sex'];
  $existingImage = $_POST['existing_image'];

  $imagePath = $existingImage;

  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../assets/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    $tmp = $_FILES['image']['tmp_name'];
    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $imagePath = $uploadDir . $fileName;

    move_uploaded_file($tmp, $imagePath);
  }

  $stmt = $conn->prepare("UPDATE customers SET name=?, phone=?, location=?, age=?, sex=?, image=? WHERE id=?");
  $stmt->bind_param("sssissi", $name, $phone, $location, $age, $sex, $imagePath, $id);

  if ($stmt->execute()) {
    header("Location: profile.php?updated=1");
    exit();
  } else {
    echo "Update failed: " . $stmt->error;
  }
}
?>
