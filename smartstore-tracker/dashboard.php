<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'backend/db_config.php';
$conn = getDBConnection();

$user_id = $_SESSION['user_id'];

// Fetch total income
$income_query = "SELECT SUM(amount) AS total_income FROM transactions WHERE user_id = $user_id AND type = 'income'";
$income_result = $conn->query($income_query);
$total_income_row = $income_result->fetch(PDO::FETCH_ASSOC);
$total_income = $total_income_row['total_income'] ?? 0;

// Fetch total expenses
$expenses_query = "SELECT SUM(amount) AS total_expenses FROM transactions WHERE user_id = $user_id AND type = 'expense'";
$expenses_result = $conn->query($expenses_query);
$total_expenses_row = $expenses_result->fetch(PDO::FETCH_ASSOC);
$total_expenses = $total_expenses_row['total_expenses'] ?? 0;

// Calculate balance
$balance = $total_income - $total_expenses;

// Fetch recent transactions
$transactions_query = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY date DESC LIMIT 5";
$transactions_stmt = $conn->prepare($transactions_query);
$transactions_stmt->execute(['user_id' => $user_id]);
$transactions_result = $transactions_stmt;

// FIXED: Fetch categories - include both system categories (user_id IS NULL) and user's custom categories
$categories_query = "SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY type, name";
$categories_stmt = $conn->prepare($categories_query);
$categories_stmt->execute([$user_id]);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// If no categories exist, create default ones
if (empty($categories)) {
    // Insert default categories
    $default_categories = [
        // Income categories
        ['Product Sales', 'income', 'trending-up'],
        ['Service Revenue', 'income', 'briefcase'],
        ['Online Sales', 'income', 'shopping-cart'],
        ['Consultation Fees', 'income', 'users'],
        ['Other Income', 'income', 'plus-circle'],
        
        // Expense categories
        ['Inventory/Stock', 'expense', 'package'],
        ['Shop/Office Rent', 'expense', 'home'],
        ['Electricity Bills', 'expense', 'zap'],
        ['Transport/Fuel', 'expense', 'truck'],
        ['Marketing/Advertising', 'expense', 'megaphone'],
        ['Office Supplies', 'expense', 'clipboard'],
        ['Staff Salaries', 'expense', 'users'],
        ['Internet/Airtime', 'expense', 'wifi'],
        ['Bank Charges', 'expense', 'credit-card'],
        ['Other Expenses', 'expense', 'minus-circle']
    ];
    
    $insert_category = $conn->prepare("INSERT INTO categories (name, type, icon, user_id) VALUES (?, ?, ?, NULL)");
    
    foreach ($default_categories as $category) {
        $insert_category->execute($category);
    }
    
    // Fetch categories again after inserting defaults
    $categories_stmt->execute([$user_id]);
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmartStore Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js"></script>
    

</head>
<body class="bg-gray-100 dark:bg-gray-900">

<?php include 'navbar.php'; ?>

<div class="container mx-auto mt-8 px-4">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-arrow-up text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Income</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">KSh <?php echo number_format($total_income, 2); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-arrow-down text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Expenses</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">KSh <?php echo number_format($total_expenses, 2); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 <?php echo $balance >= 0 ? 'bg-blue-500' : 'bg-orange-500'; ?> rounded-md flex items-center justify-center">
                            <i class="fas fa-wallet text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Balance</dt>
                            <dd class="text-lg font-medium <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">KSh <?php echo number_format($balance, 2); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Enhanced Add Transaction Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Add Transaction</h2>
            </div>
            <div class="p-6">
                <form id="transactionForm" enctype="multipart/form-data">
                    <div class="space-y-4">
                        <!-- Type Selection -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transaction Type</label>
                            <select id="type" name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select type</option>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>

                        <!-- Category Selection -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                            <select id="category_id" name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>" data-type="<?php echo htmlspecialchars($category['type']); ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Amount Input with Voice -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 z-10">KSh</span>
                                <input type="number" id="amount" name="amount" step="0.01" required 
                                       class="w-full pl-12 pr-12 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <button type="button" id="voiceBtn" class="absolute right-2 top-2 p-1 text-gray-500 hover:text-blue-500 transition-colors" title="Voice input">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                            <div id="voiceStatus" class="hidden mt-1 text-sm text-blue-600">
                                <i class="fas fa-circle animate-pulse"></i> Listening...
                            </div>
                        </div>

                        <!-- Date Input -->
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                            <input type="date" id="date" name="date" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <!-- Description Input -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (Optional)</label>
                            <textarea id="description" name="description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                      placeholder="Enter transaction details..."></textarea>
                        </div>

                        <!-- Receipt Upload/Camera Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Receipt (Optional)</label>
                            <div class="space-y-3">
                                <!-- File Upload -->
                                <div class="flex items-center justify-center w-full">
                                    <label for="receiptFile" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold">Click to upload</span> or drag and drop
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG or PDF (MAX. 10MB)</p>
                                        </div>
                                        <input id="receiptFile" name="receipt" type="file" class="hidden" accept="image/*,.pdf" />
                                    </label>
                                </div>

                                <!-- Camera Buttons -->
                                <div class="flex space-x-2">
                                    <button type="button" id="cameraBtn" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                        <i class="fas fa-camera mr-2"></i>
                                        Take Photo
                                    </button>
                                    <button type="button" id="scanBtn" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                        <i class="fas fa-scan mr-2"></i>
                                        Scan Receipt
                                    </button>
                                </div>

                                <!-- OCR Progress -->
                                <div id="ocrProgress" class="hidden">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i>
                                            <span class="text-sm text-blue-700 dark:text-blue-300">Processing receipt...</span>
                                        </div>
                                        <div class="mt-2 bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                            <div id="ocrProgressBar" class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preview Area -->
                                <div id="imagePreview" class="hidden">
                                    <div class="relative">
                                        <img id="previewImg" class="w-full h-48 object-cover rounded-lg border border-gray-300">
                                        <button type="button" id="removeImage" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" id="submitBtn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="submitText">Add Transaction</span>
                                <i id="submitSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Recent Transactions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php 
                        $category_map = [];
                        foreach ($categories as $cat) {
                            $category_map[$cat['id']] = $cat['name'];
                        }
                        while ($transaction = $transactions_result->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $transaction['type'] === 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'; ?>">
                                        <i class="fas fa-<?php echo $transaction['type'] === 'income' ? 'arrow-up' : 'arrow-down'; ?> mr-1"></i>
                                        <?php echo ucfirst($transaction['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($category_map[$transaction['category_id']] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?php echo $transaction['type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                                    KSh <?php echo number_format($transaction['amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo date('M j, Y', strtotime($transaction['date'])); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Camera Modal -->
<div id="cameraModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Take Photo</h3>
                <button id="closeCameraModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <video id="cameraVideo" class="w-full h-64 bg-black rounded-lg" autoplay playsinline></video>
                <canvas id="cameraCanvas" class="hidden"></canvas>
                
                <div class="flex justify-center space-x-4">
                    <button id="captureBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-camera mr-2"></i>Capture
                    </button>
                    <button id="retakeBtn" class="hidden px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-redo mr-2"></i>Retake
                    </button>
                    <button id="usePhotoBtn" class="hidden px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Use Photo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

<script>
// Global variables
let recognition;
let isListening = false;
let cameraStream;
let capturedImageBlob;

// Initialize categories data for JavaScript
const categories = <?php echo json_encode($categories); ?>;

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

async function initializeApp() {
    // Set today's date as default
    document.getElementById('date').value = new Date().toISOString().split('T')[0];
    
    // Initialize speech recognition
    initializeSpeechRecognition();
    
    // Setup event listeners
    setupEventListeners();
    
    // Setup category filtering
    setupCategoryFiltering();
}

function setupCategoryFiltering() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category_id');
    
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        const categoryOptions = categorySelect.querySelectorAll('option');
        
        // Show/hide categories based on selected type
        categoryOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block'; // Always show "Select category" option
                return;
            }
            
            const categoryType = option.getAttribute('data-type');
            if (!selectedType || categoryType === selectedType) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset category selection if current selection doesn't match type
        const currentCategory = categorySelect.querySelector('option:checked');
        if (currentCategory && currentCategory.getAttribute('data-type') !== selectedType && selectedType !== '') {
            categorySelect.value = '';
        }
    });
}

function initializeSpeechRecognition() {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        
        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            processVoiceInput(transcript);
        };
        
        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            stopListening();
        };
        
        recognition.onend = function() {
            stopListening();
        };
    }
}

