<?php
require_once 'config.php';
require_once 'db_connect.php';

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

function redirect($url) {
    header("Location: ".BASE_URL.$url);
    exit();
}

function send_email($to, $subject, $message) {
    $headers = "From: ".MAIL_FROM_NAME." <".MAIL_FROM.">\r\n";
    $headers .= "Reply-To: ".MAIL_FROM."\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

function get_user_by_id($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function predict_best_booking_time($route, $days_ahead = 90) {
    global $pdo;
    
    // Get historical price data for this route
    $stmt = $pdo->prepare("
        SELECT 
            DAYOFWEEK(departure_time) as day_of_week,
            WEEK(departure_time) as week_of_year,
            AVG(economy_price) as avg_price
        FROM flights
        WHERE CONCAT(departure_airport, '-', arrival_airport) = ?
        AND departure_time >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        GROUP BY DAYOFWEEK(departure_time), WEEK(departure_time)
        ORDER BY avg_price ASC
        LIMIT 3
    ");
    $stmt->execute([$route]);
    $best_times = $stmt->fetchAll();
    
    // Generate recommendations
    $recommendations = [];
    foreach ($best_times as $time) {
        $day_name = date('l', strtotime("Sunday +{$time['day_of_week']} days"));
        $recommendations[] = "{$day_name}s (average $" . number_format($time['avg_price'], 2) . ")";
    }
    
    return [
        'route' => $route,
        'best_times' => $recommendations,
        'confidence' => count($best_times) > 2 ? 'high' : (count($best_times) > 0 ? 'medium' : 'low')
    ];
}

function submit_feedback($user_id, $booking_id, $rating, $comments) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO feedback 
                          (user_id, booking_id, rating, comments) 
                          VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $booking_id, $rating, $comments]);
}

function get_feedback_stats($flight_id = null) {
    global $pdo;
    
    $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total 
              FROM feedback";
    $params = [];
    
    if ($flight_id) {
        $query .= " WHERE booking_id IN (SELECT id FROM bookings WHERE flight_id = ?)";
        $params[] = $flight_id;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch();
}