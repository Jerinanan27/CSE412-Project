<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

$booking_id = intval($_POST['booking_id'] ?? 0);
$confirm = isset($_POST['confirm']);

if (!$booking_id || !$confirm) {
    header("Location: /hotels/my_bookings.php");
    exit;
}

try {
    $pdo->beginTransaction();

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

    // Update booking status
    $stmt = $pdo->prepare("UPDATE hotel_bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$booking_id]);

    // Log booking status
    $stmt = $pdo->prepare("INSERT INTO hotel_bookings_status (booking_id, status) VALUES (?, ?)");
    $stmt->execute([$booking_id, 'cancelled']);

    // Refund payment
    $stmt = $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE id = ?");
    $stmt->execute([$booking['payment_id']]);

    // Restore hotel availability
    $stmt = $pdo->prepare("UPDATE hotels SET available_rooms = available_rooms + ? WHERE id = ?");
    $stmt->execute([$booking['rooms'], $booking['hotel_id']]);

    $pdo->commit();

    header("Location: /hotels/my_bookings.php?success=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Booking cancellation failed: " . $e->getMessage());
    header("Location: /hotels/my_bookings.php?error=1");
    exit;
}
?>
