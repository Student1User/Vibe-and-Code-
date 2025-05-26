<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'backend/db_config.php';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get categories for filter dropdown
$categories_query = "SELECT DISTINCT name FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY name";
$categories_stmt = $conn->prepare($categories_query);
$categories_stmt->execute([$user_id]);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get summary statistics
$summary_query = "
    SELECT 
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
        COUNT(*) as total_transactions
    FROM transactions 
    WHERE user_id = ?
";
$summary_stmt = $conn->prepare($summary_query);
$summary_stmt->execute([$user_id]);
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

$balance = $summary['total_income'] - $summary['total_expenses'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - SmartStore Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">

<?php include 'navbar.php'; ?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Transactions</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Track and analyze your financial transactions</p>
            </div>
            <div class="mt-4 md:mt-0">
                <button id="exportBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export Data
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                    <i class="fas fa-arrow-up text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Income</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">KSh <?php echo number_format($summary['total_income'], 2); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-lg">
                    <i class="fas fa-arrow-down text-red-600 dark:text-red-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Expenses</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">KSh <?php echo number_format($summary['total_expenses'], 2); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="p-3 <?php echo $balance >= 0 ? 'bg-blue-100 dark:bg-blue-900/20' : 'bg-orange-100 dark:bg-orange-900/20'; ?> rounded-lg">
                    <i class="fas fa-wallet <?php echo $balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400'; ?> text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Balance</p>
                    <p class="text-2xl font-bold <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">KSh <?php echo number_format($balance, 2); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                    <i class="fas fa-list text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($summary['total_transactions']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Monthly Trends Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Monthly Trends</h3>
            <div class="h-64">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Category Breakdown Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Category Breakdown</h3>
            <div class="h-64">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filter Transactions</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="searchInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search transactions..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label for="typeFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                <select id="typeFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Types</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>

            <div>
                <label for="categoryFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select id="categoryFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="dateFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                <input type="date" id="dateFilter" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button id="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-filter mr-2"></i>Apply Filters
            </button>
            <button id="clearFilters" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-times mr-2"></i>Clear Filters
            </button>
            <button id="refreshData" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-refresh mr-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Transaction History</h3>
                <div class="mt-2 md:mt-0 flex items-center space-x-2">
                    <span id="transactionCount" class="text-sm text-gray-600 dark:text-gray-400">Loading...</span>
                    <div id="loadingSpinner" class="hidden">
                        <i class="fas fa-spinner fa-spin text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-100" onclick="sortTable('date')">
                                <span>Date</span>
                                <i class="fas fa-sort text-xs"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-100" onclick="sortTable('amount')">
                                <span>Amount</span>
                                <i class="fas fa-sort text-xs"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Receipt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="transactionsTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Transactions will be loaded here -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalRecords">0</span> results
                </span>
            </div>
            <div class="flex items-center space-x-2">
                <button id="prevPage" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-500 disabled:opacity-50">
                    Previous
                </button>
                <span id="pageNumbers" class="flex space-x-1">
                    <!-- Page numbers will be inserted here -->
                </span>
                <button id="nextPage" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-500 disabled:opacity-50">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div id="transactionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-lg bg-white dark:bg-gray-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Transaction Details</h3>
            <button id="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modalContent">
            <!-- Transaction details will be loaded here -->
        </div>
    </div>
</div>

<script>
// Global variables
let currentPage = 1;
let itemsPerPage = 10;
let allTransactions = [];
let filteredTransactions = [];
let sortField = 'date';
let sortDirection = 'desc';

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    loadTransactions();
    setupEventListeners();
});

function setupEventListeners() {
    // Filter controls
    document.getElementById('applyFilters').addEventListener('click', applyFilters);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    document.getElementById('refreshData').addEventListener('click', loadTransactions);
    document.getElementById('exportBtn').addEventListener('click', exportData);
    
    // Search input with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    });
    
    // Modal controls
    document.getElementById('closeModal').addEventListener('click', closeModal);
    
    // Pagination
    document.getElementById('prevPage').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('nextPage').addEventListener('click', () => changePage(currentPage + 1));
}

