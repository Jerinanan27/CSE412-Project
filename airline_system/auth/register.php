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

<style>
    body {
        background-image: url('https://images.unsplash.com/photo-1553570739-330b8db8a925?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NjJ8fG9jZWFufGVufDB8fDB8fHww');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        position: relative;
    }
    
    body::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.5); /* This creates the opacity effect */
        z-index: -1;
    }
    
    .card {
        background-color: rgba(255, 255, 255, 0.85); /* Slightly transparent card */
        backdrop-filter: blur(5px); /* Optional: adds a blur effect to the background behind the card */
    }
</style>

<div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card shadow p-4" style="min-width: 350px; max-width: 400px; width: 100%;">
        <h2 class="text-center mb-4">Register</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= $success ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="password" class="form-label">Password (min 8 characters)</label>
                <input type="password" name="password" id="password" class="form-control" minlength="8" required>
            </div>

            <div class="form-group mb-4">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="mt-3 text-center">Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Login here</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>