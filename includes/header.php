<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FlyOn - Book your flights with ease. Best prices, instant confirmation.">
    <meta name="keywords" content="flight booking, air tickets, cheap flights, travel">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME . ' - Book Your Flight'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/custom-theme.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/dark-mode.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#016B61',      // Deep teal
                        secondary: '#70B2B2',    // Medium teal
                        accent: '#9ECFD4',       // Light teal
                        light: '#E5E9C5',        // Cream/beige
                    }
                }
            }
        }
        
        // Dark mode initialization
        function initDarkMode() {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const storedPreference = localStorage.getItem('darkMode');
            
            if (storedPreference === 'true' || (storedPreference === null && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        
        // Initialize dark mode on page load
        document.addEventListener('DOMContentLoaded', initDarkMode);
        
        // Watch for system color scheme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (localStorage.getItem('darkMode') === null) {
                initDarkMode();
            }
        });
        
        // Global toggle function
        window.toggleDarkMode = function() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('darkMode', 'false');
            } else {
                html.classList.add('dark');
                localStorage.setItem('darkMode', 'true');
            }
        };
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <?php include 'navbar.php'; ?>
