<?php
$conn = new mysqli("localhost", "username", "password", "airline_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$flight_id = $_POST['flight_id'];
$passenger_name = $_POST['passenger_name'];
$email = $_POST['email'];
$seats = $_POST['seats'];

// Check seat availability
$check = $conn->query("SELECT available_seats FROM flights WHERE flight_id=$flight_id");
$row = $check->fetch_assoc();

if ($row['available_seats'] < $seats) {
    echo "Not enough seats available.";
} else {
    // Insert booking
    $conn->query("INSERT INTO flight_bookings (flight_id, passenger_name, email, seats) VALUES ($flight_id, '$passenger_name', '$email', $seats)");
    // Update seats
    $conn->query("UPDATE flights SET available_seats = available_seats - $seats WHERE flight_id=$flight_id");
    echo "Booking successful!";
}

$conn->close();
?>
