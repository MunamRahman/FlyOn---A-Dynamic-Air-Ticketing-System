<?php
/**
 * Test Connection - Verify PHP and Database Connection
 * Access this file to test if the server is working
 */

echo "<!DOCTYPE html><html><head><title>FlyOn Connection Test</title></head><body>";
echo "<h1>FlyOn Connection Test</h1>";
echo "<hr>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Status: " . (version_compare(phpversion(), '8.0.0', '>=') ? "✅ OK" : "❌ PHP 8.0+ required") . "</p>";

// Test 2: File System
echo "<h2>2. File System</h2>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>index.php exists: " . (file_exists(__DIR__ . '/index.php') ? "✅ Yes" : "❌ No") . "</p>";
echo "<p>config.php exists: " . (file_exists(__DIR__ . '/config.php') ? "✅ Yes" : "❌ No") . "</p>";

// Test 3: Database Connection
echo "<h2>3. Database Connection</h2>";
try {
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
        require_once __DIR__ . '/includes/db_connect.php';
        
        $db = getDB();
        echo "<p>Database Connection: ✅ Success</p>";
        
        // Test query
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "<p>Users in database: " . $result['count'] . "</p>";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM flights");
        $result = $stmt->fetch();
        echo "<p>Flights in database: " . $result['count'] . "</p>";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM airlines");
        $result = $stmt->fetch();
        echo "<p>Airlines in database: " . $result['count'] . "</p>";
        
    } else {
        echo "<p>Database Connection: ❌ config.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p>Database Connection: ❌ Error - " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 4: Apache Modules
echo "<h2>4. Apache Modules</h2>";
echo "<p>mod_rewrite: " . (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? "✅ Enabled" : "⚠️ Unknown/Not Available") . "</p>";

// Test 5: URL Configuration
echo "<h2>5. URL Configuration</h2>";
echo "<p>APP_URL: " . (defined('APP_URL') ? APP_URL : 'Not defined') . "</p>";
echo "<p>Current URL: " . (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : 'Unknown') . "</p>";

// Test 6: Directories
echo "<h2>6. Required Directories</h2>";
$dirs = ['uploads', 'cache', 'includes', 'assets', 'admin', 'user', 'booking', 'api'];
foreach ($dirs as $dir) {
    $exists = is_dir(__DIR__ . '/' . $dir);
    echo "<p>$dir/: " . ($exists ? "✅ Exists" : "❌ Missing") . "</p>";
}

echo "<hr>";
echo "<h2>Quick Links</h2>";
echo "<p><a href='index.php'>Home Page (index.php)</a></p>";
echo "<p><a href='admin/login.php'>Admin Login</a></p>";
echo "<p><a href='user/login.php'>User Login</a></p>";
echo "<p><a href='search.php'>Search Flights</a></p>";

echo "</body></html>";
?>

