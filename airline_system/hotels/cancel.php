<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

$booking_id = intval($_GET['booking_id'] ?? 0);

if (!$booking_id) {
    header("Location: /hotels/my_bookings.php");
    exit;
}

try {
    // Get booking details
    $stmt = $pdo->prepare("SELECT * FROM hotel_bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    if ($booking['status'] !== 'confirmed') {
        throw new Exception("Only confirmed bookings can be cancelled");
    }

    if (strtotime($booking['check_in']) <= strtotime(date('Y-m-d'))) {
        throw new Exception("Cannot cancel booking as check-in date has passed");
    }

    $page_title = "Cancel Hotel Booking";
    include '../includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-body">
            <h2>Cancel Booking</h2>
            
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Are you sure you want to cancel this booking? This action cannot be undone.
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Hotel:</strong> <?= htmlspecialchars($booking['hotel_name']) ?></p>
                    <p><strong>Check-in:</strong> <?= date('M j, Y', strtotime($booking['check_in'])) ?></p>
                    <p><strong>Check-out:</strong> <?= date('M j, Y', strtotime($booking['check_out'])) ?></p>
                    <p><strong>Rooms:</strong> <?= $booking['rooms'] ?></p>
                    <p><strong>Total Price:</strong> $<?= number_format($booking['total_price'], 2) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Booking Reference:</strong> <?= htmlspecialchars($booking['booking_reference']) ?></p>
                    <p><strong>Guest Name:</strong> <?= htmlspecialchars($booking['guest_first_name'] . ' ' . $booking['guest_last_name']) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($booking['status']) ?></p>
                </div>
            </div>

            <form method="post" action="process_cancel.php">
                <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Cancellation Policy: Full refund will be issued if cancelled before check-in date.
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="confirmCancellation" name="confirm" required>
                    <label class="form-check-label" for="confirmCancellation">
                        I confirm that I want to cancel this booking and understand the cancellation policy.
                    </label>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                    <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    include '../includes/header.php';
    ?>
    <div class="container">
        <div class="alert alert-danger"><?= $error ?></div>
        <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
    <?php
}

include '../includes/footer.php';
?>
