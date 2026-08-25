<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Passenger Details';

// Get parameters
$flightId = $_GET['flight_id'] ?? 0;
$class = $_GET['class'] ?? 'economy';
$passengers = $_GET['passengers'] ?? 1;

// Fetch flight details
$db = getDB();
$stmt = $db->prepare("SELECT f.*, al.name as airline_name, al.code as airline_code FROM flights f JOIN airlines al ON f.airline_id = al.id WHERE f.id = ?");
$stmt->execute([$flightId]);
$flight = $stmt->fetch();

if (!$flight) {
    redirect('/search.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['booking_data'] = [
        'flight_id' => $flightId,
        'class' => $class,
        'passengers' => $_POST['passengers'],
        'contact_email' => sanitize($_POST['contact_email']),
        'contact_phone' => sanitize($_POST['contact_phone'])
    ];
    redirect('/booking/step2_seat.php');
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center">
                <div class="bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center font-bold">1</div>
                <span class="ml-2 font-semibold text-primary">Passenger Info</span>
            </div>
            <div class="w-24 h-1 bg-gray-300 mx-4"></div>
            <div class="flex items-center">
                <div class="bg-gray-300 text-gray-600 w-10 h-10 rounded-full flex items-center justify-center font-bold">2</div>
                <span class="ml-2 text-gray-600">Seat Selection</span>
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
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-users text-primary"></i> Passenger Information
                </h2>
                
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <?php for ($i = 1; $i <= $passengers; $i++): ?>
                    <div class="border-b pb-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Passenger <?php echo $i; ?></h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Title *</label>
                                <select name="passengers[<?php echo $i; ?>][title]" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Ms">Ms</option>
                                    <option value="Dr">Dr</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">First Name *</label>
                                <input type="text" name="passengers[<?php echo $i; ?>][first_name]" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Last Name *</label>
                                <input type="text" name="passengers[<?php echo $i; ?>][last_name]" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Date of Birth *</label>
                                <input type="date" name="passengers[<?php echo $i; ?>][dob]" required max="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Gender *</label>
                                <select name="passengers[<?php echo $i; ?>][gender]" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Passport Number</label>
                                <input type="text" name="passengers[<?php echo $i; ?>][passport]" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                    
                    <!-- Contact Information -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Contact Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Email *</label>
                                <input type="email" name="contact_email" required value="<?php echo getCurrentUser()['email'] ?? ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Phone *</label>
                                <input type="tel" name="contact_phone" required value="<?php echo getCurrentUser()['phone'] ?? ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms -->
                    <div class="flex items-start">
                        <input type="checkbox" required class="mt-1 mr-2">
                        <label class="text-sm text-gray-600">
                            I agree to the <a href="#" class="text-primary hover:underline">Terms & Conditions</a> and <a href="#" class="text-primary hover:underline">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <!-- Submit -->
                    <div class="flex justify-between">
                        <a href="../flight_details.php?id=<?php echo $flightId; ?>&class=<?php echo $class; ?>&passengers=<?php echo $passengers; ?>" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Continue to Seat Selection <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Flight Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Flight</span>
                        <span class="font-semibold"><?php echo htmlspecialchars($flight['flight_number']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Airline</span>
                        <span class="font-semibold"><?php echo htmlspecialchars($flight['airline_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Class</span>
                        <span class="font-semibold"><?php echo ucfirst($class); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Passengers</span>
                        <span class="font-semibold"><?php echo $passengers; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Departure</span>
                        <span class="font-semibold"><?php echo formatDateTime($flight['departure_time']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
