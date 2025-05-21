<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

// Function to generate unique booking reference
function generate_booking_reference() {
    global $pdo;
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $length = 10;
    $max_attempts = 10;
    $attempt = 0;

    do {
        $reference = '';
        for ($i = 0; $i < $length; $i++) {
            $reference .= $characters[rand(0, strlen($characters) - 1)];
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM hotel_bookings WHERE booking_reference = ?");
        $stmt->execute([$reference]);
        $exists = $stmt->fetchColumn();

        $attempt++;
        if ($attempt >= $max_attempts) {
            throw new Exception("Unable to generate unique booking reference after $max_attempts attempts");
        }
    } while ($exists);

    return $reference;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotel_id = intval($_POST['hotel_id']);
    $check_in = sanitize($_POST['check_in']);
    $check_out = sanitize($_POST['check_out']);
    $rooms = intval($_POST['rooms']);
    $total_price = floatval($_POST['total_price']);
    
    // Get guest information
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $special_requests = sanitize($_POST['special_requests'] ?? '');

    try {
        $pdo->beginTransaction();

        // Generate booking reference
        $booking_ref = generate_booking_reference();

        // Generate transaction ID
        $transaction_id = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));

        // Create booking with confirmed status
        $stmt = $pdo->prepare("INSERT INTO hotel_bookings 
                              (user_id, hotel_id, check_in, check_out, rooms, total_price, 
                               status, booking_reference, guest_first_name, guest_last_name, 
                               guest_email, guest_phone, special_requests, payment_method, 
                               transaction_id)
                              VALUES (?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $hotel_id,
            $check_in,
            $check_out,
            $rooms,
            $total_price,
            $booking_ref,
            $first_name,
            $last_name,
            $email,
            $phone,
            $special_requests,
            'direct_payment',
            $transaction_id
        ]);
        $booking_id = $pdo->lastInsertId();

        // Log booking status
        $stmt = $pdo->prepare("INSERT INTO hotel_bookings_status (booking_id, status) VALUES (?, ?)");
        $stmt->execute([$booking_id, 'confirmed']);

        // Update hotel availability
        $stmt = $pdo->prepare("UPDATE hotels 
                              SET available_rooms = available_rooms - ? 
                              WHERE id = ?");
        $stmt->execute([$rooms, $hotel_id]);

        $pdo->commit();

        // Redirect to confirmation page
        header("Location: confirm_booking.php?booking_id=$booking_id");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to create booking: " . $e->getMessage();
    }
}

$page_title = "Hotel Booking";
include '../includes/header.php';
?>

<div class="container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <h1>Hotel Booking</h1>
    
    <div class="card">
        <div class="card-body">
            <p>Your booking has been created. Please proceed to payment.</p>
            <a href="payment.php?booking_id=<?= $booking_id ?>" class="btn btn-primary">Continue to Payment</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
