<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';


require_admin();

// Get booking trends (last 30 days)
$booking_trends = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as bookings, SUM(total_price) as revenue
    FROM bookings
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
")->fetchAll();

// Get revenue by flight
$revenue_by_flight = $pdo->query("
    SELECT f.flight_number, f.departure_airport, f.arrival_airport, 
           COUNT(b.id) as bookings, SUM(b.total_price) as revenue
    FROM flights f
    LEFT JOIN bookings b ON f.id = b.flight_id AND b.status = 'confirmed'
    GROUP BY f.id
    ORDER BY revenue DESC
")->fetchAll();

// Get user registrations
$user_registrations = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as users
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
")->fetchAll();

$page_title = "Analytics Dashboard";
include '../includes/header.php';
?>

<div class="container">
    <h1>Analytics Dashboard</h1>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Booking Trends (Last 30 Days)</h2>
                </div>
                <div class="card-body">
                    <canvas id="bookingsChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>User Registrations (Last 30 Days)</h2>
                </div>
                <div class="card-body">
                    <canvas id="usersChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2>Revenue by Flight</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Flight</th>
                            <th>Route</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenue_by_flight as $flight): ?>
                            <tr>
                                <td><?= $flight['flight_number'] ?></td>
                                <td><?= $flight['departure_airport'] ?> to <?= $flight['arrival_airport'] ?></td>
                                <td><?= $flight['bookings'] ?></td>
                                <td>$<?= number_format($flight['revenue'] ?? 0, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Bookings Chart
    const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
    const bookingsChart = new Chart(bookingsCtx, {
        type: 'line',
        data: {
            labels: [<?= implode(',', array_map(function ($day) {
                            return "'" . date('M j', strtotime($day['date'])) . "'";
                        }, $booking_trends)) ?>],
            datasets: [{
                label: 'Bookings',
                data: [<?= implode(',', array_column($booking_trends, 'bookings')) ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Revenue ($)',
                data: [<?= implode(',', array_column($booking_trends, 'revenue')) ?>],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });

    // Users Chart
    const usersCtx = document.getElementById('usersChart').getContext('2d');
    const usersChart = new Chart(usersCtx, {
        type: 'bar',
        data: {
            labels: [<?= implode(',', array_map(function ($day) {
                            return "'" . date('M j', strtotime($day['date'])) . "'";
                        }, $user_registrations)) ?>],
            datasets: [{
                label: 'New Users',
                data: [<?= implode(',', array_column($user_registrations, 'users')) ?>],
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>