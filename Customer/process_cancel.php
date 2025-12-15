<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];

    // Get reservation details
    $res = $conn->query("SELECT car_id, quantity FROM reservations WHERE id = $reservation_id");
    if ($res && $res->num_rows > 0) {
        $reservation = $res->fetch_assoc();
        $car_id = $reservation['car_id'];
        $returned_quantity = $reservation['quantity'];

        // Update car stock
        $conn->query("UPDATE cars SET quantity = quantity + $returned_quantity WHERE id = $car_id");

        // Set status to 'Available' if quantity > 0
        $conn->query("UPDATE cars SET status = 'Available' WHERE id = $car_id AND quantity > 0");

        // Mark the reservation as canceled
        $conn->query("UPDATE reservations SET status = 'Cancelled' WHERE id = $reservation_id");
    }

    header("Location: customer_reservations.php"); // or your reservation list
    exit();
}
?>
