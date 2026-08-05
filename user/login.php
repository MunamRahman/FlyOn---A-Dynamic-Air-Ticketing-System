<?php
require_once '../config.php';
require_once '../includes/functions.php';

$pageTitle = 'Login';

if (isLoggedIn()) {
    redirect('/user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Update last login
            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Redirect
            $redirect = $_GET['redirect'] ?? '/user/dashboard.php';
            redirect($redirect);
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/FlyOn/assets/image/online-shopping-concept.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <i class="fas fa-plane-departure text-5xl text-primary mb-4"></i>
            <h2 class="text-3xl font-bold text-gray-800">Welcome Back</h2>
            <p class="text-gray-600 mt-2">Sign in to your FlyOn account</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Email Address</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>"
                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">Don't have an account? 
                <a href="register.php" class="text-primary hover:underline font-semibold">Sign Up</a>
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
