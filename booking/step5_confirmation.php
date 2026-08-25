<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();
$bookingRef = $_GET['booking'] ?? '';

if (empty($bookingRef)) redirect('/user/dashboard.php');

$db = getDB();
$stmt = $db->prepare("SELECT b.*, f.*, al.name as airline_name, al.code as airline_code, dep.name as departure_airport, dep.code as departure_code, dep.city as departure_city, arr.name as arrival_airport, arr.code as arrival_code, arr.city as arrival_city FROM bookings b JOIN flights f ON b.flight_id = f.id JOIN airlines al ON f.airline_id = al.id JOIN airports dep ON f.departure_airport_id = dep.id JOIN airports arr ON f.arrival_airport_id = arr.id WHERE b.booking_reference = ? AND b.user_id = ?");
$stmt->execute([$bookingRef, getCurrentUserId()]);
$booking = $stmt->fetch();

if (!$booking) redirect('/user/dashboard.php');

$isConfirmed = $booking['booking_status'] === 'confirmed';
$pageTitle = $isConfirmed ? 'Booking Confirmed' : 'Request to Confirm';

$stmt = $db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
$stmt->execute([$booking['id']]);
$passengers = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="<?php echo $isConfirmed ? 'bg-green-50 border-green-500' : 'bg-yellow-50 border-yellow-500'; ?> border-2 rounded-lg p-8 mb-8 text-center">
        <div class="<?php echo $isConfirmed ? 'bg-green-500' : 'bg-yellow-500'; ?> text-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas <?php echo $isConfirmed ? 'fa-check' : 'fa-clock'; ?> text-4xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <?php echo $isConfirmed ? 'Booking Confirmed!' : 'Request to Confirm'; ?>
        </h1>
        <p class="text-gray-600 mb-4">
            <?php if ($isConfirmed): ?>
                Your flight has been successfully booked
            <?php else: ?>
                Your confirmation request has been received. Our team will review and confirm your ticket shortly.
            <?php endif; ?>
        </p>
        <p class="text-2xl font-bold text-primary mb-4">Booking Reference: <?php echo htmlspecialchars($bookingRef); ?></p>
        <div class="text-lg">
            Current Status: <?php echo getStatusBadge($booking['booking_status']); ?>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Flight Information</h2>
        <div class="grid grid-cols-2 gap-4">
            <div><p class="text-sm text-gray-600">Airline</p><p class="font-semibold"><?php echo htmlspecialchars($booking['airline_name']); ?></p></div>
            <div><p class="text-sm text-gray-600">Flight</p><p class="font-semibold"><?php echo htmlspecialchars($booking['flight_number']); ?></p></div>
        </div>
    </div>
    
    <div class="text-center space-x-4">
        <a href="../user/bookings.php" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-block">View My Bookings</a>
        <a href="../index.php" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition inline-block">Book Another Flight</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
