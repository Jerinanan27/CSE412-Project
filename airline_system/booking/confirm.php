<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

if (!isset($_GET['booking_id'])) {
    redirect('/user/dashboard.php');
}

$booking_id = intval($_GET['booking_id']);

// Verify booking belongs to user and is confirmed
$stmt = $pdo->prepare("SELECT b.*, f.flight_number, f.departure_airport, f.arrival_airport, 
                              f.departure_time, f.arrival_time, p.transaction_id
                       FROM bookings b
                       JOIN flights f ON b.flight_id = f.id
                       LEFT JOIN payments p ON p.booking_id = b.id
                       WHERE b.id = ? AND b.user_id = ? AND b.status = 'confirmed'");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('/user/dashboard.php?error=invalid_booking');
}

// Get passengers
$stmt = $pdo->prepare("SELECT * FROM passengers WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$passengers = $stmt->fetchAll();

$page_title = "Booking Confirmation";
include '../includes/header.php';
?>

<div class="container">
    <div class="text-center mb-5">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#28a745" class="bi bi-check-circle" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
        </svg>
        <h1>Booking Confirmed!</h1>
        <p class="lead">Your flight has been successfully booked</p>
        <p>Booking Reference: <strong><?= $booking['booking_reference'] ?></strong></p>
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

    <script>
        // Show feedback popup after 2 seconds
        setTimeout(function() {
            document.getElementById('feedbackPopup').style.display = 'block';
            document.getElementById('feedbackOverlay').style.display = 'block';
        }, 2000);

        // Close popup when clicking overlay
        document.getElementById('feedbackOverlay').addEventListener('click', function() {
            closeFeedback();
        });

        // Handle skip
        document.getElementById('skipFeedback').addEventListener('click', function() {
            closeFeedback();
        });

        // Handle submit
        document.getElementById('submitFeedback').addEventListener('click', function() {
            alert('Thank you for your feedback!');
            closeFeedback();
        });

        function closeFeedback() {
            document.getElementById('feedbackPopup').style.display = 'none';
            document.getElementById('feedbackOverlay').style.display = 'none';
        }
    </script>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Flight Details</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Flight Number:</strong> <?= $booking['flight_number'] ?></p>
                    <p><strong>Airline:</strong> SkyHigh Airlines</p>
                    <p><strong>From:</strong> <?= $booking['departure_airport'] ?></p>
                    <p><strong>To:</strong> <?= $booking['arrival_airport'] ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Departure:</strong> <?= date('M j, Y H:i', strtotime($booking['departure_time'])) ?></p>
                    <p><strong>Arrival:</strong> <?= date('M j, Y H:i', strtotime($booking['arrival_time'])) ?></p>
                    <p><strong>Class:</strong> <?= ucfirst($booking['travel_class']) ?></p>
                    <p><strong>Passengers:</strong> <?= $booking['passengers'] ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Passenger Details</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Passport</th>
                            <th>Date of Birth</th>
                            <th>Meal</th>
                            <th>Special Assistance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($passengers as $passenger): ?>
                            <tr>
                                <td><?= $passenger['first_name'] ?> <?= $passenger['last_name'] ?></td>
                                <td><?= $passenger['passport_number'] ?></td>
                                <td><?= date('M j, Y', strtotime($passenger['date_of_birth'])) ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $passenger['meal_preference'])) ?></td>
                                <td><?= $passenger['special_assistance'] ?: 'None' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Payment Details</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Transaction ID:</strong> <?= $booking['transaction_id'] ?></p>
                    <p><strong>Total Paid:</strong> $<?= number_format($booking['total_price'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center">
        <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        <a href="<?= BASE_URL ?>/booking/ticket.php?booking_id=<?= $booking_id ?>" class="btn btn-secondary">View E-Ticket</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>