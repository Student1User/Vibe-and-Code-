<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'backend/db_config.php';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get date range from URL parameters or set defaults
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month
$report_type = $_GET['report_type'] ?? 'summary';

// Fetch summary data for the selected period
$summary_query = "
    SELECT 
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
        COUNT(CASE WHEN type = 'income' THEN 1 END) as income_count,
        COUNT(CASE WHEN type = 'expense' THEN 1 END) as expense_count
    FROM transactions 
    WHERE user_id = ? AND date BETWEEN ? AND ?
";
$summary_stmt = $conn->prepare($summary_query);
$summary_stmt->execute([$user_id, $start_date, $end_date]);
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

$total_income = $summary['total_income'] ?? 0;
$total_expenses = $summary['total_expenses'] ?? 0;
$net_profit = $total_income - $total_expenses;
$income_count = $summary['income_count'] ?? 0;
$expense_count = $summary['expense_count'] ?? 0;

// Fetch category breakdown
$category_query = "
    SELECT 
        c.name as category_name,
        c.type,
        SUM(t.amount) as total_amount,
        COUNT(t.id) as transaction_count,
        AVG(t.amount) as avg_amount
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = ? AND t.date BETWEEN ? AND ?
    GROUP BY c.id, c.name, c.type
    ORDER BY total_amount DESC
";
$category_stmt = $conn->prepare($category_query);
$category_stmt->execute([$user_id, $start_date, $end_date]);
$category_breakdown = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch daily trends
$daily_query = "
    SELECT 
        date,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as daily_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as daily_expenses
    FROM transactions 
    WHERE user_id = ? AND date BETWEEN ? AND ?
    GROUP BY date
    ORDER BY date
";
$daily_stmt = $conn->prepare($daily_query);
$daily_stmt->execute([$user_id, $start_date, $end_date]);
$daily_trends = $daily_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch monthly comparison (last 12 months)
$monthly_query = "
    SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as monthly_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as monthly_expenses
    FROM transactions 
    WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month
";
$monthly_stmt = $conn->prepare($monthly_query);
$monthly_stmt->execute([$user_id]);
$monthly_trends = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch top transactions
$top_transactions_query = "
    SELECT 
        t.*,
        c.name as category_name
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = ? AND t.date BETWEEN ? AND ?
    ORDER BY t.amount DESC
    LIMIT 10
";
$top_stmt = $conn->prepare($top_transactions_query);
$top_stmt->execute([$user_id, $start_date, $end_date]);
$top_transactions = $top_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - SmartStore Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">

<?php include 'navbar.php'; ?>

