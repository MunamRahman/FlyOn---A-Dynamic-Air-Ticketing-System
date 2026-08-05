<?php
require_once '../config.php';
require_once '../includes/functions.php';

$pageTitle = 'Register';

if (isLoggedIn()) {
    redirect('/user/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $db = getDB();
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            // Create user
            $hashedPassword = hashPassword($password);
            $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, phone, password, role, status, email_verified) VALUES (?, ?, ?, ?, ?, 'user', 'active', 1)");
            
            if ($stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword])) {
                $userId = $db->lastInsertId();
                
                // Create loyalty account
                $referralCode = strtoupper(substr(uniqid(), -8));
                $stmt = $db->prepare("INSERT INTO loyalty (user_id, referral_code) VALUES (?, ?)");
                $stmt->execute([$userId, $referralCode]);
                
                $success = 'Registration successful! Please login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <i class="fas fa-user-plus text-5xl text-primary mb-4"></i>
            <h2 class="text-3xl font-bold text-gray-800">Create Account</h2>
            <p class="text-gray-600 mt-2">Join FlyOn and start your journey</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
            <a href="login.php" class="underline ml-2">Login now</a>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">First Name *</label>
                    <input type="text" name="first_name" required value="<?php echo htmlspecialchars($firstName ?? ''); ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Last Name *</label>
                    <input type="text" name="last_name" required value="<?php echo htmlspecialchars($lastName ?? ''); ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Email Address *</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Password *</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Confirm Password *</label>
                <input type="password" name="confirm_password" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-user-plus mr-2"></i>Create Account
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">Already have an account? 
                <a href="login.php" class="text-primary hover:underline font-semibold">Sign In</a>
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
