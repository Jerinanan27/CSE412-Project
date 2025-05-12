<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_admin();

// Handle form submission for both add and edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        if (isset($_POST['add_flight'])) {
            // Add new flight
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
          $_SESSION['success'] = "Flight added successfully!";
header("Location: flights.php");
exit;

        } elseif (isset($_POST['edit_flight']) && isset($_POST['flight_id'])) {
            // Update existing flight
            $flight_id = intval($_POST['flight_id']);
            $stmt = $pdo->prepare("UPDATE flights SET 
                                  flight_number = ?, airline = ?, departure_airport = ?, 
                                  arrival_airport = ?, departure_time = ?, arrival_time = ?, 
                                  economy_price = ?, business_price = ?, first_class_price = ?, 
                                  total_seats = ?, available_seats = ? 
                                  WHERE id = ?");
            $stmt->execute([
                $flight_number, $airline, $departure_airport, $arrival_airport,
                $departure_time, $arrival_time, $economy_price, $business_price,
                $first_class_price, $total_seats, $total_seats, $flight_id
            ]);
            $_SESSION['success'] = "Flight updated successfully!";
            header("Location: flights.php");
            exit;



        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle edit request
$edit_flight = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $flight_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->execute([$flight_id]);
    $edit_flight = $stmt->fetch();
}

// Fetch all flights
$stmt = $pdo->query("SELECT * FROM flights ORDER BY departure_time DESC");
$flights = $stmt->fetchAll();

$page_title = "Manage Flights";

include '../includes/header.php';
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
            <h2><?= $edit_flight ? 'Edit Flight' : 'Add New Flight' ?></h2>
        </div>
        <div class="card-body">
            <form method="post">
                <?php if ($edit_flight): ?>
                    <input type="hidden" name="flight_id" value="<?= $edit_flight['id'] ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Flight Number</label>
                            <input type="text" name="flight_number" class="form-control" 
                                   value="<?= $edit_flight ? htmlspecialchars($edit_flight['flight_number']) : '' ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Airline</label>
                            <input type="text" name="airline" class="form-control" 
                                   value="<?= $edit_flight ? htmlspecialchars($edit_flight['airline']) : '' ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Total Seats</label>
                            <input type="number" name="total_seats" class="form-control" min="1" 
                                   value="<?= $edit_flight ? $edit_flight['total_seats'] : '' ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departure Airport</label>
                            <input type="text" name="departure_airport" class="form-control" 
                                   value="<?= $edit_flight ? htmlspecialchars($edit_flight['departure_airport']) : '' ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Arrival Airport</label>
                            <input type="text" name="arrival_airport" class="form-control" 
                                   value="<?= $edit_flight ? htmlspecialchars($edit_flight['arrival_airport']) : '' ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departure Time</label>
                            <input type="datetime-local" name="departure_time" class="form-control" 
                                   value="<?= $edit_flight ? date('Y-m-d\TH:i', strtotime($edit_flight['departure_time'])) : '' ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Arrival Time</label>
                            <input type="datetime-local" name="arrival_time" class="form-control" 
                                   value="<?= $edit_flight ? date('Y-m-d\TH:i', strtotime($edit_flight['arrival_time'])) : '' ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Economy Price ($)</label>
                            <input type="number" step="0.01" name="economy_price" class="form-control" 
                                   value="<?= $edit_flight ? $edit_flight['economy_price'] : '' ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Business Price ($)</label>
                            <input type="number" step="0.01" name="business_price" class="form-control" 
                                   value="<?= $edit_flight ? $edit_flight['business_price'] : '' ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>First Class Price ($)</label>
                            <input type="number" step="0.01" name="first_class_price" class="form-control" 
                                   value="<?= $edit_flight ? $edit_flight['first_class_price'] : '' ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="<?= $edit_flight ? 'edit_flight' : 'add_flight' ?>" 
                        class="btn btn-primary"><?= $edit_flight ? 'Update Flight' : 'Add Flight' ?></button>
                <?php if ($edit_flight): ?>
                    <button type="button" onclick="resetForm()" class="btn btn-secondary">Cancel Edit</button>
                <?php endif; ?>
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
                                <td><?= htmlspecialchars($flight['flight_number']) ?></td>
                                <td><?= htmlspecialchars($flight['airline']) ?></td>
                                <td><?= htmlspecialchars($flight['departure_airport']) ?> to <?= htmlspecialchars($flight['arrival_airport']) ?></td>
                                <td><?= $departure->format('M j, Y H:i') ?></td>
                                <td><?= $arrival->format('M j, Y H:i') ?></td>
                                <td><?= $flight['available_seats'] ?>/<?= $flight['total_seats'] ?></td>
                                <td><?= ucfirst($flight['status']) ?></td>
                                <td>
                                    <a href="?edit=<?= $flight['id'] ?>" 
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

<script>
function resetForm() {
    document.querySelector('form').reset();
    const submitButton = document.querySelector('button[name="edit_flight"], button[name="add_flight"]');
    submitButton.name = 'add_flight';
    submitButton.textContent = 'Add Flight';
    document.querySelector('.card-header h2').textContent = 'Add New Flight';
    window.location.href = 'flights.php';
}
</script>

<?php include '../includes/footer.php'; ?>