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

<!DOCTYPE html>
<html>
<head>
    <title>Flight Status - SkyHigh Airlines</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/status.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1>Flight Status Dashboard</h1>
        
        <div class="status-overview">
            <div class="status-card scheduled">
                <h3>Scheduled</h3>
                <div class="count"><?php echo $status_counts['scheduled']; ?></div>
            </div>
            <div class="status-card delayed">
                <h3>Delayed</h3>
                <div class="count"><?php echo $status_counts['delayed']; ?></div>
            </div>
            <div class="status-card cancelled">
                <h3>Cancelled</h3>
                <div class="count"><?php echo $status_counts['cancelled']; ?></div>
            </div>
            <div class="status-card completed">
                <h3>Completed</h3>
                <div class="count"><?php echo $status_counts['completed']; ?></div>
            </div>
        </div>
        
        <div class="flight-search">
            <h2>Check Flight Status</h2>
            <form method="GET" class="inline-form">
                <div class="form-group">
                    <label for="flight_number">Flight Number</label>
                    <input type="text" id="flight_number" name="flight_number" placeholder="SH123">
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo $today; ?>">
                </div>
                <button type="submit" class="btn-primary">Check Status</button>
            </form>
        </div>
        
        <h2>Today's Flights</h2>
        <table class="flight-table">
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
                    <td><?php echo $flight['flight_number']; ?></td>
                    <td><?php echo $flight['departure_airport']; ?> to <?php echo $flight['arrival_airport']; ?></td>
                    <td><?php echo date('H:i', strtotime($flight['departure_time'])); ?></td>
                    <td><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo $flight['status']; ?>">
                            <?php echo ucfirst($flight['status']); ?>
                            <?php if ($flight['status'] === 'delayed'): ?>
                                (New dep: <?php echo date('H:i', strtotime($flight['departure_time'] . ' + 1 hour')); ?>)
                            <?php endif; ?>
                        </span>
                    </td>
                    <td><?php echo $flight['gate_number'] ? $flight['gate_number'] : 'TBD'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Auto-refresh every 60 seconds
        setTimeout(function() {
            location.reload();
        }, 60000);
        
        // Search functionality
        document.querySelector('.inline-form').addEventListener('submit', function(e) {
            const flightNumber = document.getElementById('flight_number').value.trim();
            const date = document.getElementById('date').value;
            
            if (!flightNumber && !date) {
                e.preventDefault();
                alert('Please enter a flight number or date');
            }
        });
    </script>
</body>
</html>