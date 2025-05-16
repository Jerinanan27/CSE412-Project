<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flight_id'])) {
    try {
        $flight_id = intval($_POST['flight_id']);

        // Check if flight exists
        $stmt = $pdo->prepare("SELECT id FROM flights WHERE id = ?");
        $stmt->execute([$flight_id]);

        if ($stmt->rowCount() === 0) {
            $_SESSION['error'] = "Flight not found!";
            header("Location: flights.php");
            exit;
        }

        // Delete flight
        $stmt = $pdo->prepare("DELETE FROM flights WHERE id = ?");
        $stmt->execute([$flight_id]);

        $_SESSION['success'] = "Flight deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting flight: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: flights.php");
exit;
