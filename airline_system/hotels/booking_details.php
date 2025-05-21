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
    $stmt = $pdo->prepare("SELECT hb.*, h.name as hotel_name, h.location, 
                           h.description as hotel_description, 
                           h.amenities as hotel_amenities,
                           h.price_per_night as hotel_price,
                           rt.room_type,
                           rt.price_per_night as room_price,
                           rt.max_occupancy,
                           rt.amenities as room_amenities,
                           IFNULL(hb.payment_method, 'direct_payment') as payment_method,
                           IFNULL(hb.transaction_id, CONCAT('TXN-', UPPER(SUBSTRING(MD5(RAND()), 1, 10)))) as transaction_id
                           FROM hotel_bookings hb 
                           JOIN hotels h ON hb.hotel_id = h.id 
                           LEFT JOIN room_types rt ON hb.hotel_id = rt.hotel_id
                           WHERE hb.id = ? AND hb.user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    $page_title = "Booking Details - " . $booking['hotel_name'];
    include '../includes/header.php';
?>

<div class="container py-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Booking Details</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4>Hotel Information</h4>
                    <p><strong>Hotel:</strong> <?= htmlspecialchars($booking['hotel_name']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($booking['location']) ?></p>
                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($booking['hotel_description'])) ?></p>
                    <p><strong>Amenities:</strong> <?= nl2br(htmlspecialchars($booking['hotel_amenities'])) ?></p>
                </div>
                <div class="col-md-6">
                    <h4>Booking Details</h4>
                    <p><strong>Booking Reference:</strong> <?= htmlspecialchars($booking['booking_reference']) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($booking['status']) ?></p>
                    <p><strong>Check-in:</strong> <?= date('M j, Y', strtotime($booking['check_in'])) ?></p>
                    <p><strong>Check-out:</strong> <?= date('M j, Y', strtotime($booking['check_out'])) ?></p>
                    <p><strong>Rooms:</strong> <?= $booking['rooms'] ?></p>
                    <p><strong>Price per Night:</strong> $<?= number_format($booking['hotel_price'], 2) ?></p>
                    <p><strong>Total Price:</strong> $<?= number_format($booking['total_price'], 2) ?></p>
                </div>
            </div>

            <div class="mt-4">
                <h4>Guest Information</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($booking['guest_first_name'] . ' ' . $booking['guest_last_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($booking['guest_email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($booking['guest_phone']) ?></p>
                <?php if ($booking['special_requests']): ?>
                    <p><strong>Special Requests:</strong> <?= nl2br(htmlspecialchars($booking['special_requests'])) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($booking['status'] === 'confirmed'): ?>
                <div class="mt-4">
                    <h4>Payment Details</h4>
                    <p><strong>Transaction ID:</strong> <?= htmlspecialchars($booking['transaction_id']) ?></p>
                    <p><strong>Payment Method:</strong> <?= ucfirst(str_replace('_', ' ', $booking['payment_method'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <?php if ($booking['status'] === 'confirmed'): ?>
                    <a href="<?= BASE_URL ?>/hotels/cancel.php?booking_id=<?= $booking_id ?>" class="btn btn-danger">Cancel Booking</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php
} catch (Exception $e) {
    $error = "Error loading booking details: " . $e->getMessage();
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
