<?php
require_once 'config.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section with Search Form -->
<section class="relative text-white py-20" style="background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('/FlyOn/assets/image/Flyon2.png') no-repeat center center; background-size: cover;">
    <div class="absolute inset-0 bg-black opacity-30"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold mb-4">Find Your Perfect Flight</h1>
            <p class="text-xl text-white">Book flights to destinations worldwide at the best prices</p>
        </div>
        
        <!-- Flight Search Form -->
        <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-2xl p-8">
            <form action="search.php" method="GET" class="space-y-6">
                <!-- Trip Type -->
                <div class="flex space-x-4">
                    <label class="flex items-center text-gray-700">
                        <input type="radio" name="trip_type" value="one_way" checked class="mr-2">
                        <span>One Way</span>
                    </label>
                    <label class="flex items-center text-gray-700">
                        <input type="radio" name="trip_type" value="round_trip" class="mr-2">
                        <span>Round Trip</span>
                    </label>
                </div>
                
                <!-- Search Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- From -->
                    <div class="relative">
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-plane-departure text-primary"></i> From
                        </label>
                        <input type="text" id="from-input" name="from" placeholder="e.g., Dhaka, Chittagong" required autocomplete="off"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900">
                        <div id="from-suggestions" class="absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden max-h-60 overflow-y-auto"></div>
                    </div>
                    
                    <!-- To -->
                    <div class="relative">
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-plane-arrival text-primary"></i> To
                        </label>
                        <input type="text" id="to-input" name="to" placeholder="e.g., Dubai, Singapore" required autocomplete="off"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900">
                        <div id="to-suggestions" class="absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden max-h-60 overflow-y-auto"></div>
                    </div>
                    
                    <!-- Departure Date -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-calendar text-primary"></i> Departure
                        </label>
                        <input type="date" name="departure_date" required
                               min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900">
                    </div>
                    
                    <!-- Passengers -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-users text-primary"></i> Passengers
                        </label>
                        <select name="passengers" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900">
                            <option value="1">1 Passenger</option>
                            <option value="2">2 Passengers</option>
                            <option value="3">3 Passengers</option>
                            <option value="4">4 Passengers</option>
                            <option value="5">5+ Passengers</option>
                        </select>
                    </div>
                </div>
                
                <!-- Class Selection -->
                <div class="flex space-x-4">
                    <label class="flex items-center text-gray-700">
                        <input type="radio" name="class" value="economy" checked class="mr-2">
                        <span>Economy</span>
                    </label>
                    <label class="flex items-center text-gray-700">
                        <input type="radio" name="class" value="business" class="mr-2">
                        <span>Business</span>
                    </label>
                    <label class="flex items-center text-gray-700">
                        <input type="radio" name="class" value="first" class="mr-2">
                        <span>First Class</span>
                    </label>
                </div>
                
                <!-- Search Button -->
                <button type="submit" class="w-full bg-primary text-white py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition transform hover:scale-105">
                    <i class="fas fa-search"></i> Search Flights
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Popular Destinations -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Popular Destinations</h2>
            <p class="text-gray-600">Explore the world's most amazing places</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            $destinations = [
                ['city' => 'Cox\'s Bazar', 'country' => 'Bangladesh', 'price' => 4500, 'code' => 'CXB', 'icon' => 'fa-umbrella-beach'],
                ['city' => 'Dubai', 'country' => 'UAE', 'price' => 35000, 'code' => 'DXB', 'icon' => 'fa-building'],
                ['city' => 'Bangkok', 'country' => 'Thailand', 'price' => 25000, 'code' => 'BKK', 'icon' => 'fa-temple'],
                ['city' => 'Singapore', 'country' => 'Singapore', 'price' => 38000, 'code' => 'SIN', 'icon' => 'fa-city'],
            ];
            
            foreach ($destinations as $dest):
            ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-2">
                <div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                    <i class="fas <?php echo $dest['icon']; ?> text-white text-6xl opacity-50"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo $dest['city']; ?></h3>
                    <p class="text-gray-600 mb-4">
                        <i class="fas fa-map-marker-alt text-primary"></i> <?php echo $dest['country']; ?>
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-primary"><?php echo formatPrice($dest['price']); ?></span>
                        <a href="search.php?from=<?php echo urlencode('Dhaka (DAC)'); ?>&to=<?php echo urlencode($dest['city'] . ' (' . $dest['code'] . ')'); ?>&departure_date=<?php echo date('Y-m-d', strtotime('+1 day')); ?>&passengers=1&class=economy" 
                           class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            Book Now
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Promotional Offers -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Special Offers</h2>
            <p class="text-gray-600">Don't miss out on these amazing deals</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition">
                <div class="bg-accent text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-percent text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">10% OFF</h3>
                <p class="text-gray-600 mb-4">Welcome offer on your first booking</p>
                <span class="inline-block bg-accent text-white px-4 py-2 rounded-lg font-semibold cursor-pointer" 
                      onclick="navigator.clipboard.writeText('WELCOME10'); alert('Promo code copied!')">
                    WELCOME10
                </span>
                <p class="text-xs text-gray-500 mt-2">Click to copy</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition">
                <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tag text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">৳500 OFF</h3>
                <p class="text-gray-600 mb-4">Flat discount on domestic flights</p>
                <span class="inline-block bg-primary text-white px-4 py-2 rounded-lg font-semibold cursor-pointer"
                      onclick="navigator.clipboard.writeText('DOMESTIC500'); alert('Promo code copied!')">
                    DOMESTIC500
                </span>
                <p class="text-xs text-gray-500 mt-2">Click to copy</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition">
                <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Loyalty Rewards</h3>
                <p class="text-gray-600 mb-4">Earn 10 points per ৳100 spent</p>
                <a href="<?php echo isLoggedIn() ? 'user/loyalty.php' : 'user/register.php'; ?>" 
                   class="inline-block bg-green-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-600 transition">
                    Join Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Why Choose FlyOn?</h2>
            <p class="text-gray-600">Your journey begins with us</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-dollar-sign text-primary text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Best Prices</h3>
                <p class="text-gray-600">Competitive rates guaranteed</p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-primary text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Secure Booking</h3>
                <p class="text-gray-600">Your data is safe with us</p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-primary text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">24/7 Support</h3>
                <p class="text-gray-600">We're here to help anytime</p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-primary text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Instant Confirmation</h3>
                <p class="text-gray-600">Get your tickets immediately</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">What Our Customers Say</h2>
            <p class="text-gray-600">Real experiences from real travelers</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $testimonials = [
                ['name' => 'Ahmed Rahman', 'rating' => 5, 'text' => 'Best flight booking experience! Booked Dhaka to Cox\'s Bazar in minutes. Highly recommended!'],
                ['name' => 'Fatima Khan', 'rating' => 5, 'text' => 'The loyalty program is fantastic. Already saved thousands of taka on my trips!'],
                ['name' => 'Karim Hassan', 'rating' => 5, 'text' => 'Customer support is outstanding. They helped me reschedule my Dubai flight without any hassle.'],
            ];
            
            foreach ($testimonials as $test):
            ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center font-bold text-xl">
                        <?php echo substr($test['name'], 0, 1); ?>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-bold text-gray-800"><?php echo $test['name']; ?></h4>
                        <div class="text-yellow-400">
                            <?php for($i = 0; $i < $test['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"<?php echo $test['text']; ?>"</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
