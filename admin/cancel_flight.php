<?php
/**
 * Cancel Flight - Admin Endpoint
 * Allows admin to cancel flights
 */

require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flightId = $_POST['flight_id'] ?? 0;
    $reason = $_POST['reason'] ?? 'Flight cancelled by admin';
    
    if ($flightId) {
        $db = getDB();
        
        try {
            // Update flight status to cancelled
            $stmt = $db->prepare("UPDATE flights SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$flightId]);
            
            if ($stmt->rowCount() > 0) {
                // Get all bookings for this flight
                $stmt = $db->prepare("
                    SELECT b.*, u.email, u.first_name 
                    FROM bookings b 
                    JOIN users u ON b.user_id = u.id 
                    WHERE b.flight_id = ? AND b.booking_status != 'cancelled'
                ");
                $stmt->execute([$flightId]);
                $bookings = $stmt->fetchAll();
                
                // Cancel all bookings for this flight
                $stmt = $db->prepare("
                    UPDATE bookings 
                    SET booking_status = 'cancelled', 
                        payment_status = 'refunded' 
                    WHERE flight_id = ? AND booking_status != 'cancelled'
                ");
                $stmt->execute([$flightId]);
                
                // TODO: Send cancellation emails to all affected customers
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Flight cancelled successfully',
                    'affected_bookings' => count($bookings),
                    'reason' => $reason
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Flight not found'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid flight ID'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
