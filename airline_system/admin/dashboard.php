<?php
require_once  '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_admin();

// Get stats
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$bookings_count = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$flights_count = $pdo->query("SELECT COUNT(*) FROM flights")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'confirmed'")->fetchColumn();

// Recent bookings
$recent_bookings = $pdo->query("SELECT b.*, u.username, f.flight_number 
                                FROM bookings b
                                JOIN users u ON b.user_id = u.id
                                JOIN flights f ON b.flight_id = f.id
                                ORDER BY b.created_at DESC LIMIT 5")->fetchAll();

$page_title = "Admin Dashboard";
include '../includes/header.php';
?>

<div class="container">
    <h1>Admin Dashboard</h1>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="display-4"><?= $users_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Bookings</h5>
                    <p class="display-4"><?= $bookings_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Flights</h5>
                    <p class="display-4"><?= $flights_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Revenue</h5>
                   <p class="display-4">$<?= number_format((float) ($revenue ?? 0), 2) ?></p>

                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Recent Bookings</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_bookings)): ?>
                        <div class="alert alert-info">No recent bookings</div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recent_bookings as $booking): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mb-1">#<?= $booking['booking_reference'] ?></h5>
                                        <span class="badge 
                                            <?= $booking['status'] == 'confirmed' ? 'badge-success' : ($booking['status'] == 'cancelled' ? 'badge-danger' : 'badge-warning') ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </div>
                                    <p class="mb-1">Flight: <?= $booking['flight_number'] ?></p>
                                    <small>User: <?= $booking['username'] ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="<?= BASE_URL ?>/admin/flights.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-plane mr-2"></i> Manage Flights
                        </a>
                        <a href="<?= BASE_URL ?>/admin/bookings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-ticket-alt mr-2"></i> Manage Bookings
                        </a>
                        <a href="<?= BASE_URL ?>/admin/users.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users mr-2"></i> Manage Users
                        </a>
                        <a href="<?= BASE_URL ?>/admin/hotels.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-hotel mr-2"></i> Manage Hotels
                        </a>
                        <a href="<?= BASE_URL ?>/admin/analytics.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar mr-2"></i> View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>