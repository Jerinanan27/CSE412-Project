<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

if (!isset($_GET['booking_id'])) {
    redirect('/user/dashboard.php');
}

$booking_id = intval($_GET['booking_id']);

// Verify booking belongs to user
$stmt = $pdo->prepare("SELECT b.*, f.flight_number, f.departure_airport, f.arrival_airport, f.departure_time 
                       FROM bookings b
                       JOIN flights f ON b.flight_id = f.id
                       WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('/user/dashboard.php?error=invalid_booking');
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitize($_POST['payment_method']);
    $card_number = isset($_POST['card_number']) ? sanitize($_POST['card_number']) : '';
    $card_expiry = isset($_POST['card_expiry']) ? sanitize($_POST['card_expiry']) : '';
    $card_cvv = isset($_POST['card_cvv']) ? sanitize($_POST['card_cvv']) : '';
    
    try {
        $pdo->beginTransaction();
        
        // Record payment
        $transaction_id = 'PAY-' . strtoupper(bin2hex(random_bytes(5)));
        $stmt = $pdo->prepare("INSERT INTO payments 
                              (booking_id, amount, payment_method, transaction_id, status) 
                              VALUES (?, ?, ?, ?, 'completed')");
        $stmt->execute([
            $booking_id,
            $booking['total_price'],
            $payment_method,
            $transaction_id
        ]);
        
        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        $pdo->commit();
        
        // Send confirmation email
        $user = get_user_by_id($_SESSION['user_id']);
        $subject = "Booking Confirmation #" . $booking['booking_reference'];
        $message = "Thank you for your booking with " . SITE_NAME . "!\n\n";
        $message .= "Booking Reference: " . $booking['booking_reference'] . "\n";
        $message .= "Flight: " . $booking['flight_number'] . " from " . $booking['departure_airport'] . " to " . $booking['arrival_airport'] . "\n";
        $message .= "Departure: " . date('M j, Y H:i', strtotime($booking['departure_time'])) . "\n";
        $message .= "Passengers: " . $booking['passengers'] . "\n";
        $message .= "Total Paid: $" . number_format($booking['total_price'], 2) . "\n\n";
        $message .= "You can view your booking details at any time in your account.\n";
        
        send_email($user['email'], $subject, $message);
        
        redirect("/booking/confirm.php?booking_id=$booking_id");
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Payment processing failed: " . $e->getMessage();
    }
}

$page_title = "Payment";
include '../includes/header.php';
?>

<div class="container">
    <h1>Complete Payment</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h2>Booking Summary</h2>
                </div>
                <div class="card-body">
                    <p><strong>Booking Reference:</strong> <?= $booking['booking_reference'] ?></p>
                    <p><strong>Flight:</strong> <?= $booking['flight_number'] ?></p>
                    <p><strong>Route:</strong> <?= $booking['departure_airport'] ?> to <?= $booking['arrival_airport'] ?></p>
                    <p><strong>Departure:</strong> <?= date('M j, Y H:i', strtotime($booking['departure_time'])) ?></p>
                    <p><strong>Passengers:</strong> <?= $booking['passengers'] ?></p>
                    <p><strong>Class:</strong> <?= ucfirst($booking['travel_class']) ?></p>
                    <hr>
                    <h4>Total Amount: $<?= number_format($booking['total_price'], 2) ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Payment Method</h2>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" id="payment-method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>
                        
                        <div id="card-details" style="display:none;">
                            <div class="form-group">
                                <label>Card Number</label>
                                <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Expiry Date</label>
                                        <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>CVV</label>
                                        <input type="text" name="card_cvv" class="form-control" placeholder="123">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Complete Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payment-method').addEventListener('change', function() {
    const cardDetails = document.getElementById('card-details');
    if (this.value === 'credit_card' || this.value === 'debit_card') {
        cardDetails.style.display = 'block';
    } else {
        cardDetails.style.display = 'none';
    }
});
</script>

<?php include '../includes/footer.php'; ?>