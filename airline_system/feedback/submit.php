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

<style>
    .container {
        background-color: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(5px);
        border-radius: 10px;
        padding: 30px;
        margin-top: 30px;
        margin-bottom: 30px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .rating-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    /* Star rating styles - simplified and fixed */
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        font-size: 2rem;
    }
    
    .star-rating input {
        display: none;
    }
    
    .star-rating label {
        color: #ddd;
        cursor: pointer;
        padding: 0 5px;
        transition: color 0.2s;
    }
    
    /* When a radio is checked, all labels after it will be highlighted */
    .star-rating input:checked ~ label {
        color: #ffc107;
    }
    
    /* When hovering, highlight the label that's hovered and all labels after it */
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #ffc107;
    }
    
    .rating-value {
        margin-left: 15px;
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
    }
</style>

<div class="container">
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($booking_id): ?>
        <form method="POST">
            <div class="rating-container">
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                </div>
                <span id="rating-text" class="rating-value">Not rated yet</span>
            </div>
            
            <div class="form-group">
                <label for="comments">Comments</label>
                <textarea class="form-control" id="comments" name="comments" rows="4"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Submit Feedback</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">Invalid booking ID or you don't have permission to provide feedback for this booking.</div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating input');
    const ratingText = document.getElementById('rating-text');
    
    // Simple click event for each star
    stars.forEach(star => {
        star.addEventListener('click', function() {
            // Update the text display
            ratingText.textContent = `${this.value}/5`;
        });
    });
    
    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const ratingSelected = document.querySelector('.star-rating input:checked');
            if (!ratingSelected) {
                e.preventDefault();
                alert('Please select a rating before submitting.');
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>