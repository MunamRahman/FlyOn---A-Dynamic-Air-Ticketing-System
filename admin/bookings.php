<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$pageTitle = 'Manage Bookings';
$db = getDB();

// Fetch all bookings
$stmt = $db->query("
    SELECT b.*, 
           u.first_name, u.last_name, u.email,
           f.flight_number,
           al.name as airline_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    JOIN airlines al ON f.airline_id = al.id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-ticket-alt text-primary"></i> Manage Bookings
        </h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booking Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flight</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passengers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($bookings as $booking): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                            <?php echo htmlspecialchars($booking['booking_reference']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?><br>
                            <span class="text-gray-500 text-xs"><?php echo htmlspecialchars($booking['email']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo htmlspecialchars($booking['flight_number']); ?><br>
                            <span class="text-xs"><?php echo htmlspecialchars($booking['airline_name']); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $booking['total_passengers']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            <?php echo formatPrice($booking['total_price']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                    echo $booking['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                        ($booking['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                ?>">
                                <?php echo ucfirst($booking['payment_status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                    echo $booking['booking_status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                        ($booking['booking_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                                ?>">
                                <?php echo ucfirst($booking['booking_status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="viewBooking('<?php echo $booking['booking_reference']; ?>')" 
                                    class="text-blue-600 hover:text-blue-900 mr-2">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($booking['booking_status'] === 'pending'): ?>
                            <button onclick="approveBooking(<?php echo $booking['id']; ?>, '<?php echo $booking['booking_reference']; ?>')" 
                                    class="text-green-600 hover:text-green-900 mr-2">
                                <i class="fas fa-check-circle"></i> Approve
                            </button>
                            <button onclick="rejectBooking(<?php echo $booking['id']; ?>, '<?php echo $booking['booking_reference']; ?>')" 
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-times-circle"></i> Reject
                            </button>
                            <?php else: ?>
                            <button onclick="updateStatus(<?php echo $booking['id']; ?>)" 
                                    class="text-purple-600 hover:text-purple-900">
                                <i class="fas fa-edit"></i> Status
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($bookings)): ?>
    <div class="text-center py-12">
        <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg">No bookings found</p>
    </div>
    <?php endif; ?>
</div>

<script>
function viewBooking(ref) {
    alert('View booking: ' + ref + '\n\nThis will show full booking details.');
    // TODO: Implement view modal
}

function approveBooking(id, ref) {
    if (!confirm('Approve booking ' + ref + '?\n\nThis will confirm the booking and notify the customer.')) {
        return;
    }

    fetch('approve_booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ booking_id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Booking ' + ref + ' approved!\n\nUser will now see the ticket as confirmed.');
            location.reload();
        } else {
            alert('❌ Unable to approve booking: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error approving booking: ' + error);
    });
}

function rejectBooking(id, ref) {
    const reason = prompt('Reject booking ' + ref + '?\n\nPlease enter rejection reason:');
    if (reason === null) {
        return;
    }

    fetch('reject_booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ booking_id: id, reason: reason || 'No reason provided' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('❌ Booking ' + ref + ' rejected!\n\nReason: ' + (reason || 'No reason provided'));
            location.reload();
        } else {
            alert('❌ Unable to reject booking: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error rejecting booking: ' + error);
    });
}

function updateStatus(id) {
    alert('Update booking status #' + id + '\n\nThis will allow changing payment/booking status.');
    // TODO: Implement status update modal
}
</script>

<?php include '../includes/footer.php'; ?>
