<?php
require_once 'config.php';
require_once 'includes/functions.php';

$pageTitle = 'Flight Details';

// Get flight ID and parameters
$flightId = $_GET['id'] ?? 0;
$class = $_GET['class'] ?? 'economy';
$passengers = $_GET['passengers'] ?? 1;

// Fetch flight details
$db = getDB();
$stmt = $db->prepare("
    SELECT f.*, 
    al.name as airline_name, al.code as airline_code, al.logo as airline_logo,
    dep.name as departure_airport, dep.code as departure_code, dep.city as departure_city, dep.country as departure_country,
    arr.name as arrival_airport, arr.code as arrival_code, arr.city as arrival_city, arr.country as arrival_country
    FROM flights f
    JOIN airlines al ON f.airline_id = al.id
    JOIN airports dep ON f.departure_airport_id = dep.id
    JOIN airports arr ON f.arrival_airport_id = arr.id
    WHERE f.id = ?
");
$stmt->execute([$flightId]);
$flight = $stmt->fetch();

if (!$flight) {
    redirect('/search.php');
}

// Calculate pricing
$basePrice = $class === 'business' ? $flight['base_price_business'] : $flight['base_price_economy'];
$finalPrice = calculateDynamicPrice($basePrice, $flight['id'], $flight['departure_time']);
$taxAmount = $finalPrice * 0.15; // 15% tax
$totalPrice = ($finalPrice + $taxAmount) * $passengers;
$duration = calculateDuration($flight['departure_time'], $flight['arrival_time']);
?>

<?php include 'includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <a href="index.php" class="text-primary hover:underline">Home</a>
        <span class="mx-2 text-gray-500">/</span>
        <a href="search.php" class="text-primary hover:underline">Search</a>
        <span class="mx-2 text-gray-500">/</span>
        <span class="text-gray-600">Flight Details</span>
    </nav>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Flight Overview -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <i class="fas fa-plane text-primary text-4xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($flight['airline_name']); ?></h1>
                            <p class="text-gray-600"><?php echo htmlspecialchars($flight['flight_number']); ?> • <?php echo ucfirst($class); ?> Class</p>
                        </div>
                    </div>
                    <?php echo getStatusBadge($flight['status']); ?>
                </div>
                
                <!-- Flight Route -->
                <div class="flex items-center justify-between py-6 border-t border-b">
                    <div class="text-center flex-1">
                        <p class="text-3xl font-bold text-gray-800"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></p>
                        <p class="text-lg font-semibold text-gray-700 mt-2"><?php echo htmlspecialchars($flight['departure_code']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($flight['departure_city']); ?>, <?php echo htmlspecialchars($flight['departure_country']); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo formatDate($flight['departure_time'], 'D, M d, Y'); ?></p>
                    </div>
                    
                    <div class="text-center px-8">
                        <p class="text-sm text-gray-600 mb-2"><?php echo $duration; ?></p>
                        <div class="flex items-center">
                            <div class="w-24 h-0.5 bg-gray-300"></div>
                            <i class="fas fa-plane text-primary mx-2"></i>
                            <div class="w-24 h-0.5 bg-gray-300"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Direct Flight</p>
                    </div>
                    
                    <div class="text-center flex-1">
                        <p class="text-3xl font-bold text-gray-800"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></p>
                        <p class="text-lg font-semibold text-gray-700 mt-2"><?php echo htmlspecialchars($flight['arrival_code']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($flight['arrival_city']); ?>, <?php echo htmlspecialchars($flight['arrival_country']); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo formatDate($flight['arrival_time'], 'D, M d, Y'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Baggage & Policies -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-suitcase text-primary"></i> Baggage & Policies
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Baggage Allowance</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Check-in: 20kg</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Cabin: 7kg</li>
                            <li><i class="fas fa-info-circle text-blue-500 mr-2"></i> Extra baggage available</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Cancellation Policy</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Free cancellation up to 24hrs</li>
                            <li><i class="fas fa-info-circle text-blue-500 mr-2"></i> 50% refund after 24hrs</li>
                            <li><i class="fas fa-times text-red-500 mr-2"></i> No refund 6hrs before departure</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Amenities -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-star text-primary"></i> Amenities
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-wifi text-primary text-2xl mb-2"></i>
                        <p class="text-sm text-gray-700">WiFi</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-utensils text-primary text-2xl mb-2"></i>
                        <p class="text-sm text-gray-700">Meals</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-tv text-primary text-2xl mb-2"></i>
                        <p class="text-sm text-gray-700">Entertainment</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-plug text-primary text-2xl mb-2"></i>
                        <p class="text-sm text-gray-700">Power Outlet</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar - Price Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Price Summary</h2>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-700">
                        <span>Base Fare (<?php echo $passengers; ?> × <?php echo formatPrice($finalPrice); ?>)</span>
                        <span class="font-semibold"><?php echo formatPrice($finalPrice * $passengers); ?></span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Taxes & Fees</span>
                        <span class="font-semibold"><?php echo formatPrice($taxAmount * $passengers); ?></span>
                    </div>
                    <div class="border-t pt-3 flex justify-between text-lg font-bold text-gray-800">
                        <span>Total Amount</span>
                        <span class="text-primary"><?php echo formatPrice($totalPrice); ?></span>
                    </div>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <a href="booking/step1_passenger.php?flight_id=<?php echo $flightId; ?>&class=<?php echo $class; ?>&passengers=<?php echo $passengers; ?>" 
                       class="block w-full bg-primary text-white text-center py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-ticket-alt"></i> Proceed to Book
                    </a>
                <?php else: ?>
                    <a href="user/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                       class="block w-full bg-primary text-white text-center py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-sign-in-alt"></i> Login to Book
                    </a>
                    <p class="text-xs text-gray-600 text-center mt-3">
                        Don't have an account? <a href="user/register.php" class="text-primary hover:underline">Sign up</a>
                    </p>
                <?php endif; ?>
                
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-semibold text-gray-700 mb-3">Why book with us?</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Best price guarantee</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Instant confirmation</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> 24/7 customer support</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Secure payment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
