<nav class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50 transition-colors duration-200">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <a href="<?php echo APP_URL; ?>/index.php" class="flex items-center space-x-2">
                <i class="fas fa-plane-departure text-3xl text-primary"></i>
                <span class="text-2xl font-bold text-gray-800 dark:text-white">Fly<span class="text-primary">On</span></span>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="<?php echo APP_URL; ?>/index.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">Home</a>
                <a href="<?php echo APP_URL; ?>/search.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">Search Flights</a>
                <a href="<?php echo APP_URL; ?>/about.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">About</a>
                <a href="<?php echo APP_URL; ?>/contact.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">Contact</a>
                
                <!-- Dark Mode Toggle -->
                <button onclick="toggleDarkMode()" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:inline"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">
                            <i class="fas fa-cog"></i> Admin
                        </a>
                    <?php else: ?>
                        <a href="<?php echo APP_URL; ?>/user/dashboard.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">
                            <i class="fas fa-user"></i> Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo APP_URL; ?>/user/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/user/login.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?php echo APP_URL; ?>/user/register.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-700 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden pb-4">
            <a href="<?php echo APP_URL; ?>/index.php" class="block py-2 text-gray-700 hover:text-primary">Home</a>
            <a href="<?php echo APP_URL; ?>/search.php" class="block py-2 text-gray-700 hover:text-primary">Search Flights</a>
            <a href="<?php echo APP_URL; ?>/about.php" class="block py-2 text-gray-700 hover:text-primary">About</a>
            <a href="<?php echo APP_URL; ?>/contact.php" class="block py-2 text-gray-700 hover:text-primary">Contact</a>
            
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="block py-2 text-gray-700 hover:text-primary">Admin Panel</a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/user/dashboard.php" class="block py-2 text-gray-700 hover:text-primary">Dashboard</a>
                <?php endif; ?>
                <a href="<?php echo APP_URL; ?>/user/logout.php" class="block py-2 text-red-500 hover:text-red-700">Logout</a>
            <?php else: ?>
                <a href="<?php echo APP_URL; ?>/user/login.php" class="block py-2 text-gray-700 hover:text-primary">Login</a>
                <a href="<?php echo APP_URL; ?>/user/register.php" class="block py-2 text-primary hover:text-blue-700">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
    
    // Dark mode toggle function
    function toggleDarkMode() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        
        if (isDark) {
            html.classList.remove('dark');
            localStorage.setItem('darkMode', 'false');
        } else {
            html.classList.add('dark');
            localStorage.setItem('darkMode', 'true');
        }
    }
</script>
