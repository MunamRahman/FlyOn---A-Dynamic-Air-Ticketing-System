<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$pageTitle = 'Admin Dashboard';
$db = getDB();

// Fetch statistics
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'total_bookings' => $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'total_flights' => $db->query("SELECT COUNT(*) FROM flights")->fetchColumn(),
    'total_revenue' => $db->query("SELECT SUM(total_price) FROM bookings WHERE payment_status = 'paid'")->fetchColumn()
];

// Recent bookings
$recentBookings = $db->query("SELECT b.*, u.first_name, u.last_name, f.flight_number FROM bookings b JOIN users u ON b.user_id = u.id JOIN flights f ON b.flight_id = f.id ORDER BY b.created_at DESC LIMIT 10")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Admin Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Users</p>
                    <p class="text-3xl font-bold text-primary"><?php echo number_format($stats['total_users']); ?></p>
                </div>
                <i class="fas fa-users text-4xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Bookings</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo number_format($stats['total_bookings']); ?></p>
                </div>
                <i class="fas fa-ticket-alt text-4xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Flights</p>
                    <p class="text-3xl font-bold text-accent"><?php echo number_format($stats['total_flights']); ?></p>
                </div>
                <i class="fas fa-plane text-4xl text-yellow-200"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Revenue</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo formatPrice($stats['total_revenue'] ?? 0); ?></p>
                </div>
                <i class="fas fa-dollar-sign text-4xl text-purple-200"></i>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8">
        <a href="flights.php" class="bg-primary text-white p-4 rounded-lg text-center hover:bg-blue-700 transition">
            <i class="fas fa-plane text-2xl mb-2"></i>
            <p class="font-semibold">Manage Flights</p>
        </a>
        <a href="bookings.php" class="bg-green-500 text-white p-4 rounded-lg text-center hover:bg-green-600 transition">
            <i class="fas fa-list text-2xl mb-2"></i>
            <p class="font-semibold">Manage Bookings</p>
        </a>
        <a href="users.php" class="bg-accent text-white p-4 rounded-lg text-center hover:bg-yellow-600 transition">
            <i class="fas fa-users text-2xl mb-2"></i>
            <p class="font-semibold">Manage Users</p>
        </a>
        <a href="promotions.php" class="bg-purple-500 text-white p-4 rounded-lg text-center hover:bg-purple-600 transition">
            <i class="fas fa-tags text-2xl mb-2"></i>
            <p class="font-semibold">Promotions</p>
        </a>
        <a href="sync_gozayaan.php" class="bg-indigo-500 text-white p-4 rounded-lg text-center hover:bg-indigo-600 transition">
            <i class="fas fa-sync-alt text-2xl mb-2"></i>
            <p class="font-semibold">Sync GoZayaan</p>
        </a>
        <a href="add_seats.php" class="bg-teal-500 text-white p-4 rounded-lg text-center hover:bg-teal-600 transition">
            <i class="fas fa-chair text-2xl mb-2"></i>
            <p class="font-semibold">Manage Seats</p>
        </a>
    </div>
    
    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Recent Bookings</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Booking Ref</th>
                        <th class="text-left py-3 px-4">Customer</th>
                        <th class="text-left py-3 px-4">Flight</th>
                        <th class="text-left py-3 px-4">Amount</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-left py-3 px-4">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $booking): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold"><?php echo htmlspecialchars($booking['booking_reference']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($booking['flight_number']); ?></td>
                        <td class="py-3 px-4"><?php echo formatPrice($booking['total_price']); ?></td>
                        <td class="py-3 px-4"><?php echo getStatusBadge($booking['booking_status']); ?></td>
                        <td class="py-3 px-4"><?php echo formatDate($booking['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
