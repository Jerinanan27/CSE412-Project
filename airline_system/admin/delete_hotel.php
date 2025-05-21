<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Ensure only admins can access this page
if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied. You must be an administrator to perform this action.');
}

// Check if hotel ID is provided
if (!isset($_POST['hotel_id']) || !is_numeric($_POST['hotel_id'])) {
    $_SESSION['error'] = 'Invalid hotel ID';
    header('Location: ' . BASE_URL . '/admin/hotels.php');
    exit;
}

$hotel_id = (int)$_POST['hotel_id'];

try {
    $pdo->beginTransaction();

    // Check if there are any active bookings for this hotel
    $stmt = $pdo->prepare("SELECT COUNT(*) as booking_count FROM hotel_bookings WHERE hotel_id = ? AND status != 'cancelled'");
    $stmt->execute([$hotel_id]);
    $result = $stmt->fetch();

    if ($result && $result['booking_count'] > 0) {
        throw new Exception('Cannot delete hotel with active bookings. Please cancel all bookings first.');
    }

    // Delete related records first to maintain referential integrity
    // Delete hotel photos
    $stmt = $pdo->prepare("DELETE FROM hotel_photos WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);

    // Delete hotel facilities
    $stmt = $pdo->prepare("DELETE FROM hotel_facilities WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);

    // Delete hotel reviews
    $stmt = $pdo->prepare("DELETE FROM hotel_reviews WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);

    // Delete cancelled bookings
    $stmt = $pdo->prepare("DELETE FROM hotel_bookings WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);

    // Finally, delete the hotel
    $stmt = $pdo->prepare("DELETE FROM hotels WHERE id = ?");
    $stmt->execute([$hotel_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Hotel not found or already deleted');
    }

    $pdo->commit();
    $_SESSION['success'] = 'Hotel deleted successfully';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error deleting hotel: ' . $e->getMessage();
}

// Redirect back to the hotels page
header('Location: ' . BASE_URL . '/admin/hotels.php');
exit;
