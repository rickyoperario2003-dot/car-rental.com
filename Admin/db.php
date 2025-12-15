<?php
// db.php - database connection
$conn = new mysqli("localhost", "root", "", "car_rental_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>