<div class="container mx-auto mt-8 px-4 max-w-7xl">
    <!-- Page Header -->
    <div class="mb-8 fade-in">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-chart-bar text-blue-600 mr-3"></i>
                    Financial Reports
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Analyze your business performance from <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?>
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button id="printReportBtn" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-print mr-2"></i>
                    Print Report
                </button>
                <button id="exportReportBtn" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-download mr-2"></i>
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-8 border border-gray-200 dark:border-gray-700 fade-in">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-filter text-indigo-600 mr-2"></i>
            Report Filters
        </h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Report Type</label>
                <select name="report_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-200">
                    <option value="summary" <?php echo $report_type === 'summary' ? 'selected' : ''; ?>>Summary</option>
                    <option value="detailed" <?php echo $report_type === 'detailed' ? 'selected' : ''; ?>>Detailed</option>
                    <option value="trends" <?php echo $report_type === 'trends' ? 'selected' : ''; ?>>Trends</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-search mr-2"></i>Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 fade-in">
            <div class="flex items-center">
                <div class="p-4 bg-gradient-to-br from-green-400 to-green-600 rounded-xl shadow-lg">
                    <i class="fas fa-arrow-up text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Total Income</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">KSh <?php echo number_format($total_income, 2); ?></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo $income_count; ?> transactions</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 fade-in">
            <div class="flex items-center">
                <div class="p-4 bg-gradient-to-br from-red-400 to-red-600 rounded-xl shadow-lg">
                    <i class="fas fa-arrow-down text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Total Expenses</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">KSh <?php echo number_format($total_expenses, 2); ?></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo $expense_count; ?> transactions</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 fade-in">
            <div class="flex items-center">
                <div class="p-4 bg-gradient-to-br <?php echo $net_profit >= 0 ? 'from-blue-400 to-blue-600' : 'from-orange-400 to-orange-600'; ?> rounded-xl shadow-lg">
                    <i class="fas fa-chart-line text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Net Profit</p>
                    <p class="text-2xl font-bold <?php echo $net_profit >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                        KSh <?php echo number_format($net_profit, 2); ?>
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo $net_profit >= 0 ? 'Profit' : 'Loss'; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 fade-in">
            <div class="flex items-center">
                <div class="p-4 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl shadow-lg">
                    <i class="fas fa-percentage text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Profit Margin</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        <?php echo $total_income > 0 ? number_format(($net_profit / $total_income) * 100, 1) : '0'; ?>%
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo $net_profit >= 0 ? 'Healthy' : 'Needs attention'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Daily Trends Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 fade-in">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                    Daily Trends
                </h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">Selected period</div>
            </div>
            <div class="chart-container">
                <canvas id="dailyTrendsChart"></canvas>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 fade-in">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-chart-pie text-purple-600 mr-2"></i>
                    Category Breakdown
                </h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">Top categories</div>
            </div>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 mb-8 fade-in">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                12-Month Trends
            </h3>
            <div class="text-sm text-gray-500 dark:text-gray-400">Last 12 months</div>
        </div>
        <div class="chart-container">
            <canvas id="monthlyTrendsChart"></canvas>
        </div>
    </div>

    <!-- Category Analysis Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-8 fade-in">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-table text-indigo-600 mr-2"></i>
                Category Analysis
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Transactions</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Average</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">% of Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($category_breakdown)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-chart-pie text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No category data available</p>
                                    <p class="text-sm">Add some transactions to see category analysis</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($category_breakdown as $category): ?>
                            <?php 
                            $total_for_type = $category['type'] === 'income' ? $total_income : $total_expenses;
                            $percentage = $total_for_type > 0 ? ($category['total_amount'] / $total_for_type) * 100 : 0;
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex items-center">
                                        <i class="fas fa-tag text-gray-400 mr-2"></i>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $category['type'] === 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'; ?>">
                                        <i class="fas fa-<?php echo $category['type'] === 'income' ? 'arrow-up' : 'arrow-down'; ?> mr-1"></i>
                                        <?php echo ucfirst($category['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold <?php echo $category['type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                                    KSh <?php echo number_format($category['total_amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                    <?php echo $category['transaction_count']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                    KSh <?php echo number_format($category['avg_amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                    <?php echo number_format($percentage, 1); ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Transactions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden fade-in">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-trophy text-yellow-600 mr-2"></i>
                Top Transactions
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($top_transactions)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-receipt text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No transactions found</p>
                                    <p class="text-sm">Add some transactions to see top transactions</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($top_transactions as $index => $transaction): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold mr-3">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <?php echo date('M j, Y', strtotime($transaction['date'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $transaction['type'] === 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'; ?>">
                                        <i class="fas fa-<?php echo $transaction['type'] === 'income' ? 'arrow-up' : 'arrow-down'; ?> mr-1"></i>
                                        <?php echo ucfirst($transaction['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-tag text-gray-400 mr-2"></i>
                                        <?php echo htmlspecialchars($transaction['category_name']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold <?php echo $transaction['type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                                    KSh <?php echo number_format($transaction['amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs">
                                    <div class="truncate" title="<?php echo htmlspecialchars($transaction['description'] ?: 'No description'); ?>">
                                        <?php echo htmlspecialchars($transaction['description'] ?: 'No description'); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Chart data from PHP
const dailyTrends = <?php echo json_encode($daily_trends); ?>;
const categoryBreakdown = <?php echo json_encode($category_breakdown); ?>;
const monthlyTrends = <?php echo json_encode($monthly_trends); ?>;

console.log('Chart data loaded:', {
    dailyTrends: dailyTrends,
    categoryBreakdown: categoryBreakdown,
    monthlyTrends: monthlyTrends
});

// Chart instances
let dailyChart, categoryChart, monthlyChart;

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing charts...');
    initializeCharts();
    setupEventListeners();
});

function initializeCharts() {
    try {
        // Initialize Daily Trends Chart
        initializeDailyTrendsChart();
        
        // Initialize Category Breakdown Chart
        initializeCategoryChart();
        
        // Initialize Monthly Trends Chart
        initializeMonthlyTrendsChart();
        
        console.log('All charts initialized successfully');
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}

function initializeDailyTrendsChart() {
    const dailyCtx = document.getElementById('dailyTrendsChart');
    if (!dailyCtx) {
        console.error('Daily trends chart canvas not found');
        return;
    }

    // Destroy existing chart if it exists
    if (dailyChart) {
        dailyChart.destroy();
    }

    // Prepare data
    const labels = dailyTrends.map(d => {
        const date = new Date(d.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    const incomeData = dailyTrends.map(d => parseFloat(d.daily_income) || 0);
    const expenseData = dailyTrends.map(d => parseFloat(d.daily_expenses) || 0);

    console.log('Daily trends data:', { labels, incomeData, expenseData });

    dailyChart = new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Income',
                data: incomeData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(34, 197, 94)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
            }, {
                label: 'Expenses',
                data: expenseData,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(239, 68, 68)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'KSh ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
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

function initializeCategoryChart() {
    const categoryCtx = document.getElementById('categoryChart');
    if (!categoryCtx) {
        console.error('Category chart canvas not found');
        return;
    }

    // Destroy existing chart if it exists
    if (categoryChart) {
        categoryChart.destroy();
    }

    // Prepare data - top 8 categories
    const categoryData = categoryBreakdown.slice(0, 8);
    
    if (categoryData.length === 0) {
        // Show empty state
        const ctx = categoryCtx.getContext('2d');
        ctx.clearRect(0, 0, categoryCtx.width, categoryCtx.height);
        ctx.fillStyle = '#9CA3AF';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('No category data available', categoryCtx.width / 2, categoryCtx.height / 2);
        return;
    }

    const labels = categoryData.map(c => c.category_name);
    const data = categoryData.map(c => parseFloat(c.total_amount));
    const colors = [
        '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
        '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'
    ];

    console.log('Category data:', { labels, data });

    categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverBorderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
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

function initializeMonthlyTrendsChart() {
    const monthlyCtx = document.getElementById('monthlyTrendsChart');
    if (!monthlyCtx) {
        console.error('Monthly trends chart canvas not found');
        return;
    }

    // Destroy existing chart if it exists
    if (monthlyChart) {
        monthlyChart.destroy();
    }

    // Prepare data
    const labels = monthlyTrends.map(m => {
        const date = new Date(m.month + '-01');
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    
    const incomeData = monthlyTrends.map(m => parseFloat(m.monthly_income) || 0);
    const expenseData = monthlyTrends.map(m => parseFloat(m.monthly_expenses) || 0);

    console.log('Monthly trends data:', { labels, incomeData, expenseData });

    monthlyChart = new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Income',
                data: incomeData,
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false
            }, {
                label: 'Expenses',
                data: expenseData,
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'KSh ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
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

function setupEventListeners() {
    // Export report functionality
    const exportBtn = document.getElementById('exportReportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportReport();
        });
    }

    // Print report functionality
    const printBtn = document.getElementById('printReportBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }
}

function exportReport() {
    // Create CSV content
    const headers = ['Category', 'Type', 'Total Amount', 'Transaction Count', 'Average Amount', 'Percentage'];
    const csvContent = [
        headers.join(','),
        ...categoryBreakdown.map(c => [
            c.category_name,
            c.type,
            c.total_amount,
            c.transaction_count,
            c.avg_amount,
            ((c.total_amount / (c.type === 'income' ? <?php echo $total_income; ?> : <?php echo $total_expenses; ?>)) * 100).toFixed(1) + '%'
        ].join(','))
    ].join('\n');
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `financial_report_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    // Show success message
    if (typeof Swal !== 'undefined') {
        Swal.fire('Success', 'Report exported successfully!', 'success');
    } else {
        alert('Report exported successfully!');
    }
}

// Handle window resize
window.addEventListener('resize', function() {
    if (dailyChart) dailyChart.resize();
    if (categoryChart) categoryChart.resize();
    if (monthlyChart) monthlyChart.resize();
});
</script>

</body>
</html>
