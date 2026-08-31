<?php
// Simple check - Does admin exist?
require_once 'config.php';
require_once 'includes/db_connect.php';

$db = getDB();

echo "<h1>Admin Check</h1>";
echo "<hr>";

// Check if admin exists
$stmt = $db->query("SELECT * FROM users WHERE email = 'admin@flyon.com'");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "<h2 style='color: green;'>✅ Admin EXISTS</h2>";
    echo "<pre>";
    print_r($admin);
    echo "</pre>";
    
    // Test password
    $testPass = 'admin123';
    if (password_verify($testPass, $admin['password'])) {
        echo "<h2 style='color: green;'>✅ Password 'admin123' is CORRECT</h2>";
    } else {
        echo "<h2 style='color: red;'>❌ Password 'admin123' is WRONG</h2>";
        echo "<p>Run this SQL:</p>";
        echo "<pre>";
        echo "UPDATE users SET password = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@flyon.com';";
        echo "</pre>";
    }
} else {
    echo "<h2 style='color: red;'>❌ Admin DOES NOT EXIST</h2>";
    echo "<p>Run this SQL in phpMyAdmin:</p>";
    echo "<pre>";
    echo "INSERT INTO users (first_name, last_name, email, password, phone, role, email_verified) \n";
    echo "VALUES ('Admin', 'User', 'admin@flyon.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000000', 'admin', 1);";
    echo "</pre>";
}

echo "<hr>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
?>