function setupEventListeners() {
    // Form submission
    document.getElementById('transactionForm').addEventListener('submit', handleFormSubmit);
    
    // Voice input
    document.getElementById('voiceBtn').addEventListener('click', toggleVoiceInput);
    
    // Camera functionality
    document.getElementById('cameraBtn').addEventListener('click', openCamera);
    document.getElementById('scanBtn').addEventListener('click', openCamera);
    document.getElementById('closeCameraModal').addEventListener('click', closeCamera);
    document.getElementById('captureBtn').addEventListener('click', capturePhoto);
    document.getElementById('retakeBtn').addEventListener('click', retakePhoto);
    document.getElementById('usePhotoBtn').addEventListener('click', usePhoto);
    
    // File upload
    document.getElementById('receiptFile').addEventListener('change', handleFileUpload);
    
    // Image preview removal
    document.getElementById('removeImage').addEventListener('click', removeImage);
}

function toggleVoiceInput() {
    if (!recognition) {
        Swal.fire('Error', 'Speech recognition is not supported in your browser.', 'error');
        return;
    }
    
    if (isListening) {
        recognition.stop();
    } else {
        recognition.start();
        startListening();
    }
}

function startListening() {
    isListening = true;
    document.getElementById('voiceStatus').classList.remove('hidden');
    document.getElementById('voiceBtn').innerHTML = '<i class="fas fa-stop text-red-500"></i>';
}

