<?php
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_admin();

// Update flight status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $flight_id = intval($_POST['flight_id']);
    $status = sanitize($_POST['status']);
    $delay_reason = isset($_POST['delay_reason']) ? sanitize($_POST['delay_reason']) : null;

    try {
        $stmt = $pdo->prepare("UPDATE flights SET status = ? WHERE id = ?");
        $stmt->execute([$status, $flight_id]);

        // If delayed, log reason
        if ($status == 'delayed' && $delay_reason) {
            $stmt = $pdo->prepare("INSERT INTO flight_status_logs (flight_id, status, reason) VALUES (?, ?, ?)");
            $stmt->execute([$flight_id, $status, $delay_reason]);
        }

        $success = "Flight status updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating flight status: " . $e->getMessage();
    }
}

// Get all flights
$flights = $pdo->query("SELECT * FROM flights ORDER BY departure_time DESC")->fetchAll();

$page_title = "Flight Status Management";
include '../includes/header.php';
?>

<div class="container">
    <h1>Flight Status Management</h1>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Flight #</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Current Status</th>
                            <th>Update Status</th>
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
                                <td><?= $flight['departure_airport'] ?> to <?= $flight['arrival_airport'] ?></td>
                                <td><?= $departure->format('M j, Y H:i') ?></td>
                                <td><?= $arrival->format('M j, Y H:i') ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $flight['status'] == 'scheduled' ? 'badge-primary' : ($flight['status'] == 'delayed' ? 'badge-warning' : ($flight['status'] == 'cancelled' ? 'badge-danger' : 'badge-success')) ?>">
                                        <?= ucfirst($flight['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" class="form-inline">
                                        <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">
                                        <select name="status" class="form-control form-control-sm mr-2">
                                            <option value="scheduled" <?= $flight['status'] == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                            <option value="delayed" <?= $flight['status'] == 'delayed' ? 'selected' : '' ?>>Delayed</option>
                                            <option value="cancelled" <?= $flight['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            <option value="completed" <?= $flight['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                    <?php if ($flight['status'] == 'delayed'): ?>
                                        <small class="text-muted">Reason: <?= $delay_reason ?? 'Not specified' ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/flight_details.php?id=<?= $flight['id'] ?>"
                                        class="btn btn-sm btn-info">Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



</div>

<?php include '../includes/footer.php'; ?>