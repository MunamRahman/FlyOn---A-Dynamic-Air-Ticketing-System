<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Seat Selection';

// Check if passenger data exists
if (!isset($_SESSION['booking_data'])) {
    redirect('/search.php');
}

$bookingData = $_SESSION['booking_data'];
$flightId = $bookingData['flight_id'];
$class = strtolower(trim($bookingData['class'] ?? 'economy'));

// Normalize class value to match database enum
if (!in_array($class, ['economy', 'business', 'first'])) {
    $class = 'economy'; // Default to economy if invalid
}

$passengerCount = count($bookingData['passengers'] ?? []);

// Fetch flight and seats
$db = getDB();
$stmt = $db->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$flightId]);
$flight = $stmt->fetch();

if (!$flight) {
    $_SESSION['error'] = "Flight not found.";
    redirect('/search.php');
}

// Get or create seats for this flight
// First, try to get seats with exact class match
$stmt = $db->prepare("SELECT * FROM seats WHERE flight_id = ? AND class = ? ORDER BY seat_number");
$stmt->execute([$flightId, $class]);
$seats = $stmt->fetchAll();

// If no seats found, check if seats exist with different class (for debugging)
if (empty($seats)) {
    $checkStmt = $db->prepare("SELECT DISTINCT class, COUNT(*) as count FROM seats WHERE flight_id = ? GROUP BY class");
    $checkStmt->execute([$flightId]);
    $existingClasses = $checkStmt->fetchAll();
    if (!empty($existingClasses)) {
        error_log("Seats exist for flight $flightId but with different classes: " . json_encode($existingClasses));
    }
}

