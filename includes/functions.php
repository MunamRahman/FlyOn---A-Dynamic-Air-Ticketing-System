<?php
/**
 * Core Functions Library
 * Common utility functions used throughout the application
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db_connect.php';

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ============================================
// AUTHENTICATION FUNCTIONS
// ============================================

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/user/login.php');
    }
}

/**
 * Require admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('/index.php');
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

// ============================================
// REDIRECT & URL FUNCTIONS
// ============================================

/**
 * Redirect to URL
 */
function redirect($url, $statusCode = 302) {
    if (!headers_sent()) {
        header('Location: ' . APP_URL . $url, true, $statusCode);
        exit;
    }
    echo '<script>window.location.href="' . APP_URL . $url . '";</script>';
    exit;
}

/**
 * Get current URL
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// ============================================
// VALIDATION FUNCTIONS
// ============================================

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone
 */
function isValidPhone($phone) {
    return preg_match('/^[+]?[0-9]{10,15}$/', $phone);
}

/**
 * Validate date
 */
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// ============================================
// FORMATTING FUNCTIONS
// ============================================

/**
 * Format price
 */
function formatPrice($amount, $currency = 'BDT') {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'BDT' => '৳',
        'INR' => '₹'
    ];
    $symbol = $symbols[$currency] ?? $currency . ' ';
    return $symbol . ' ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'M d, Y h:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return formatDate($datetime);
}

// ============================================
// BOOKING FUNCTIONS
// ============================================

/**
 * Generate unique booking reference
 */
function generateBookingReference() {
    return 'FO' . strtoupper(substr(uniqid(), -8));
}

/**
 * Calculate flight duration
 */
function calculateDuration($departure, $arrival) {
    $diff = strtotime($arrival) - strtotime($departure);
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    return sprintf('%dh %dm', $hours, $minutes);
}

/**
 * Get flight status badge
 */
function getStatusBadge($status) {
    $badges = [
        'scheduled' => '<span class="badge bg-primary">Scheduled</span>',
        'delayed' => '<span class="badge bg-warning">Delayed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'confirmed' => '<span class="badge bg-success">Confirmed</span>',
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'paid' => '<span class="badge bg-success">Paid</span>',
        'refunded' => '<span class="badge bg-info">Refunded</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

// ============================================
// DYNAMIC PRICING FUNCTIONS
// ============================================

/**
 * Calculate dynamic price
 */
function calculateDynamicPrice($basePrice, $flightId, $departureTime) {
    $db = getDB();
    $finalPrice = $basePrice;
    
    // Get active pricing rules
    $stmt = $db->query("SELECT * FROM pricing_rules WHERE status = 'active' ORDER BY id ASC");
    $rules = $stmt->fetchAll();
    
    foreach ($rules as $rule) {
        $adjustment = 0;
        
        switch ($rule['rule_type']) {
            case 'time_based':
                $daysUntil = (strtotime($departureTime) - time()) / 86400;
                if ($daysUntil < 3) {
                    $adjustment = ($rule['adjustment_type'] === 'percentage') 
                        ? $basePrice * ($rule['adjustment_value'] / 100) 
                        : $rule['adjustment_value'];
                }
                break;
                
            case 'seat_based':
                $stmt = $db->prepare("SELECT available_seats_economy FROM flights WHERE id = ?");
                $stmt->execute([$flightId]);
                $seats = $stmt->fetchColumn();
                if ($seats < 10) {
                    $adjustment = ($rule['adjustment_type'] === 'percentage') 
                        ? $basePrice * ($rule['adjustment_value'] / 100) 
                        : $rule['adjustment_value'];
                }
                break;
                
            case 'demand_based':
                $stmt = $db->prepare("SELECT search_count FROM flights WHERE id = ?");
                $stmt->execute([$flightId]);
                $searchCount = $stmt->fetchColumn();
                if ($searchCount > 50) {
                    $adjustment = ($rule['adjustment_type'] === 'percentage') 
                        ? $basePrice * ($rule['adjustment_value'] / 100) 
                        : $rule['adjustment_value'];
                }
                break;
        }
        
        $finalPrice += $adjustment;
    }
    
    return max($finalPrice, $basePrice * 0.5); // Never go below 50% of base price
}

// ============================================
// NOTIFICATION FUNCTIONS
// ============================================

/**
 * Send email notification
 */
function sendEmail($to, $subject, $message, $isHTML = true) {
    // This is a placeholder - integrate with PHPMailer or similar
    $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_ADDRESS . ">\r\n";
    if ($isHTML) {
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    }
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Send SMS notification
 */
function sendSMS($phone, $message) {
    // Placeholder for SMS gateway integration
    return true;
}

/**
 * Log notification
 */
function logNotification($userId, $type, $subject, $message) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, type, subject, message) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$userId, $type, $subject, $message]);
}

// ============================================
// FILE UPLOAD FUNCTIONS
// ============================================

/**
 * Upload file
 */
function uploadFile($file, $directory = 'uploads') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return false;
    }
    
    $filename = uniqid() . '.' . $extension;
    $destination = ROOT_PATH . '/' . $directory . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    
    return false;
}

// ============================================
// LOYALTY FUNCTIONS
// ============================================

/**
 * Calculate loyalty points
 */
function calculateLoyaltyPoints($amount) {
    return floor($amount * POINTS_PER_DOLLAR);
}

/**
 * Get loyalty tier
 */
function getLoyaltyTier($points) {
    if ($points >= PLATINUM_THRESHOLD) return 'platinum';
    if ($points >= GOLD_THRESHOLD) return 'gold';
    if ($points >= SILVER_THRESHOLD) return 'silver';
    return 'bronze';
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get client IP
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Log activity
 */
function logActivity($action, $entityType = null, $entityId = null, $details = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        getCurrentUserId(),
        $action,
        $entityType,
        $entityId,
        getClientIP(),
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $details
    ]);
}
