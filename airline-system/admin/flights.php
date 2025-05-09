<?php
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_flight'])) {
        $flight_number = sanitize($_POST['flight_number']);
        $airline = sanitize($_POST['airline']);
        $departure_airport = sanitize($_POST['departure_airport']);
        $arrival_airport = sanitize($_POST['arrival_airport']);
        $departure_time = sanitize($_POST['departure_time']);
        $arrival_time = sanitize($_POST['arrival_time']);
        $economy_price = floatval($_POST['economy_price']);
        $business_price = floatval($_POST['business_price']);
        $first_class_price = floatval($_POST['first_class_price']);
        $total_seats = intval($_POST['total_seats']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO flights 
                                  (flight_number, airline, departure_airport, arrival_airport, 
                                   departure_time, arrival_time, economy_price, business_price, 
                                   first_class_price, total_seats, available_seats) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $flight_number, $airline, $departure_airport, $arrival_airport,
                $departure_time, $arrival_time, $economy_price, $business_price,
                $first_class_price, $total_seats, $total_seats
            ]);
            $success = "Flight added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding flight: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->query("SELECT * FROM flights ORDER BY departure_time DESC");
$flights = $stmt->fetchAll();

$page_title = "Manage Flights";
include '../../includes/admin_header.php';
?>

<div class="container">
    <h1>Flight Management</h1>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Add New Flight</h2>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Flight Number</label>
                            <input type="text" name="flight_number" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Airline</label>
                            <input type="text" name="airline" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Total Seats</label>
                            <input type="number" name="total_seats" class="form-control" min="1" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departure Airport</label>
                            <input type="text" name="departure_airport" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Arrival Airport</label>
                            <input type="text" name="arrival_airport" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departure Time</label>
                            <input type="datetime-local" name="departure_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Arrival Time</label>
                            <input type="datetime-local" name="arrival_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Economy Price ($)</label>
                            <input type="number" step="0.01" name="economy_price" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Business Price ($)</label>
                            <input type="number" step="0.01" name="business_price" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>First Class Price ($)</label>
                            <input type="number" step="0.01" name="first_class_price" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="add_flight" class="btn btn-primary">Add Flight</button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Existing Flights</h2>
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
                            <th>Seats</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $flight): ?>
                            <?php
                            $departure = new DateTime($flight['departure_time']);
                            $arrival = new DateTime($flight['arrival_time']);
                            ?>
                            <tr>
                                <td><?= $flight['flight_number'] ?></td>
                                <td><?= $flight['airline'] ?></td>
                                <td><?= $flight['departure_airport'] ?> to <?= $flight['arrival_airport'] ?></td>
                                <td><?= $departure->format('M j, Y H:i') ?></td>
                                <td><?= $arrival->format('M j, Y H:i') ?></td>
                                <td><?= $flight['available_seats'] ?>/<?= $flight['total_seats'] ?></td>
                                <td><?= ucfirst($flight['status']) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/edit_flight.php?id=<?= $flight['id'] ?>" 
                                       class="btn btn-sm btn-primary">Edit</a>
                                    <a href="<?= BASE_URL ?>/admin/delete_flight.php?id=<?= $flight['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>