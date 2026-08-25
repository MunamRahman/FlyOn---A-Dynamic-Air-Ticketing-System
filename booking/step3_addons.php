<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Add-ons';

if (!isset($_SESSION['booking_data'])) {
    redirect('/search.php');
}

$bookingData = $_SESSION['booking_data'];

// Available add-ons
$addons = [
    'meals' => [
        ['name' => 'Vegetarian Meal', 'price' => 15],
        ['name' => 'Non-Vegetarian Meal', 'price' => 18],
        ['name' => 'Special Dietary Meal', 'price' => 20],
    ],
    'baggage' => [
        ['name' => 'Extra 10kg Baggage', 'price' => 50],
        ['name' => 'Extra 20kg Baggage', 'price' => 90],
        ['name' => 'Extra 30kg Baggage', 'price' => 120],
    ],
    'insurance' => [
        ['name' => 'Basic Travel Insurance', 'price' => 25],
        ['name' => 'Premium Travel Insurance', 'price' => 50],
    ],
    'services' => [
        ['name' => 'Priority Boarding', 'price' => 30],
        ['name' => 'Lounge Access', 'price' => 45],
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['booking_data']['addons'] = $_POST['addons'] ?? [];
    redirect('/booking/step4_payment.php');
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
                <div class="bg-green-500 text-white w-10 h-10 rounded-full flex items-center justify-center"><i class="fas fa-check"></i></div>
                <span class="ml-2 text-gray-600">Seat Selection</span>
            </div>
            <div class="w-24 h-1 bg-green-500 mx-4"></div>
            <div class="flex items-center">
                <div class="bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center font-bold">3</div>
                <span class="ml-2 font-semibold text-primary">Add-ons</span>
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
            <i class="fas fa-plus-circle text-primary"></i> Enhance Your Journey
        </h2>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <!-- Meals -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">
                    <i class="fas fa-utensils text-primary"></i> In-Flight Meals
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ($addons['meals'] as $index => $meal): ?>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-primary hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-2">
                            <input type="checkbox" name="addons[meals][]" value="<?php echo $index; ?>" class="mr-2">
                            <span class="font-semibold text-primary"><?php echo formatPrice($meal['price']); ?></span>
                        </div>
                        <p class="text-gray-700"><?php echo $meal['name']; ?></p>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Baggage -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">
                    <i class="fas fa-suitcase text-primary"></i> Extra Baggage
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ($addons['baggage'] as $index => $baggage): ?>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-primary hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-2">
                            <input type="radio" name="addons[baggage]" value="<?php echo $index; ?>" class="mr-2">
                            <span class="font-semibold text-primary"><?php echo formatPrice($baggage['price']); ?></span>
                        </div>
                        <p class="text-gray-700"><?php echo $baggage['name']; ?></p>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Insurance -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">
                    <i class="fas fa-shield-alt text-primary"></i> Travel Insurance
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($addons['insurance'] as $index => $insurance): ?>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-primary hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-2">
                            <input type="radio" name="addons[insurance]" value="<?php echo $index; ?>" class="mr-2">
                            <span class="font-semibold text-primary"><?php echo formatPrice($insurance['price']); ?></span>
                        </div>
                        <p class="text-gray-700"><?php echo $insurance['name']; ?></p>
                        <p class="text-xs text-gray-500 mt-2">Coverage up to $50,000</p>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Services -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">
                    <i class="fas fa-star text-primary"></i> Premium Services
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($addons['services'] as $index => $service): ?>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-primary hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-2">
                            <input type="checkbox" name="addons[services][]" value="<?php echo $index; ?>" class="mr-2">
                            <span class="font-semibold text-primary"><?php echo formatPrice($service['price']); ?></span>
                        </div>
                        <p class="text-gray-700"><?php echo $service['name']; ?></p>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="flex justify-between">
                <a href="step2_seat.php" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Continue to Payment <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
