// Global variables
let recognition;
let isListening = false;

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Set today's date as default
    document.getElementById('transaction_date').value = new Date().toISOString().split('T')[0];
    
    // Initialize theme
    initializeTheme();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load initial data
    loadTransactions();
    
    // Initialize speech recognition
    initializeSpeechRecognition();
    
    // Populate categories
    populateCategories();
}

function initializeTheme() {
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
}

function setupEventListeners() {
    // Transaction form submission
    document.getElementById('transactionForm').addEventListener('submit', handleTransactionSubmit);
    
    // Type change event to filter categories
    document.getElementById('type').addEventListener('change', filterCategories);
    
    // Voice input button
    document.getElementById('voiceBtn').addEventListener('click', toggleVoiceInput);
    
    // Receipt upload for OCR
    document.getElementById('receipt').addEventListener('change', handleReceiptUpload);
    
    // Filter events
    document.getElementById('filterDate').addEventListener('change', loadTransactions);
    document.getElementById('filterCategory').addEventListener('change', loadTransactions);
    
    // Export button
    document.getElementById('exportBtn').addEventListener('click', exportTransactions);
}

function populateCategories() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category');
    const filterCategorySelect = document.getElementById('filterCategory');
    
    // Populate filter category dropdown with all categories
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.name;
        option.textContent = category.name;
        filterCategorySelect.appendChild(option);
    });
    
    // Filter categories based on type selection
    filterCategories();
}

function filterCategories() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category');
    const selectedType = typeSelect.value;
    
    // Clear existing options
    categorySelect.innerHTML = '<option value="">Select category</option>';
    
    if (selectedType) {
        const filteredCategories = categories.filter(cat => cat.type === selectedType);
        filteredCategories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.name;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
    }
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
            isListening = false;
            updateVoiceButton();
        };
        
        recognition.onend = function() {
            isListening = false;
            updateVoiceButton();
        };
    }
}

function toggleVoiceInput() {
    if (!recognition) {
        alert('Speech recognition is not supported in your browser.');
        return;
    }
    
    if (isListening) {
        recognition.stop();
    } else {
        recognition.start();
        isListening = true;
        updateVoiceButton();
    }
}

function updateVoiceButton() {
    const voiceBtn = document.getElementById('voiceBtn');
    if (isListening) {
        voiceBtn.innerHTML = `
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
    } else {
        voiceBtn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
            </svg>
        `;
    }
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
        filterCategories();
    } else if (transcript.toLowerCase().includes('expense') || transcript.toLowerCase().includes('cost') || transcript.toLowerCase().includes('spend')) {
        document.getElementById('type').value = 'expense';
        filterCategories();
    }
    
    // Try to extract description
    document.getElementById('description').value = transcript;
}

function handleReceiptUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const progressDiv = document.getElementById('ocrProgress');
    progressDiv.classList.remove('hidden');
    
    Tesseract.recognize(file, 'eng', {
        logger: m => {
            if (m.status === 'recognizing text') {
                progressDiv.textContent = `Processing image... ${Math.round(m.progress * 100)}%`;
            }
        }
    }).then(({ data: { text } }) => {
        progressDiv.classList.add('hidden');
        processOCRText(text);
    }).catch(err => {
        console.error('OCR Error:', err);
        progressDiv.textContent = 'Error processing image';
        setTimeout(() => progressDiv.classList.add('hidden'), 3000);
    });
}

function processOCRText(text) {
    console.log('OCR Text:', text);
    
    // Try to extract amount (look for currency patterns)
    const amountMatch = text.match(/\$?(\d+\.?\d{0,2})/);
    if (amountMatch) {
        document.getElementById('amount').value = amountMatch[1];
    }
    
    // Add OCR text to description
    const currentDescription = document.getElementById('description').value;
    const newDescription = currentDescription ? `${currentDescription}\n\nFrom receipt: ${text}` : `From receipt: ${text}`;
    document.getElementById('description').value = newDescription;
}

async function handleTransactionSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('add_transaction.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Reset form
            event.target.reset();
            document.getElementById('transaction_date').value = new Date().toISOString().split('T')[0];
            
            // Reload transactions and update charts
            loadTransactions();
            
            // Show success message
            showNotification('Transaction added successfully!', 'success');
        } else {
            showNotification(result.error || 'Failed to add transaction', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to add transaction', 'error');
    }
}

async function loadTransactions() {
    try {
        const params = new URLSearchParams();
        
        const filterDate = document.getElementById('filterDate').value;
        const filterCategory = document.getElementById('filterCategory').value;
        
        if (filterDate) params.append('date', filterDate);
        if (filterCategory) params.append('category', filterCategory);
        
        const response = await fetch(`get_transactions.php?${params}`);
        const data = await response.json();
        
        if (data.transactions) {
            displayTransactions(data.transactions);
            updateCharts(data.category_breakdown, data.monthly_data);
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
        showNotification('Failed to load transactions', 'error');
    }
}

function displayTransactions(transactions) {
    const tbody = document.getElementById('transactionsTable');
    tbody.innerHTML = '';
    
    if (transactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    No transactions found
                </td>
            </tr>
        `;
        return;
    }
    
    transactions.forEach(transaction => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700';
        
        const typeColor = transaction.type === 'income' ? 'text-green-600' : 'text-red-600';
        const typeIcon = transaction.type === 'income' ? '↗' : '↘';
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                ${new Date(transaction.transaction_date).toLocaleDateString()}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm ${typeColor}">
                ${typeIcon} ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                ${transaction.category}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${typeColor}">
                $${parseFloat(transaction.amount).toFixed(2)}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs truncate">
                ${transaction.description || '-'}
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

function updateCharts(categoryBreakdown, monthlyData) {
    updateIncomeExpenseChart(monthlyData);
    updateCategoryChart(categoryBreakdown);
}

function updateIncomeExpenseChart(monthlyData) {
    const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
    
    // Process monthly data
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
        type: 'bar',
        data: {
            labels: months.map(month => new Date(month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' })),
            datasets: [{
                label: 'Income',
                data: incomeData,
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1
            }, {
                label: 'Expenses',
                data: expenseData,
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgba(239, 68, 68, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(0);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function updateCategoryChart(categoryBreakdown) {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    // Get top 5 categories by total amount
    const sortedCategories = categoryBreakdown
        .sort((a, b) => parseFloat(b.total) - parseFloat(a.total))
        .slice(0, 5);
    
    const labels = sortedCategories.map(item => item.category);
    const data = sortedCategories.map(item => parseFloat(item.total));
    const colors = [
        'rgba(59, 130, 246, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(239, 68, 68, 0.8)',
        'rgba(139, 92, 246, 0.8)'
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.8', '1')),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}

function exportTransactions() {
    window.location.href = 'export_csv.php';
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}