<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$pageTitle = 'Manage Flights';
$db = getDB();

// Fetch all flights
$stmt = $db->query("
    SELECT f.*, 
           al.name as airline_name, 
           dep.name as departure_airport, dep.city as departure_city, dep.code as departure_code,
           arr.name as arrival_airport, arr.city as arrival_city, arr.code as arrival_code
    FROM flights f
    JOIN airlines al ON f.airline_id = al.id
    JOIN airports dep ON f.departure_airport_id = dep.id
    JOIN airports arr ON f.arrival_airport_id = arr.id
    ORDER BY f.departure_time DESC
");
$flights = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-plane text-primary"></i> Manage Flights
        </h1>
        <div class="flex gap-3">
            <a href="add_flight.php" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                <i class="fas fa-plus-circle"></i> Add New Flight
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flight #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Airline</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Arrival</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($flights as $flight): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($flight['flight_number']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($flight['airline_name']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo htmlspecialchars($flight['departure_city']); ?> (<?php echo htmlspecialchars($flight['departure_code']); ?>)
                            <i class="fas fa-arrow-right text-primary mx-2"></i>
                            <?php echo htmlspecialchars($flight['arrival_city']); ?> (<?php echo htmlspecialchars($flight['arrival_code']); ?>)
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M d, Y H:i', strtotime($flight['departure_time'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M d, Y H:i', strtotime($flight['arrival_time'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                            <?php echo formatPrice($flight['base_price']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $flight['status'] === 'scheduled' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($flight['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="editFlight(<?php echo $flight['id']; ?>)" 
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteFlight(<?php echo $flight['id']; ?>)" 
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-ban"></i> Cancel
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($flights)): ?>
    <div class="text-center py-12">
        <i class="fas fa-plane-slash text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg">No flights found</p>
    </div>
    <?php endif; ?>
</div>

<script>
function editFlight(id) {
    alert('Edit flight #' + id + '\n\nEdit functionality will open a modal to update flight details.');
    // TODO: Implement edit modal
}

function deleteFlight(id) {
    const reason = prompt('Cancel this flight?\n\nPlease enter cancellation reason:');
    if (reason) {
        fetch('cancel_flight.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'flight_id=' + id + '&reason=' + encodeURIComponent(reason)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Flight cancelled successfully!\n\n' + 
                      'Affected bookings: ' + data.affected_bookings + '\n' +
                      'All customers will be notified and refunded.');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error cancelling flight: ' + error);
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
