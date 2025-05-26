<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'backend/db_config.php';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Fetch all categories for the user
$categories_query = "
    SELECT 
        c.*,
        COUNT(t.id) as transaction_count,
        COALESCE(SUM(t.amount), 0) as total_amount
    FROM categories c
    LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ?
    WHERE c.user_id = ? OR c.user_id IS NULL
    GROUP BY c.id
    ORDER BY c.type, c.name
";
$categories_stmt = $conn->prepare($categories_query);
$categories_stmt->execute([$user_id, $user_id]);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate categories by type
$income_categories = array_filter($categories, function($cat) { return $cat['type'] === 'income'; });
$expense_categories = array_filter($categories, function($cat) { return $cat['type'] === 'expense'; });

// Available icons for categories
$available_icons = [
    'trending-up', 'trending-down', 'dollar-sign', 'credit-card', 'shopping-cart', 'shopping-bag',
    'briefcase', 'home', 'car', 'truck', 'plane', 'coffee', 'utensils', 'gift',
    'heart', 'book', 'music', 'film', 'camera', 'smartphone', 'laptop', 'monitor',
    'wifi', 'zap', 'tool', 'wrench', 'package', 'box', 'archive', 'folder',
    'file-text', 'clipboard', 'calendar', 'clock', 'map-pin', 'globe', 'users', 'user',
    'star', 'award', 'target', 'flag', 'tag', 'bookmark', 'shield', 'lock'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - SmartStore Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .icon-option.selected {
            background-color: #dbeafe !important;
            color: #2563eb !important;
            border: 2px solid #2563eb;
        }
        
        .dark .icon-option.selected {
            background-color: #1e3a8a !important;
            color: #60a5fa !important;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">

<?php include 'navbar.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Page Header -->
    <div class="mb-8 fade-in">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-tags text-purple-600 mr-3"></i>
                    Categories
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Manage your income and expense categories to better organize your transactions.
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <button id="addCategoryBtn" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-plus mr-2"></i>
                    Add Category
                </button>
            </div>
        </div>
    </div>

    <!-- Category Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 fade-in">
            <div class="flex items-center">
                <div class="p-4 bg-gradient-to-br from-green-400 to-green-600 rounded-xl shadow-lg">
                    <i class="fas fa-arrow-up text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Income Categories</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo count($income_categories); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 fade-in">
            <div class="flex items-center">
                <div class="p-4 bg-gradient-to-br from-red-400 to-red-600 rounded-xl shadow-lg">
                    <i class="fas fa-arrow-down text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Expense Categories</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo count($expense_categories); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 fade-in">
            <div class="flex items-center">
                <div class="p-4 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl shadow-lg">
                    <i class="fas fa-chart-bar text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Total Categories</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo count($categories); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Income Categories -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 fade-in">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-t-2xl">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-arrow-up text-green-500 mr-2"></i>
                    Income Categories
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($income_categories as $category): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                    <i class="fas fa-<?php echo htmlspecialchars($category['icon'] ?: 'tag'); ?> text-green-600 dark:text-green-400 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo $category['transaction_count']; ?> transactions • KSh <?php echo number_format($category['total_amount'], 2); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>', '<?php echo $category['type']; ?>', '<?php echo htmlspecialchars($category['icon'] ?: 'tag'); ?>')" 
                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition-all dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20" title="Edit Category">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($category['user_id']): // Only show delete for user-created categories ?>
                                    <button onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>')" 
                                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-lg transition-all dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20" title="Delete Category">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($income_categories)): ?>
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-folder-open text-6xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium mb-2">No income categories yet</p>
                            <p class="text-sm">Add your first income category to get started!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Expense Categories -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 fade-in">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-t-2xl">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-arrow-down text-red-500 mr-2"></i>
                    Expense Categories
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($expense_categories as $category): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                    <i class="fas fa-<?php echo htmlspecialchars($category['icon'] ?: 'tag'); ?> text-red-600 dark:text-red-400 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo $category['transaction_count']; ?> transactions • KSh <?php echo number_format($category['total_amount'], 2); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>', '<?php echo $category['type']; ?>', '<?php echo htmlspecialchars($category['icon'] ?: 'tag'); ?>')" 
                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition-all dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20" title="Edit Category">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($category['user_id']): // Only show delete for user-created categories ?>
                                    <button onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>')" 
                                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-lg transition-all dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20" title="Delete Category">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($expense_categories)): ?>
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-folder-open text-6xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium mb-2">No expense categories yet</p>
                            <p class="text-sm">Add your first expense category to get started!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative mx-auto p-6 border w-11/12 max-w-lg shadow-2xl rounded-2xl bg-white dark:bg-gray-800 transform transition-all duration-300">
        <div class="flex items-center justify-between mb-6">
            <h3 id="modalTitle" class="text-2xl font-bold text-gray-900 dark:text-white">Add Category</h3>
            <button id="closeCategoryModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form id="categoryForm" class="space-y-6">
            <input type="hidden" id="categoryId" name="category_id">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tag mr-2"></i>Category Name
                </label>
                <input type="text" id="categoryName" name="name" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200"
                       placeholder="Enter category name">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-list mr-2"></i>Type
                </label>
                <select id="categoryType" name="type" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200">
                    <option value="">Select type</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-icons mr-2"></i>Icon
                </label>
                <div class="grid grid-cols-8 gap-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                    <?php foreach ($available_icons as $icon): ?>
                        <button type="button" onclick="selectIcon('<?php echo $icon; ?>')" 
                                class="icon-option w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 border border-transparent"
                                data-icon="<?php echo $icon; ?>" title="<?php echo $icon; ?>">
                            <i class="fas fa-<?php echo $icon; ?> text-gray-600 dark:text-gray-400"></i>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="categoryIcon" name="icon" required>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Selected icon: <span id="selectedIconName" class="font-medium">None</span>
                </p>
            </div>
            
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-600">
                <button type="button" id="cancelCategory" 
                        class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                        class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:-translate-y-1">
                    <span id="submitText">Add Category</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditMode = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Categories page loaded');
    setupEventListeners();
});

