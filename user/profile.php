<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'My Profile';
$db = getDB();
$userId = getCurrentUserId();
$user = getCurrentUser();

// Handle profile update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $country = sanitize($_POST['country'] ?? '');
    
    if (!empty($firstName) && !empty($lastName)) {
        try {
            $stmt = $db->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?, date_of_birth = ?, 
                    gender = ?, address = ?, city = ?, country = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $firstName, $lastName, $phone, $dateOfBirth, 
                $gender, $address, $city, $country, $userId
            ]);
            
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user = getCurrentUser();
        } catch (PDOException $e) {
            $error = 'Error updating profile. Please try again.';
        }
    } else {
        $error = 'First name and last name are required.';
    }
}

// Get loyalty info
$stmt = $db->prepare("SELECT * FROM loyalty WHERE user_id = ?");
$stmt->execute([$userId]);
$loyalty = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">
        <i class="fas fa-user-circle text-primary"></i> My Profile
    </h1>

    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Personal Information</h2>
                
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <!-- Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="first_name" required
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="last_name" required
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Email (Read-only) -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Email Address
                        </label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                        <p class="text-sm text-gray-500 mt-1">Email cannot be changed</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Phone Number
                        </label>
                        <input type="tel" name="phone"
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               placeholder="+880 1700-000000"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Date of Birth & Gender -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                Date of Birth
                            </label>
                            <input type="date" name="date_of_birth"
                                   value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                Gender
                            </label>
                            <select name="gender"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Address
                        </label>
                        <textarea name="address" rows="3"
                                  placeholder="Street address, apartment, suite, etc."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <!-- City & Country -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                City
                            </label>
                            <input type="text" name="city"
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
                                   placeholder="e.g., Dhaka"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                Country
                            </label>
                            <input type="text" name="country"
                                   value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>"
                                   placeholder="e.g., Bangladesh"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button type="submit" 
                                class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="dashboard.php" 
                           class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 transition font-semibold">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Account Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Account Info</h2>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Member Since</p>
                        <p class="font-semibold"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email Verified</p>
                        <p class="font-semibold">
                            <?php if ($user['email_verified']): ?>
                                <i class="fas fa-check-circle text-green-500"></i> Verified
                            <?php else: ?>
                                <i class="fas fa-times-circle text-red-500"></i> Not Verified
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Account Type</p>
                        <p class="font-semibold"><?php echo ucfirst($user['role']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Loyalty Status -->
            <?php if ($loyalty): ?>
            <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 text-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">
                    <i class="fas fa-star"></i> Loyalty Status
                </h2>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-yellow-100 text-sm">Current Tier</p>
                        <p class="text-2xl font-bold"><?php echo ucfirst($loyalty['tier']); ?></p>
                    </div>
                    <div>
                        <p class="text-yellow-100 text-sm">Available Points</p>
                        <p class="text-3xl font-bold"><?php echo number_format($loyalty['points'] ?? 0); ?></p>
                    </div>
                    <a href="loyalty.php" class="block text-center bg-white text-yellow-600 py-2 rounded-lg hover:bg-yellow-50 transition font-semibold mt-4">
                        View Rewards
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Security -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Security</h2>
                
                <div class="space-y-3">
                    <button onclick="changePassword()" 
                            class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <button onclick="enable2FA()" 
                            class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-shield-alt"></i> Enable 2FA
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changePassword() {
    alert('Change Password\n\nThis will open a form to update your password.');
    // TODO: Implement password change modal
}

function enable2FA() {
    alert('Enable Two-Factor Authentication\n\nThis will set up 2FA for your account.');
    // TODO: Implement 2FA setup
}
</script>

<?php include '../includes/footer.php'; ?>
