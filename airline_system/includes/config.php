<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'airline_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('BASE_URL', 'http://localhost/airline_system');
define('SITE_NAME', 'SkyHigh Airlines');

// Session Configuration
define('SESSION_EXPIRE', 3600); // 1 hour

// Email Configuration
define('MAIL_FROM', 'no-reply@skyhigh.com');
define('MAIL_FROM_NAME', 'SkyHigh Airlines');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Start session

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}