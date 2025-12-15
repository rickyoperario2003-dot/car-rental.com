<?php
session_start();
include 'db.php';

// Ensure all required data is submitted
if (
    isset($_POST['car_id'], $_POST['customer_id'], $_POST['reservation_date'], $_POST['return_date'], $_POST['quantity'])
) {
    $car_id = (int)$_POST['car_id'];
    $customer_id = (int)$_POST['customer_id'];
    $reservation_date = $_POST['reservation_date'];
    $return_date = $_POST['return_date'];
    $quantity = (int)$_POST['quantity'];

    // Check car availability
    $carQuery = $conn->prepare("SELECT quantity FROM cars WHERE id = ?");
    $carQuery->bind_param("i", $car_id);
    $carQuery->execute();
    $carResult = $carQuery->get_result();

    if ($carResult->num_rows === 0) {
        die("Car not found.");
    }

    $car = $carResult->fetch_assoc();

    if ($car['quantity'] < $quantity) {
        die("Not enough quantity available.");
    }

    // Insert reservation
    $insert = $conn->prepare("INSERT INTO reservations (customer_id, car_id, reservation_date, return_date, status) VALUES (?, ?, ?, ?, 'Pending')");
    $insert->bind_param("iiss", $customer_id, $car_id, $reservation_date, $return_date);
    $insert->execute();

    // Update car quantity
    $new_quantity = $car['quantity'] - $quantity;

    if ($new_quantity <= 0) {
        // Set to unavailable if no stock
        $update = $conn->prepare("UPDATE cars SET quantity = 0, status = 'Unavailable' WHERE id = ?");
    } else {
        $update = $conn->prepare("UPDATE cars SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $new_quantity, $car_id);
    }

    if ($new_quantity <= 0) {
        $update->bind_param("i", $car_id);
    }

    $update->execute();

    // Redirect after success
    header("Location: available_cars.php?success=1");
    exit();
} else {
    die("Missing reservation data.");
}
