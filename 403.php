<?php
require_once 'config.php';
$pageTitle = '403 - Access Forbidden';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-9xl font-bold text-red-600">403</h1>
            <h2 class="text-3xl font-semibold text-gray-800 mt-4">Access Forbidden</h2>
            <p class="text-gray-600 mt-2">You don't have permission to access this resource.</p>
            <a href="<?php echo APP_URL; ?>" class="inline-block mt-6 bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-home mr-2"></i> Go Home
            </a>
        </div>
    </div>
</body>
</html>

