<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_admin();

// Handle form submission for both add and edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $location = sanitize($_POST['location']);
    $description = sanitize($_POST['description']);
    $amenities = sanitize($_POST['amenities']);
    $price_per_night = floatval($_POST['price_per_night']);
    $rating = floatval($_POST['rating']);
    $available_rooms = intval($_POST['available_rooms']);

    try {
        if (isset($_POST['add_hotel'])) {
            // Add new hotel
            $stmt = $pdo->prepare("INSERT INTO hotels 
                                  (name, location, description, amenities, price_per_night, rating, available_rooms) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name,
                $location,
                $description,
                $amenities,
                $price_per_night,
                $rating,
                $available_rooms
            ]);
            $_SESSION['success'] = "Hotel added successfully!";
            header("Location: hotels.php");
            exit;
        } elseif (isset($_POST['edit_hotel']) && isset($_POST['hotel_id'])) {
            // Update existing hotel
            $hotel_id = intval($_POST['hotel_id']);
            $stmt = $pdo->prepare("UPDATE hotels SET 
                                  name = ?, location = ?, description = ?, amenities = ?, 
                                  price_per_night = ?, rating = ?, available_rooms = ? 
                                  WHERE id = ?");
            $stmt->execute([
                $name,
                $location,
                $description,
                $amenities,
                $price_per_night,
                $rating,
                $available_rooms,
                $hotel_id
            ]);
            $_SESSION['success'] = "Hotel updated successfully!";
            header("Location: hotels.php");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle edit request
$edit_hotel = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $hotel_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$hotel_id]);
    $edit_hotel = $stmt->fetch();
}

// Fetch all hotels
$stmt = $pdo->query("SELECT * FROM hotels ORDER BY created_at DESC");
$hotels = $stmt->fetchAll();

$page_title = "Manage Hotels";

include '../includes/header.php';
?>

<div class="container">
    <h1>Hotel Management</h1>

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
            <h2><?= $edit_hotel ? 'Edit Hotel' : 'Add New Hotel' ?></h2>
        </div>
        <div class="card-body">
            <form method="post">
                <?php if ($edit_hotel): ?>
                    <input type="hidden" name="hotel_id" value="<?= $edit_hotel['id'] ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Hotel Name</label>
                            <input type="text" name="name" class="form-control"
                                value="<?= $edit_hotel ? htmlspecialchars($edit_hotel['name']) : '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control"
                                value="<?= $edit_hotel ? htmlspecialchars($edit_hotel['location']) : '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Available Rooms</label>
                            <input type="number" name="available_rooms" class="form-control" min="1"
                                value="<?= $edit_hotel ? $edit_hotel['available_rooms'] : '' ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4" required><?= $edit_hotel ? htmlspecialchars($edit_hotel['description']) : '' ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Amenities</label>
                            <textarea name="amenities" class="form-control" rows="4" required><?= $edit_hotel ? htmlspecialchars($edit_hotel['amenities']) : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Price Per Night ($)</label>
                            <input type="number" step="0.01" name="price_per_night" class="form-control"
                                value="<?= $edit_hotel ? $edit_hotel['price_per_night'] : '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Rating (1-5)</label>
                            <input type="number" step="0.1" min="1" max="5" name="rating" class="form-control"
                                value="<?= $edit_hotel ? $edit_hotel['rating'] : '' ?>" required>
                        </div>
                    </div>
                </div>

                <button type="submit" name="<?= $edit_hotel ? 'edit_hotel' : 'add_hotel' ?>"
                    class="btn btn-primary"><?= $edit_hotel ? 'Update Hotel' : 'Add Hotel' ?></button>
                <?php if ($edit_hotel): ?>
                    <button type="button" onclick="resetForm()" class="btn btn-secondary">Cancel Edit</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Existing Hotels</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Price/Night</th>
                            <th>Rating</th>
                            <th>Available Rooms</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hotels as $hotel): ?>
                            <tr>
                                <td><?= htmlspecialchars($hotel['name']) ?></td>
                                <td><?= htmlspecialchars($hotel['location']) ?></td>
                                <td>$<?= number_format($hotel['price_per_night'], 2) ?></td>
                                <td><?= $hotel['rating'] ?></td>
                                <td><?= $hotel['available_rooms'] ?></td>
                                <td>
                                    <a href="?edit=<?= $hotel['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <button onclick="confirmDelete(<?= $hotel['id'] ?>)"
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
    <div class="modal fade" id="deleteHotelModal" tabindex="-1" aria-labelledby="deleteHotelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteHotelModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this hotel? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteHotelForm" method="post" action="<?= BASE_URL ?>/admin/delete_hotel.php">
                        <input type="hidden" name="hotel_id" id="deleteHotelId">
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
        const submitButton = document.querySelector('button[name="edit_hotel"], button[name="add_hotel"]');
        
        console.log(submitButton);

        submitButton.name = 'add_hotel';
        submitButton.textContent = 'Add Hotel';
        document.querySelector('.card-header h2').textContent = 'Add New Hotel';
        window.location.href = 'hotels.php';
    }

    function confirmDelete(hotelId) {
        document.getElementById('deleteHotelId').value = hotelId;
        const modal = new bootstrap.Modal(document.getElementById('deleteHotelModal'));
        modal.show();
    }
</script>

<?php include '../includes/footer.php'; ?>