async function loadTransactions() {
    showLoading(true);
    
    try {
        const response = await fetch('backend/get_transactions.php');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        allTransactions = data.transactions || [];
        filteredTransactions = [...allTransactions];
        
        updateTransactionTable();
        updateCharts(data);
        
    } catch (error) {
        console.error('Error loading transactions:', error);
        Swal.fire('Error', 'Failed to load transactions: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const categoryFilter = document.getElementById('categoryFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    filteredTransactions = allTransactions.filter(transaction => {
        // Search filter
        if (searchTerm && !transaction.description.toLowerCase().includes(searchTerm)) {
            return false;
        }
        
        // Type filter
        if (typeFilter && transaction.type !== typeFilter) {
            return false;
        }
        
        // Category filter
        if (categoryFilter && transaction.category !== categoryFilter) {
            return false;
        }
        
        // Date filter
        if (dateFilter && transaction.transaction_date !== dateFilter) {
            return false;
        }
        
        return true;
    });
    
    currentPage = 1;
    updateTransactionTable();
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('dateFilter').value = '';
    
    filteredTransactions = [...allTransactions];
    currentPage = 1;
    updateTransactionTable();
}

function updateTransactionTable() {
    const tbody = document.getElementById('transactionsTableBody');
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageTransactions = filteredTransactions.slice(startIndex, endIndex);
    
    if (pageTransactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg font-medium">No transactions found</p>
                        <p class="text-sm">Try adjusting your filters or add some transactions</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        tbody.innerHTML = pageTransactions.map(transaction => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    ${formatDate(transaction.transaction_date)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        transaction.type === 'income' 
                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' 
                            : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
                    }">
                        <i class="fas fa-${transaction.type === 'income' ? 'arrow-up' : 'arrow-down'} mr-1"></i>
                        ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    ${transaction.category || 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${
                    transaction.type === 'income' ? 'text-green-600' : 'text-red-600'
                }">
                    KSh ${parseFloat(transaction.amount).toLocaleString('en-KE', {minimumFractionDigits: 2})}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs truncate">
                    ${transaction.description || 'No description'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    ${transaction.receipt_image ? 
                        `<button onclick="viewReceipt('${transaction.receipt_image}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            <i class="fas fa-image mr-1"></i>View
                        </button>` : 
                        '<span class="text-gray-400">No receipt</span>'
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                        <button onclick="viewTransaction(${transaction.id})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editTransaction(${transaction.id})" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteTransaction(${transaction.id})" class="text-red-600 hover:text-red-800 dark:text-red-400" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    updatePagination();
    updateTransactionCount();
}

function updatePagination() {
    const totalPages = Math.ceil(filteredTransactions.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage + 1;
    const endIndex = Math.min(currentPage * itemsPerPage, filteredTransactions.length);
    
    document.getElementById('showingFrom').textContent = filteredTransactions.length > 0 ? startIndex : 0;
    document.getElementById('showingTo').textContent = endIndex;
    document.getElementById('totalRecords').textContent = filteredTransactions.length;
    
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;
    
    // Generate page numbers
    const pageNumbers = document.getElementById('pageNumbers');
    pageNumbers.innerHTML = '';
    
    for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.className = `px-3 py-1 text-sm rounded ${
            i === currentPage 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500'
        }`;
        button.onclick = () => changePage(i);
        pageNumbers.appendChild(button);
    }
}

function changePage(page) {
    const totalPages = Math.ceil(filteredTransactions.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        updateTransactionTable();
    }
}

function updateTransactionCount() {
    document.getElementById('transactionCount').textContent = 
        `${filteredTransactions.length} transaction${filteredTransactions.length !== 1 ? 's' : ''}`;
}

function sortTable(field) {
    if (sortField === field) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortField = field;
        sortDirection = 'desc';
    }
    
    filteredTransactions.sort((a, b) => {
        let aVal = a[field];
        let bVal = b[field];
        
        if (field === 'amount') {
            aVal = parseFloat(aVal);
            bVal = parseFloat(bVal);
        } else if (field === 'date') {
            aVal = new Date(a.transaction_date);
            bVal = new Date(b.transaction_date);
        }
        
        if (sortDirection === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
    
    updateTransactionTable();
}

function updateCharts(data) {
    // Update monthly trends chart
    if (data.monthly_data) {
        updateMonthlyChart(data.monthly_data);
    }
    
    // Update category breakdown chart
    if (data.category_breakdown) {
        updateCategoryChart(data.category_breakdown);
    }
}

function updateMonthlyChart(monthlyData) {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    // Process data for chart
    const months = [...new Set(monthlyData.map(item => item.month))].sort();
    const incomeData = months.map(month => {
        const item = monthlyData.find(d => d.month === month && d.type === 'income');
        return item ? parseFloat(item.total) : 0;
    });
    const expenseData = months.map(month => {
        const item = monthlyData.find(d => d.month === month && d.type === 'expense');
        return item ? parseFloat(item.total) : 0;
    });
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months.map(month => {
                const date = new Date(month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Income',
                data: incomeData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }, {
                label: 'Expenses',
                data: expenseData,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KSh ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': KSh ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function updateCategoryChart(categoryData) {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    // Process data for chart (top 10 categories)
    const sortedData = categoryData.sort((a, b) => parseFloat(b.total) - parseFloat(a.total)).slice(0, 10);
    const labels = sortedData.map(item => item.category);
    const data = sortedData.map(item => parseFloat(item.total));
    const colors = [
        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
        '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': KSh ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function viewTransaction(id) {
    const transaction = allTransactions.find(t => t.id == id);
    if (!transaction) return;
    
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        transaction.type === 'income' 
                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' 
                            : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
                    }">
                        <i class="fas fa-${transaction.type === 'income' ? 'arrow-up' : 'arrow-down'} mr-1"></i>
                        ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                    <p class="text-lg font-semibold ${transaction.type === 'income' ? 'text-green-600' : 'text-red-600'}">
                        KSh ${parseFloat(transaction.amount).toLocaleString('en-KE', {minimumFractionDigits: 2})}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <p class="text-gray-900 dark:text-white">${transaction.category || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <p class="text-gray-900 dark:text-white">${formatDate(transaction.transaction_date)}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <p class="text-gray-900 dark:text-white">${transaction.description || 'No description provided'}</p>
            </div>
            ${transaction.receipt_image ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Receipt</label>
                    <img src="${transaction.receipt_image}" alt="Receipt" class="max-w-full h-auto rounded-lg border">
                </div>
            ` : ''}
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Created: ${formatDateTime(transaction.created_at)}
            </div>
        </div>
    `;
    
    document.getElementById('transactionModal').classList.remove('hidden');
}

function editTransaction(id) {
    // Redirect to edit page or open edit modal
    window.location.href = `edit_transaction.php?id=${id}`;
}

async function deleteTransaction(id) {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'This transaction will be permanently deleted!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch('backend/delete_transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire('Deleted!', 'Transaction has been deleted.', 'success');
                loadTransactions(); // Reload data
            } else {
                throw new Error(data.message || 'Failed to delete transaction');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to delete transaction: ' + error.message, 'error');
        }
    }
}

function viewReceipt(imagePath) {
    Swal.fire({
        title: 'Receipt',
        imageUrl: imagePath,
        imageAlt: 'Transaction Receipt',
        showCloseButton: true,
        showConfirmButton: false,
        width: 'auto',
        customClass: {
            image: 'max-h-96'
        }
    });
}

function closeModal() {
    document.getElementById('transactionModal').classList.add('hidden');
}

function exportData() {
    // Create CSV content
    const headers = ['Date', 'Type', 'Category', 'Amount', 'Description'];
    const csvContent = [
        headers.join(','),
        ...filteredTransactions.map(t => [
            t.transaction_date,
            t.type,
            t.category || '',
            t.amount,
            `"${(t.description || '').replace(/"/g, '""')}"`
        ].join(','))
    ].join('\n');
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `transactions_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    Swal.fire('Success', 'Transactions exported successfully!', 'success');
}

function showLoading(show) {
    const spinner = document.getElementById('loadingSpinner');
    if (show) {
        spinner.classList.remove('hidden');
    } else {
        spinner.classList.add('hidden');
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

</body>
</html>
