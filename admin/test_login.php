<?php
/**
 * DEBUG: Test Admin Login
 * Visit this page to see what's wrong
 */

require_once '../config.php';
require_once '../includes/functions.php';

echo "<h1>Admin Login Debug</h1>";
echo "<hr>";

// Test 1: Check if admin exists
echo "<h2>Test 1: Check Admin User</h2>";
$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE email = 'admin@flyon.com'");
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    echo "✅ Admin user EXISTS<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    echo "Email Verified: " . $user['email_verified'] . "<br>";
    echo "Password Hash: " . substr($user['password'], 0, 20) . "...<br>";
} else {
    echo "❌ Admin user DOES NOT EXIST<br>";
    echo "<strong>Solution: Run the SQL to create admin user!</strong><br>";
}

echo "<hr>";

// Test 2: Check password verification
echo "<h2>Test 2: Password Verification</h2>";
$testPassword = 'admin123';
$expectedHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if (password_verify($testPassword, $expectedHash)) {
    echo "✅ Password 'admin123' verifies correctly with expected hash<br>";
} else {
    echo "❌ Password verification FAILED<br>";
}

if ($user) {
    if (password_verify($testPassword, $user['password'])) {
        echo "✅ Password 'admin123' verifies correctly with database hash<br>";
    } else {
        echo "❌ Password 'admin123' DOES NOT match database hash<br>";
        echo "<strong>Solution: Password hash in database is wrong!</strong><br>";
    }
}

echo "<hr>";

// Test 3: Check role
echo "<h2>Test 3: Check Role</h2>";
if ($user) {
    if ($user['role'] === 'admin') {
        echo "✅ Role is 'admin'<br>";
    } else {
        echo "❌ Role is '" . $user['role'] . "' (should be 'admin')<br>";
        echo "<strong>Solution: Update role to 'admin'!</strong><br>";
    }
}

echo "<hr>";

// Test 4: Session test
echo "<h2>Test 4: Session Test</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
    echo "Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session is NOT active<br>";
}

echo "<hr>";

// Test 5: Database connection
echo "<h2>Test 5: Database Connection</h2>";
try {
    $db = getDB();
    echo "✅ Database connection successful<br>";
    echo "Database: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Summary
echo "<h2>Summary</h2>";
if ($user && $user['role'] === 'admin' && password_verify('admin123', $user['password'])) {
    echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3 style='color: #155724;'>✅ Everything looks good!</h3>";
    echo "<p>Admin user exists with correct password and role.</p>";
    echo "<p><strong>Try logging in again at:</strong> <a href='login.php'>admin/login.php</a></p>";
    echo "<p>If login still fails, clear your browser cache (Ctrl+Shift+Delete)</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ Problem Found!</h3>";
    echo "<p><strong>Run this SQL in phpMyAdmin:</strong></p>";
    echo "<pre style='background: #fff; padding: 10px; border: 1px solid #ccc;'>";
    echo "USE flyon_db;\n\n";
    echo "DELETE FROM users WHERE email = 'admin@flyon.com';\n\n";
    echo "INSERT INTO users (id, first_name, last_name, email, password, phone, role, email_verified, created_at)\n";
    echo "VALUES (\n";
    echo "    1,\n";
    echo "    'Admin',\n";
    echo "    'User',\n";
    echo "    'admin@flyon.com',\n";
    echo "    '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',\n";
    echo "    '01700000000',\n";
    echo "    'admin',\n";
    echo "    1,\n";
    echo "    NOW()\n";
    echo ");";
    echo "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='login.php'>← Back to Admin Login</a></p>";
?>
