<?php
/**
 * Add Seats for All Flights
 * Admin tool to create seats for all flights
 */

require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_seats'])) {
    try {
        // Get all flights
        $stmt = $db->query("SELECT id, flight_number, total_seats_economy, total_seats_business FROM flights WHERE status = 'scheduled'");
        $flights = $stmt->fetchAll();
        
        $totalSeatsCreated = 0;
        $flightsProcessed = 0;
        
        foreach ($flights as $flight) {
            $flightId = $flight['id'];
            $economySeats = $flight['total_seats_economy'];
            $businessSeats = $flight['total_seats_business'];
            
            // Create Economy Class Seats
            $row = 1;
            $seatCount = 0;
            $seatLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
            
            while ($seatCount < $economySeats) {
                foreach ($seatLetters as $letter) {
                    if ($seatCount >= $economySeats) break;
                    
                    $seatNumber = str_pad($row, 2, '0', STR_PAD_LEFT) . $letter;
                    
                    // Insert seat if it doesn't exist
                    $insertStmt = $db->prepare("INSERT IGNORE INTO seats (flight_id, seat_number, class, status) VALUES (?, ?, 'economy', 'available')");
                    $insertStmt->execute([$flightId, $seatNumber]);
                    
                    if ($insertStmt->rowCount() > 0) {
                        $totalSeatsCreated++;
                    }
                    $seatCount++;
                }
                $row++;
            }
            
            // Create Business Class Seats
            if ($businessSeats > 0) {
                $row = 1;
                $seatCount = 0;
                
                while ($seatCount < $businessSeats) {
                    foreach ($seatLetters as $letter) {
                        if ($seatCount >= $businessSeats) break;
                        
                        $seatNumber = str_pad($row, 2, '0', STR_PAD_LEFT) . $letter;
                        
                        // Insert seat if it doesn't exist
                        $insertStmt = $db->prepare("INSERT IGNORE INTO seats (flight_id, seat_number, class, status) VALUES (?, ?, 'business', 'available')");
                        $insertStmt->execute([$flightId, $seatNumber]);
                        
                        if ($insertStmt->rowCount() > 0) {
                            $totalSeatsCreated++;
                        }
                        $seatCount++;
                    }
                    $row++;
                }
            }
            
            $flightsProcessed++;
        }
        
        $message = "Successfully created seats for {$flightsProcessed} flights. Total seats created: {$totalSeatsCreated}";
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get statistics
$stats = [
    'total_flights' => $db->query("SELECT COUNT(*) FROM flights WHERE status = 'scheduled'")->fetchColumn(),
    'flights_with_seats' => $db->query("SELECT COUNT(DISTINCT flight_id) FROM seats")->fetchColumn(),
    'total_seats' => $db->query("SELECT COUNT(*) FROM seats")->fetchColumn(),
    'economy_seats' => $db->query("SELECT COUNT(*) FROM seats WHERE class = 'economy'")->fetchColumn(),
    'business_seats' => $db->query("SELECT COUNT(*) FROM seats WHERE class = 'business'")->fetchColumn(),
];

// Get flights without seats
$stmt = $db->query("
    SELECT f.*, al.name as airline_name
    FROM flights f
    LEFT JOIN airlines al ON f.airline_id = al.id
    WHERE f.status = 'scheduled'
    AND f.id NOT IN (SELECT DISTINCT flight_id FROM seats)
    ORDER BY f.id
    LIMIT 20
");
$flightsWithoutSeats = $stmt->fetchAll();

?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-chair text-primary"></i> Manage Seats
        </h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4">
            <p class="text-sm text-gray-600">Total Flights</p>
            <p class="text-2xl font-bold text-primary"><?php echo number_format($stats['total_flights']); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <p class="text-sm text-gray-600">Flights with Seats</p>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['flights_with_seats']); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <p class="text-sm text-gray-600">Total Seats</p>
            <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total_seats']); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <p class="text-sm text-gray-600">Economy Seats</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['economy_seats']); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <p class="text-sm text-gray-600">Business Seats</p>
            <p class="text-2xl font-bold text-orange-600"><?php echo number_format($stats['business_seats']); ?></p>
        </div>
    </div>

    <!-- Create Seats Button -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Create Seats for All Flights</h2>
        <p class="text-gray-600 mb-4">
            This will create seats for all scheduled flights based on their total_seats_economy and total_seats_business values.
            Existing seats will not be duplicated.
        </p>
        <form method="POST">
            <button type="submit" name="create_seats" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus-circle"></i> Create Seats for All Flights
            </button>
        </form>
    </div>

    <!-- Flights Without Seats -->
    <?php if (!empty($flightsWithoutSeats)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Flights Without Seats (<?php echo count($flightsWithoutSeats); ?>)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flight #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Airline</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Economy Seats</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business Seats</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($flightsWithoutSeats as $flight): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($flight['flight_number']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($flight['airline_name'] ?? 'N/A'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo number_format($flight['total_seats_economy']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo number_format($flight['total_seats_business']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
        <p class="text-green-800 font-semibold">All flights have seats configured!</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

