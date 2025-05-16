<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect('/user/dashboard.php');
}

$error = '';
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate username
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least three characters long.";
    } elseif (preg_match('/^[0-9]/', $username)) {
        $errors[] = "Username cannot start with a number.";
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $errors[] = "Username can only contain English letters and numbers.";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-zA-Z0-9]+@gmail\.com$/', $email)) {
        $errors[] = "Email must follow the format of a valid Gmail address.";
    }

    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least eight characters long.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must include at least one uppercase letter.";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must include at least one lowercase letter.";
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must include at least one special character.";
    }

    if (empty($errors)) {
        if (empty($username) || empty($email) || empty($password)) {
            $error = "All fields are required";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->rowCount() > 0) {
                $error = "Username or email already exists";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

                if ($stmt->execute([$username, $email, $hashed_password])) {
                    // send_email($email, "Welcome to " . SITE_NAME, "Thank you for registering!");
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

$page_title = "Register";
include '../includes/header.php';
?>

<div class="container">
    <h1>Register</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Password (min 8 characters)</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <p class="mt-3">Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Login here</a></p>
</div>

<?php include '../includes/footer.php'; ?>