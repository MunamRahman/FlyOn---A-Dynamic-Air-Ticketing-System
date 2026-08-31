<?php
/**
 * FlyOn Configuration File
 * Loads environment variables and defines application constants
 */

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        // Check if line contains '='
        if (strpos($line, '=') === false) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        if (!empty($name) && !array_key_exists($name, $_ENV)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

// Helper function to get environment variables
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'flyon_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// Application Settings
define('APP_NAME', env('APP_NAME', 'FlyOn'));
define('APP_URL', env('APP_URL', 'http://localhost/FlyOn'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', env('APP_DEBUG', 'true') === 'true');

// Paths
define('ROOT_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('API_PATH', ROOT_PATH . '/api');

// URLs
define('ASSETS_URL', APP_URL . '/assets');
define('UPLOADS_URL', APP_URL . '/uploads');

// Security
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 120));
define('CSRF_TOKEN_NAME', env('CSRF_TOKEN_NAME', 'csrf_token'));

// Email Configuration
define('MAIL_HOST', env('MAIL_HOST', 'smtp.mailtrap.io'));
define('MAIL_PORT', env('MAIL_PORT', 2525));
define('MAIL_USERNAME', env('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', ''));
define('MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'noreply@flyon.com'));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'FlyOn'));

// SMS Configuration
define('SMS_API_KEY', env('SMS_API_KEY', ''));
define('SMS_SENDER_ID', env('SMS_SENDER_ID', 'FlyOn'));

// GoZayaan Integration
define('GOZAYAAN_API_KEY', env('GOZAYAAN_API_KEY', ''));
define('GOZAYAAN_API_URL', env('GOZAYAAN_API_URL', 'https://gozayaan.com/api/v1'));
define('GOZAYAAN_SYNC_ENABLED', env('GOZAYAAN_SYNC_ENABLED', 'false') === 'true');
define('GOZAYAAN_SYNC_INTERVAL', env('GOZAYAAN_SYNC_INTERVAL', 3600)); // seconds

// Payment Gateways
define('SSLCOMMERZ_STORE_ID', env('SSLCOMMERZ_STORE_ID', ''));
define('SSLCOMMERZ_STORE_PASSWORD', env('SSLCOMMERZ_STORE_PASSWORD', ''));
define('SSLCOMMERZ_MODE', env('SSLCOMMERZ_MODE', 'sandbox'));

define('STRIPE_PUBLIC_KEY', env('STRIPE_PUBLIC_KEY', ''));
define('STRIPE_SECRET_KEY', env('STRIPE_SECRET_KEY', ''));

define('PAYPAL_CLIENT_ID', env('PAYPAL_CLIENT_ID', ''));
define('PAYPAL_SECRET', env('PAYPAL_SECRET', ''));
define('PAYPAL_MODE', env('PAYPAL_MODE', 'sandbox'));

// File Upload
define('MAX_UPLOAD_SIZE', env('MAX_UPLOAD_SIZE', 5242880)); // 5MB
define('ALLOWED_IMAGE_TYPES', explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif')));

// Loyalty Program
define('POINTS_PER_DOLLAR', env('POINTS_PER_DOLLAR', 10));
define('SILVER_THRESHOLD', env('SILVER_THRESHOLD', 1000));
define('GOLD_THRESHOLD', env('GOLD_THRESHOLD', 5000));
define('PLATINUM_THRESHOLD', env('PLATINUM_THRESHOLD', 10000));

// Booking Settings
define('SEAT_LOCK_DURATION', env('SEAT_LOCK_DURATION', 600)); // 10 minutes
define('BOOKING_CANCELLATION_HOURS', env('BOOKING_CANCELLATION_HOURS', 24));

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
