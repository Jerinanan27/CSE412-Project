<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php'; // Include auth.php for authentication functions
require_once '../includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Get user's bookings
$stmt = $pdo->prepare("SELECT b.*, f.flight_number, f.departure_airport, f.arrival_airport, 
                              f.departure_time, f.arrival_time, f.status as flight_status
                       FROM bookings b
                       JOIN flights f ON b.flight_id = f.id
                       WHERE b.user_id = ?
                       ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

$page_title = "User Dashboard";
include '../includes/header.php';
?>

<div class="container">
    <h1>Welcome, <?= $_SESSION['username'] ?>!</h1>

    <?php if (isset($_GET['cancel_success'])): ?>
        <div class="alert alert-success">Your booking has been cancelled successfully.</div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Upcoming Trips</h5>
                    <p class="display-4">
                        <?= count(array_filter($bookings, function ($b) {
                            return $b['status'] == 'confirmed' && strtotime($b['departure_time']) > time();
                        })) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Past Trips</h5>
                    <p class="display-4">
                        <?= count(array_filter($bookings, function ($b) {
                            return strtotime($b['departure_time']) < time();
                        })) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Cancelled</h5>
                    <p class="display-4">
                        <?= count(array_filter($bookings, function ($b) {
                            return $b['status'] == 'cancelled';
                        })) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2>Your Bookings</h2>
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info">You have no bookings yet. <a href="<?= BASE_URL ?>/flights/search.php">Search for flights</a></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
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
                            <?php foreach ($bookings as $booking): ?>
                                <?php
                                $departure_time = new DateTime($booking['departure_time']);
                                $is_upcoming = $departure_time > new DateTime();
                                ?>
                                <tr>
                                    <td><?= $booking['booking_reference'] ?></td>
                                    <td><?= $booking['flight_number'] ?></td>
                                    <td><?= $booking['departure_airport'] ?> to <?= $booking['arrival_airport'] ?></td>
                                    <td><?= $departure_time->format('M j, Y H:i') ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $booking['status'] == 'confirmed' ? 'badge-success' : ($booking['status'] == 'cancelled' ? 'badge-danger' : 'badge-warning') ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                        <?php if ($booking['flight_status'] != 'scheduled' && $booking['status'] == 'confirmed'): ?>
                                            <br><small>Flight: <?= ucfirst($booking['flight_status']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/booking/ticket.php?booking_id=<?= $booking['id'] ?>"
                                            class="btn btn-sm btn-info">View Ticket</a>
                                        <?php if ($booking['status'] == 'confirmed' && $is_upcoming): ?>
                                            <a href="<?= BASE_URL ?>/booking/cancel.php?booking_id=<?= $booking['id'] ?>"
                                                class="btn btn-sm btn-danger">Cancel</a>
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