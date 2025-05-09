<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Get user's loyalty points
$user = $pdo->prepare("SELECT loyalty_points FROM users WHERE id = ?")->execute([$user_id])->fetch();

// Get available rewards
$rewards = $pdo->query("SELECT * FROM loyalty_rewards WHERE is_active = TRUE ORDER BY points_required")->fetchAll();

// Calculate next reward
$next_reward = null;
foreach ($rewards as $reward) {
    if ($user['loyalty_points'] < $reward['points_required']) {
        $next_reward = $reward;
        break;
    }
}

$page_title = "Loyalty Program";
include '../includes/header.php';
?>

<div class="container">
    <h1>Loyalty Program</h1>
    
    <div class="card mb-4">
        <div class="card-body text-center">
            <h2>Your Points</h2>
            <div class="display-4 text-primary"><?= $user['loyalty_points'] ?></div>
            
            <?php if ($next_reward): ?>
                <div class="mt-3">
                    <p>Earn <?= $next_reward['points_required'] - $user['loyalty_points'] ?> more points to reach:</p>
                    <div class="card bg-light">
                        <div class="card-body">
                            <h4><?= $next_reward['name'] ?></h4>
                            <p><?= $next_reward['discount_percent'] ?>% discount on all flights</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="mt-3">You've reached the highest loyalty tier!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Available Rewards</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($rewards as $reward): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 <?= $user['loyalty_points'] >= $reward['points_required'] ? 'border-success' : '' ?>">
                            <div class="card-body text-center">
                                <h3><?= $reward['name'] ?></h3>
                                <div class="mb-3">
                                    <span class="badge badge-pill badge-primary"><?= $reward['points_required'] ?> points</span>
                                </div>
                                <p class="card-text"><?= $reward['discount_percent'] ?>% discount on all flights</p>
                                <?php if ($user['loyalty_points'] >= $reward['points_required']): ?>
                                    <span class="badge badge-success">Unlocked</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h2>How to Earn Points</h2>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item">10 points per $1 spent on flights</li>
                <li class="list-group-item">500 points for completing your first booking</li>
                <li class="list-group-item">200 points for referring a friend</li>
                <li class="list-group-item">100 points for writing a review</li>
            </ul>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>