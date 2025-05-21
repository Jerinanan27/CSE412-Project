<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_admin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $flight_id = intval($_POST['flight_id']);
    $status = sanitize($_POST['status']);
    $delay_reason = isset($_POST['delay_reason']) ? sanitize($_POST['delay_reason']) : null;
    $gate_number = isset($_POST['gate_number']) ? sanitize($_POST['gate_number']) : null;

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update flight status and gate number
        $stmt = $pdo->prepare("UPDATE flights SET status = ?, gate_number = ? WHERE id = ?");
        $stmt->execute([$status, $gate_number, $flight_id]);

        // Log status change
        $stmt = $pdo->prepare("INSERT INTO flight_status_logs 
                             (flight_id, status, reason, changed_by) 
                             VALUES (?, ?, ?, ?)");
        $stmt->execute([$flight_id, $status, $delay_reason, $_SESSION['user_id']]);

        $pdo->commit();
        $_SESSION['success'] = "Flight status updated successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error updating flight status: " . $e->getMessage();
    }
    header("Location: flight_status.php");
    exit;
}

// Fetch all flights with their current status
try {
    $stmt = $pdo->query("SELECT f.*, 
                        u.username as changed_by_username,
                        (SELECT status FROM flight_status_logs 
                         WHERE flight_id = f.id ORDER BY changed_at DESC LIMIT 1) as last_status,
                        (SELECT reason FROM flight_status_logs 
                         WHERE flight_id = f.id ORDER BY changed_at DESC LIMIT 1) as last_reason
                        FROM flights f
                        LEFT JOIN flight_status_logs fsl ON f.id = fsl.flight_id
                        LEFT JOIN users u ON fsl.changed_by = u.id
                        GROUP BY f.id
                        ORDER BY f.departure_time DESC");
    $flights = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching flights: " . $e->getMessage();
    $flights = [];
}

// Fetch status history for a specific flight if requested
$status_history = [];
if (isset($_GET['view_history']) && is_numeric($_GET['view_history'])) {
    $flight_id = intval($_GET['view_history']);
    try {
        $stmt = $pdo->prepare("SELECT fsl.*, u.username 
                              FROM flight_status_logs fsl
                              JOIN users u ON fsl.changed_by = u.id
                              WHERE flight_id = ? 
                              ORDER BY changed_at DESC");
        $stmt->execute([$flight_id]);
        $status_history = $stmt->fetchAll();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error fetching status history: " . $e->getMessage();
    }
}

$page_title = "Flight Status Management";
include '../includes/header.php';
?>

<div class="container">
    <h1>Flight Status Management</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Current Flight Statuses</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Flight #</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Gate</th>
                            <th>Current Status</th>
                            <th>Update Status</th>
                            <th>History</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $flight): ?>
                            <?php
                            $departure = new DateTime($flight['departure_time']);
                            $arrival = new DateTime($flight['arrival_time']);
                            $current_status = $flight['status'] ?: 'scheduled';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($flight['flight_number']) ?></td>
                                <td><?= htmlspecialchars($flight['departure_airport']) ?> to <?= htmlspecialchars($flight['arrival_airport']) ?></td>
                                <td><?= $departure->format('M j, Y H:i') ?></td>
                                <td><?= $arrival->format('M j, Y H:i') ?></td>
                                <td><?= $flight['gate_number'] ? htmlspecialchars($flight['gate_number']) : 'TBD' ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $current_status == 'scheduled' ? 'bg-primary' : 
                                           ($current_status == 'delayed' ? 'bg-warning text-dark' : 
                                           ($current_status == 'cancelled' ? 'bg-danger' : 'bg-success')) ?>">
                                        <?= ucfirst($current_status) ?>
                                    </span>
                                    <?php if ($current_status == 'delayed' && $flight['last_reason']): ?>
                                        <br><small class="text-muted">Reason: <?= htmlspecialchars($flight['last_reason']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">
                                        <div class="mb-2">
                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="scheduled" <?= $current_status == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                <option value="delayed" <?= $current_status == 'delayed' ? 'selected' : '' ?>>Delayed</option>
                                                <option value="cancelled" <?= $current_status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                <option value="departed" <?= $current_status == 'departed' ? 'selected' : '' ?>>Departed</option>
                                                <option value="arrived" <?= $current_status == 'arrived' ? 'selected' : '' ?>>Arrived</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="gate_number" class="form-control form-control-sm" 
                                                   value="<?= htmlspecialchars($flight['gate_number'] ?? '') ?>" 
                                                   placeholder="Gate number">
                                        </div>
                                        <div class="delay-reason" style="<?= $current_status != 'delayed' ? 'display:none;' : '' ?>">
                                            <input type="text" name="delay_reason" class="form-control form-control-sm mb-2" 
                                                   placeholder="Delay reason (if applicable)" 
                                                   value="<?= htmlspecialchars($flight['last_reason'] ?? '') ?>">
                                        </div>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <a href="?view_history=<?= $flight['id'] ?>" class="btn btn-sm btn-info">View History</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($status_history)): ?>
        <div class="card">
            <div class="card-header">
                <h2>Status History for Flight <?= htmlspecialchars($status_history[0]['flight_number'] ?? '') ?></h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Changed By</th>
                                <th>Changed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($status_history as $history): ?>
                                <tr>
                                    <td>
                                        <span class="badge 
                                            <?= $history['status'] == 'scheduled' ? 'bg-primary' : 
                                               ($history['status'] == 'delayed' ? 'bg-warning text-dark' : 
                                               ($history['status'] == 'cancelled' ? 'bg-danger' : 'bg-success')) ?>">
                                            <?= ucfirst($history['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= $history['reason'] ? htmlspecialchars($history['reason']) : 'N/A' ?></td>
                                    <td><?= htmlspecialchars($history['username']) ?></td>
                                    <td><?= date('M j, Y H:i', strtotime($history['changed_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="flight_status.php" class="btn btn-secondary mt-3">Back to Status Management</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Show/hide delay reason field based on status selection
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelects = document.querySelectorAll('select[name="status"]');
        
        statusSelects.forEach(select => {
            select.addEventListener('change', function() {
                const form = this.closest('form');
                const delayReasonField = form.querySelector('.delay-reason');
                if (this.value === 'delayed') {
                    delayReasonField.style.display = 'block';
                    form.querySelector('input[name="delay_reason"]').setAttribute('required', 'required');
                } else {
                    delayReasonField.style.display = 'none';
                    form.querySelector('input[name="delay_reason"]').removeAttribute('required');
                }
            });
            
            // Trigger change event on page load
            const event = new Event('change');
            select.dispatchEvent(event);
        });
    });
</script>

<?php include '../includes/footer.php'; ?>