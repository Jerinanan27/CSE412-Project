<?php
$conn = new mysqli("localhost", "username", "password", "airline_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$source = $_POST['source'];
$destination = $_POST['destination'];
$departure_date = $_POST['departure_date'];

$sql = "SELECT * FROM flights WHERE source='$source' AND destination='$destination' AND DATE(departure_time)='$departure_date'";
$result = $conn->query($sql);

echo "<h2>Available Flights</h2>";
if ($result->num_rows > 0) {
    echo "<table><tr><th>Flight No</th><th>Airline</th><th>Time</th><th>Seats</th><th>Price</th><th>Book</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['flight_number']}</td>
            <td>{$row['airline_name']}</td>
            <td>{$row['departure_time']}</td>
            <td>{$row['available_seats']}</td>
            <td>{$row['price']}</td>
            <td><a href='book_flight.html?flight_id={$row['flight_id']}'>Book</a></td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "No flights found.";
}
$conn->close();
?>
