<?php
session_start();
include 'db.php';

// Only allow if admin or logged-in user (optional security check)
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

echo "<h2>🛠️ Image File Check Report</h2>";
echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr style='background-color: #f0f0f0;'>
        <th>#</th>
        <th>Model</th>
        <th>Image Filename</th>
        <th>Status</th>
      </tr>";

$sql = "SELECT model, image FROM cars";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $index = 1;
    while ($row = $result->fetch_assoc()) {
        $model = htmlspecialchars($row['model']);
        $image = trim($row['image']);
        $imagePath = '../uploads/' . $image;
        $status = '';

        if (empty($image)) {
            $status = "<span style='color: orange;'>⚠️ No image filename</span>";
        } elseif (file_exists($imagePath)) {
            $status = "<span style='color: green;'>✅ Exists</span>";
        } else {
            $status = "<span style='color: red;'>❌ Missing</span>";
        }

        echo "<tr>
                <td>$index</td>
                <td>$model</td>
                <td>$image</td>
                <td>$status</td>
              </tr>";
        $index++;
    }
} else {
    echo "<tr><td colspan='4'>No cars found in the database.</td></tr>";
}

echo "</table>";
?>
