<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Payment';

if (!isset($_SESSION['booking_data'])) {
    redirect('/search.php');
}

$bookingData = $_SESSION['booking_data'];
$db = getDB();

// Fetch flight details
$stmt = $db->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$bookingData['flight_id']]);
$flight = $stmt->fetch();

// Calculate total price
$basePrice = $bookingData['class'] === 'business' ? $flight['base_price_business'] : $flight['base_price_economy'];
$finalPrice = calculateDynamicPrice($basePrice, $flight['id'], $flight['departure_time']);
$passengerCount = count($bookingData['passengers']);
$subtotal = $finalPrice * $passengerCount;
$taxAmount = $subtotal * 0.15;
$totalPrice = $subtotal + $taxAmount;

// Fetch promotions
$stmt = $db->query("SELECT * FROM promotions WHERE status = 'active' AND valid_from <= NOW() AND valid_until >= NOW()");
$promotions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $paymentMethod = sanitize($_POST['payment_method']);
    $promoCode = sanitize($_POST['promo_code'] ?? '');
    
    // Apply promo code
    $discount = 0;
    if (!empty($promoCode)) {
        $stmt = $db->prepare("SELECT * FROM promotions WHERE code = ? AND status = 'active' AND valid_from <= NOW() AND valid_until >= NOW()");
        $stmt->execute([$promoCode]);
        $promo = $stmt->fetch();
        
        if ($promo) {
            if ($promo['discount_type'] === 'percentage') {
                $discount = $totalPrice * ($promo['discount_value'] / 100);
            } else {
                $discount = $promo['discount_value'];
            }
        }
    }
    
    $finalTotal = $totalPrice - $discount;
    
    // Create booking
    $bookingRef = generateBookingReference();
    
    $stmt = $db->prepare("INSERT INTO bookings (booking_reference, user_id, flight_id, travel_class, total_passengers, base_price, tax_amount, discount_amount, total_price, payment_method, promo_code, booking_status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
    
    $stmt->execute([
        $bookingRef,
        getCurrentUserId(),
        $bookingData['flight_id'],
        $bookingData['class'],
        $passengerCount,
        $subtotal,
        $taxAmount,
        $discount,
        $finalTotal,
        $paymentMethod,
        $promoCode
    ]);
    
    $bookingId = $db->lastInsertId();
    
    // Insert passengers
    foreach ($bookingData['passengers'] as $passenger) {
        $stmt = $db->prepare("INSERT INTO passengers (booking_id, title, first_name, last_name, date_of_birth, gender, passport_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $bookingId,
            $passenger['title'],
            $passenger['first_name'],
            $passenger['last_name'],
            $passenger['dob'],
            $passenger['gender'],
            $passenger['passport'] ?? null
        ]);
    }
    
    // Update seat status
    if (isset($bookingData['selected_seats'])) {
        foreach ($bookingData['selected_seats'] as $seatId) {
            $stmt = $db->prepare("UPDATE seats SET status = 'booked' WHERE id = ?");
            $stmt->execute([$seatId]);
        }
    }
    
    // Update flight availability
    $seatColumn = $bookingData['class'] === 'business' ? 'available_seats_business' : 'available_seats_economy';
    $stmt = $db->prepare("UPDATE flights SET $seatColumn = $seatColumn - ? WHERE id = ?");
    $stmt->execute([$passengerCount, $bookingData['flight_id']]);
    
    // Simulate payment processing
    if ($paymentMethod !== 'manual') {
        $stmt = $db->prepare("UPDATE bookings SET payment_status = 'paid', booking_status = 'confirmed' WHERE id = ?");
        $stmt->execute([$bookingId]);
        
        // Add loyalty points
        $points = calculateLoyaltyPoints($finalTotal);
        $stmt = $db->prepare("UPDATE loyalty SET total_points = total_points + ?, available_points = available_points + ? WHERE user_id = ?");
        $stmt->execute([$points, $points, getCurrentUserId()]);
    }
    
    // Clear session
    unset($_SESSION['booking_data']);
    
    redirect('/booking/step5_confirmation.php?booking=' . $bookingRef);
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
                <div class="bg-green-500 text-white w-10 h-10 rounded-full flex items-center justify-center"><i class="fas fa-check"></i></div>
                <span class="ml-2 text-gray-600">Add-ons</span>
            </div>
            <div class="w-24 h-1 bg-green-500 mx-4"></div>
            <div class="flex items-center">
                <div class="bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center font-bold">4</div>
                <span class="ml-2 font-semibold text-primary">Payment</span>
            </div>
            <div class="w-24 h-1 bg-gray-300 mx-4"></div>
            <div class="flex items-center">
                <div class="bg-gray-300 text-gray-600 w-10 h-10 rounded-full flex items-center justify-center font-bold">5</div>
                <span class="ml-2 text-gray-600">Confirmation</span>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Payment Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-credit-card text-primary"></i> Payment Details
                </h2>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <!-- Promo Code -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Promo Code (Optional)</label>
                        <div class="flex gap-2">
                            <input type="text" name="promo_code" placeholder="Enter promo code" class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            <button type="button" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">Apply</button>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-4">Select Payment Method</label>
                        
                        <div class="space-y-3">
                            <label class="border rounded-lg p-4 flex items-center cursor-pointer hover:border-primary transition">
                                <input type="radio" name="payment_method" value="stripe" required class="mr-3">
                                <i class="fab fa-cc-stripe text-3xl text-blue-600 mr-3"></i>
                                <span class="font-semibold">Credit/Debit Card (Stripe)</span>
                            </label>
                            
                            <label class="border rounded-lg p-4 flex items-center cursor-pointer hover:border-primary transition">
                                <input type="radio" name="payment_method" value="paypal" class="mr-3">
                                <i class="fab fa-paypal text-3xl text-blue-500 mr-3"></i>
                                <span class="font-semibold">PayPal</span>
                            </label>
                            
                            <label class="border rounded-lg p-4 flex items-center cursor-pointer hover:border-primary transition">
                                <input type="radio" name="payment_method" value="sslcommerz" class="mr-3">
                                <i class="fas fa-lock text-3xl text-green-600 mr-3"></i>
                                <span class="font-semibold">SSLCommerz</span>
                            </label>
                            
                            <label class="border rounded-lg p-4 flex items-center cursor-pointer hover:border-primary transition">
                                <input type="radio" name="payment_method" value="manual" class="mr-3">
                                <i class="fas fa-money-bill text-3xl text-gray-600 mr-3"></i>
                                <span class="font-semibold">Pay Later (Manual)</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Terms -->
                    <div class="mb-6 flex items-start">
                        <input type="checkbox" required class="mt-1 mr-2">
                        <label class="text-sm text-gray-600">
                            I agree to the booking terms and conditions and understand the cancellation policy
                        </label>
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="step3_addons.php" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Complete Booking <i class="fas fa-check"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Price Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Booking Summary</h3>
                
                <div class="space-y-3 text-sm mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base Fare (<?php echo $passengerCount; ?> × <?php echo formatPrice($finalPrice); ?>)</span>
                        <span class="font-semibold"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxes & Fees</span>
                        <span class="font-semibold"><?php echo formatPrice($taxAmount); ?></span>
                    </div>
                    <div class="border-t pt-3 flex justify-between text-lg font-bold">
                        <span>Total Amount</span>
                        <span class="text-primary"><?php echo formatPrice($totalPrice); ?></span>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-xs text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Earn <?php echo calculateLoyaltyPoints($totalPrice); ?> loyalty points</strong> with this booking!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
