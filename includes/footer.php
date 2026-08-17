    <footer class="bg-gray-900 text-white mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About Section -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-plane-departure text-2xl text-primary"></i>
                        <span class="text-xl font-bold">Fly<span class="text-primary">On</span></span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Your trusted partner for seamless flight bookings. Best prices, instant confirmation, and 24/7 support.
                    </p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-primary transition"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-primary transition"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-primary transition"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-primary transition"><i class="fab fa-linkedin text-xl"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo APP_URL; ?>/about.php" class="text-gray-400 hover:text-primary transition text-sm">About Us</a></li>
                        <li><a href="<?php echo APP_URL; ?>/search.php" class="text-gray-400 hover:text-primary transition text-sm">Search Flights</a></li>
                        <li><a href="<?php echo APP_URL; ?>/contact.php" class="text-gray-400 hover:text-primary transition text-sm">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition text-sm">FAQs</a></li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-primary transition text-sm">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition text-sm">Terms & Conditions</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition text-sm">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition text-sm">Refund Policy</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-map-marker-alt text-primary mt-1"></i>
                            <span class="text-gray-400">123 Airport Road, Dhaka, Bangladesh</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-phone text-primary"></i>
                            <span class="text-gray-400">+880 1234-567890</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-envelope text-primary"></i>
                            <span class="text-gray-400">support@flyon.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> FlyOn. All rights reserved. | Designed with <i class="fas fa-heart text-red-500"></i> for travelers
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Custom JS -->
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
</body>
</html>
