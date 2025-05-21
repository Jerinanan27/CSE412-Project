<?php
// Database Configuration
define('DB_HOST', 'sql110.infinityfree.com');
define('DB_NAME', 'if0_39005639_sky_high_v2');
define('DB_USER', 'if0_39005639');
define('DB_PASS', 'NVbtNNXMGe');

// Application Configuration
define('BASE_URL', 'https://skyhigh.kesug.com');
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
