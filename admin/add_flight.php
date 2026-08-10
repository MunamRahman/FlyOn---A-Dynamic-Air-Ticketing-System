<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$pageTitle = 'Add New Flight';
$db = getDB();

$success = '';
$error = '';

// Fetch airlines and airports for dropdowns
$airlines = $db->query("SELECT * FROM airlines ORDER BY name")->fetchAll();
$airports = $db->query("SELECT * FROM airports ORDER BY city, name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $flightNumber = sanitize($_POST['flight_number'] ?? '');
    $airlineId = $_POST['airline_id'] ?? 0;
    $departureAirportId = $_POST['departure_airport_id'] ?? 0;
    $arrivalAirportId = $_POST['arrival_airport_id'] ?? 0;
    $departureTime = $_POST['departure_time'] ?? '';
    $arrivalTime = $_POST['arrival_time'] ?? '';
    $basePriceEconomy = $_POST['base_price_economy'] ?? 0;
    $basePriceBusiness = $_POST['base_price_business'] ?? 0;
    $totalSeatsEconomy = $_POST['total_seats_economy'] ?? 0;
    $totalSeatsBusiness = $_POST['total_seats_business'] ?? 0;
    $status = $_POST['status'] ?? 'scheduled';
    
    if (!empty($flightNumber) && $airlineId && $departureAirportId && $arrivalAirportId && $departureTime && $arrivalTime) {
        try {
            // Insert flight
            $stmt = $db->prepare("
                INSERT INTO flights (
                    flight_number, airline_id, departure_airport_id, arrival_airport_id,
                    departure_time, arrival_time, base_price_economy, base_price_business,
                    total_seats_economy, total_seats_business, 
                    available_seats_economy, available_seats_business, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $flightNumber, $airlineId, $departureAirportId, $arrivalAirportId,
                $departureTime, $arrivalTime, $basePriceEconomy, $basePriceBusiness,
                $totalSeatsEconomy, $totalSeatsBusiness,
                $totalSeatsEconomy, $totalSeatsBusiness, $status
            ]);
            
            $flightId = $db->lastInsertId();
            
            // Create seats for this flight
            $seatClasses = [
                'economy' => $totalSeatsEconomy,
                'business' => $totalSeatsBusiness
            ];
            
            foreach ($seatClasses as $class => $totalSeats) {
                $rows = ceil($totalSeats / 6); // 6 seats per row (A-F)
                $seatLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
                
                for ($row = 1; $row <= $rows; $row++) {
                    foreach ($seatLetters as $letter) {
                        $seatNumber = $row . $letter;
                        $seatStmt = $db->prepare("
                            INSERT IGNORE INTO seats (flight_id, seat_number, class, status)
                            VALUES (?, ?, ?, 'available')
                        ");
                        try {
                            $seatStmt->execute([$flightId, $seatNumber, $class]);
                        } catch (PDOException $e) {
                            // Ignore duplicate key errors
                            if ($e->getCode() !== '23000') {
                                throw $e;
                            }
                        }
                        
                        // Stop if we've created enough seats
                        if (--$totalSeats <= 0) break 2;
                    }
                }
            }
            
            $success = "Flight added successfully! Flight #$flightNumber";
            
            // Clear form
            $_POST = [];
        } catch (PDOException $e) {
            $error = "Error adding flight: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-plus-circle text-primary"></i> Add New Flight
        </h1>
        <a href="flights.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left"></i> Back to Flights
        </a>
    </div>

    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <a href="flights.php" class="underline ml-2">View All Flights</a>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <!-- Flight Number & Airline -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        Flight Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="flight_number" required
                           placeholder="e.g., BG101"
                           value="<?php echo htmlspecialchars($_POST['flight_number'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        Airline <span class="text-red-500">*</span>
                    </label>
                    <select name="airline_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">Select Airline</option>
                        <?php foreach ($airlines as $airline): ?>
                        <option value="<?php echo $airline['id']; ?>" 
                                <?php echo (($_POST['airline_id'] ?? '') == $airline['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($airline['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Departure & Arrival Airports -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        Departure Airport <span class="text-red-500">*</span>
                    </label>
                    <select name="departure_airport_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">Select Departure Airport</option>
                        <?php foreach ($airports as $airport): ?>
                        <option value="<?php echo $airport['id']; ?>"
                                <?php echo (($_POST['departure_airport_id'] ?? '') == $airport['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($airport['city'] . ' - ' . $airport['name'] . ' (' . $airport['code'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        Arrival Airport <span class="text-red-500">*</span>
                    </label>
                    <select name="arrival_airport_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">Select Arrival Airport</option>
                        <?php foreach ($airports as $airport): ?>
                        <option value="<?php echo $airport['id']; ?>"
                                <?php echo (($_POST['arrival_airport_id'] ?? '') == $airport['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($airport['city'] . ' - ' . $airport['name'] . ' (' . $airport['code'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Departure & Arrival Times -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        Departure Time <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="departure_time" required
                           value="<?php echo htmlspecialchars($_POST['departure_time'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        Arrival Time <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="arrival_time" required
                           value="<?php echo htmlspecialchars($_POST['arrival_time'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <!-- Pricing -->
            <div class="border-t pt-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Pricing (BDT ৳)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Economy Class Price <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="base_price_economy" required min="0" step="0.01"
                               placeholder="e.g., 5000"
                               value="<?php echo htmlspecialchars($_POST['base_price_economy'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Business Class Price <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="base_price_business" required min="0" step="0.01"
                               placeholder="e.g., 15000"
                               value="<?php echo htmlspecialchars($_POST['base_price_business'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            <!-- Seats -->
            <div class="border-t pt-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Seat Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Economy Seats <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_seats_economy" required min="0"
                               placeholder="e.g., 150"
                               value="<?php echo htmlspecialchars($_POST['total_seats_economy'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Business Seats <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_seats_business" required min="0"
                               placeholder="e.g., 30"
                               value="<?php echo htmlspecialchars($_POST['total_seats_business'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">
                    Flight Status <span class="text-red-500">*</span>
                </label>
                <select name="status" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value="scheduled" <?php echo (($_POST['status'] ?? 'scheduled') == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="cancelled" <?php echo (($_POST['status'] ?? '') == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="delayed" <?php echo (($_POST['status'] ?? '') == 'delayed') ? 'selected' : ''; ?>>Delayed</option>
                </select>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4 pt-6">
                <button type="submit" 
                        class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-plus-circle"></i> Add Flight
                </button>
                <a href="flights.php" 
                   class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 transition font-semibold">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
