<?php
/**
 * Booking API
 * Handles booking operations (create, update, cancel)
 */

require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $db = getDB();
    $userId = getCurrentUserId();
    
    switch ($method) {
        case 'POST':
            if ($action === 'create') {
                createBooking($db, $userId);
            } elseif ($action === 'update') {
                updateBooking($db, $userId);
            } else {
                jsonResponse(['error' => 'Invalid action'], 400);
            }
            break;
            
        case 'DELETE':
            cancelBooking($db, $userId);
            break;
            
        case 'GET':
            if ($action === 'list') {
                listBookings($db, $userId);
            } elseif ($action === 'details') {
                getBookingDetails($db, $userId);
            } else {
                jsonResponse(['error' => 'Invalid action'], 400);
            }
            break;
            
        default:
            jsonResponse(['error' => 'Method not allowed'], 405);
    }
    
} catch (Exception $e) {
    if (APP_DEBUG) {
        jsonResponse(['error' => $e->getMessage()], 500);
    } else {
        jsonResponse(['error' => 'An error occurred'], 500);
    }
}

function createBooking($db, $userId) {
    $flightId = intval($_POST['flight_id'] ?? 0);
    $passengers = json_decode($_POST['passengers'] ?? '[]', true);
    $seats = json_decode($_POST['seats'] ?? '[]', true);
    $addons = json_decode($_POST['addons'] ?? '[]', true);
    $promoCode = sanitize($_POST['promo_code'] ?? '');
    
    if (!$flightId || empty($passengers)) {
        jsonResponse(['error' => 'Missing required fields'], 400);
    }
    
    // Verify flight exists and has available seats
    $stmt = $db->prepare("SELECT * FROM flights WHERE id = ? AND status = 'scheduled'");
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch();
    
    if (!$flight) {
        jsonResponse(['error' => 'Flight not found or not available'], 404);
    }
    
    // Calculate total price
    $totalPrice = calculateBookingTotal($db, $flight, $passengers, $seats, $addons, $promoCode);
    
    // Generate booking reference
    $bookingRef = generateBookingReference();
    
    // Create booking
    $stmt = $db->prepare("
        INSERT INTO bookings (user_id, flight_id, booking_reference, total_amount, discount_amount, booking_status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $discountAmount = $totalPrice['discount'] ?? 0;
    $stmt->execute([
        $userId,
        $flightId,
        $bookingRef,
        $totalPrice['total'],
        $discountAmount
    ]);
    
    $bookingId = $db->lastInsertId();
    
    jsonResponse([
        'success' => true,
        'booking_id' => $bookingId,
        'booking_reference' => $bookingRef,
        'total' => $totalPrice['total']
    ]);
}

function updateBooking($db, $userId) {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    
    if (!$bookingId) {
        jsonResponse(['error' => 'Booking ID required'], 400);
    }
    
    // Verify ownership
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        jsonResponse(['error' => 'Booking not found'], 404);
    }
    
    // Update logic here
    jsonResponse(['success' => true, 'message' => 'Booking updated']);
}

function cancelBooking($db, $userId) {
    $bookingId = intval($_GET['id'] ?? 0);
    
    if (!$bookingId) {
        jsonResponse(['error' => 'Booking ID required'], 400);
    }
    
    // Verify ownership and status
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        jsonResponse(['error' => 'Booking not found'], 404);
    }
    
    if ($booking['booking_status'] === 'cancelled') {
        jsonResponse(['error' => 'Booking already cancelled'], 400);
    }
    
    // Update booking status
    $stmt = $db->prepare("UPDATE bookings SET booking_status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
    $stmt->execute([$bookingId]);
    
    jsonResponse(['success' => true, 'message' => 'Booking cancelled']);
}

function listBookings($db, $userId) {
    $stmt = $db->prepare("
        SELECT b.*, f.flight_number, f.departure_time, f.arrival_time,
               dep.city as departure_city, arr.city as arrival_city
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        JOIN airports dep ON f.departure_airport_id = dep.id
        JOIN airports arr ON f.arrival_airport_id = arr.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$userId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse(['success' => true, 'bookings' => $bookings]);
}

function getBookingDetails($db, $userId) {
    $bookingId = intval($_GET['id'] ?? 0);
    
    if (!$bookingId) {
        jsonResponse(['error' => 'Booking ID required'], 400);
    }
    
    $stmt = $db->prepare("
        SELECT b.*, f.*, 
               al.name as airline_name,
               dep.name as departure_airport, dep.code as departure_code,
               arr.name as arrival_airport, arr.code as arrival_code
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        JOIN airlines al ON f.airline_id = al.id
        JOIN airports dep ON f.departure_airport_id = dep.id
        JOIN airports arr ON f.arrival_airport_id = arr.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        jsonResponse(['error' => 'Booking not found'], 404);
    }
    
    jsonResponse(['success' => true, 'booking' => $booking]);
}

function calculateBookingTotal($db, $flight, $passengers, $seats, $addons, $promoCode) {
    $basePrice = $flight['base_price_economy'] ?? 0;
    $passengerCount = count($passengers);
    $subtotal = $basePrice * $passengerCount;
    
    // Add seat selection fees
    $seatFees = 0;
    foreach ($seats as $seat) {
        $seatFees += $seat['fee'] ?? 0;
    }
    
    // Add addons
    $addonTotal = 0;
    foreach ($addons as $addon) {
        $addonTotal += $addon['price'] ?? 0;
    }
    
    $total = $subtotal + $seatFees + $addonTotal;
    
    // Apply promo code discount
    $discount = 0;
    if ($promoCode) {
        $stmt = $db->prepare("SELECT * FROM promotions WHERE code = ? AND status = 'active' AND valid_until >= NOW()");
        $stmt->execute([$promoCode]);
        $promo = $stmt->fetch();
        
        if ($promo) {
            if ($promo['discount_type'] === 'percentage') {
                $discount = $total * ($promo['discount_value'] / 100);
            } else {
                $discount = $promo['discount_value'];
            }
        }
    }
    
    $finalTotal = max(0, $total - $discount);
    
    return [
        'subtotal' => $subtotal,
        'seat_fees' => $seatFees,
        'addons' => $addonTotal,
        'discount' => $discount,
        'total' => $finalTotal
    ];
}

