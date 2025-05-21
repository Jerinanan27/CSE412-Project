<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/airports.php';
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
                $flight_number,
                $airline,
                $departure_airport,
                $arrival_airport,
                $departure_time,
                $arrival_time,
                $economy_price,
                $business_price,
                $first_class_price,
                $total_seats,
                $total_seats
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
                $flight_number,
                $airline,
                $departure_airport,
                $arrival_airport,
                $departure_time,
                $arrival_time,
                $economy_price,
                $business_price,
                $first_class_price,
                $total_seats,
                $total_seats,
                $flight_id
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

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $_SESSION['success'];
                                                                        unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                        unset($_SESSION['error']); ?></div>
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
                                value="<?= $edit_flight ? htmlspecialchars($edit_flight['airline']) : '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Total Seats</label>
                            <input type="number" name="total_seats" class="form-control" min="1"
                                value="<?= $edit_flight ? $edit_flight['total_seats'] : '' ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departure Airport</label>
                            <select name="departure_airport" class="form-control" required>
                                <option value="">Select Departure Airport</option>
                                <?php foreach (getAllAirports() as $airport): ?>
                                    <option value="<?= $airport['code'] ?>" 
                                        <?= $edit_flight && $edit_flight['departure_airport'] == $airport['code'] ? 'selected' : '' ?>>
                                        <?= $airport['city'] ?> (<?= $airport['code'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Arrival Airport</label>
                            <select name="arrival_airport" class="form-control" required>
                                <option value="">Select Arrival Airport</option>
                                <?php foreach (getAllAirports() as $airport): ?>
                                    <option value="<?= $airport['code'] ?>" 
                                        <?= $edit_flight && $edit_flight['arrival_airport'] == $airport['code'] ? 'selected' : '' ?>>
                                        <?= $airport['city'] ?> (<?= $airport['code'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
    <div class="form-group">
        <label>Departure Time</label>
        <input type="datetime-local" name="departure_time" id="departure_time" class="form-control"
            value="<?= $edit_flight ? date('Y-m-d\TH:i', strtotime($edit_flight['departure_time'])) : '' ?>"
            min="<?= date('Y-m-d\TH:i') ?>" required>
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label>Arrival Time</label>
        <input type="datetime-local" name="arrival_time" id="arrival_time" class="form-control"
            value="<?= $edit_flight ? date('Y-m-d\TH:i', strtotime($edit_flight['arrival_time'])) : '' ?>"
            required>
    </div>
</div>

<script>
    const departureInput = document.getElementById('departure_time');
    const arrivalInput = document.getElementById('arrival_time');

    // Set initial minimums
    const now = new Date().toISOString().slice(0, 16);
    departureInput.min = now;

    // When departure time changes, update arrival time min
    departureInput.addEventListener('change', () => {
        arrivalInput.min = departureInput.value;
        if (arrivalInput.value < departureInput.value) {
            arrivalInput.value = departureInput.value; // auto-correct if invalid
        }
    });

    // Optional: also prevent form submission if arrival < departure
    document.querySelector('form').addEventListener('submit', function (e) {
        if (arrivalInput.value < departureInput.value) {
            e.preventDefault();
            alert('Arrival time must be after departure time.');
        }
    });
</script>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Economy Price ($)</label>
                            <input type="number" step="0.01" name="economy_price" class="form-control"
                                value="<?= $edit_flight ? $edit_flight['economy_price'] : '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Business Price ($)</label>
                            <input type="number" step="0.01" name="business_price" class="form-control"
                                value="<?= $edit_flight ? $edit_flight['business_price'] : '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>First Class Price ($)</label>
                            <input type="number" step="0.01" name="first_class_price" class="form-control"
                                value="<?= $edit_flight ? $edit_flight['first_class_price'] : '' ?>" required>
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
                                <td><?= htmlspecialchars($flight['departure_airport']) ?> to
                                    <?= htmlspecialchars($flight['arrival_airport']) ?></td>
                                <td><?= $departure->format('M j, Y H:i') ?></td>
                                <td><?= $arrival->format('M j, Y H:i') ?></td>
                                <td><?= $flight['available_seats'] ?>/<?= $flight['total_seats'] ?></td>
                                <td><?= ucfirst($flight['status']) ?></td>
                                <td>
                                    <a href="?edit=<?= $flight['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <button onclick="confirmDelete(<?= $flight['id'] ?>)"
                                        class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteFlightModal" tabindex="-1" aria-labelledby="deleteFlightModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteFlightModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this flight? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteFlightForm" method="post" action="<?= BASE_URL ?>/admin/delete_flight.php">
                        <input type="hidden" name="flight_id" id="deleteFlightId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
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

    function confirmDelete(flightId) {
        document.getElementById('deleteFlightId').value = flightId;
        const modal = new bootstrap.Modal(document.getElementById('deleteFlightModal'));
        modal.show();
    }
</script>

<?php include '../includes/footer.php'; ?>