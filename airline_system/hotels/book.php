<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

$hotel_id = $_GET['hotel_id'] ?? 0;
$check_in = $_GET['check_in'] ?? date('Y-m-d');
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$rooms = $_GET['rooms'] ?? 1;

$stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    header("Location: search.php");
    exit;
}

$days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
$total_price = $hotel['price_per_night'] * $days * $rooms;

$page_title = "Book Hotel - " . $hotel['name'];
include '../includes/header.php';
?>

<div class="container" style="min-height: 67vh;">
    <h1>Book Your Stay at <?php echo htmlspecialchars($hotel['name']); ?></h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h3>Booking Details</h3>
                    <p><strong>Hotel:</strong> <?php echo htmlspecialchars($hotel['name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($hotel['location']); ?></p>
                    <p><strong>Check-in:</strong> <?php echo $check_in; ?></p>
                    <p><strong>Check-out:</strong> <?php echo $check_out; ?></p>
                    <p><strong>Rooms:</strong> <?php echo $rooms; ?></p>
                    <p><strong>Price per night:</strong> $<?php echo number_format($hotel['price_per_night'], 2); ?></p>
                    <p><strong>Total Price:</strong> $<?php echo number_format($total_price, 2); ?> 
                        (<?php echo $days; ?> nights)</p>
                </div>
                <div class="col-md-6">
                    <h3>Amenities</h3>
                    <?php foreach (explode(',', $hotel['amenities']) as $amenity): ?>
                        <span class="badge badge-secondary"><?php echo trim($amenity); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <form method="post" action="confirm_booking.php">
                <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
                <input type="hidden" name="rooms" value="<?php echo $rooms; ?>">
                <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                
                <h3 class="mt-4">Payment Information</h3>
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block mt-4">Confirm Booking</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>