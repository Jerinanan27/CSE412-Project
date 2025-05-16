<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get all flights for today and tomorrow
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$stmt = $pdo->prepare("SELECT * FROM flights 
                       WHERE DATE(departure_time) BETWEEN ? AND ? 
                       ORDER BY departure_time ASC");
$stmt->execute([$today, $tomorrow]);
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group flights by status for the dashboard
$status_counts = [
    'scheduled' => 0,
    'delayed' => 0,
    'cancelled' => 0,
    'completed' => 0
];

foreach ($flights as $flight) {
    $status_counts[$flight['status']]++;
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4">Flight Status Dashboard</h1>

    <!-- Status Overview -->
    <div class="row status-overview mb-5">
        <div class="col-md-3 mb-3">
            <div class="card status-card scheduled shadow-sm">
                <div class="card-body text-center">
                    <h3 class="card-title">Scheduled</h3>
                    <div class="count display-4"><?php echo $status_counts['scheduled']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card status-card delayed shadow-sm">
                <div class="card-body text-center">
                    <h3 class="card-title">Delayed</h3>
                    <div class="count display-4"><?php echo $status_counts['delayed']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card status-card cancelled shadow-sm">
                <div class="card-body text-center">
                    <h3 class="card-title">Cancelled</h3>
                    <div class="count display-4"><?php echo $status_counts['cancelled']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card status-card completed shadow-sm">
                <div class="card-body text-center">
                    <h3 class="card-title">Completed</h3>
                    <div class="count display-4"><?php echo $status_counts['completed']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flight Search -->
    <div class="card mb-5 shadow-sm">
        <div class="card-header">
            <h2 class="h4 mb-0">Check Flight Status</h2>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="flight_number" class="form-label">Flight Number</label>
                    <input type="text" class="form-control" id="flight_number" name="flight_number" placeholder="SH123">
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?php echo $today; ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Check Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flight Table -->
    <h2 class="mb-3">Today's Flights</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Flight #</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Status</th>
                            <th>Gate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $flight): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($flight['flight_number']); ?></td>
                            <td><?php echo htmlspecialchars($flight['departure_airport']); ?> to <?php echo htmlspecialchars($flight['arrival_airport']); ?></td>
                            <td><?php echo date('H:i', strtotime($flight['departure_time'])); ?></td>
                            <td><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></td>
                            <td>
                                <span class="badge status-badge <?php echo $flight['status']; ?>">
                                    <?php echo ucfirst($flight['status']); ?>
                                    <?php if ($flight['status'] === 'delayed'): ?>
                                        (New dep: <?php echo date('H:i', strtotime($flight['departure_time'] . ' + 1 hour')); ?>)
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><?php echo $flight['gate_number'] ? htmlspecialchars($flight['gate_number']) : 'TBD'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Custom CSS -->
<style>
.status-card {
    border: none;
    border-radius: 10px;
    transition: transform 0.2s;
}
.status-card:hover {
    transform: translateY(-5px);
}
.status-card.scheduled {
    background-color: #e7f3ff;
    color: #005566;
}
.status-card.delayed {
    background-color: #fff3e0;
    color: #e65100;
}
.status-card.cancelled {
    background-color: #ffebee;
    color: #b71c1c;
}
.status-card.completed {
    background-color: #e8f5e9;
    color: #2e7d32;
}
.status-card .count {
    font-weight: bold;
}
.status-badge {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 0.9em;
}
.status-badge.scheduled {
    background-color: #2196f3;
}
.status-badge.delayed {
    background-color: #ff9800;
}
.status-badge.cancelled {
    background-color: #f44336;
}
.status-badge.completed {
    background-color: #4caf50;
}
</style>

<!-- JavaScript -->
<script>
// Auto-refresh every 60 seconds
setTimeout(function() {
    location.reload();
}, 60000);

// Search functionality
document.querySelector('form').addEventListener('submit', function(e) {
    const flightNumber = document.getElementById('flight_number').value.trim();
    const date = document.getElementById('date').value;
    
    if (!flightNumber && !date) {
        e.preventDefault();
        alert('Please enter a flight number or date');
    }
});
</script>