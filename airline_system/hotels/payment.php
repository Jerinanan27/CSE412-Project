<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

$booking_id = intval($_GET['booking_id'] ?? 0);

if (!$booking_id) {
    header("Location: /hotels/search.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT hb.*, h.name as hotel_name, h.location 
                          FROM hotel_bookings hb 
                          JOIN hotels h ON hb.hotel_id = h.id 
                          WHERE hb.id = ? AND hb.user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    if ($booking['status'] !== 'pending') {
        // If booking is already processed, redirect to booking details
        header("Location: " . BASE_URL . "/hotels/booking_details.php?booking_id=$booking_id");
        exit;
    }

    $page_title = "Hotel Payment - " . $booking['hotel_name'];
    include '../includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-body">
            <h2>Payment Details</h2>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Hotel:</strong> <?= htmlspecialchars($booking['hotel_name']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($booking['location']) ?></p>
                    <p><strong>Check-in:</strong> <?= date('M j, Y', strtotime($booking['check_in'])) ?></p>
                    <p><strong>Check-out:</strong> <?= date('M j, Y', strtotime($booking['check_out'])) ?></p>
                    <p><strong>Rooms:</strong> <?= $booking['rooms'] ?></p>
                    <p><strong>Total Price:</strong> $<?= number_format($booking['total_price'], 2) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Guest Name:</strong> <?= htmlspecialchars($booking['guest_first_name'] . ' ' . $booking['guest_last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($booking['guest_email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($booking['guest_phone']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="confirm_booking.php" id="paymentForm">
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
        
        <div class="card mt-4">
            <div class="card-body">
                <h3>Payment Information</h3>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" class="form-control" id="payment_method" required>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>

                <div id="card-details" style="display: none;">
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" class="form-control" 
                               placeholder="1234 5678 9012 3456" 
                               pattern="\d{4} [\d\s]{15}" 
                               required
                               autocomplete="cc-number">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expiration Date</label>
                                <input type="text" name="exp_date" class="form-control" 
                                       placeholder="MM/YY" 
                                       pattern="(0[1-9]|1[0-2])\/\d{2}" 
                                       required
                                       autocomplete="cc-exp">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>CVV</label>
                                <input type="text" name="cvv" class="form-control" 
                                       placeholder="123" 
                                       pattern="\d{3}" 
                                       required
                                       autocomplete="cc-csc">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="paypal-details" style="display: none;">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="paypal_email" class="form-control" 
                               placeholder="your-email@paypal.com" 
                               autocomplete="username" 
                               required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="paypal_password" class="form-control" 
                               placeholder="Your PayPal password" 
                               autocomplete="current-password" 
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-4" id="submitBtn">Confirm Payment</button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethod = document.getElementById('payment_method');
            const cardDetails = document.getElementById('card-details');
            const paypalDetails = document.getElementById('paypal-details');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('paymentForm');

            paymentMethod.addEventListener('change', function() {
                if (this.value === 'paypal') {
                    cardDetails.style.display = 'none';
                    paypalDetails.style.display = 'block';
                } else {
                    cardDetails.style.display = 'block';
                    paypalDetails.style.display = 'none';
                }
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate form
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Get payment method
                const method = paymentMethod.value;
                
                // Validate required fields based on payment method
                if (method === 'paypal') {
                    if (!form.paypal_email.value || !form.paypal_password.value) {
                        alert('Please fill in all PayPal fields');
                        return;
                    }
                } else {
                    if (!form.card_number.value || !form.exp_date.value || !form.cvv.value) {
                        alert('Please fill in all card details');
                        return;
                    }
                }

                // Submit form
                form.submit();
            });
        });
    </script>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethod = document.getElementById('payment_method');
            const cardDetails = document.getElementById('card-details');
            const paypalDetails = document.getElementById('paypal-details');

            paymentMethod.addEventListener('change', function() {
                if (this.value === 'paypal') {
                    cardDetails.style.display = 'none';
                    paypalDetails.style.display = 'block';
                } else {
                    cardDetails.style.display = 'block';
                    paypalDetails.style.display = 'none';
                }
            });
        });
    </script>
</div>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelector('select[name="payment_method"]').addEventListener('change', function() {
    const cardDetails = document.getElementById('card-details');
    if (this.value === 'paypal') {
        cardDetails.style.display = 'none';
    } else {
        cardDetails.style.display = 'block';
    }
});
</script>

<?php
} catch (Exception $e) {
    $error = "Error loading booking details: " . $e->getMessage();
    include '../includes/header.php';
    ?>
    <div class="container">
        <div class="alert alert-danger"><?= $error ?></div>
        <a href="/hotels/search.php" class="btn btn-primary">Back to Search</a>
    </div>
    <?php
}

include '../includes/footer.php';
?>