function setupEventListeners() {
    // Add category button
    document.getElementById('addCategoryBtn').addEventListener('click', openAddModal);
    
    // Modal close buttons
    document.getElementById('closeCategoryModal').addEventListener('click', closeModal);
    document.getElementById('cancelCategory').addEventListener('click', closeModal);
    
    // Form submission
    document.getElementById('categoryForm').addEventListener('submit', handleFormSubmit);
    
    // Close modal when clicking outside
    document.getElementById('categoryModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeModal();
        }
    });
    
    // Escape key to close modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !document.getElementById('categoryModal').classList.contains('hidden')) {
            closeModal();
        }
    });
}

function openAddModal() {
    console.log('Opening add modal');
    isEditMode = false;
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('submitText').textContent = 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    clearIconSelection();
    document.getElementById('categoryModal').classList.remove('hidden');
    document.getElementById('categoryName').focus();
}

function editCategory(id, name, type, icon) {
    console.log('Editing category:', { id, name, type, icon });
    isEditMode = true;
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('submitText').textContent = 'Update Category';
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryType').value = type;
    selectIcon(icon);
    document.getElementById('categoryModal').classList.remove('hidden');
    document.getElementById('categoryName').focus();
}

function closeModal() {
    console.log('Closing modal');
    document.getElementById('categoryModal').classList.add('hidden');
    document.getElementById('categoryForm').reset();
    clearIconSelection();
}

function selectIcon(iconName) {
    console.log('Selecting icon:', iconName);
    // Clear previous selection
    clearIconSelection();
    
    // Select new icon
    const iconButton = document.querySelector(`[data-icon="${iconName}"]`);
    if (iconButton) {
        iconButton.classList.add('selected');
        document.getElementById('categoryIcon').value = iconName;
        document.getElementById('selectedIconName').textContent = iconName;
    }
}

function clearIconSelection() {
    document.querySelectorAll('.icon-option').forEach(button => {
        button.classList.remove('selected');
    });
    document.getElementById('categoryIcon').value = '';
    document.getElementById('selectedIconName').textContent = 'None';
}

async function handleFormSubmit(event) {
    event.preventDefault();
    console.log('Form submitted');
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = document.getElementById('submitText').textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    document.getElementById('submitText').textContent = 'Saving...';
    
    const formData = new FormData(event.target);
    const endpoint = isEditMode ? 'backend/update_category.php' : 'backend/add_category.php';
    
    // Debug: Log form data
    console.log('Form data:', Object.fromEntries(formData));
    console.log('Endpoint:', endpoint);
    
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Response data:', result);
        
        if (result.success) {
            Swal.fire({
                title: 'Success!',
                text: result.message,
                icon: 'success',
                confirmButtonText: 'Great!',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(result.message || 'Operation failed');
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: error.message || 'Failed to save category. Please try again.',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        document.getElementById('submitText').textContent = originalText;
    }
}

async function deleteCategory(id, name) {
    console.log('Deleting category:', { id, name });
    
    const result = await Swal.fire({
        title: 'Delete Category?',
        text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });
    
    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('category_id', id);
            
            const response = await fetch('backend/delete_category.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('Delete response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const deleteResult = await response.json();
            console.log('Delete response data:', deleteResult);
            
            if (deleteResult.success) {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Category has been deleted.',
                    icon: 'success',
                    confirmButtonText: 'Ok',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(deleteResult.message || 'Delete failed');
            }
            
        } catch (error) {
            console.error('Delete error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to delete category. Please try again.',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        }
    }
}
</script>

</body>
</html>
