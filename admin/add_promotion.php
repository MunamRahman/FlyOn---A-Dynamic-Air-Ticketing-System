<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$pageTitle = 'Add New Promotion';
$db = getDB();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $code = strtoupper(sanitize($_POST['code'] ?? ''));
    $description = sanitize($_POST['description'] ?? '');
    $discountType = $_POST['discount_type'] ?? 'percentage';
    $discountValue = $_POST['discount_value'] ?? 0;
    $validFrom = $_POST['valid_from'] ?? '';
    $validUntil = $_POST['valid_until'] ?? '';
    $maxUses = $_POST['max_uses'] ?? null;
    $minPurchase = $_POST['min_purchase'] ?? 0;
    $status = $_POST['status'] ?? 'active';
    
    if (!empty($code) && !empty($description) && $discountValue > 0 && $validFrom && $validUntil) {
        try {
            // Check if code already exists
            $stmt = $db->prepare("SELECT id FROM promotions WHERE code = ?");
            $stmt->execute([$code]);
            if ($stmt->fetch()) {
                $error = "Promo code '$code' already exists. Please use a different code.";
            } else {
                // Insert promotion
                $stmt = $db->prepare("
                    INSERT INTO promotions (
                        code, description, discount_type, discount_value,
                        valid_from, valid_until, max_uses, min_purchase_amount, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $code, $description, $discountType, $discountValue,
                    $validFrom, $validUntil, $maxUses, $minPurchase, $status
                ]);
                
                $success = "Promotion '$code' added successfully!";
                
                // Clear form
                $_POST = [];
            }
        } catch (PDOException $e) {
            $error = "Error adding promotion: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-plus-circle text-primary"></i> Add New Promotion
        </h1>
        <a href="promotions.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left"></i> Back to Promotions
        </a>
    </div>

    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <a href="promotions.php" class="underline ml-2">View All Promotions</a>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <!-- Promo Code & Description -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        Promo Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code" required
                           placeholder="e.g., SAVE20, SUMMER2024"
                           value="<?php echo htmlspecialchars($_POST['code'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary uppercase">
                    <p class="text-sm text-gray-500 mt-1">Will be converted to uppercase</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="active" <?php echo (($_POST['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (($_POST['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea name="description" required rows="3"
                          placeholder="e.g., Get 20% off on all domestic flights"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <!-- Discount Type & Value -->
            <div class="border-t pt-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Discount Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Discount Type <span class="text-red-500">*</span>
                        </label>
                        <select name="discount_type" id="discountType" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="percentage" <?php echo (($_POST['discount_type'] ?? 'percentage') == 'percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
                            <option value="fixed" <?php echo (($_POST['discount_type'] ?? '') == 'fixed') ? 'selected' : ''; ?>>Fixed Amount (৳)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Discount Value <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="discount_value" id="discountValue" required min="0" step="0.01"
                               placeholder="e.g., 20 or 500"
                               value="<?php echo htmlspecialchars($_POST['discount_value'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <p class="text-sm text-gray-500 mt-1" id="discountHint">Enter percentage (e.g., 20 for 20%)</p>
                    </div>
                </div>
            </div>

            <!-- Validity Period -->
            <div class="border-t pt-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Validity Period</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Valid From <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="valid_from" required
                               value="<?php echo htmlspecialchars($_POST['valid_from'] ?? date('Y-m-d')); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Valid Until <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="valid_until" required
                               value="<?php echo htmlspecialchars($_POST['valid_until'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            <!-- Usage Limits -->
            <div class="border-t pt-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Usage Limits</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Maximum Uses
                        </label>
                        <input type="number" name="max_uses" min="0"
                               placeholder="Leave empty for unlimited"
                               value="<?php echo htmlspecialchars($_POST['max_uses'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <p class="text-sm text-gray-500 mt-1">Leave empty for unlimited uses</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Minimum Purchase Amount (৳)
                        </label>
                        <input type="number" name="min_purchase" min="0" step="0.01"
                               placeholder="e.g., 5000"
                               value="<?php echo htmlspecialchars($_POST['min_purchase'] ?? '0'); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <p class="text-sm text-gray-500 mt-1">Minimum amount required to use promo</p>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4 pt-6">
                <button type="submit" 
                        class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-plus-circle"></i> Add Promotion
                </button>
                <a href="promotions.php" 
                   class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 transition font-semibold">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Update discount hint based on type
document.getElementById('discountType').addEventListener('change', function() {
    const hint = document.getElementById('discountHint');
    const valueInput = document.getElementById('discountValue');
    
    if (this.value === 'percentage') {
        hint.textContent = 'Enter percentage (e.g., 20 for 20%)';
        valueInput.placeholder = 'e.g., 20';
        valueInput.max = '100';
    } else {
        hint.textContent = 'Enter fixed amount in BDT (e.g., 500)';
        valueInput.placeholder = 'e.g., 500';
        valueInput.removeAttribute('max');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