// If no seats exist, create them
if (empty($seats)) {
    $seatLayout = ['A', 'B', 'C', 'D', 'E', 'F'];
    $rows = $class === 'business' ? 5 : 20;
    
    try {
        // Use INSERT IGNORE to avoid duplicate key errors
        // This will silently skip inserts if the seat already exists
        $insertStmt = $db->prepare("INSERT IGNORE INTO seats (flight_id, seat_number, class, status) VALUES (?, ?, ?, 'available')");
        
        $createdCount = 0;
        for ($row = 1; $row <= $rows; $row++) {
            foreach ($seatLayout as $letter) {
                $seatNumber = $row . $letter;
                // INSERT IGNORE will not throw errors for duplicates, but we catch any other errors
                try {
                    $result = $insertStmt->execute([$flightId, $seatNumber, $class]);
                    if ($result && $insertStmt->rowCount() > 0) {
                        $createdCount++;
                    }
                } catch (PDOException $e) {
                    // Log the error but continue - seat might already exist
                    error_log("Seat creation warning: " . $e->getMessage());
                    // Only throw if it's not a duplicate key error (23000)
                    $errorInfo = $insertStmt->errorInfo();
                    if ($errorInfo[0] !== '00000' && $errorInfo[1] !== 1062) {
                        throw $e;
                    }
                }
            }
        }
        
        // Small delay to ensure database commit
        usleep(100000); // 0.1 second
        
        // Fetch again after creation attempt
        $stmt = $db->prepare("SELECT * FROM seats WHERE flight_id = ? AND class = ? ORDER BY seat_number");
        $stmt->execute([$flightId, $class]);
        $seats = $stmt->fetchAll();
        
        // If still empty, try without class filter to see if seats exist with different class
        if (empty($seats)) {
            $stmt = $db->prepare("SELECT * FROM seats WHERE flight_id = ? ORDER BY seat_number LIMIT 5");
            $stmt->execute([$flightId]);
            $anySeats = $stmt->fetchAll();
            if (!empty($anySeats)) {
                error_log("Seats exist but with different class. Found classes: " . implode(', ', array_unique(array_column($anySeats, 'class'))));
            }
        }
        
        // Update booking data with normalized class
        $_SESSION['booking_data']['class'] = $class;
        
        // If still no seats after creation attempt, there might be a database issue
        if (empty($seats)) {
            error_log("Warning: No seats found for flight $flightId, class $class after creation attempt");
            // Try one more time with a direct query to see what's in the database
            $debugStmt = $db->prepare("SELECT COUNT(*) as count FROM seats WHERE flight_id = ?");
            $debugStmt->execute([$flightId]);
            $totalSeats = $debugStmt->fetch();
            error_log("Total seats for flight $flightId: " . ($totalSeats['count'] ?? 0));
            
            $debugStmt = $db->prepare("SELECT COUNT(*) as count FROM seats WHERE flight_id = ? AND class = ?");
            $debugStmt->execute([$flightId, $class]);
            $classSeats = $debugStmt->fetch();
            error_log("Seats for flight $flightId, class $class: " . ($classSeats['count'] ?? 0));
        }
    } catch (PDOException $e) {
        // If creation fails completely, try to fetch existing seats anyway
        error_log("Error creating seats: " . $e->getMessage());
        $stmt = $db->prepare("SELECT * FROM seats WHERE flight_id = ? AND class = ? ORDER BY seat_number");
        $stmt->execute([$flightId, $class]);
        $seats = $stmt->fetchAll();
        
        // If still no seats, show user-friendly error
        if (empty($seats)) {
            $_SESSION['error'] = "Unable to load seats for this flight. Please try again or contact support.";
            redirect('/search.php');
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $selectedSeats = $_POST['seats'] ?? [];
    
    if (count($selectedSeats) === $passengerCount) {
        // Lock seats temporarily
        foreach ($selectedSeats as $seatId) {
            $lockUntil = date('Y-m-d H:i:s', time() + SEAT_LOCK_DURATION);
            $stmt = $db->prepare("UPDATE seats SET status = 'locked', locked_until = ?, locked_by_session = ? WHERE id = ? AND status = 'available'");
            $stmt->execute([$lockUntil, session_id(), $seatId]);
        }
        
        $_SESSION['booking_data']['selected_seats'] = $selectedSeats;
        redirect('/booking/step3_addons.php');
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center">
                <div class="bg-green-500 text-white w-10 h-10 rounded-full flex items-center justify-center"><i class="fas fa-check"></i></div>
                <span class="ml-2 text-gray-600">Passenger Info</span>
            </div>
            <div class="w-24 h-1 bg-green-500 mx-4"></div>
            <div class="flex items-center">
                <div class="bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center font-bold">2</div>
                <span class="ml-2 font-semibold text-primary">Seat Selection</span>
            </div>
            <div class="w-24 h-1 bg-gray-300 mx-4"></div>
            <div class="flex items-center">
                <div class="bg-gray-300 text-gray-600 w-10 h-10 rounded-full flex items-center justify-center font-bold">3</div>
                <span class="ml-2 text-gray-600">Add-ons</span>
            </div>
            <div class="w-24 h-1 bg-gray-300 mx-4"></div>
            <div class="flex items-center">
                <div class="bg-gray-300 text-gray-600 w-10 h-10 rounded-full flex items-center justify-center font-bold">4</div>
                <span class="ml-2 text-gray-600">Payment</span>
            </div>
            <div class="w-24 h-1 bg-gray-300 mx-4"></div>
            <div class="flex items-center">
                <div class="bg-gray-300 text-gray-600 w-10 h-10 rounded-full flex items-center justify-center font-bold">5</div>
                <span class="ml-2 text-gray-600">Confirmation</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-chair text-primary"></i> Select Your Seats
        </h2>
        
        <div class="mb-6">
            <p class="text-gray-600 mb-6">Please select <?php echo $passengerCount; ?> seat(s) for your journey</p>
            
            <!-- Legend -->
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 rounded mr-2"></div>
                    <span>Available</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 rounded mr-2"></div>
                    <span>Selected</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-400 rounded mr-2"></div>
                    <span>Booked</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-yellow-400 rounded mr-2"></div>
                    <span>Locked</span>
                </div>
            </div>
        </div>
        
        <form method="POST" id="seatForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <!-- Seat Map -->
            <div class="bg-gray-100 p-8 rounded-lg mb-6">
                <div class="text-center mb-4">
                    <i class="fas fa-plane text-4xl text-gray-400"></i>
                    <p class="text-sm text-gray-600 mt-2">Front of Aircraft</p>
                </div>
                
                <div class="max-w-2xl mx-auto">
                    <?php
                    if (empty($seats)) {
                        echo '<div class="text-center py-8">';
                        echo '<p class="text-red-600 mb-4 font-semibold">No seats available for this flight.</p>';
                        echo '<p class="text-sm text-gray-600 mb-2">Flight ID: ' . htmlspecialchars($flightId) . '</p>';
                        echo '<p class="text-sm text-gray-600 mb-2">Class: ' . htmlspecialchars($class) . '</p>';
                        echo '<p class="text-sm text-gray-600 mb-4">Seats were attempted to be created. Please refresh the page or contact support.</p>';
                        echo '<a href="?refresh=1" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Refresh Page</a>';
                        echo '</div>';
                    } else {
                        $seatsByRow = [];
                        foreach ($seats as $seat) {
                            $row = preg_replace('/[^0-9]/', '', $seat['seat_number']);
                            if (empty($row)) {
                                // Fallback: use first character as row if no number found
                                $row = substr($seat['seat_number'], 0, 1);
                            }
                            if (!isset($seatsByRow[$row])) {
                                $seatsByRow[$row] = [];
                            }
                            $seatsByRow[$row][] = $seat;
                        }
                        
                        // Sort rows numerically
                        ksort($seatsByRow, SORT_NATURAL);
                        
                        if (empty($seatsByRow)) {
                            // Fallback: Display seats in a simple grid if grouping fails
                            echo '<div class="text-center mb-4">';
                            echo '<p class="text-yellow-600 mb-2">Displaying seats in grid format</p>';
                            echo '</div>';
                            echo '<div class="grid grid-cols-6 gap-2 max-w-md mx-auto">';
                            foreach ($seats as $seat) {
                                $isBooked = $seat['status'] === 'booked';
                                $isLocked = $seat['status'] === 'locked' && ($seat['locked_by_session'] ?? '') !== session_id();
                                $disabled = $isBooked || $isLocked;
                                $seatColor = $disabled ? 'bg-gray-400' : 'bg-green-500 hover:bg-green-600';
                                ?>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="seats[]" value="<?php echo $seat['id']; ?>" 
                                           class="hidden seat-checkbox" 
                                           <?php echo $disabled ? 'disabled' : ''; ?>
                                           data-seat="<?php echo htmlspecialchars($seat['seat_number']); ?>">
                                    <div class="w-12 h-12 rounded flex items-center justify-center text-xs font-semibold transition text-white <?php echo $seatColor; ?>">
                                        <?php echo htmlspecialchars($seat['seat_number']); ?>
                                    </div>
                                </label>
                                <?php
                            }
                            echo '</div>';
                        } else {
                            // Removed debug output for production
                            foreach ($seatsByRow as $row => $rowSeats):
                                // Sort seats in row by letter
                                usort($rowSeats, function($a, $b) {
                                    return strcmp($a['seat_number'], $b['seat_number']);
                                });
                            ?>
                            <div class="flex items-center justify-center mb-2">
                                <span class="text-sm text-gray-600 w-8"><?php echo htmlspecialchars($row); ?></span>
                                <?php foreach ($rowSeats as $index => $seat): 
                                    $isBooked = $seat['status'] === 'booked';
                                    $isLocked = $seat['status'] === 'locked' && ($seat['locked_by_session'] ?? '') !== session_id();
                                    $disabled = $isBooked || $isLocked;
                                    
                                    // Determine seat color
                                    $seatColor = 'bg-green-500 hover:bg-green-600';
                                    if ($isBooked) {
                                        $seatColor = 'bg-gray-400 cursor-not-allowed';
                                    } elseif ($isLocked) {
                                        $seatColor = 'bg-yellow-400 cursor-not-allowed';
                                    }
                                ?>
                                    <?php if ($index === 3): ?>
                                        <div class="w-8"></div> <!-- Aisle -->
                                    <?php endif; ?>
                                    
                                    <label class="cursor-pointer mx-1">
                                        <input type="checkbox" name="seats[]" value="<?php echo $seat['id']; ?>" 
                                               class="hidden seat-checkbox" 
                                               <?php echo $disabled ? 'disabled' : ''; ?>
                                               data-seat="<?php echo htmlspecialchars($seat['seat_number']); ?>">
                                        <div class="w-10 h-10 rounded flex items-center justify-center text-xs font-semibold transition text-white seat-box <?php echo $seatColor; ?>">
                                            <?php echo htmlspecialchars($seat['seat_number']); ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php 
                            endforeach;
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="flex justify-between">
                <a href="step1_passenger.php?flight_id=<?php echo $flightId; ?>&class=<?php echo $class; ?>&passengers=<?php echo $passengerCount; ?>" 
                   class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" id="continueBtn" disabled class="bg-gray-400 text-white px-6 py-3 rounded-lg cursor-not-allowed">
                    Continue to Add-ons <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const requiredSeats = <?php echo $passengerCount; ?>;
const continueBtn = document.getElementById('continueBtn');
const checkboxes = document.querySelectorAll('.seat-checkbox:not([disabled])');

checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const selected = document.querySelectorAll('.seat-checkbox:checked').length;
        
        // Update UI
        if (this.checked) {
            this.nextElementSibling.classList.remove('bg-green-500', 'hover:bg-green-600');
            this.nextElementSibling.classList.add('bg-blue-500');
        } else {
            this.nextElementSibling.classList.remove('bg-blue-500');
            this.nextElementSibling.classList.add('bg-green-500', 'hover:bg-green-600');
        }
        
        // Disable other checkboxes if limit reached
        if (selected >= requiredSeats) {
            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    cb.disabled = true;
                    cb.nextElementSibling.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });
        } else {
            checkboxes.forEach(cb => {
                cb.disabled = false;
                cb.nextElementSibling.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }
        
        // Enable/disable continue button
        if (selected === requiredSeats) {
            continueBtn.disabled = false;
            continueBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            continueBtn.classList.add('bg-primary', 'hover:bg-blue-700');
        } else {
            continueBtn.disabled = true;
            continueBtn.classList.remove('bg-primary', 'hover:bg-blue-700');
            continueBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
