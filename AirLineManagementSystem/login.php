<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "airline_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - SkyTrack</title>
  <link rel="stylesheet" href="auth.css" />
</head>
<body>
  <div class="form-container">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
      <p>Don't have an account? Click Here. <a href="register.php">Register</a></p>
    </form>
  </div>
</body>
</html>

