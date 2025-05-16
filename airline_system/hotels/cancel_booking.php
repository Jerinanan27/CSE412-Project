<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $booking_id = $_GET['booking_id'] ?? 0;
    
    if (!$booking_id) {
        header("Location: ../user/dashboard.php?error=invalid_booking");
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Verify booking exists and belongs to user
        $stmt = $pdo->prepare("SELECT hb.*, h.available_rooms, h.id as hotel_id 
                              FROM hotel_bookings hb 
                              JOIN hotels h ON hb.hotel_id = h.id 
                              WHERE hb.id = ? AND hb.user_id = ? AND hb.status = 'confirmed'");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch();

        if (!$booking) {
            throw new Exception('Invalid or already cancelled booking');
        }

        // Update booking status to cancelled
        $stmt = $pdo->prepare("UPDATE hotel_bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);

        // Refund available rooms back to hotel
        $stmt = $pdo->prepare("UPDATE hotels SET available_rooms = available_rooms + ? WHERE id = ?");
        $stmt->execute([$booking['rooms'], $booking['hotel_id']]);

        // Commit transaction
        $pdo->commit();

        // Redirect back to dashboard with success message
        header("Location: ../user/dashboard.php?cancel_success=1");
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        header("Location: ../user/dashboard.php?error=cancel_failed");
        exit;
    }
} else {
    // Redirect if accessed via POST
    header("Location: ../user/dashboard.php");
    exit;
}
?>
