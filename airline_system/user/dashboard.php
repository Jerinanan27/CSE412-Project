<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Get user's flight bookings
$stmt = $pdo->prepare("
    SELECT b.*, f.flight_number, f.departure_airport, f.arrival_airport, 
           f.departure_time, f.arrival_time, f.status as flight_status
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$flight_bookings = $stmt->fetchAll();

// Get user's hotel bookings
$stmt = $pdo->prepare("
    SELECT hb.id, hb.check_in, hb.check_out, hb.rooms, hb.total_price, hb.status, h.name, h.location
    FROM hotel_bookings hb
    JOIN hotels h ON hb.hotel_id = h.id
    WHERE hb.user_id = ?
    ORDER BY hb.created_at DESC
");
$stmt->execute([$user_id]);
$hotel_bookings = $stmt->fetchAll();

$page_title = "User Dashboard";
include '../includes/header.php';
?>

<div class="container py-5" style="min-height: 67vh;">
    <h1 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>

    <?php if (isset($_GET['cancel_success'])): ?>
        <div class="alert alert-success">Your booking has been cancelled successfully.</div>
    <?php endif; ?>

    <!-- Trip Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Upcoming Trips</h5>
                    <p class="display-4">
                        <?= count(array_filter($flight_bookings, function ($b) {
                            return $b['status'] == 'confirmed' && strtotime($b['departure_time']) > time();
                        })) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Past Trips</h5>
                    <p class="display-4">
                        <?= count(array_filter($flight_bookings, function ($b) {
                            return strtotime($b['departure_time']) < time();
                        })) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Cancelled</h5>
                    <p class="display-4">
                        <?= count(array_filter($flight_bookings, function ($b) {
                            return $b['status'] == 'cancelled';
                        })) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Flight Bookings Section -->
    <div class="card mt-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-plane me-2"></i>Your Flight Bookings</h2>
        </div>
        <div class="card-body">
            <?php if (empty($flight_bookings)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You have no flight bookings yet. 
                    <a href="<?= BASE_URL ?>/flights/search.php" class="alert-link">Search for flights</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Flight</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flight_bookings as $booking): ?>
                                <?php
                                $departure_time = new DateTime($booking['departure_time']);
                                $is_upcoming = $departure_time > new DateTime();
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['booking_reference']) ?></td>
                                    <td><?= htmlspecialchars($booking['flight_number']) ?></td>
                                    <td><?= htmlspecialchars($booking['departure_airport']) ?> to <?= htmlspecialchars($booking['arrival_airport']) ?></td>
                                    <td><?= $departure_time->format('M j, Y H:i') ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $booking['status'] == 'confirmed' ? 'bg-success' : ($booking['status'] == 'cancelled' ? 'bg-danger' : 'bg-warning') ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                        <?php if ($booking['flight_status'] != 'scheduled' && $booking['status'] == 'confirmed'): ?>
                                            <br><small>Flight: <?= ucfirst($booking['flight_status']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/booking/ticket.php?booking_id=<?= $booking['id'] ?>"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-ticket-alt me-1"></i>View Ticket
                                        </a>
                                        <?php if ($booking['status'] == 'confirmed' && $is_upcoming): ?>
                                            <a href="<?= BASE_URL ?>/booking/cancel.php?booking_id=<?= $booking['id'] ?>"
                                               class="btn btn-sm btn-danger">
                                                <i class="fas fa-times me-1"></i>Cancel
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hotel Bookings Section -->
    <div class="card mt-4 shadow-lg border-0">
        <div class="card-header bg-info text-white">
            <h2 class="mb-0"><i class="fas fa-hotel me-2"></i>Your Hotel Bookings</h2>
        </div>
        <div class="card-body">
            <?php if (empty($hotel_bookings)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You have no hotel bookings yet. 
                    <a href="<?= BASE_URL ?>/hotels/search.php" class="alert-link">Search for hotels</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Hotel Name</th>
                                <th>Location</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Rooms</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hotel_bookings as $booking): ?>
                                <?php
                                $check_in = new DateTime($booking['check_in']);
                                $check_out = new DateTime($booking['check_out']);
                                $is_upcoming = $check_in > new DateTime();
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['id']) ?></td>
                                    <td><?= htmlspecialchars($booking['name']) ?></td>
                                    <td><?= htmlspecialchars($booking['location']) ?></td>
                                    <td><?= $check_in->format('M j, Y') ?></td>
                                    <td><?= $check_out->format('M j, Y') ?></td>
                                    <td><?= htmlspecialchars($booking['rooms']) ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $booking['status'] == 'confirmed' ? 'bg-success' : ($booking['status'] == 'cancelled' ? 'bg-danger' : 'bg-warning') ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/hotels/booking_details.php?booking_id=<?= $booking['id'] ?>"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-info-circle me-1"></i>Details
                                        </a>
                                        <?php if ($booking['status'] == 'confirmed' && $is_upcoming): ?>
                                            <a href="<?= BASE_URL ?>/hotels/cancel_booking.php?booking_id=<?= $booking['id'] ?>"
                                               class="btn btn-sm btn-danger">
                                                <i class="fas fa-times me-1"></i>Cancel
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>