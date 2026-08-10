<?php
/**
 * Approve Booking - AJAX Endpoint
 * Allows admin to approve pending bookings
 */

require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? 0;
    
    if ($bookingId) {
        $db = getDB();
        
        try {
            // Update booking status to confirmed
            $stmt = $db->prepare("
                UPDATE bookings 
                SET booking_status = 'confirmed',
                    payment_status = 'paid'
                WHERE id = ? AND booking_status = 'pending'
            ");
            $stmt->execute([$bookingId]);
            
            if ($stmt->rowCount() > 0) {
                // Get booking details for email
                $stmt = $db->prepare("
                    SELECT b.*, u.email, u.first_name 
                    FROM bookings b 
                    JOIN users u ON b.user_id = u.id 
                    WHERE b.id = ?
                ");
                $stmt->execute([$bookingId]);
                $booking = $stmt->fetch();
                
                // TODO: Send confirmation email to customer
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking approved successfully',
                    'booking_ref' => $booking['booking_reference']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking not found or already processed'
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
            'message' => 'Invalid booking ID'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
