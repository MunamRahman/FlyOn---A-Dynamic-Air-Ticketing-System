<?php
/**
 * Test Seats - Quick test to verify seat creation and display
 */

require_once 'config.php';
require_once 'includes/db_connect.php';

$flightId = $_GET['flight_id'] ?? 1;
$class = $_GET['class'] ?? 'economy';

$db = getDB();

echo "<h1>Seat Test for Flight ID: $flightId, Class: $class</h1>";

// Check if flight exists
$stmt = $db->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$flightId]);
$flight = $stmt->fetch();

if (!$flight) {
    die("Flight not found!");
}

echo "<h2>Flight: " . htmlspecialchars($flight['flight_number']) . "</h2>";

// Check existing seats
$stmt = $db->prepare("SELECT * FROM seats WHERE flight_id = ? AND class = ?");
$stmt->execute([$flightId, $class]);
$seats = $stmt->fetchAll();

echo "<p>Existing seats: " . count($seats) . "</p>";

// Create seats if none exist
if (empty($seats)) {
    echo "<p>Creating seats...</p>";
    $seatLayout = ['A', 'B', 'C', 'D', 'E', 'F'];
    $rows = $class === 'business' ? 5 : 20;
    
    $insertStmt = $db->prepare("INSERT IGNORE INTO seats (flight_id, seat_number, class, status) VALUES (?, ?, ?, 'available')");
    $created = 0;
    
    for ($row = 1; $row <= $rows; $row++) {
        foreach ($seatLayout as $letter) {
            $seatNumber = $row . $letter;
            $result = $insertStmt->execute([$flightId, $seatNumber, $class]);
            if ($insertStmt->rowCount() > 0) {
                $created++;
            }
        }
    }
    
    echo "<p>Created $created seats</p>";
    
    // Fetch again
    $stmt = $db->prepare("SELECT * FROM seats WHERE flight_id = ? AND class = ? ORDER BY seat_number");
    $stmt->execute([$flightId, $class]);
    $seats = $stmt->fetchAll();
}

// Display seats
echo "<h2>Seats (" . count($seats) . " total):</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; max-width: 600px;'>";

foreach ($seats as $seat) {
    $color = $seat['status'] === 'booked' ? 'gray' : ($seat['status'] === 'locked' ? 'yellow' : 'green');
    echo "<div style='background: $color; color: white; padding: 10px; text-align: center; border-radius: 4px;'>";
    echo htmlspecialchars($seat['seat_number']);
    echo "<br><small>" . htmlspecialchars($seat['status']) . "</small>";
    echo "</div>";
}

echo "</div>";

// Show raw data
echo "<h2>Raw Data (first 5 seats):</h2>";
echo "<pre>";
print_r(array_slice($seats, 0, 5));
echo "</pre>";

?>

