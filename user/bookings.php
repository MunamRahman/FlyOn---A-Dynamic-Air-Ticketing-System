<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();
$pageTitle = 'My Bookings';

$db = getDB();
$stmt = $db->prepare("SELECT b.*, f.*, al.name as airline_name, dep.city as departure_city, arr.city as arrival_city FROM bookings b JOIN flights f ON b.flight_id = f.id JOIN airlines al ON f.airline_id = al.id JOIN airports dep ON f.departure_airport_id = dep.id JOIN airports arr ON f.arrival_airport_id = arr.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->execute([getCurrentUserId()]);
$bookings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Bookings</h1>
    
    <?php if (empty($bookings)): ?>
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">No Bookings Yet</h2>
        <p class="text-gray-600 mb-6">Start your journey by booking your first flight</p>
        <a href="../search.php" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-block">
            Search Flights
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($bookings as $booking): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($booking['departure_city']); ?> → <?php echo htmlspecialchars($booking['arrival_city']); ?></h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($booking['airline_name']); ?> • <?php echo htmlspecialchars($booking['flight_number']); ?></p>
                </div>
                <div class="text-right">
                    <?php echo getStatusBadge($booking['booking_status']); ?>
                    <?php echo getStatusBadge($booking['payment_status']); ?>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Booking Reference</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($booking['booking_reference']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Departure</p>
                    <p class="font-semibold"><?php echo formatDateTime($booking['departure_time']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Class</p>
                    <p class="font-semibold"><?php echo ucfirst($booking['travel_class']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Amount</p>
                    <p class="font-semibold text-primary"><?php echo formatPrice($booking['total_price']); ?></p>
                </div>
            </div>
            
            <div class="flex gap-2">
                <a href="booking_details.php?ref=<?php echo $booking['booking_reference']; ?>" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                    View Details
                </a>
                <?php if ($booking['booking_status'] === 'confirmed'): ?>
                <button onclick="rateBooking(<?php echo $booking['id']; ?>, '<?php echo $booking['booking_reference']; ?>')" 
                        class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition text-sm">
                    <i class="fas fa-star"></i> Rate Flight
                </button>
                <button onclick="cancelBooking(<?php echo $booking['id']; ?>, '<?php echo $booking['booking_reference']; ?>')" 
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm">
                    Cancel Booking
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function rateBooking(bookingId, bookingRef) {
    const rating = prompt('Rate your flight experience (1-5 stars)\n\nBooking: ' + bookingRef + '\n\nEnter rating (1 = Poor, 5 = Excellent):');
    if (rating && rating >= 1 && rating <= 5) {
        const review = prompt('Add a review (optional):');
        
        fetch('rate_flight.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=' + bookingId + '&rating=' + rating + '&review=' + encodeURIComponent(review || '')
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('⭐ ' + data.message + '\n\nYour rating: ' + '⭐'.repeat(data.rating) + '\n\nThank you for your feedback!');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error submitting rating: ' + error);
        });
    } else if (rating !== null) {
        alert('Please enter a valid rating between 1 and 5');
    }
}

function cancelBooking(bookingId, bookingRef) {
    const reason = prompt('Cancel booking ' + bookingRef + '?\n\nPlease enter cancellation reason (optional):');
    if (reason !== null) {
        fetch('cancel_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=' + bookingId + '&reason=' + encodeURIComponent(reason || 'Cancelled by customer')
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Booking cancelled successfully!\n\n' + 
                      'Booking Reference: ' + data.booking_ref + '\n' +
                      'Refund Amount: ৳' + data.refund_amount + '\n\n' +
                      'You will receive your refund within 7-10 business days.');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error cancelling booking: ' + error);
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
