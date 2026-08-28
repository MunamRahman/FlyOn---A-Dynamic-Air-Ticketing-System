<?php
/**
 * Payment API
 * Handles payment processing for bookings
 */

require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$bookingId = intval($_POST['booking_id'] ?? 0);
$paymentMethod = sanitize($_POST['payment_method'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);

if (!$bookingId || !$paymentMethod || !$amount) {
    jsonResponse(['error' => 'Missing required fields'], 400);
}

try {
    $db = getDB();
    $userId = getCurrentUserId();
    
    // Verify booking belongs to user
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        jsonResponse(['error' => 'Booking not found'], 404);
    }
    
    if ($booking['booking_status'] !== 'pending') {
        jsonResponse(['error' => 'Booking cannot be paid'], 400);
    }
    
    // Process payment based on method
    $paymentResult = processPayment($db, $booking, $paymentMethod, $amount);
    
    if ($paymentResult['success']) {
        // Update booking status
        $stmt = $db->prepare("
            UPDATE bookings 
            SET booking_status = 'confirmed', 
                payment_status = 'paid',
                payment_method = ?,
                paid_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$paymentMethod, $bookingId]);
        
        // Record payment transaction
        $stmt = $db->prepare("
            INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status, created_at)
            VALUES (?, ?, ?, ?, 'completed', NOW())
        ");
        $stmt->execute([
            $bookingId,
            $amount,
            $paymentMethod,
            $paymentResult['transaction_id'] ?? null
        ]);
        
        // Award loyalty points
        $points = calculateLoyaltyPoints($amount);
        $stmt = $db->prepare("
            INSERT INTO loyalty (user_id, points, description, created_at)
            VALUES (?, ?, 'Booking payment', NOW())
            ON DUPLICATE KEY UPDATE points = points + ?
        ");
        $stmt->execute([$userId, $points, $points]);
        
        // Send confirmation email
        $user = getCurrentUser();
        if ($user) {
            sendEmail(
                $user['email'],
                'Payment Confirmation - ' . $booking['booking_reference'],
                "Your payment of " . formatPrice($amount) . " has been confirmed. Booking Reference: " . $booking['booking_reference']
            );
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Payment processed successfully',
            'transaction_id' => $paymentResult['transaction_id'] ?? null
        ]);
    } else {
        jsonResponse(['error' => $paymentResult['message'] ?? 'Payment failed'], 400);
    }
    
} catch (Exception $e) {
    if (APP_DEBUG) {
        jsonResponse(['error' => $e->getMessage()], 500);
    } else {
        jsonResponse(['error' => 'Payment processing failed'], 500);
    }
}

function processPayment($db, $booking, $method, $amount) {
    switch ($method) {
        case 'sslcommerz':
            return processSSLCommerz($booking, $amount);
            
        case 'stripe':
            return processStripe($booking, $amount);
            
        case 'paypal':
            return processPayPal($booking, $amount);
            
        case 'cash':
            // For cash payments (e.g., at airport)
            return ['success' => true, 'transaction_id' => 'CASH-' . time()];
            
        default:
            return ['success' => false, 'message' => 'Invalid payment method'];
    }
}

function processSSLCommerz($booking, $amount) {
    // SSLCommerz integration placeholder
    // In production, integrate with SSLCommerz API
    if (empty(SSLCOMMERZ_STORE_ID) || empty(SSLCOMMERZ_STORE_PASSWORD)) {
        return ['success' => false, 'message' => 'SSLCommerz not configured'];
    }
    
    // Simulate payment processing
    $transactionId = 'SSL-' . uniqid();
    return ['success' => true, 'transaction_id' => $transactionId];
}

function processStripe($booking, $amount) {
    // Stripe integration placeholder
    if (empty(STRIPE_SECRET_KEY)) {
        return ['success' => false, 'message' => 'Stripe not configured'];
    }
    
    // Simulate payment processing
    $transactionId = 'STRIPE-' . uniqid();
    return ['success' => true, 'transaction_id' => $transactionId];
}

function processPayPal($booking, $amount) {
    // PayPal integration placeholder
    if (empty(PAYPAL_CLIENT_ID) || empty(PAYPAL_SECRET)) {
        return ['success' => false, 'message' => 'PayPal not configured'];
    }
    
    // Simulate payment processing
    $transactionId = 'PAYPAL-' . uniqid();
    return ['success' => true, 'transaction_id' => $transactionId];
}

