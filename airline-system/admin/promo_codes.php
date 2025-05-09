<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Admin check
if (!is_admin()) {
    header("Location: /auth/login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_promo'])) {
        $code = sanitize($_POST['code']);
        $discount_value = floatval($_POST['discount_value']);
        $discount_type = sanitize($_POST['discount_type']);
        $valid_from = sanitize($_POST['valid_from']);
        $valid_until = sanitize($_POST['valid_until']);
        $max_uses = !empty($_POST['max_uses']) ? intval($_POST['max_uses']) : null;
        $min_booking = !empty($_POST['min_booking']) ? floatval($_POST['min_booking']) : 0;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO promo_codes 
                                  (code, discount_value, discount_type, valid_from, valid_until, max_uses, min_booking_amount) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $discount_value, $discount_type, $valid_from, $valid_until, $max_uses, $min_booking]);
            $success = "Promo code created successfully!";
        } catch (PDOException $e) {
            $error = "Error creating promo code: " . $e->getMessage();
        }
    }
}

// Get all promo codes
$stmt = $pdo->query("SELECT * FROM promo_codes ORDER BY valid_until DESC");
$promo_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manage Promo Codes";
include '../../includes/admin_header.php';
?>

<div class="admin-container">
    <?php include '../../includes/admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="card">
            <h2>Create New Promo Code</h2>
            
            <?php if (isset($success)): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="code">Promo Code</label>
                        <input type="text" id="code" name="code" required 
                               placeholder="e.g. SUMMER20" style="text-transform:uppercase">
                    </div>
                    <div class="form-group">
                        <label for="discount_type">Discount Type</label>
                        <select id="discount_type" name="discount_type" required>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="discount_value">Discount Value</label>
                        <input type="number" step="0.01" id="discount_value" name="discount_value" required>
                        <span id="value_suffix">%</span>
                    </div>
                    <div class="form-group">
                        <label for="min_booking">Minimum Booking Amount</label>
                        <input type="number" step="0.01" id="min_booking" name="min_booking" value="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="valid_from">Valid From</label>
                        <input type="date" id="valid_from" name="valid_from" required>
                    </div>
                    <div class="form-group">
                        <label for="valid_until">Valid Until</label>
                        <input type="date" id="valid_until" name="valid_until" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="max_uses">Maximum Uses (leave blank for unlimited)</label>
                    <input type="number" id="max_uses" name="max_uses" min="1">
                </div>
                
                <button type="submit" name="add_promo" class="btn-primary">Create Promo Code</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Existing Promo Codes</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Valid Period</th>
                        <th>Uses</th>
                        <th>Min. Booking</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promo_codes as $promo): ?>
                    <tr>
                        <td><?php echo $promo['code']; ?></td>
                        <td>
                            <?php echo $promo['discount_type'] === 'percentage' 
                                ? $promo['discount_value'] . '%' 
                                : '$' . $promo['discount_value']; ?>
                        </td>
                        <td>
                            <?php echo date('M j, Y', strtotime($promo['valid_from'])); ?> - 
                            <?php echo date('M j, Y', strtotime($promo['valid_until'])); ?>
                        </td>
                        <td>
                            <?php echo $promo['current_uses']; ?>
                            <?php echo $promo['max_uses'] ? '/'.$promo['max_uses'] : ''; ?>
                        </td>
                        <td>
                            <?php echo $promo['min_booking_amount'] > 0 
                                ? '$'.number_format($promo['min_booking_amount'], 2) 
                                : 'None'; ?>
                        </td>
                        <td>
                            <?php 
                            $today = date('Y-m-d');
                            $status = '';
                            if ($today < $promo['valid_from']) {
                                $status = 'Pending';
                            } elseif ($today > $promo['valid_until'] || 
                                     ($promo['max_uses'] && $promo['current_uses'] >= $promo['max_uses'])) {
                                $status = 'Expired';
                            } else {
                                $status = 'Active';
                            }
                            echo '<span class="status-badge '.strtolower($status).'">'.$status.'</span>';
                            ?>
                        </td>
                        <td>
                            <a href="edit_promo.php?id=<?php echo $promo['id']; ?>" class="btn-edit">Edit</a>
                            <a href="delete_promo.php?id=<?php echo $promo['id']; ?>" class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this promo code?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    // Update discount value suffix when type changes
    document.getElementById('discount_type').addEventListener('change', function() {
        const suffix = this.value === 'percentage' ? '%' : '$';
        document.getElementById('value_suffix').textContent = suffix;
    });
    
    // Set default dates
    document.getElementById('valid_from').valueAsDate = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 30);
    document.getElementById('valid_until').valueAsDate = tomorrow;
</script>

<?php include '../../includes/footer.php'; ?>