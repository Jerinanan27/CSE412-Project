<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

if (!isset($_GET['booking_id'])) {
    redirect('/user/dashboard.php');
}

$booking_id = intval($_GET['booking_id']);

// Verify booking belongs to user
$stmt = $pdo->prepare("SELECT b.*, f.flight_number, f.airline, f.departure_airport, f.arrival_airport, 
                              f.departure_time, f.arrival_time, f.gate_number
                       FROM bookings b
                       JOIN flights f ON b.flight_id = f.id
                       WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('/user/dashboard.php?error=invalid_booking');
}

// Get passengers
$stmt = $pdo->prepare("SELECT * FROM passengers WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$passengers = $stmt->fetchAll();

$page_title = "E-Ticket";
include '../includes/header.php';
?>

<div class="container">
    <div class="text-center mb-4">
        <h1>E-Ticket</h1>
        <p>Booking Reference: <strong><?= $booking['booking_reference'] ?></strong></p>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Flight Information</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Flight Number:</strong> <?= $booking['flight_number'] ?></p>
                    <p><strong>Airline:</strong> <?= $booking['airline'] ?></p>
                    <p><strong>From:</strong> <?= $booking['departure_airport'] ?></p>
                    <p><strong>To:</strong> <?= $booking['arrival_airport'] ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Departure:</strong> <?= date('M j, Y H:i', strtotime($booking['departure_time'])) ?></p>
                    <p><strong>Arrival:</strong> <?= date('M j, Y H:i', strtotime($booking['arrival_time'])) ?></p>
                    <p><strong>Gate:</strong> <?= $booking['gate_number'] ?: 'TBD' ?></p>
                    <p><strong>Class:</strong> <?= ucfirst($booking['travel_class']) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Passenger Information</h2>
        </div>
        <div class="card-body">
            <?php foreach ($passengers as $passenger): ?>
                <div class="passenger-ticket mb-4 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><?= $passenger['first_name'] ?> <?= $passenger['last_name'] ?></h4>
                            <p><strong>Passport:</strong> <?= $passenger['passport_number'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Meal:</strong> <?= ucfirst(str_replace('_', ' ', $passenger['meal_preference'])) ?></p>
                            <p><strong>Special Assistance:</strong> <?= $passenger['special_assistance'] ?: 'None' ?></p>
                        </div>
                    </div>
                    <div class="barcode mt-3 text-center">
                        <svg id="barcode-<?= $passenger['id'] ?>"></svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="text-center">
        <button onclick="window.print()" class="btn btn-primary">Print Ticket</button>
        <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<!-- Barcode generator library -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
// Generate barcodes for each passenger
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($passengers as $passenger): ?>
        JsBarcode("#barcode-<?= $passenger['id'] ?>", "<?= $booking['booking_reference'] ?>-<?= $passenger['id'] ?>", {
            format: "CODE128",
            lineColor: "#000",
            width: 2,
            height: 50,
            displayValue: true
        });
    <?php endforeach; ?>
});
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .container, .container * {
        visibility: visible;
    }
    .container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .btn {
        display: none !important;
    }
    .passenger-ticket {
        page-break-inside: avoid;
        margin-bottom: 20px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>