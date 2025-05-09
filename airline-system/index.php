<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Fetch featured flights
$featured_flights = $pdo->query("
    SELECT * FROM flights 
    WHERE departure_time > NOW() 
    ORDER BY RAND() LIMIT 3
")->fetchAll();

$page_title = "SkyHigh Airlines | Home";
include 'includes/header.php';
?>

<div class="hero-section">
    <div class="container text-center text-white py-5">
        <h1 class="display-4">Fly Beyond Horizons</h1>
        <p class="lead">Book your next adventure with the world's most trusted airline</p>
        <a href="<?= BASE_URL ?>/flights/search.php" class="btn btn-primary btn-lg">Search Flights</a>
    </div>
</div>

<div class="container py-5">
    <h2 class="text-center mb-4">Featured Flights</h2>
    <div class="row">
        <?php foreach ($featured_flights as $flight): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= $flight['flight_number'] ?></h5>
                        <p class="card-text">
                            <?= $flight['departure_airport'] ?> → <?= $flight['arrival_airport'] ?><br>
                            <small><?= date('M j, Y', strtotime($flight['departure_time'])) ?></small>
                        </p>
                        <p class="text-success font-weight-bold">From $<?= number_format($flight['economy_price'], 2) ?></p>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="<?= BASE_URL ?>/flights/details.php?id=<?= $flight['id'] ?>" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container text-center">
        <h2>Why Choose Us?</h2>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="p-4">
                    <h3>★ 4.8/5</h3>
                    <p>Customer Satisfaction</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <h3>150+</h3>
                    <p>Destinations Worldwide</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <h3>24/7</h3>
                    <p>Customer Support</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>