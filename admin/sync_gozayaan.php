<?php
/**
 * GoZayaan Sync Management
 * Admin interface for syncing flight data from GoZayaan
 */

require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/GoZayaanIntegration.php';

requireAdmin();

$pageTitle = 'Sync Flights from GoZayaan';
$db = getDB();
$gozayaan = new GoZayaanIntegration();

$message = '';
$messageType = '';

// Handle sync action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $flightId = intval($_POST['flight_id'] ?? 0);
    
    if ($action === 'sync_all') {
        $result = $gozayaan->syncFlightTimes();
        if ($result['success']) {
            $message = "Successfully synced {$result['updated']} flights.";
            if (!empty($result['errors'])) {
                $message .= " Errors: " . count($result['errors']);
            }
            $messageType = 'success';
        } else {
            $message = "Sync failed: " . $result['error'];
            $messageType = 'error';
        }
    } elseif ($action === 'sync_single' && $flightId) {
        $result = $gozayaan->syncFlightTimes($flightId);
        if ($result['success']) {
            $message = "Flight synced successfully.";
            $messageType = 'success';
        } else {
            $message = "Sync failed: " . ($result['error'] ?? 'Unknown error');
            $messageType = 'error';
        }
    }
}

// Get last sync time
$lastSyncTime = $gozayaan->getLastSyncTime();

// Get upcoming flights
$stmt = $db->query("
    SELECT f.*, 
           al.name as airline_name, al.code as airline_code,
           dep.city as departure_city, dep.code as departure_code,
           arr.city as arrival_city, arr.code as arrival_code
    FROM flights f
    JOIN airlines al ON f.airline_id = al.id
    JOIN airports dep ON f.departure_airport_id = dep.id
    JOIN airports arr ON f.arrival_airport_id = arr.id
    WHERE f.departure_time >= NOW() 
    AND f.status = 'scheduled'
    ORDER BY f.departure_time ASC
    LIMIT 50
");
$flights = $stmt->fetchAll();

// Get sync logs
$stmt = $db->query("
    SELECT fsl.*, f.flight_number
    FROM flight_sync_logs fsl
    JOIN flights f ON fsl.flight_id = f.id
    ORDER BY fsl.created_at DESC
    LIMIT 20
");
$syncLogs = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-sync-alt text-primary"></i> Sync Flights from GoZayaan
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

    <!-- Sync Controls -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Sync Controls</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Last Sync Time</p>
                <p class="text-lg font-semibold text-gray-800">
                    <?php echo $lastSyncTime ? formatDateTime($lastSyncTime) : 'Never'; ?>
                </p>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Upcoming Flights</p>
                <p class="text-lg font-semibold text-gray-800"><?php echo count($flights); ?></p>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">API Status</p>
                <p class="text-lg font-semibold text-gray-800">
                    <?php echo !empty(env('GOZAYAAN_API_KEY')) ? 'Configured' : 'Not Configured'; ?>
                </p>
            </div>
        </div>
        
        <form method="POST" class="flex gap-3">
            <input type="hidden" name="action" value="sync_all">
            <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-sync"></i> Sync All Upcoming Flights
            </button>
        </form>
        
        <p class="text-sm text-gray-600 mt-2">
            <i class="fas fa-info-circle"></i> This will update flight times for all scheduled flights from GoZayaan.
        </p>
    </div>

    <!-- Upcoming Flights -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Upcoming Flights</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flight #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Arrival</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($flights as $flight): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($flight['flight_number']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($flight['airline_name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($flight['departure_code']); ?> → <?php echo htmlspecialchars($flight['arrival_code']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($flight['departure_city']); ?> → <?php echo htmlspecialchars($flight['arrival_city']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatDateTime($flight['departure_time'], 'M d, Y h:i A'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatDateTime($flight['arrival_time'], 'M d, Y h:i A'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo getStatusBadge($flight['status']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="sync_single">
                                <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                <button type="submit" class="text-primary hover:text-blue-700">
                                    <i class="fas fa-sync"></i> Sync
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sync Logs -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Recent Sync Logs</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flight #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Changes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Synced At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($syncLogs)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No sync logs yet</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($syncLogs as $log): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($log['flight_number']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php
                                $changes = json_decode($log['changes'], true);
                                if ($changes) {
                                    echo "Departure: " . ($changes['old_departure'] ?? 'N/A') . " → " . ($changes['new_departure'] ?? 'N/A');
                                    echo "<br>Arrival: " . ($changes['old_arrival'] ?? 'N/A') . " → " . ($changes['new_arrival'] ?? 'N/A');
                                    if (isset($changes['old_status']) && isset($changes['new_status'])) {
                                        echo "<br>Status: " . $changes['old_status'] . " → " . $changes['new_status'];
                                    }
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDateTime($log['created_at']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

