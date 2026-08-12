<?php
require_once '../config.php';
require_once '../includes/functions.php';

$pageTitle = 'Admin Login';
$error = '';

// Redirect if already logged in as admin
if (isLoggedIn() && isAdmin()) {
    redirect('/admin/dashboard.php');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            redirect('/admin/dashboard.php');
        } else {
            $error = 'Invalid admin credentials';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - FlyOn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-plane text-4xl text-purple-600"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">FlyOn Admin</h1>
                <p class="text-purple-200">Secure Administrator Access</p>
            </div>
            
            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                    <i class="fas fa-shield-alt text-purple-600"></i> Admin Login
                </h2>
                
                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-envelope text-purple-600"></i> Email Address
                        </label>
                        <input type="email" name="email" required autofocus
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                               placeholder="admin@flyon.com"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-lock text-purple-600"></i> Password
                        </label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                               placeholder="Enter your password">
                    </div>
                    
                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="mr-2">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 rounded-lg font-semibold text-lg hover:from-purple-700 hover:to-purple-800 transition transform hover:scale-105 shadow-lg">
                        <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                    </button>
                </form>
                
                <!-- Footer Links -->
                <div class="mt-6 text-center">
                    <a href="../index.php" class="text-purple-600 hover:text-purple-800 text-sm">
                        <i class="fas fa-arrow-left"></i> Back to Main Site
                    </a>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="mt-6 text-center text-white text-sm">
                <i class="fas fa-lock"></i> This is a secure area. All access is logged.
            </div>
        </div>
    </div>
</body>
</html>
