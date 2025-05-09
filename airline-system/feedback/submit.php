<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : null;

// Validate booking ownership
if ($booking_id) {
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    if (!$stmt->fetch()) $booking_id = null; // Invalid booking
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $comments = sanitize($_POST['comments']);

    if ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating (1-5 stars)";
    } else {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, booking_id, rating, comments) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $booking_id, $rating, $comments])) {
            $success = "Thank you for your feedback!";
        } else {
            $error = "Failed to submit feedback. Please try again.";
        }
    }
}

$page_title = "Submit Feedback";
include '../includes/header.php';
?>

<div class="container">
    <h1>Share Your Experience</h1>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <?php if ($booking_id): ?>
            <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
            <p>You're reviewing <strong>Booking #<?= $booking_id ?></strong></p>
        <?php endif; ?>

        <div class="form-group">
            <label>Rating</label><br>
            <div class="rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                    <label for="star<?= $i ?>">★</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Comments</label>
            <textarea name="comments" class="form-control" rows="5" placeholder="What did you like or improve?"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<style>
.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    font-size: 2rem;
}
.rating input { display: none; }
.rating label { color: #ddd; cursor: pointer; }
.rating input:checked ~ label,
.rating label:hover,
.rating label:hover ~ label { color: #ffc107; }
</style>

<?php include '../includes/footer.php'; ?>