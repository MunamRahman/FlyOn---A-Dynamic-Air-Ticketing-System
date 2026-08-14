<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$pageTitle = 'Manage Promotions';
$db = getDB();

// Fetch all promotions
$stmt = $db->query("
    SELECT p.*,
           COUNT(DISTINCT b.id) as times_used
    FROM promotions p
    LEFT JOIN bookings b ON p.code = b.promo_code
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$promotions = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-tags text-primary"></i> Manage Promotions
        </h1>
        <div class="flex gap-3">
            <a href="add_promotion.php" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                <i class="fas fa-plus-circle"></i> Add New Promotion
            </a>
            <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($promotions as $promo): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 bg-primary text-white font-mono font-bold rounded">
                                <?php echo htmlspecialchars($promo['code']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo htmlspecialchars($promo['description']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                            <?php 
                                if ($promo['discount_type'] === 'percentage') {
                                    echo $promo['discount_value'] . '%';
                                } else {
                                    echo formatPrice($promo['discount_value']);
                                }
                            ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo date('M d, Y', strtotime($promo['valid_from'])); ?><br>
                            <span class="text-xs">to <?php echo date('M d, Y', strtotime($promo['valid_until'])); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $promo['times_used']; ?> / 
                            <?php echo $promo['max_uses'] ?? '∞'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                                $now = date('Y-m-d');
                                $isActive = $promo['status'] === 'active' && 
                                           $now >= $promo['valid_from'] && 
                                           $now <= $promo['valid_until'];
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="editPromo(<?php echo $promo['id']; ?>)" 
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deletePromo(<?php echo $promo['id']; ?>, '<?php echo htmlspecialchars($promo['code']); ?>')" 
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($promotions)): ?>
    <div class="text-center py-12">
        <i class="fas fa-tags text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg">No promotions found</p>
    </div>
    <?php endif; ?>
</div>

<script>
function editPromo(id) {
    alert('Edit promotion #' + id + '\n\nThis will open a form to update promo code details.');
    // TODO: Implement edit modal
}

function deletePromo(id, code) {
    if (confirm('Are you sure you want to delete promo code: ' + code + '?')) {
        alert('Promo code "' + code + '" will be deleted.');
        // TODO: Implement delete via AJAX
    }
}
</script>

<?php include '../includes/footer.php'; ?>
