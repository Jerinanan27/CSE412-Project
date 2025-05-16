<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

$location = $_GET['location'] ?? '';
$check_in = $_GET['check_in'] ?? date('Y-m-d');
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$guests = $_GET['guests'] ?? 1;

if (!empty($location)) {
    $stmt = $pdo->prepare("SELECT * FROM hotels 
                          WHERE location LIKE ? 
                          AND available_rooms >= ?
                          ORDER BY price_per_night");
    $stmt->execute(["%$location%", $guests]);
    $hotels = $stmt->fetchAll();
} else {
    $hotels = [];
}

$page_title = "Hotel Search";
include '../includes/header.php';
?>

<div class="container" style="min-height: 67vh;">
    <h1>Find Hotels</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($location) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Check-in</label>
                            <input type="date" name="check_in" class="form-control" value="<?= $check_in ?>" min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Check-out</label>
                            <input type="date" name="check_out" class="form-control" value="<?= $check_out ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Guests</label>
                            <select name="guests" class="form-control">
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= $guests == $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (!empty($hotels)): ?>
        <div class="row">
            <?php foreach ($hotels as $hotel): ?>
                <div class="col-md-3 mb-4">
                    <div class="card ">
                        <img src="https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="card-img-top" alt="Hotel Image">
                        <div class="card-body">
                            <h3><?= $hotel['name'] ?></h3>
                            <div class="mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= floor($hotel['rating'])): ?>
                                        <span class="fa fa-star checked"></span>
                                    <?php elseif ($i == ceil($hotel['rating']) && $hotel['rating'] > floor($hotel['rating'])): ?>
                                        <span class="fa fa-star-half-alt checked"></span>
                                    <?php else: ?>
                                        <span class="fa fa-star"></span>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="ml-1"><?= $hotel['rating'] ?></span>
                            </div>
                            <p><i class="fas fa-map-marker-alt"></i> <?= $hotel['location'] ?></p>
                            <p class="card-text"><?= $hotel['description'] ?></p>
                            <div class="mb-3">
                                <?php foreach (explode(',', $hotel['amenities']) as $amenity): ?>
                                    <span class="badge badge-secondary"><?= trim($amenity) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">$<?= number_format($hotel['price_per_night'], 2) ?></h4>
                                    <small>per night</small>
                                </div>
                                <a href="<?= BASE_URL ?>/hotels/book.php?hotel_id=<?= $hotel['id'] ?>&check_in=<?= $check_in ?>&check_out=<?= $check_out ?>&guests=<?= $guests ?>" 
                                   class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!empty($location)): ?>
        <div class="alert alert-info">No hotels found in <?= htmlspecialchars($location) ?></div>
    <?php endif; ?>
</div>

<style>
.checked {
    color: orange;
}
</style>

<?php include '../includes/footer.php'; ?>