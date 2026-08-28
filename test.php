<?php
// Simple test page - Does FlyOn work?
echo "<h1>✅ FlyOn Test Page</h1>";
echo "<p>If you see this, Apache and PHP are working!</p>";
echo "<hr>";

echo "<h2>Test Links:</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Homepage</a></li>";
echo "<li><a href='admin/login.php'>Admin Login</a></li>";
echo "<li><a href='admin/dashboard.php'>Admin Dashboard</a></li>";
echo "<li><a href='admin/flights.php'>Manage Flights</a></li>";
echo "<li><a href='admin/bookings.php'>Manage Bookings</a></li>";
echo "<li><a href='admin/users.php'>Manage Users</a></li>";
echo "<li><a href='admin/promotions.php'>Manage Promotions</a></li>";
echo "<li><a href='check_admin.php'>Check Admin</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h2>Server Info:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current File: " . __FILE__ . "</p>";
?>
