<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Booking Details';
$db = getDB();
$userId = getCurrentUserId();

// Get booking reference from URL
$bookingRef = $_GET['ref'] ?? '';

if (empty($bookingRef)) {
    redirect('/user/bookings.php');
}

// Fetch booking details
$stmt = $db->prepare("
    SELECT b.*, 
           f.flight_number, f.departure_time, f.arrival_time,
           al.name as airline_name, al.logo as airline_logo,
           dep.name as departure_airport, dep.code as departure_code, dep.city as departure_city,
           arr.name as arrival_airport, arr.code as arrival_code, arr.city as arrival_city
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN airlines al ON f.airline_id = al.id
    JOIN airports dep ON f.departure_airport_id = dep.id
    JOIN airports arr ON f.arrival_airport_id = arr.id
    WHERE b.booking_reference = ? AND b.user_id = ?
");
$stmt->execute([$bookingRef, $userId]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('/user/bookings.php');
}

// Get passengers
$stmt = $db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
$stmt->execute([$booking['id']]);
$passengers = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="bookings.php" class="text-primary hover:text-blue-700">
            <i class="fas fa-arrow-left"></i> Back to My Bookings
        </a>
    </div>

    <!-- Booking Status Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold mb-2">Booking Confirmation</h1>
                <p class="text-blue-100">Booking Reference: <span class="font-mono font-bold text-xl"><?php echo htmlspecialchars($booking['booking_reference']); ?></span></p>
            </div>
            <div class="text-right">
                <span class="px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm font-semibold">
                    <?php echo ucfirst($booking['booking_status']); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Flight Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-plane text-primary"></i> Flight Details
                </h2>
                
                <div class="flex items-center justify-between mb-6">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($booking['departure_code']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['departure_city']); ?></p>
                        <p class="text-lg font-semibold text-primary mt-2"><?php echo date('H:i', strtotime($booking['departure_time'])); ?></p>
                        <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($booking['departure_time'])); ?></p>
                    </div>
                    
                    <div class="flex-1 px-4">
                        <div class="border-t-2 border-dashed border-gray-300 relative">
                            <i class="fas fa-plane text-primary absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-2"></i>
                        </div>
                        <p class="text-center text-sm text-gray-600 mt-2">
                            <?php 
                                $duration = (strtotime($booking['arrival_time']) - strtotime($booking['departure_time'])) / 3600;
                                echo floor($duration) . 'h ' . (($duration - floor($duration)) * 60) . 'm';
                            ?>
                        </p>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($booking['arrival_code']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['arrival_city']); ?></p>
                        <p class="text-lg font-semibold text-primary mt-2"><?php echo date('H:i', strtotime($booking['arrival_time'])); ?></p>
                        <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($booking['arrival_time'])); ?></p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Airline</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($booking['airline_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Flight Number</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($booking['flight_number']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Class</p>
                            <p class="font-semibold"><?php echo ucfirst($booking['travel_class']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Passengers</p>
                            <p class="font-semibold"><?php echo $booking['total_passengers']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passenger Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-users text-primary"></i> Passenger Details
                </h2>
                
                <div class="space-y-4">
                    <?php foreach ($passengers as $index => $passenger): ?>
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Passenger <?php echo $index + 1; ?></h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Name</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($passenger['title'] . ' ' . $passenger['first_name'] . ' ' . $passenger['last_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Date of Birth</p>
                                <p class="font-semibold"><?php echo date('M d, Y', strtotime($passenger['date_of_birth'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Gender</p>
                                <p class="font-semibold"><?php echo ucfirst($passenger['gender']); ?></p>
                            </div>
                            <?php if (!empty($passenger['passport_number'])): ?>
                            <div>
                                <p class="text-gray-600">Passport</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($passenger['passport_number']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Price Summary -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Price Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base Price</span>
                        <span class="font-semibold"><?php echo formatPrice($booking['base_price']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax & Fees</span>
                        <span class="font-semibold"><?php echo formatPrice($booking['tax_amount']); ?></span>
                    </div>
                    <?php if ($booking['discount_amount'] > 0): ?>
                    <div class="flex justify-between text-green-600">
                        <span>Discount</span>
                        <span class="font-semibold">-<?php echo formatPrice($booking['discount_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="border-t pt-3 flex justify-between">
                        <span class="text-lg font-bold">Total</span>
                        <span class="text-2xl font-bold text-primary"><?php echo formatPrice($booking['total_price']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Status</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Status</span>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            <?php echo $booking['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                            <?php echo ucfirst($booking['payment_status']); ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Method</span>
                        <span class="font-semibold"><?php echo ucfirst($booking['payment_method'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Booked On</span>
                        <span class="font-semibold"><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Actions</h2>
                
                <div class="space-y-3">
                    <button onclick="window.print()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-print"></i> Print Ticket
                    </button>
                    <button onclick="downloadPDF()" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition">
                        <i class="fas fa-download"></i> Download PDF
                    </button>
                    <?php if ($booking['booking_status'] === 'confirmed'): ?>
                    <button onclick="rateThisFlight()" class="w-full bg-yellow-500 text-white py-2 rounded-lg hover:bg-yellow-600 transition">
                        <i class="fas fa-star"></i> Rate Flight
                    </button>
                    <button onclick="cancelBooking()" class="w-full bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-times"></i> Cancel Booking
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function downloadPDF() {
    alert('Download PDF functionality\n\nThis will generate a PDF ticket for booking: <?php echo $booking['booking_reference']; ?>');
    // TODO: Implement PDF generation
}

function rateThisFlight() {
    const rating = prompt('Rate your flight experience (1-5 stars)\n\nBooking: <?php echo $booking['booking_reference']; ?>\n\nEnter rating (1 = Poor, 5 = Excellent):');
    if (rating && rating >= 1 && rating <= 5) {
        const review = prompt('Add a review (optional):');
        
        fetch('rate_flight.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=<?php echo $booking['id']; ?>&rating=' + rating + '&review=' + encodeURIComponent(review || '')
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('⭐ ' + data.message + '\n\nYour rating: ' + '⭐'.repeat(parseInt(data.rating)) + '\n\nThank you for your feedback!');
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

function cancelBooking() {
    const reason = prompt('Cancel this booking?\n\nPlease enter cancellation reason (optional):');
    if (reason !== null) {
        fetch('cancel_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=<?php echo $booking['id']; ?>&reason=' + encodeURIComponent(reason || 'Cancelled by customer')
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Booking cancelled successfully!\n\n' + 
                      'Booking Reference: ' + data.booking_ref + '\n' +
                      'Refund Amount: ৳' + data.refund_amount + '\n\n' +
                      'You will receive your refund within 7-10 business days.');
                window.location.href = 'bookings.php';
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
