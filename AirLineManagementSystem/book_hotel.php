<?php
$conn = new mysqli("localhost", "username", "password", "airline_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$hotel_id = $_POST['hotel_id'];
$guest_name = $_POST['guest_name'];
$email = $_POST['email'];
$rooms = $_POST['rooms'];
$checkin = $_POST['checkin'];
$checkout = $_POST['checkout'];

// Check room availability
$check = $conn->query("SELECT available_rooms FROM hotels WHERE hotel_id=$hotel_id");
$row = $check->fetch_assoc();

if ($row['available_rooms'] < $rooms) {
    echo "Not enough rooms available.";
} else {
    $conn->query("INSERT INTO hotel_bookings (hotel_id, guest_name, email, rooms, checkin, checkout) VALUES 
    ($hotel_id, '$guest_name', '$email', $rooms, '$checkin', '$checkout')");
    
    $conn->query("UPDATE hotels SET available_rooms = available_rooms - $rooms WHERE hotel_id=$hotel_id");

    echo "Hotel booked successfully! <br><a href='home.html'>← Back to Home</a>";
}
$conn->close();
?>
