<?php
// Make sure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

// Get current page for active navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="bg-white dark:bg-gray-800 shadow-lg border-b border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'index.php'; ?>" class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <span class="ml-3 text-xl font-bold text-gray-900 dark:text-white">SmartStore Tracker</span>
                    </a>
                </div>

                <?php if ($isLoggedIn): ?>
                <!-- Desktop Navigation Menu -->
                <div class="hidden md:ml-10 md:flex md:space-x-8">
                    <a href="dashboard.php" 
                       class="<?php echo $currentPage == 'dashboard.php' ? 'border-green-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-200'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7"></path>
                        </svg>
                        Dashboard
                    </a>
                    
                    <a href="transactions.php" 
                       class="<?php echo $currentPage == 'transactions.php' ? 'border-green-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-200'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Transactions
                    </a>
                    
                    <a href="reports.php" 
                       class="<?php echo $currentPage == 'reports.php' ? 'border-green-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-200'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Reports
                    </a>
                    
                    <a href="categories.php" 
                       class="<?php echo $currentPage == 'categories.php' ? 'border-green-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-200'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        Categories
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right side of navbar -->
            <div class="flex items-center space-x-4">
                <!-- Theme Toggle -->
                <button id="themeToggle" 
                        class="p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                        title="Toggle dark mode">
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>

                <?php if ($isLoggedIn): ?>
              <!-- User Dropdown -->
                <div class="relative">
                    <button id="userMenuButton" 
                            class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            aria-expanded="false" 
                            aria-haspopup="true">
                        <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium text-sm">
                                <?php echo strtoupper(substr($username, 0, 1)); ?>
                            </span>
                        </div>
                        <span class="ml-2 text-gray-700 dark:text-gray-300 font-medium hidden md:block">
                            <?php echo htmlspecialchars($username); ?>
                        </span>
                        <svg class="ml-1 w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="userDropdown" 
                         class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                            <div class="font-medium"><?php echo htmlspecialchars($username); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Business Owner</div>
                        </div>
                        
                        <a href="profile.php" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile Settings
                        </a>
                        
                        <a href="backend\export_csv.php" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Data
                        </a>
                        
                        <a href="backup.php" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Backup Data
                        </a>
                        
                        <div class="border-t border-gray-200 dark:border-gray-700"></div>
                        
                        <a href="help.php" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Help & Support
                        </a>
                        
                        <a href="auth\logout.php" 
                           class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Sign Out
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <button id="mobileMenuButton" 
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500"
                        aria-expanded="false">
                    <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <?php else: ?>
                <!-- Not logged in - Show login/register buttons -->
                <a href="login.php" 
                   class="text-gray-700 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Sign In
                </a>
                <a href="register.php" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Get Started
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($isLoggedIn): ?>
    <!-- Mobile menu -->
    <div id="mobileMenu" class="hidden md:hidden">
        <div class="pt-2 pb-3 space-y-1 bg-gray-50 dark:bg-gray-700">
            <a href="dashboard.php" 
               class="<?php echo $currentPage == 'dashboard.php' ? 'bg-green-50 dark:bg-green-900/20 border-green-500 text-green-700 dark:text-green-300' : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-300 hover:text-gray-800 dark:hover:text-white'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                Dashboard
            </a>
            <a href="transactions.php" 
               class="<?php echo $currentPage == 'transactions.php' ? 'bg-green-50 dark:bg-green-900/20 border-green-500 text-green-700 dark:text-green-300' : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-300 hover:text-gray-800 dark:hover:text-white'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                Transactions
            </a>
            <a href="reports.php" 
               class="<?php echo $currentPage == 'reports.php' ? 'bg-green-50 dark:bg-green-900/20 border-green-500 text-green-700 dark:text-green-300' : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-300 hover:text-gray-800 dark:hover:text-white'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                Reports
            </a>
            <a href="categories.php" 
               class="<?php echo $currentPage == 'categories.php' ? 'bg-green-50 dark:bg-green-900/20 border-green-500 text-green-700 dark:text-green-300' : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-300 hover:text-gray-800 dark:hover:text-white'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                Categories
            </a>
        </div>
        
        <!-- Mobile Quick Actions -->
        <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <button id="mobileQuickAddBtn" 
                        class="w-full flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Quick Add Transaction
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</nav>

<!-- Quick Add Transaction Modal -->
<?php if ($isLoggedIn): ?>
<div id="quickAddModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Quick Add Transaction</h3>
                <button id="closeQuickAddModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="quickAddForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select id="quickType" name="type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select type</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">KSh</span>
                        <input type="number" id="quickAmount" name="amount" step="0.01" required 
                               class="mt-1 block w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <input type="text" id="quickDescription" name="description" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelQuickAdd" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                        Add Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const isDark = localStorage.getItem('theme') === 'dark' || 
                   (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    if (isDark) {
        document.documentElement.classList.add('dark');
    }
    
    themeToggle.addEventListener('click', function() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });

    <?php if ($isLoggedIn): ?>
    // User dropdown functionality
    const userMenuButton = document.getElementById('userMenuButton');
    const userDropdown = document.getElementById('userDropdown');
    
    userMenuButton.addEventListener('click', function() {
        userDropdown.classList.toggle('hidden');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.add('hidden');
        }
    });
    
    // Mobile menu functionality
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');
    
    mobileMenuButton.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
        
        // Toggle hamburger/close icon
        const hamburger = mobileMenuButton.querySelector('svg:first-child');
        const close = mobileMenuButton.querySelector('svg:last-child');
        
        hamburger.classList.toggle('hidden');
        close.classList.toggle('hidden');
    });
    
    // Quick add modal functionality
    const quickAddBtn = document.getElementById('quickAddBtn');
    const mobileQuickAddBtn = document.getElementById('mobileQuickAddBtn');
    const quickAddModal = document.getElementById('quickAddModal');
    const closeQuickAddModal = document.getElementById('closeQuickAddModal');
    const cancelQuickAdd = document.getElementById('cancelQuickAdd');
    
    function openQuickAddModal() {
        quickAddModal.classList.remove('hidden');
        document.getElementById('quickAmount').focus();
    }
    
    function closeQuickAddModalFunc() {
        quickAddModal.classList.add('hidden');
        document.getElementById('quickAddForm').reset();
    }
    
    if (quickAddBtn) quickAddBtn.addEventListener('click', openQuickAddModal);
    if (mobileQuickAddBtn) mobileQuickAddBtn.addEventListener('click', openQuickAddModal);
    if (closeQuickAddModal) closeQuickAddModal.addEventListener('click', closeQuickAddModalFunc);
    if (cancelQuickAdd) cancelQuickAdd.addEventListener('click', closeQuickAddModalFunc);
    
    // Close modal when clicking outside
    quickAddModal.addEventListener('click', function(event) {
        if (event.target === quickAddModal) {
            closeQuickAddModalFunc();
        }
    });
    
    // Quick add form submission
    document.getElementById('quickAddForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('transaction_date', new Date().toISOString().split('T')[0]);
        formData.append('category', 'Quick Entry'); // Default category for quick entries
        
        try {
            const response = await fetch('add_transaction.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeQuickAddModalFunc();
                // Show success notification
                showNotification('Transaction added successfully!', 'success');
                // Reload page if on dashboard
                if (window.location.pathname.includes('dashboard.php')) {
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                showNotification(result.error || 'Failed to add transaction', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Failed to add transaction', 'error');
        }
    });
    <?php endif; ?>
});

// Notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>