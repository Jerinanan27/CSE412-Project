<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Validate
    if (empty($username) || empty($email)) {
        $error = "Username and email are required!";
    } else {
        // Check if email/username exists (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $user_id]);
        if ($stmt->fetch()) {
            $error = "Username or email already taken!";
        } else {
            // Update profile
            $update_data = [$username, $email, $user_id];
            $update_sql = "UPDATE users SET username = ?, email = ?";

            // Password change (if requested)
            if (!empty($new_password)) {
                if (!password_verify($current_password, $user['password'])) {
                    $error = "Current password is incorrect!";
                } else {
                    $update_sql .= ", password = ?";
                    $update_data[] = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }

            if (!isset($error)) {
                $update_sql .= " WHERE id = ?";
                $stmt = $pdo->prepare($update_sql);
                if ($stmt->execute($update_data)) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $success = "Profile updated successfully!";
                } else {
                    $error = "Failed to update profile.";
                }
            }
        }
    }
}

$page_title = "My Profile";
include '../includes/header.php';
?>

<div class="container">
    <h1>My Profile</h1>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?= $user['username'] ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
        </div>
        <div class="form-group">
            <label>Current Password (for verification)</label>
            <input type="password" name="current_password" class="form-control">
        </div>
        <div class="form-group">
            <label>New Password (leave blank to keep unchanged)</label>
            <input type="password" name="new_password" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>