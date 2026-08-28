<?php
require_once 'config.php';
require_once 'includes/functions.php';

$pageTitle = 'Search Flights';

// Get search parameters
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$departure_date = $_GET['departure_date'] ?? '';
$passengers = $_GET['passengers'] ?? 1;
$class = $_GET['class'] ?? 'economy';

// Extract city names from "City (CODE)" format if present
$fromCity = preg_replace('/\s*\([A-Z]{3}\)\s*$/', '', $from);
$toCity = preg_replace('/\s*\([A-Z]{3}\)\s*$/', '', $to);

// Fetch flights from database
$flights = [];
if (!empty($fromCity) && !empty($toCity) && !empty($departure_date)) {
    $db = getDB();
    
    // Build query
    $query = "SELECT f.*, 
              al.name as airline_name, al.code as airline_code, al.logo as airline_logo,
              dep.name as departure_airport, dep.code as departure_code, dep.city as departure_city,
              arr.name as arrival_airport, arr.code as arrival_code, arr.city as arrival_city
              FROM flights f
              JOIN airlines al ON f.airline_id = al.id
              JOIN airports dep ON f.departure_airport_id = dep.id
              JOIN airports arr ON f.arrival_airport_id = arr.id
              WHERE DATE(f.departure_time) = ? 
              AND dep.city LIKE ? 
              AND arr.city LIKE ?
              AND f.status = 'scheduled'
              ORDER BY f.departure_time ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$departure_date, "%$fromCity%", "%$toCity%"]);
    $flights = $stmt->fetchAll();
    
    // Update search count for demand-based pricing
    foreach ($flights as $flight) {
        $updateStmt = $db->prepare("UPDATE flights SET search_count = search_count + 1 WHERE id = ?");
        $updateStmt->execute([$flight['id']]);
    }
}
?>

<?php include 'includes/header.php'; ?>
<!-- Full Page Background with Overlay -->
<div class="min-h-screen" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('/FlyOn/assets/image/FlyOn3.png') no-repeat center center fixed; background-size: cover; padding: 2rem 0;">
<div class="container mx-auto px-4 py-8">
    <!-- Search Summary -->
    <div class="bg-white bg-opacity-90 rounded-lg shadow-md p-6 mb-8">
        <div class="flex flex-wrap items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-search text-primary"></i> Search Results
                </h1>
                <p class="text-gray-600">
                    <?php echo htmlspecialchars($fromCity); ?> 
                    <i class="fas fa-arrow-right text-primary mx-2"></i> 
                    <?php echo htmlspecialchars($toCity); ?> 
                    <span class="mx-2">•</span>
                    <?php echo formatDate($departure_date); ?>
                    <span class="mx-2">•</span>
                    <?php echo $passengers; ?> Passenger(s)
                    <span class="mx-2">•</span>
                    <?php echo ucfirst($class); ?> Class
                </p>
            </div>
            <a href="index.php" class="mt-4 md:mt-0 bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-edit"></i> Modify Search
            </a>
        </div>
    </div>
    
    <?php if (empty($flights)): ?>
        <!-- No Results -->
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-plane-slash text-gray-400 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">No Flights Found</h2>
            <p class="text-gray-600 mb-6">We couldn't find any flights matching your search criteria.</p>
            <a href="index.php" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-block">
                <i class="fas fa-search"></i> Try Another Search
            </a>
        </div>
    <?php else: ?>
        <!-- Filters & Sort -->
        <div class="bg-white bg-opacity-90 rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 font-medium">Sort by:</span>
                    <select id="sortFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="departure_early">Departure: Earliest</option>
                        <option value="departure_late">Departure: Latest</option>
                        <option value="duration">Duration: Shortest</option>
                    </select>
                </div>
                <div class="text-gray-600">
                    <strong><?php echo count($flights); ?></strong> flights found
                </div>
            </div>
        </div>
        
        <!-- Flight Results -->
        <div id="flightResults" class="space-y-4">
            <?php foreach ($flights as $flight): 
                // Calculate dynamic price
                $basePrice = $class === 'business' ? $flight['base_price_business'] : $flight['base_price_economy'];
                $finalPrice = calculateDynamicPrice($basePrice, $flight['id'], $flight['departure_time']);
                $duration = calculateDuration($flight['departure_time'], $flight['arrival_time']);
                $availableSeats = $class === 'business' ? $flight['available_seats_business'] : $flight['available_seats_economy'];
            ?>
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition p-6" 
                 data-price="<?php echo $finalPrice; ?>" 
                 data-departure="<?php echo strtotime($flight['departure_time']); ?>"
                 data-duration="<?php echo strtotime($flight['arrival_time']) - strtotime($flight['departure_time']); ?>">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <!-- Airline Info -->
                    <div class="flex items-center space-x-4">
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <i class="fas fa-plane text-primary text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($flight['airline_name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($flight['flight_number']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Flight Details -->
                    <div class="flex items-center space-x-8 flex-1 justify-center">
                        <!-- Departure -->
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-800"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($flight['departure_code']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($flight['departure_city']); ?></p>
                        </div>
                        
                        <!-- Duration -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600"><?php echo $duration; ?></p>
                            <div class="flex items-center space-x-2 my-2">
                                <div class="w-16 h-0.5 bg-gray-300"></div>
                                <i class="fas fa-plane text-primary"></i>
                                <div class="w-16 h-0.5 bg-gray-300"></div>
                            </div>
                            <p class="text-xs text-gray-500">Non-stop</p>
                        </div>
                        
                        <!-- Arrival -->
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-800"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($flight['arrival_code']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($flight['arrival_city']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Price & Book -->
                    <div class="text-right">
                        <div class="mb-2">
                            <?php if ($finalPrice > $basePrice): ?>
                                <p class="text-sm text-gray-500 line-through"><?php echo formatPrice($basePrice); ?></p>
                            <?php endif; ?>
                            <p class="text-3xl font-bold text-primary"><?php echo formatPrice($finalPrice); ?></p>
                            <p class="text-xs text-gray-600">per person</p>
                        </div>
                        <a href="flight_details.php?id=<?php echo $flight['id']; ?>&class=<?php echo $class; ?>&passengers=<?php echo $passengers; ?>" 
                           class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-block font-semibold">
                            View Details <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <?php if ($availableSeats < 10): ?>
                            <p class="text-xs text-red-500 mt-2">
                                <i class="fas fa-exclamation-circle"></i> Only <?php echo $availableSeats; ?> seats left!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Sort functionality
document.getElementById('sortFilter')?.addEventListener('change', function() {
    const sortBy = this.value;
    const container = document.getElementById('flightResults');
    const flights = Array.from(container.children);
    
    flights.sort((a, b) => {
        switch(sortBy) {
            case 'price_low':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price_high':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'departure_early':
                return parseInt(a.dataset.departure) - parseInt(b.dataset.departure);
            case 'departure_late':
                return parseInt(b.dataset.departure) - parseInt(a.dataset.departure);
            case 'duration':
                return parseInt(a.dataset.duration) - parseInt(b.dataset.duration);
            default:
                return 0;
        }
    });
    
    flights.forEach(flight => container.appendChild(flight));
});
</script>

</div>
<?php include 'includes/footer.php'; ?>