function stopListening() {
    isListening = false;
    document.getElementById('voiceStatus').classList.add('hidden');
    document.getElementById('voiceBtn').innerHTML = '<i class="fas fa-microphone"></i>';
}

function processVoiceInput(transcript) {
    console.log('Voice input:', transcript);
    
    // Try to extract amount from speech
    const amountMatch = transcript.match(/(\d+(?:\.\d{2})?)/);
    if (amountMatch) {
        document.getElementById('amount').value = amountMatch[1];
    }
    
    // Try to extract type
    if (transcript.toLowerCase().includes('income') || transcript.toLowerCase().includes('revenue') || transcript.toLowerCase().includes('sale')) {
        document.getElementById('type').value = 'income';
        document.getElementById('type').dispatchEvent(new Event('change')); // Trigger category filtering
    } else if (transcript.toLowerCase().includes('expense') || transcript.toLowerCase().includes('cost') || transcript.toLowerCase().includes('spend')) {
        document.getElementById('type').value = 'expense';
        document.getElementById('type').dispatchEvent(new Event('change')); // Trigger category filtering
    }
    
    // Add to description
    const currentDescription = document.getElementById('description').value;
    const newDescription = currentDescription ? `${currentDescription}\n\nVoice note: ${transcript}` : `Voice note: ${transcript}`;
    document.getElementById('description').value = newDescription;
}

async function openCamera() {
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment' } // Use back camera on mobile
        });
        
        const video = document.getElementById('cameraVideo');
        video.srcObject = cameraStream;
        
        document.getElementById('cameraModal').classList.remove('hidden');
        document.getElementById('captureBtn').classList.remove('hidden');
        document.getElementById('retakeBtn').classList.add('hidden');
        document.getElementById('usePhotoBtn').classList.add('hidden');
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        Swal.fire('Error', 'Could not access camera. Please check permissions.', 'error');
    }
}

function closeCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
    document.getElementById('cameraModal').classList.add('hidden');
}

