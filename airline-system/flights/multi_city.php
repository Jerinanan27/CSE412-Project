<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

$segments = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_segment'])) {
    $from = sanitize($_POST['from']);
    $to = sanitize($_POST['to']);
    $date = sanitize($_POST['date']);
    $class = sanitize($_POST['class']);

    if (empty($from) || empty($to) || empty($date)) {
        $error = "Please fill all fields";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM flights 
                              WHERE departure_airport LIKE ? 
                              AND arrival_airport LIKE ?
                              AND DATE(departure_time) = ?
                              AND available_seats > 0
                              ORDER BY departure_time");
        $stmt->execute(["%$from%", "%$to%", $date]);
        $flights = $stmt->fetchAll();

        if (empty($flights)) {
            $error = "No flights found for $from to $to on $date";
        } else {
            $segments[] = [
                'from' => $from,
                'to' => $to,
                'date' => $date,
                'class' => $class,
                'flights' => $flights
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    // Process multi-city booking
    if (count($_POST['selected_flights']) < 2) {
        $error = "Please select at least 2 flight segments";
    } else {
        try {
            $pdo->beginTransaction();
            $booking_ref = generate_booking_reference();
            $total_price = 0;
            
            // Create main booking record
            $stmt = $pdo->prepare("INSERT INTO multi_city_bookings 
                                  (user_id, booking_reference, total_price) 
                                  VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $booking_ref, 0]);
            $booking_id = $pdo->lastInsertId();
            
            // Add each segment
            foreach ($_POST['selected_flights'] as $i => $flight_id) {
                $flight_id = intval($flight_id);
                $class = sanitize($_POST['classes'][$i]);
                
                // Get flight price
                $stmt = $pdo->prepare("SELECT {$class}_price FROM flights WHERE id = ?");
                $stmt->execute([$flight_id]);
                $price = $stmt->fetchColumn();
                $total_price += $price;
                
                // Add segment
                $stmt = $pdo->prepare("INSERT INTO multi_city_segments 
                                      (booking_id, flight_id, travel_class, segment_order) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([$booking_id, $flight_id, $class, $i+1]);
                
                // Update available seats
                $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats - 1 WHERE id = ?");
                $stmt->execute([$flight_id]);
            }
            
            // Update total price
            $stmt = $pdo->prepare("UPDATE multi_city_bookings SET total_price = ? WHERE id = ?");
            $stmt->execute([$total_price, $booking_id]);
            
            $pdo->commit();
            redirect("/booking/multi_city_confirm.php?booking_id=$booking_id");
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Booking failed: " . $e->getMessage();
        }
    }
}

$page_title = "Multi-City Booking";
include '../includes/header.php';
?>

<div class="container">
    <h1>Multi-City Flight Booking</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post" class="mb-4">
        <div class="card">
            <div class="card-header">
                <h2>Add Flight Segment</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>From</label>
                            <input type="text" name="from" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>To</label>
                            <input type="text" name="to" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Class</label>
                            <select name="class" class="form-control" required>
                                <option value="economy">Economy</option>
                                <option value="business">Business</option>
                                <option value="first">First Class</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="add_segment" class="btn btn-primary">Search Flights</button>
            </div>
        </div>
    </form>
    
    <?php if (!empty($segments)): ?>
        <form method="post">
            <?php foreach ($segments as $i => $segment): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Segment <?= $i+1 ?>: <?= $segment['from'] ?> to <?= $segment['to'] ?> (<?= date('M j, Y', strtotime($segment['date'])) ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>Flight #</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                        <th>Duration</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($segment['flights'] as $flight): ?>
                                        <?php
                                        $departure = new DateTime($flight['departure_time']);
                                        $arrival = new DateTime($flight['arrival_time']);
                                        $duration = $departure->diff($arrival);
                                        $price = $flight[$segment['class'].'_price'];
                                        ?>
                                        <tr>
                                            <td>
                                                <input type="radio" name="selected_flights[<?= $i ?>]" 
                                                       value="<?= $flight['id'] ?>" required>
                                                <input type="hidden" name="classes[<?= $i ?>]" value="<?= $segment['class'] ?>">
                                            </td>
                                            <td><?= $flight['flight_number'] ?></td>
                                            <td><?= $departure->format('M j, Y H:i') ?></td>
                                            <td><?= $arrival->format('M j, Y H:i') ?></td>
                                            <td><?= $duration->format('%hh %im') ?></td>
                                            <td>$<?= number_format($price, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="text-center">
                <button type="submit" name="confirm_booking" class="btn btn-primary btn-lg">Confirm Multi-City Booking</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>