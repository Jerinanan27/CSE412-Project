<?php
$conn = new mysqli("localhost", "username", "password", "airline_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$type = $_POST['type'];
$booking_id = $_POST['booking_id'];

if ($type == 'flight') {
    $sql = "UPDATE flight_bookings SET payment_status='Paid' WHERE booking_id=$booking_id";
} else {
    $sql = "UPDATE hotel_bookings SET payment_status='Paid' WHERE booking_id=$booking_id";
}

if ($conn->query($sql) === TRUE) {
    echo "Payment Successful! Booking ID: $booking_id marked as Paid.<br><a href='home.html'>← Back to Home</a>";
} else {
    echo "Error processing payment: " . $conn->error;
}

$conn->close();
?>
