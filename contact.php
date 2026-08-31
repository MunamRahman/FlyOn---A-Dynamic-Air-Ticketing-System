<?php
require_once 'config.php';
require_once 'includes/functions.php';

$pageTitle = 'Contact Us';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $subject, $message])) {
            $success = 'Thank you for contacting us! We will get back to you soon.';
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-4">Contact Us</h1>
        <p class="text-xl text-blue-100">We're here to help</p>
    </div>
</div>

<div class="container mx-auto px-4 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Get in Touch</h2>
            
            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Name *</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Email *</label>
                    <input type="email" name="email" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Subject *</label>
                    <input type="text" name="subject" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Message *</label>
                    <textarea name="message" required rows="5" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary"></textarea>
                </div>
                
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Send Message
                </button>
            </form>
        </div>
        
        <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Contact Information</h2>
            
            <div class="space-y-6">
                <div class="flex items-start">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Address</h3>
                        <p class="text-gray-600">123 Airport Road, Dhaka, Bangladesh</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Phone</h3>
                        <p class="text-gray-600">+880 1234-567890</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Email</h3>
                        <p class="text-gray-600">support@flyon.com</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Business Hours</h3>
                        <p class="text-gray-600">24/7 Customer Support</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
