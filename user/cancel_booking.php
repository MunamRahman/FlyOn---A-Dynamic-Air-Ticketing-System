<?php
/**
 * Cancel Booking - User Endpoint
 * Allows users to cancel their bookings
 */

require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? 0;
    $reason = $_POST['reason'] ?? 'Cancelled by customer';
    $userId = getCurrentUserId();
    
    if ($bookingId) {
        $db = getDB();
        
        try {
            // Verify booking belongs to user
            $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$bookingId, $userId]);
            $booking = $stmt->fetch();
            
            if (!$booking) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking not found or access denied'
                ]);
                exit;
            }
            
            // Check if booking can be cancelled
            if ($booking['booking_status'] === 'cancelled') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking is already cancelled'
                ]);
                exit;
            }
            
            // Update booking status
            $stmt = $db->prepare("
                UPDATE bookings 
                SET booking_status = 'cancelled', 
                    payment_status = 'refunded' 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$bookingId, $userId]);
            
            // Release seats back to available
            $stmt = $db->prepare("
                UPDATE seats 
                SET status = 'available', 
                    locked_until = NULL, 
                    locked_by_session = NULL 
                WHERE flight_id = ? 
                AND seat_number IN (
                    SELECT seat_number FROM bookings WHERE id = ?
                )
            ");
            $stmt->execute([$booking['flight_id'], $bookingId]);
            
            // Update available seats count
            $class = $booking['travel_class'];
            $passengers = $booking['total_passengers'];
            $seatColumn = $class === 'business' ? 'available_seats_business' : 'available_seats_economy';
            
            $stmt = $db->prepare("
                UPDATE flights 
                SET $seatColumn = $seatColumn + ? 
                WHERE id = ?
            ");
            $stmt->execute([$passengers, $booking['flight_id']]);
            
            // TODO: Send cancellation confirmation email
            // TODO: Process refund
            
            echo json_encode([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'booking_ref' => $booking['booking_reference'],
                'refund_amount' => $booking['total_price'],
                'reason' => $reason
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid booking ID'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
