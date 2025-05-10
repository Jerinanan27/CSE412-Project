<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

// Get flight details
if (!isset($_GET['flight_id']) || !isset($_GET['class'])) {
    redirect('/flights/search.php');
}

$flight_id = intval($_GET['flight_id']);
$class = sanitize($_GET['class']);

$stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$flight_id]);
$flight = $stmt->fetch();

if (!$flight) {
    redirect('/flights/search.php?error=invalid_flight');
}

// Calculate price
$price_column = $class.'_price';
$price = $flight[$price_column];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passengers = [];
    
    // Validate passenger data
    for ($i = 1; $i <= intval($_POST['passenger_count']); $i++) {
        $passengers[] = [
            'first_name' => sanitize($_POST["passenger_{$i}_first_name"]),
            'last_name' => sanitize($_POST["passenger_{$i}_last_name"]),
            'passport' => sanitize($_POST["passenger_{$i}_passport"]),
            'dob' => sanitize($_POST["passenger_{$i}_dob"]),
            'meal' => sanitize($_POST["passenger_{$i}_meal"] ?? 'standard'),
            'special' => sanitize($_POST["passenger_{$i}_special"] ?? '')
        ];
    }
    
    $total_price = $price * count($passengers);
    $booking_ref = generate_booking_reference();
    
    try {
        $pdo->beginTransaction();
        
        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings 
                              (user_id, flight_id, booking_reference, travel_class, passengers, total_price) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $flight_id,
            $booking_ref,
            $class,
            count($passengers),
            $total_price
        ]);
        $booking_id = $pdo->lastInsertId();
        
        // Add passengers
        foreach ($passengers as $passenger) {
            $stmt = $pdo->prepare("INSERT INTO passengers 
                                  (booking_id, first_name, last_name, passport_number, date_of_birth, meal_preference, special_assistance) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $booking_id,
                $passenger['first_name'],
                $passenger['last_name'],
                $passenger['passport'],
                $passenger['dob'],
                $passenger['meal'],
                $passenger['special']
            ]);
        }
        
        // Update available seats
        $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats - ? WHERE id = ?");
        $stmt->execute([count($passengers), $flight_id]);
        
        $pdo->commit();
        
        redirect("/booking/payment.php?booking_id=$booking_id");
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Booking failed: " . $e->getMessage();
    }
}

$page_title = "Create Booking";
include '../includes/header.php';
?>

<div class="container">
    <h1>Complete Your Booking</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Flight Details</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Flight Number:</strong> <?= $flight['flight_number'] ?></p>
                    <p><strong>Airline:</strong> <?= $flight['airline'] ?></p>
                    <p><strong>Route:</strong> <?= $flight['departure_airport'] ?> to <?= $flight['arrival_airport'] ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Departure:</strong> <?= date('M j, Y H:i', strtotime($flight['departure_time'])) ?></p>
                    <p><strong>Arrival:</strong> <?= date('M j, Y H:i', strtotime($flight['arrival_time'])) ?></p>
                    <p><strong>Class:</strong> <?= ucfirst($class) ?></p>
                    <p><strong>Price per passenger:</strong> $<?= number_format($price, 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <form method="post">
        <input type="hidden" name="passenger_count" value="1">
        
        <div class="card mb-4">
            <div class="card-header">
                <h2>Passenger Information</h2>
            </div>
            <div class="card-body" id="passengers-container">
                <div class="passenger-form mb-4">
                    <h4>Passenger #1</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="passenger_1_first_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="passenger_1_last_name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Passport Number</label>
                                <input type="text" name="passenger_1_passport" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="passenger_1_dob" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Meal Preference</label>
                                <select name="passenger_1_meal" class="form-control">
                                    <option value="standard">Standard</option>
                                    <option value="vegetarian">Vegetarian</option>
                                    <option value="vegan">Vegan</option>
                                    <option value="gluten_free">Gluten Free</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Special Assistance</label>
                                <input type="text" name="passenger_1_special" class="form-control" placeholder="Wheelchair, etc.">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" id="add-passenger" class="btn btn-secondary">Add Passenger</button>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2>Price Summary</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <table class="table">
                            <tr>
                                <td>Base Price (x<span id="passenger-count">1</span>):</td>
                                <td id="base-price">$<?= number_format($price, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Price:</strong></td>
                                <td id="total-price"><strong>$<?= number_format($price, 2) ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">Continue to Payment</button>
        </div>
    </form>
</div>

<script>
document.getElementById('add-passenger').addEventListener('click', function() {
    const count = parseInt(document.querySelector('input[name="passenger_count"]').value);
    const newCount = count + 1;
    
    if (newCount > 9) {
        alert('Maximum 9 passengers per booking');
        return;
    }
    
    document.querySelector('input[name="passenger_count"]').value = newCount;
    document.getElementById('passenger-count').textContent = newCount;
    
    const container = document.getElementById('passengers-container');
    const newPassenger = document.createElement('div');
    newPassenger.className = 'passenger-form mb-4';
    newPassenger.innerHTML = `
        <h4>Passenger #${newCount}</h4>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="passenger_${newCount}_first_name" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="passenger_${newCount}_last_name" class="form-control" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Passport Number</label>
                    <input type="text" name="passenger_${newCount}_passport" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="passenger_${newCount}_dob" class="form-control" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Meal Preference</label>
                    <select name="passenger_${newCount}_meal" class="form-control">
                        <option value="standard">Standard</option>
                        <option value="vegetarian">Vegetarian</option>
                        <option value="vegan">Vegan</option>
                        <option value="gluten_free">Gluten Free</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Special Assistance</label>
                    <input type="text" name="passenger_${newCount}_special" class="form-control" placeholder="Wheelchair, etc.">
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger remove-passenger">Remove</button>
    `;
    
    container.appendChild(newPassenger);
    
    // Update total price
    document.getElementById('total-price').innerHTML = '<strong>$' + (<?= $price ?> * newCount).toFixed(2) + '</strong>';
});

// Remove passenger
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-passenger')) {
        const passengerForm = e.target.closest('.passenger-form');
        passengerForm.remove();
        
        // Update passenger count and total price
        const forms = document.querySelectorAll('.passenger-form');
        document.querySelector('input[name="passenger_count"]').value = forms.length;
        document.getElementById('passenger-count').textContent = forms.length;
        document.getElementById('total-price').innerHTML = '<strong>$' + (<?= $price ?> * forms.length).toFixed(2) + '</strong>';
    }
});
</script>

<?php include '../includes/footer.php'; ?>