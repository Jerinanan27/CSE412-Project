<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php'; // Add this line to include auth.php
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect('/user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            $redirect_url = $_SESSION['redirect_url'] ?? '/user/dashboard.php';
            unset($_SESSION['redirect_url']);
            redirect($redirect_url);
        } else {
            $error = "Invalid username or password";
        }
    }
}

$page_title = "Login";
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
    <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
        <h2 class="text-center mb-4">Please Sign in</h2>
        <p class=" text-secondary text-center  ">You need to sign in first to continue</p>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success text-center">Registration successful! Please login.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group mb-3">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>

            <div class="form-group mb-4">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Sign in</button>
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-link">Register</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>