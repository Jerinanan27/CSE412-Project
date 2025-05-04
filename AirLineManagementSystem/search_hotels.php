<?php
$conn = new mysqli("localhost", "username", "password", "airline_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$location = $_POST['location'];

$sql = "SELECT * FROM hotels WHERE location='$location'";
$result = $conn->query($sql);

echo "<h2>Available Hotels</h2>";
echo "<a href='search_hotels.html'>← Back</a><br><br>";

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>
        <tr><th>Name</th><th>Location</th><th>Rooms</th><th>Price</th><th>Book</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['hotel_name']}</td>
            <td>{$row['location']}</td>
            <td>{$row['available_rooms']}</td>
            <td>{$row['price_per_night']}</td>
            <td><a href='book_hotel.html?hotel_id={$row['hotel_id']}'>Book</a></td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "No hotels found at this location.";
}

$conn->close();
?>