function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    // Convert to blob
    canvas.toBlob(function(blob) {
        capturedImageBlob = blob;
        
        // Show captured image in video element
        const url = URL.createObjectURL(blob);
        video.srcObject = null;
        video.src = url;
        
        // Update buttons
        document.getElementById('captureBtn').classList.add('hidden');
        document.getElementById('retakeBtn').classList.remove('hidden');
        document.getElementById('usePhotoBtn').classList.remove('hidden');
    }, 'image/jpeg', 0.8);
}

function retakePhoto() {
    const video = document.getElementById('cameraVideo');
    video.src = '';
    video.srcObject = cameraStream;
    
    document.getElementById('captureBtn').classList.remove('hidden');
    document.getElementById('retakeBtn').classList.add('hidden');
    document.getElementById('usePhotoBtn').classList.add('hidden');
}

function usePhoto() {
    if (capturedImageBlob) {
        displayImagePreview(capturedImageBlob);
        processImageWithOCR(capturedImageBlob);
    }
    closeCamera();
}

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        displayImagePreview(file);
        processImageWithOCR(file);
    }
}

function displayImagePreview(file) {
    const preview = document.getElementById('imagePreview');
    const img = document.getElementById('previewImg');
    
    const url = URL.createObjectURL(file);
    img.src = url;
    preview.classList.remove('hidden');
}

function removeImage() {
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('receiptFile').value = '';
    capturedImageBlob = null;
}

async function processImageWithOCR(file) {
    const progressDiv = document.getElementById('ocrProgress');
    const progressBar = document.getElementById('ocrProgressBar');
    
    progressDiv.classList.remove('hidden');
    
    try {
        const result = await Tesseract.recognize(file, 'eng', {
            logger: m => {
                if (m.status === 'recognizing text') {
                    const progress = Math.round(m.progress * 100);
                    progressBar.style.width = progress + '%';
                }
            }
        });
        
        const text = result.data.text;
        console.log('OCR Text:', text);
        
        // Try to extract amount (look for currency patterns)
        const amountMatch = text.match(/(?:KSh|Ksh|ksh|sh)?\s*(\d+(?:,\d{3})*(?:\.\d{2})?)/i);
        if (amountMatch) {
            const amount = amountMatch[1].replace(/,/g, '');
            document.getElementById('amount').value = amount;
        }
        
        // Add OCR text to description
        const currentDescription = document.getElementById('description').value;
        const newDescription = currentDescription ? 
            `${currentDescription}\n\nFrom receipt: ${text.substring(0, 200)}...` : 
            `From receipt: ${text.substring(0, 200)}...`;
        document.getElementById('description').value = newDescription;
        
        progressDiv.classList.add('hidden');
        
    } catch (error) {
        console.error('OCR Error:', error);
        progressDiv.classList.add('hidden');
        Swal.fire('Error', 'Failed to process image. Please try again.', 'error');
    }
}

async function handleFormSubmit(event) {
    event.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    
    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitText.textContent = 'Adding...';
    submitSpinner.classList.remove('hidden');
    
    try {
        const formData = new FormData();
        formData.append('type', document.getElementById('type').value);
        formData.append('category_id', document.getElementById('category_id').value);
        formData.append('amount', document.getElementById('amount').value);
        formData.append('date', document.getElementById('date').value);
        formData.append('description', document.getElementById('description').value);
        
        // Add file if uploaded or captured
        const fileInput = document.getElementById('receiptFile');
        if (fileInput.files[0]) {
            formData.append('receipt', fileInput.files[0]);
        } else if (capturedImageBlob) {
            formData.append('receipt', capturedImageBlob, 'captured_receipt.jpg');
        }
        
        const response = await fetch('backend/add_transaction.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'Transaction added successfully!',
                icon: 'success',
                confirmButtonText: 'Great!'
            }).then(() => {
                // Reset form
                document.getElementById('transactionForm').reset();
                document.getElementById('date').value = new Date().toISOString().split('T')[0];
                removeImage();
                
                // Reload page to show updated data
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to add transaction');
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: error.message || 'Failed to add transaction. Please try again.',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitText.textContent = 'Add Transaction';
        submitSpinner.classList.add('hidden');
    }
}
</script>

</body>
</html>