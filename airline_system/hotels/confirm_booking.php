<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

$booking_id = intval($_GET['booking_id'] ?? 0);

if (!$booking_id) {
    header("Location: /hotels/search.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT hb.*, h.name as hotel_name, h.location, h.price_per_night,
                          h.description as hotel_description, h.amenities as hotel_amenities,
                          rt.room_type, rt.price_per_night as room_price, rt.max_occupancy,
                          rt.amenities as room_amenities
                          FROM hotel_bookings hb 
                          JOIN hotels h ON hb.hotel_id = h.id 
                          LEFT JOIN room_types rt ON hb.hotel_id = rt.hotel_id
                          WHERE hb.id = ? AND hb.user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    $page_title = "Hotel Booking Confirmation";
    include '../includes/header.php';
?>

<div class="container">
    <div class="text-center mb-5">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#28a745" class="bi bi-check-circle" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
        </svg>
        <h1>Booking Confirmed!</h1>
        <p class="lead">Your hotel booking has been successfully confirmed</p>
        <p>Booking Reference: <strong><?= htmlspecialchars($booking['booking_reference']) ?></strong></p>
    </div>

    <!-- Dummy Feedback Popup -->
    <div id="feedbackPopup" class="feedback-popup" style="display: none;">
        <div class="feedback-content">
            <h3>How was your booking experience?</h3>
            <div class="rating-stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
            <div class="mt-3">
                <textarea class="form-control" placeholder="Share your thoughts..." rows="3"></textarea>
            </div>
            <div class="text-end mt-3">
                <button class="btn btn-secondary" id="skipFeedback">Skip</button>
                <button class="btn btn-primary" id="submitFeedback">Submit</button>
            </div>
        </div>
    </div>
    <div id="feedbackOverlay" class="feedback-overlay" style="display: none;"></div>

    <style>
        .feedback-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }
        .feedback-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .rating-stars i {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
        }
        .rating-stars i.active {
            color: #ffc107;
        }
    </style>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Hotel Information</h2>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($booking['hotel_name']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($booking['location']) ?></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($booking['hotel_description'])) ?></p>
            <p><strong>Amenities:</strong> <?= nl2br(htmlspecialchars($booking['hotel_amenities'])) ?></p>
            <p><strong>Price per Night:</strong> $<?= number_format($booking['price_per_night'], 2) ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Booking Details</h2>
        </div>
        <div class="card-body">
            <p><strong>Check-in:</strong> <?= date('M j, Y', strtotime($booking['check_in'])) ?></p>
            <p><strong>Check-out:</strong> <?= date('M j, Y', strtotime($booking['check_out'])) ?></p>
            <p><strong>Rooms:</strong> <?= $booking['rooms'] ?></p>
            <p><strong>Total Price:</strong> $<?= number_format($booking['total_price'], 2) ?></p>
            <p><strong>Room Type:</strong> <?= htmlspecialchars($booking['room_type'] ?? 'Not specified') ?></p>
            <p><strong>Room Price:</strong> $<?= number_format($booking['room_price'] ?? 0, 2) ?></p>
            <p><strong>Room Amenities:</strong> <?= nl2br(htmlspecialchars($booking['room_amenities'] ?? 'Not specified')) ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Guest Information</h2>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($booking['guest_first_name'] . ' ' . $booking['guest_last_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($booking['guest_email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($booking['guest_phone']) ?></p>
            <?php if ($booking['special_requests']): ?>
                <p><strong>Special Requests:</strong> <?= nl2br(htmlspecialchars($booking['special_requests'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4">
        <a href="/hotels/my_bookings.php" class="btn btn-primary">View My Bookings</a>
        <a href="/hotels/search.php" class="btn btn-secondary">Book Another Hotel</a>
    </div>
</div>

<script>
    // Feedback popup functionality
    document.addEventListener('DOMContentLoaded', function() {
        const popup = document.getElementById('feedbackPopup');
        const overlay = document.getElementById('feedbackOverlay');
        const skipBtn = document.getElementById('skipFeedback');
        const submitBtn = document.getElementById('submitFeedback');
        
        // Show popup after 2 seconds
        setTimeout(function() {
            popup.style.display = 'block';
            overlay.style.display = 'block';
        }, 2000);

        // Close popup
        skipBtn.addEventListener('click', function() {
            popup.style.display = 'none';
            overlay.style.display = 'none';
        });

        // Handle feedback submission
        submitBtn.addEventListener('click', function() {
            // Here you would typically send the feedback to the server
            alert('Thank you for your feedback!');
            popup.style.display = 'none';
            overlay.style.display = 'none';
        });
    });
</script>

<?php
} catch (Exception $e) {
    $error = "Error loading booking details: " . $e->getMessage();
    include '../includes/header.php';
    ?>
    <div class="container">
        <div class="alert alert-danger"><?= $error ?></div>
        <a href="/hotels/search.php" class="btn btn-primary">Back to Search</a>
    </div>
    <?php
}

include '../includes/footer.php';
?>