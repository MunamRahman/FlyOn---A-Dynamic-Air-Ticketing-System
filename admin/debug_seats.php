<?php
/**
 * Debug Seats - Admin tool to check seat data
 */

require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$db = getDB();
$flightId = $_GET['flight_id'] ?? null;

if ($flightId) {
    $stmt = $db->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM seats WHERE flight_id = ? ORDER BY class, seat_number");
    $stmt->execute([$flightId]);
    $seats = $stmt->fetchAll();
    
    $seatsByClass = [];
    foreach ($seats as $seat) {
        $seatsByClass[$seat['class']][] = $seat;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Seats - FlyOn Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">Debug Seats</h1>
        
        <form method="GET" class="mb-6">
            <label class="block mb-2">Flight ID:</label>
            <input type="number" name="flight_id" value="<?php echo htmlspecialchars($flightId ?? ''); ?>" class="border p-2 rounded">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded ml-2">Check</button>
        </form>
        
        <?php if ($flightId && $flight): ?>
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Flight Information</h2>
            <p><strong>Flight Number:</strong> <?php echo htmlspecialchars($flight['flight_number']); ?></p>
            <p><strong>Total Seats:</strong> <?php echo count($seats); ?></p>
        </div>
        
        <?php foreach ($seatsByClass as $class => $classSeats): ?>
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2"><?php echo ucfirst($class); ?> Class (<?php echo count($classSeats); ?> seats)</h3>
            <div class="grid grid-cols-6 gap-2">
                <?php foreach ($classSeats as $seat): ?>
                <div class="border p-2 text-center text-sm">
                    <div class="font-semibold"><?php echo htmlspecialchars($seat['seat_number']); ?></div>
                    <div class="text-xs text-gray-600"><?php echo htmlspecialchars($seat['status']); ?></div>
                    <div class="text-xs text-gray-500">ID: <?php echo $seat['id']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php elseif ($flightId): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            Flight not found!
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

