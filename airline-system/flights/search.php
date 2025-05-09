<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$departure = $_GET['from'] ?? '';
$arrival = $_GET['to'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');
$class = $_GET['class'] ?? 'economy';

if (!empty($departure) && !empty($arrival)) {
    $stmt = $pdo->prepare("SELECT * FROM flights 
                          WHERE departure_airport LIKE ? 
                          AND arrival_airport LIKE ?
                          AND DATE(departure_time) = ?
                          AND available_seats > 0
                          ORDER BY departure_time");
    $stmt->execute(["%$departure%", "%$arrival%", $date]);
    $flights = $stmt->fetchAll();
} else {
    $flights = [];
}

$page_title = "Flight Search";
include '../includes/header.php';
?>

<div class="container">
    <h1>Find Flights</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>From</label>
                            <input type="text" name="from" class="form-control" value="<?= htmlspecialchars($departure) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>To</label>
                            <input type="text" name="to" class="form-control" value="<?= htmlspecialchars($arrival) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" value="<?= $date ?>" min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Class</label>
                            <select name="class" class="form-control">
                                <option value="economy" <?= $class == 'economy' ? 'selected' : '' ?>>Economy</option>
                                <option value="business" <?= $class == 'business' ? 'selected' : '' ?>>Business</option>
                                <option value="first" <?= $class == 'first' ? 'selected' : '' ?>>First Class</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Search Flights</button>
            </form>
        </div>
    </div>
    
    <?php if (!empty($flights)): ?>
        <div class="card">
            <div class="card-header">
                <h2>Available Flights</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Flight #</th>
                                <th>Airline</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Arrival</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Seats</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flights as $flight): ?>
                                <?php
                                $departure_time = new DateTime($flight['departure_time']);
                                $arrival_time = new DateTime($flight['arrival_time']);
                                $duration = $departure_time->diff($arrival_time);
                                $price = $flight[$class.'_price'];
                                ?>
                                <tr>
                                    <td><?= $flight['flight_number'] ?></td>
                                    <td><?= $flight['airline'] ?></td>
                                    <td><?= $flight['departure_airport'] ?> to <?= $flight['arrival_airport'] ?></td>
                                    <td><?= $departure_time->format('M j, Y H:i') ?></td>
                                    <td><?= $arrival_time->format('M j, Y H:i') ?></td>
                                    <td><?= $duration->format('%hh %im') ?></td>
                                    <td>$<?= number_format($price, 2) ?></td>
                                    <td><?= $flight['available_seats'] ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/booking/create.php?flight_id=<?= $flight['id'] ?>&class=<?= $class ?>" 
                                           class="btn btn-sm btn-primary">Book Now</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif (!empty($departure) || !empty($arrival)): ?>
        <div class="alert alert-info">No flights found matching your criteria.</div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>