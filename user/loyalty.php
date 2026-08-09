<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Loyalty Rewards';
$db = getDB();
$userId = getCurrentUserId();

// Get user's loyalty info
$stmt = $db->prepare("SELECT * FROM loyalty WHERE user_id = ?");
$stmt->execute([$userId]);
$loyalty = $stmt->fetch();

// Get user's booking history
$stmt = $db->prepare("
    SELECT b.*, f.flight_number, al.name as airline_name
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN airlines al ON f.airline_id = al.id
    WHERE b.user_id = ? AND b.payment_status = 'paid'
    ORDER BY b.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

$currentTier = $loyalty['tier'] ?? 'bronze';
$currentPoints = $loyalty['points'] ?? 0;
$lifetimePoints = $loyalty['lifetime_points'] ?? 0;
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">
        <i class="fas fa-star text-yellow-500"></i> Loyalty Rewards
    </h1>

    <!-- Current Status Card -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg shadow-lg p-8 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-blue-100 mb-2">Current Tier</p>
                <p class="text-3xl font-bold"><?php echo ucfirst($currentTier); ?></p>
                <span class="inline-block mt-2 px-3 py-1 bg-white bg-opacity-20 rounded-full text-sm">
                    <i class="fas fa-medal"></i> Member
                </span>
            </div>
            <div>
                <p class="text-blue-100 mb-2">Available Points</p>
                <p class="text-3xl font-bold"><?php echo number_format($currentPoints); ?></p>
                <p class="text-sm text-blue-100 mt-2">Earn 10 points per ৳100 spent</p>
            </div>
            <div>
                <p class="text-blue-100 mb-2">Lifetime Points</p>
                <p class="text-3xl font-bold"><?php echo number_format($lifetimePoints); ?></p>
                <p class="text-sm text-blue-100 mt-2">Total points earned</p>
            </div>
        </div>
    </div>

    <!-- Tier Benefits -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Bronze Tier -->
        <div class="bg-white rounded-lg shadow-md p-6 <?php echo $currentTier === 'bronze' ? 'ring-2 ring-orange-500' : ''; ?>">
            <div class="text-center mb-4">
                <i class="fas fa-medal text-5xl text-orange-600 mb-2"></i>
                <h3 class="text-xl font-bold text-gray-800">Bronze</h3>
                <p class="text-sm text-gray-600">0 - 999 points</p>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li><i class="fas fa-check text-green-500"></i> Earn 10 points per ৳100</li>
                <li><i class="fas fa-check text-green-500"></i> Priority email support</li>
                <li><i class="fas fa-check text-green-500"></i> Birthday bonus</li>
            </ul>
        </div>

        <!-- Silver Tier -->
        <div class="bg-white rounded-lg shadow-md p-6 <?php echo $currentTier === 'silver' ? 'ring-2 ring-gray-400' : ''; ?>">
            <div class="text-center mb-4">
                <i class="fas fa-medal text-5xl text-gray-400 mb-2"></i>
                <h3 class="text-xl font-bold text-gray-800">Silver</h3>
                <p class="text-sm text-gray-600">1,000 - 4,999 points</p>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li><i class="fas fa-check text-green-500"></i> Earn 15 points per ৳100</li>
                <li><i class="fas fa-check text-green-500"></i> Priority check-in</li>
                <li><i class="fas fa-check text-green-500"></i> 5% discount on bookings</li>
                <li><i class="fas fa-check text-green-500"></i> Free seat selection</li>
            </ul>
        </div>

        <!-- Gold Tier -->
        <div class="bg-white rounded-lg shadow-md p-6 <?php echo $currentTier === 'gold' ? 'ring-2 ring-yellow-500' : ''; ?>">
            <div class="text-center mb-4">
                <i class="fas fa-medal text-5xl text-yellow-500 mb-2"></i>
                <h3 class="text-xl font-bold text-gray-800">Gold</h3>
                <p class="text-sm text-gray-600">5,000+ points</p>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li><i class="fas fa-check text-green-500"></i> Earn 20 points per ৳100</li>
                <li><i class="fas fa-check text-green-500"></i> Priority boarding</li>
                <li><i class="fas fa-check text-green-500"></i> 10% discount on bookings</li>
                <li><i class="fas fa-check text-green-500"></i> Free lounge access</li>
                <li><i class="fas fa-check text-green-500"></i> Free baggage upgrade</li>
            </ul>
        </div>
    </div>

    <!-- Referral Program -->
    <?php if (!empty($loyalty['referral_code'])): ?>
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">
            <i class="fas fa-gift text-primary"></i> Refer & Earn
        </h2>
        <p class="text-gray-600 mb-4">Share your referral code and earn 500 bonus points for each friend who books!</p>
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <input type="text" value="<?php echo htmlspecialchars($loyalty['referral_code']); ?>" 
                       id="referralCode" readonly
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 font-mono text-lg">
            </div>
            <button onclick="copyReferralCode()" 
                    class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-copy"></i> Copy Code
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-history text-primary"></i> Recent Activity
        </h2>
        <?php if (!empty($bookings)): ?>
        <div class="space-y-4">
            <?php foreach ($bookings as $booking): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($booking['flight_number']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['airline_name']); ?></p>
                    <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-primary"><?php echo formatPrice($booking['total_price']); ?></p>
                    <p class="text-sm text-green-600">+<?php echo floor($booking['total_price'] / 10); ?> points</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8">
            <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No bookings yet. Start flying to earn points!</p>
            <a href="../search.php" class="inline-block mt-4 bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Search Flights
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyReferralCode() {
    const code = document.getElementById('referralCode');
    code.select();
    document.execCommand('copy');
    alert('Referral code copied: ' + code.value);
}
</script>

<?php include '../includes/footer.php'; ?>
