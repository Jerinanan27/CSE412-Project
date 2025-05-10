<?php
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_admin();

// Handle feedback actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $feedback_id = intval($_POST['feedback_id']);

    if (in_array($action, ['approve', 'reject', 'delete'])) {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
                $stmt->execute([$feedback_id]);
            } else {
                $status = $action === 'approve' ? 'approved' : 'rejected';
                $stmt = $pdo->prepare("UPDATE feedback SET status = ? WHERE id = ?");
                $stmt->execute([$status, $feedback_id]);
            }
            $success = "Action completed successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Fetch all feedback
$feedback = $pdo->query("
    SELECT f.*, u.username, b.booking_reference
    FROM feedback f
    JOIN users u ON f.user_id = u.id
    LEFT JOIN bookings b ON f.booking_id = b.id
    ORDER BY f.created_at DESC
")->fetchAll();

$page_title = "Feedback Management";
include '../../includes/admin_header.php';
?>

<div class="container">
    <h1>Customer Feedback</h1>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Booking</th>
                            <th>Rating</th>
                            <th>Comments</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedback as $item): ?>
                            <tr>
                                <td><?= $item['username'] ?></td>
                                <td><?= $item['booking_reference'] ?: 'N/A' ?></td>
                                <td>
                                    <?= str_repeat('★', $item['rating']) . str_repeat('☆', 5 - $item['rating']) ?>
                                </td>
                                <td><?= nl2br(htmlspecialchars($item['comments'])) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $item['status'] === 'approved' ? 'badge-success' : 
                                           ($item['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" class="form-inline">
                                        <input type="hidden" name="feedback_id" value="<?= $item['id'] ?>">
                                        <select name="action" class="form-control form-control-sm mr-2">
                                            <option value="">-- Action --</option>
                                            <option value="approve">Approve</option>
                                            <option value="reject">Reject</option>
                                            <option value="delete">Delete</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                                    </form>
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