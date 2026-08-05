<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Dashboard';
$user = getCurrentUser();
$db = getDB();

// Fetch user statistics
$stmt = $db->prepare("SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?");
$stmt->execute([getCurrentUserId()]);
$stats = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM loyalty WHERE user_id = ?");
$stmt->execute([getCurrentUserId()]);
$loyalty = $stmt->fetch();

// Fetch recent bookings
$stmt = $db->prepare("SELECT b.*, f.flight_number, al.name as airline_name, dep.city as departure_city, arr.city as arrival_city FROM bookings b JOIN flights f ON b.flight_id = f.id JOIN airlines al ON f.airline_id = al.id JOIN airports dep ON f.departure_airport_id = dep.id JOIN airports arr ON f.arrival_airport_id = arr.id WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 5");
$stmt->execute([getCurrentUserId()]);
$recentBookings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
        <p class="text-gray-600">Manage your bookings and profile</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Bookings</p>
                    <p class="text-3xl font-bold text-primary"><?php echo $stats['total_bookings']; ?></p>
                </div>
                <i class="fas fa-ticket-alt text-4xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Loyalty Points</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $loyalty['available_points'] ?? 0; ?></p>
                </div>
                <i class="fas fa-star text-4xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Membership Tier</p>
                    <p class="text-2xl font-bold text-accent"><?php echo ucfirst($loyalty['tier'] ?? 'Bronze'); ?></p>
                </div>
                <i class="fas fa-crown text-4xl text-yellow-200"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Member Since</p>
                    <p class="text-lg font-bold text-gray-700"><?php echo formatDate($user['created_at'], 'M Y'); ?></p>
                </div>
                <i class="fas fa-calendar text-4xl text-gray-200"></i>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <a href="../search.php" class="bg-primary text-white p-4 rounded-lg text-center hover:bg-blue-700 transition">
            <i class="fas fa-search text-2xl mb-2"></i>
            <p class="font-semibold">Search Flights</p>
        </a>
        <a href="bookings.php" class="bg-green-500 text-white p-4 rounded-lg text-center hover:bg-green-600 transition">
            <i class="fas fa-list text-2xl mb-2"></i>
            <p class="font-semibold">My Bookings</p>
        </a>
        <a href="profile.php" class="bg-accent text-white p-4 rounded-lg text-center hover:bg-yellow-600 transition">
            <i class="fas fa-user text-2xl mb-2"></i>
            <p class="font-semibold">My Profile</p>
        </a>
        <a href="loyalty.php" class="bg-purple-500 text-white p-4 rounded-lg text-center hover:bg-purple-600 transition">
            <i class="fas fa-gift text-2xl mb-2"></i>
            <p class="font-semibold">Rewards</p>
        </a>
    </div>
    
    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Recent Bookings</h2>
        
        <?php if (empty($recentBookings)): ?>
        <div class="text-center py-12">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-600 mb-4">No bookings yet</p>
            <a href="../search.php" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-block">
                Book Your First Flight
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($recentBookings as $booking): ?>
            <div class="border rounded-lg p-4 hover:shadow-md transition">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($booking['departure_city']); ?> → <?php echo htmlspecialchars($booking['arrival_city']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['airline_name']); ?> • <?php echo htmlspecialchars($booking['flight_number']); ?></p>
                        <p class="text-xs text-gray-500">Booking Ref: <?php echo htmlspecialchars($booking['booking_reference']); ?></p>
                    </div>
                    <div class="text-right">
                        <?php echo getStatusBadge($booking['booking_status']); ?>
                        <p class="text-sm text-gray-600 mt-2"><?php echo formatPrice($booking['total_price']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-6 text-center">
            <a href="bookings.php" class="text-primary hover:underline font-semibold">View All Bookings →</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
