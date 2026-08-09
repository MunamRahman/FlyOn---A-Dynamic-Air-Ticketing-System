<?php
/**
 * Rate Flight - User Endpoint
 * Allows users to rate completed flights
 */

require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    $review = sanitize($_POST['review'] ?? '');
    $userId = getCurrentUserId();
    
    if ($bookingId && $rating >= 1 && $rating <= 5) {
        $db = getDB();
        
        try {
            // Verify booking belongs to user and is completed
            $stmt = $db->prepare("
                SELECT b.*, f.id as flight_id 
                FROM bookings b 
                JOIN flights f ON b.flight_id = f.id 
                WHERE b.id = ? AND b.user_id = ? AND b.booking_status = 'confirmed'
            ");
            $stmt->execute([$bookingId, $userId]);
            $booking = $stmt->fetch();
            
            if (!$booking) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking not found or cannot be rated'
                ]);
                exit;
            }
            
            // Check if already rated
            $stmt = $db->prepare("SELECT id FROM reviews WHERE booking_id = ? AND user_id = ?");
            $stmt->execute([$bookingId, $userId]);
            $existingReview = $stmt->fetch();
            
            if ($existingReview) {
                // Update existing review
                $stmt = $db->prepare("
                    UPDATE reviews 
                    SET rating = ?, review_text = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$rating, $review, $existingReview['id']]);
                $message = 'Rating updated successfully';
            } else {
                // Insert new review
                $stmt = $db->prepare("
                    INSERT INTO reviews (user_id, flight_id, booking_id, rating, review_text) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $booking['flight_id'], $bookingId, $rating, $review]);
                $message = 'Rating submitted successfully';
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'rating' => $rating
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
            'message' => 'Invalid rating data'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
