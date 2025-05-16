<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging - log the POST data
    error_log("Booking POST data: " . print_r($_POST, true));

    $hotel_id = $_POST['hotel_id'] ?? 0;
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $rooms = $_POST['rooms'] ?? 1;
    $total_price = $_POST['total_price'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    
    // Validate input
    if (!$hotel_id || !$check_in || !$check_out || !$total_price || !$payment_method) {
        error_log("Missing required fields for booking");
        header("Location: search.php?error=invalid_input");
        exit;
    }
    
    // Check hotel availability
    $stmt = $pdo->prepare("SELECT available_rooms FROM hotels WHERE id = ?");
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch();
    
    if (!$hotel || $hotel['available_rooms'] < $rooms) {
        error_log("No available rooms for booking");
        header("Location: search.php?error=no_rooms");
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Create payment record first
        $stmt = $pdo->prepare("INSERT INTO payments (amount, payment_method, status) 
                              VALUES (?, ?, 'completed')");
        $stmt->execute([$total_price, $payment_method]);
        $payment_id = $pdo->lastInsertId();

        // Create hotel booking with payment reference
        $stmt = $pdo->prepare("
            INSERT INTO hotel_bookings 
            (user_id, hotel_id, check_in, check_out, rooms, total_price, payment_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')");
        $stmt->execute([
            $_SESSION['user_id'], 
            $hotel_id, 
            $check_in, 
            $check_out, 
            $rooms, 
            $total_price,
            $payment_id
        ]);
        $booking_id = $pdo->lastInsertId();
        
        // Update available rooms
        $stmt = $pdo->prepare("UPDATE hotels SET available_rooms = available_rooms - ? WHERE id = ?");
        $stmt->execute([$rooms, $hotel_id]);
        
        $pdo->commit();
        
        // Get booking details for confirmation
        $stmt = $pdo->prepare("
            SELECT h.name, h.location, hb.check_in, hb.check_out, hb.rooms, hb.total_price,
                   p.payment_method
            FROM hotel_bookings hb 
            JOIN hotels h ON hb.hotel_id = h.id 
            JOIN payments p ON hb.payment_id = p.id
            WHERE hb.id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            throw new Exception('Failed to retrieve booking details');
        }
        
        $page_title = "Booking Confirmation";
        include '../includes/header.php';
?>

<div class="container py-5" style="min-height: 67vh;">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white text-center py-4">
            <h2 class="mb-0"><i class="fas fa-check-circle me-2"></i>Booking Confirmed</h2>
        </div>
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h3 class="fw-bold">Thank You for Your Booking!</h3>
                <p class="text-muted">Your hotel reservation has been successfully confirmed.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h4 class="border-bottom pb-2 mb-3">Booking Details</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Confirmation Number:</strong> <span><?php echo $booking_id; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Hotel:</strong> <span><?php echo htmlspecialchars($booking['name']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Location:</strong> <span><?php echo htmlspecialchars($booking['location']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Check-in:</strong> <span><?php echo date('F j, Y', strtotime($booking['check_in'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Check-out:</strong> <span><?php echo date('F j, Y', strtotime($booking['check_out'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Rooms:</strong> <span><?php echo htmlspecialchars($booking['rooms']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Price:</strong> <span>$<?php echo number_format($booking['total_price'], 2); ?></span>
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <h4 class="border-bottom pb-2 mb-3">Payment Details</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Payment Method:</strong> <span><?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="alert alert-info mt-4 d-flex align-items-center">
                <i class="fas fa-envelope me-2"></i>
                <span>A confirmation email has been sent to your registered email address.</span>
            </div>
            
            <div class="d-flex justify-content-center gap-3 mt-5">
                <a href="<?php echo BASE_URL; ?>/hotels/search.php" 
                   class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Book Another Hotel
                </a>
                <a href="<?php echo BASE_URL; ?>/dashboard.php" 
                   class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.list-group-item {
    border: none;
    padding: 0.75rem 0;
}
.card {
    border-radius: 10px;
}
.btn-lg {
    padding: 0.75rem 1.5rem;
}
</style>

<?php
        include '../includes/footer.php';
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Booking failed: " . $e->getMessage());
        header("Location: search.php?error=booking_failed");
        exit;
    }
} else {
    header("Location: search.php");
    exit;
}
?>