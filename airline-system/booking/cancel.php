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
$stmt = $pdo->prepare("SELECT b.*, f.flight_number, f.departure_airport, f.arrival_airport, 
                              f.departure_time, f.available_seats
                       FROM bookings b
                       JOIN flights f ON b.flight_id = f.id
                       WHERE b.id = ? AND b.user_id = ? AND b.status = 'confirmed'");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('/user/dashboard.php?error=invalid_booking');
}

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        // Update payment status (if exists)
        $stmt = $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
        // Restore available seats
        $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats + ? WHERE id = ?");
        $stmt->execute([$booking['passengers'], $booking['flight_id']]);
        
        $pdo->commit();
        
        // Send cancellation email
        $user = get_user_by_id($_SESSION['user_id']);
        $subject = "Booking Cancellation #" . $booking['booking_reference'];
        $message = "Your booking has been cancelled.\n\n";
        $message .= "Booking Reference: " . $booking['booking_reference'] . "\n";
        $message .= "Flight: " . $booking['flight_number'] . " from " . $booking['departure_airport'] . " to " . $booking['arrival_airport'] . "\n";
        $message .= "Departure: " . date('M j, Y H:i', strtotime($booking['departure_time'])) . "\n";
        $message .= "Passengers: " . $booking['passengers'] . "\n";
        $message .= "Amount Refunded: $" . number_format($booking['total_price'], 2) . "\n\n";
        $message .= "If this was a mistake, please contact our support team immediately.\n";
        
        send_email($user['email'], $subject, $message);
        
        redirect("/user/dashboard.php?cancel_success=1");
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Cancellation failed: " . $e->getMessage();
    }
}

$page_title = "Cancel Booking";
include '../includes/header.php';
?>

<div class="container">
    <h1>Cancel Booking</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Booking Details</h2>
        </div>
        <div class="card-body">
            <p><strong>Booking Reference:</strong> <?= $booking['booking_reference'] ?></p>
            <p><strong>Flight Number:</strong> <?= $booking['flight_number'] ?></p>
            <p><strong>Route:</strong> <?= $booking['departure_airport'] ?> to <?= $booking['arrival_airport'] ?></p>
            <p><strong>Departure:</strong> <?= date('M j, Y H:i', strtotime($booking['departure_time'])) ?></p>
            <p><strong>Passengers:</strong> <?= $booking['passengers'] ?></p>
            <p><strong>Total Amount:</strong> $<?= number_format($booking['total_price'], 2) ?></p>
            
            <div class="alert alert-warning mt-4">
                <h4>Cancellation Policy</h4>
                <p>You will receive a full refund of $<?= number_format($booking['total_price'], 2) ?>.</p>
                <p>This action cannot be undone.</p>
            </div>
        </div>
    </div>
    
    <form method="post">
        <div class="form-group">
            <label for="reason">Reason for Cancellation (Optional)</label>
            <textarea id="reason" name="reason" class="form-control" rows="3"></textarea>
        </div>
        
        <div class="text-center">
            <button type="submit" class="btn btn-danger btn-lg">Confirm Cancellation</button>
            <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-secondary btn-lg">Go Back</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>