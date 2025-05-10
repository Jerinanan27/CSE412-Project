<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$prediction = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = sanitize($_POST['from']);
    $to = sanitize($_POST['to']);
    
    if (empty($from) || empty($to)) {
        $error = "Please enter both departure and arrival airports";
    } else {
        $prediction = predict_best_booking_time("$from-$to");
    }
}

$page_title = "Price Prediction Tool";
include '../includes/header.php';
?>

<div class="container">
    <h1>Price Prediction Tool</h1>
    <p class="lead">Find the best time to book your flight for maximum savings</p>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>From Airport</label>
                            <input type="text" name="from" class="form-control" required 
                                   value="<?= isset($_POST['from']) ? htmlspecialchars($_POST['from']) : '' ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>To Airport</label>
                            <input type="text" name="to" class="form-control" required 
                                   value="<?= isset($_POST['to']) ? htmlspecialchars($_POST['to']) : '' ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Predict</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($prediction): ?>
        <div class="card">
            <div class="card-header">
                <h2>Prediction Results for <?= $prediction['route'] ?></h2>
            </div>
            <div class="card-body">
                <?php if (empty($prediction['best_times'])): ?>
                    <div class="alert alert-info">
                        Not enough data to make a prediction for this route yet.
                    </div>
                <?php else: ?>
                    <div class="alert alert-<?= $prediction['confidence'] == 'high' ? 'success' : 
                                           ($prediction['confidence'] == 'medium' ? 'warning' : 'info') ?>">
                        <h4>Best Times to Book (<?= $prediction['confidence'] ?> confidence)</h4>
                        <ul>
                            <?php foreach ($prediction['best_times'] as $time): ?>
                                <li><?= $time ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <h4>Tips for Best Prices</h4>
                        <ul>
                            <li>Book 6-8 weeks in advance for domestic flights</li>
                            <li>Book 3-5 months in advance for international flights</li>
                            <li>Tuesday afternoons often have price drops</li>
                            <li>Use our fare alerts to monitor price changes</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>