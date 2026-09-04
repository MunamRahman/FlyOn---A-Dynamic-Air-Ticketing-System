<?php
require_once 'config.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us';
?>

<?php include 'includes/header.php'; ?>

<div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-4">About FlyOn</h1>
        <p class="text-xl text-blue-100">Your trusted partner in air travel</p>
    </div>
</div>

<div class="container mx-auto px-4 py-16">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Story</h2>
            <p class="text-gray-600 mb-4">
                Founded in 2025, FlyOn has quickly become one of the leading online flight booking platforms. 
                We are committed to making air travel accessible, affordable, and hassle-free for everyone.
            </p>
            <p class="text-gray-600">
                With partnerships with major airlines worldwide, we offer competitive prices, instant confirmations, 
                and 24/7 customer support to ensure your journey is smooth from start to finish.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-globe text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Global Reach</h3>
                <p class="text-gray-600">Flights to 500+ destinations worldwide</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">1M+ Customers</h3>
                <p class="text-gray-600">Trusted by travelers worldwide</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="bg-accent text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-award text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Award Winning</h3>
                <p class="text-gray-600">Best booking platform 2024</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
