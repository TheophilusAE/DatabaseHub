@extends('layouts.app')

@section('title', 'Export Data')

@section('content')
<div class="space-y-6">
    <!-- Alert Container -->
    <div id="alert-container"></div>
    
    <div class="bg-gradient-to-r from-blue-800 to-green-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center space-x-4">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm p-3 rounded-xl">
                <img src="{{ asset('logo/Download.svg') }}" alt="Company Logo" class="h-8 w-8">
            </div>
            <div>
                <h1 class="text-3xl font-bold">Export Data</h1>
                <p class="text-white opacity-90 mt-1">Download your data in various formats (CSV, JSON, Excel)</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- CSV Export -->
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 border-b border-gray-100">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-3 bg-white rounded-xl shadow-sm">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">CSV Export</h3>
                        <p class="text-xs text-gray-600 mt-1">📄 Spreadsheet format</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Compatible with Excel and Google Sheets</p>
            </div>
            
            <div class="p-6 space-y-4">
                <button onclick="exportData('csv', '')" class="w-full inline-flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 transition-all transform hover:-translate-y-0.5 hover:shadow-xl">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download All Records
                </button>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Filter by Category:</label>
                    <select id="csv-category" class="w-full px-3 py-2 rounded-xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all text-sm">
                        <option value="">📂 All Categories</option>
                        <option value="electronics">📱 Electronics</option>
                        <option value="furniture">🪑 Furniture</option>
                        <option value="clothing">👕 Clothing</option>
                        <option value="books">📚 Books</option>
                        <option value="other">📦 Other</option>
                    </select>
                </div>
                
                <button onclick="exportData('csv', document.getElementById('csv-category').value)" class="w-full py-2.5 px-4 border-2 border-green-200 rounded-xl shadow-sm text-sm font-semibold text-green-700 bg-green-50 hover:bg-green-100 hover:border-green-300 transition-all">
                    Download by Category
                </button>
            </div>
        </div>

        <!-- JSON Export -->
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 border-b border-gray-100">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-3 bg-white rounded-xl shadow-sm">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">JSON Export</h3>
                        <p class="text-xs text-gray-600 mt-1">📊 Structured data format</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Perfect for APIs and data integration</p>
            </div>
            
            <div class="p-6 space-y-4">
                <button onclick="exportData('json', '')" class="w-full inline-flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-blue-700 to-green-600 hover:from-blue-800 hover:to-green-700 transition-all transform hover:-translate-y-0.5 hover:shadow-xl">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download All Records
                </button>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Filter by Category:</label>
                    <select id="json-category" class="w-full px-3 py-2 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-sm">
                        <option value="">📂 All Categories</option>
                        <option value="electronics">📱 Electronics</option>
                        <option value="furniture">🪑 Furniture</option>
                        <option value="clothing">👕 Clothing</option>
                        <option value="books">📚 Books</option>
                        <option value="other">📦 Other</option>
                    </select>
                </div>
                
                <button onclick="exportData('json', document.getElementById('json-category').value)" class="w-full py-2.5 px-4 border-2 border-blue-200 rounded-xl shadow-sm text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 hover:border-blue-300 transition-all">
                    Download by Category
                </button>
            </div>
        </div>

        <!-- Excel Export -->
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="bg-gradient-to-br from-orange-50 to-amber-50 p-6 border-b border-gray-100">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-3 bg-white rounded-xl shadow-sm">
                        <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Excel Export</h3>
                        <p class="text-xs text-gray-600 mt-1">📈 .xlsx format</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Best for Microsoft Excel users</p>
            </div>
            
            <div class="p-6 space-y-4">
                <button onclick="exportData('excel', '')" class="w-full inline-flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 transition-all transform hover:-translate-y-0.5 hover:shadow-xl">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download All Records
                </button>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Filter by Category:</label>
                    <select id="excel-category" class="w-full px-3 py-2 rounded-xl border-2 border-gray-200 shadow-sm focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all text-sm">
                        <option value="">📂 All Categories</option>
                        <option value="electronics">📱 Electronics</option>
                        <option value="furniture">🪑 Furniture</option>
                        <option value="clothing">👕 Clothing</option>
                        <option value="books">📚 Books</option>
                        <option value="other">📦 Other</option>
                    </select>
                </div>
                
                <button onclick="exportData('excel', document.getElementById('excel-category').value)" class="w-full py-2.5 px-4 border-2 border-orange-200 rounded-xl shadow-sm text-sm font-semibold text-orange-700 bg-orange-50 hover:bg-orange-100 hover:border-orange-300 transition-all">
                    Download by Category
                </button>
            </div>
        </div>
    </div>

    <!-- Export Info -->
    <div class="bg-gradient-to-r from-blue-50 to-green-50 border-l-4 border-blue-500 rounded-r-2xl p-6 shadow-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                    <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-base font-bold text-gray-900 flex items-center">
                    💡 Export Information & Format Details
                </h3>
                <div class="mt-3 text-sm text-gray-700">
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>CSV files:</strong> Compatible with Excel, Google Sheets, and most spreadsheet applications</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>JSON files:</strong> Ideal for importing into other applications, APIs, or databases</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>Excel files (.xlsx):</strong> Provide the best formatting and compatibility with Microsoft Excel</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>Category filtering:</strong> Download specific data subsets based on your needs</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Stats -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <svg class="h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Total Records Available for Export
        </h3>
        <div id="export-stats" class="grid grid-cols-2 gap-6 sm:grid-cols-5">
            <div class="text-center p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:shadow-md transition-all">
                <div class="text-3xl font-bold text-gray-900 animate-pulse" id="total-all">-</div>
                <p class="text-xs font-semibold text-gray-600 mt-2">📊 Total Records</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 hover:shadow-md transition-all">
                <div class="text-3xl font-bold text-blue-600" id="total-electronics">-</div>
                <p class="text-xs font-semibold text-blue-700 mt-2">📱 Electronics</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 hover:shadow-md transition-all">
                <div class="text-3xl font-bold text-green-600" id="total-furniture">-</div>
                <p class="text-xs font-semibold text-green-700 mt-2">🪑 Furniture</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl border border-pink-200 hover:shadow-md transition-all">
                <div class="text-3xl font-bold text-pink-600" id="total-clothing">-</div>
                <p class="text-xs font-semibold text-pink-700 mt-2">👕 Clothing</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border border-orange-200 hover:shadow-md transition-all">
                <div class="text-3xl font-bold text-orange-600" id="total-books">-</div>
                <p class="text-xs font-semibold text-orange-700 mt-2">📚 Books</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadExportStats();
});

async function loadExportStats() {
    try {
        // Get total records with animation
        const allResponse = await fetch('http://localhost:8080/data?page=1&limit=1');
        const allData = await allResponse.json();
        animateCounter('total-all', allData.total || 0);
        
        // Get counts by category
        const categories = ['electronics', 'furniture', 'clothing', 'books'];
        for (const category of categories) {
            try {
                const response = await fetch(`http://localhost:8080/data/category/${category}?page=1&limit=1`);
                const data = await response.json();
                animateCounter(`total-${category}`, data.total || 0);
            } catch (error) {
                document.getElementById(`total-${category}`).textContent = '0';
            }
        }
    } catch (error) {
        console.error('Error loading export stats:', error);
        showAlert('Failed to load export statistics', 'error');
    }
}

function animateCounter(id, target) {
    const element = document.getElementById(id);
    const duration = 1000;
    const start = 0;
    const startTime = Date.now();
    
    function update() {
        const currentTime = Date.now();
        const elapsed = currentTime - startTime;
        
        if (elapsed < duration) {
            const progress = elapsed / duration;
            const current = Math.floor(start + (target - start) * progress);
            element.textContent = current;
            requestAnimationFrame(update);
        } else {
            element.textContent = target;
        }
    }
    
    update();
}

async function exportData(format, category) {
    let url = `http://localhost:8080/download/${format}`;
    
    if (category) {
        url += `?category=${category}`;
    }
    
    try {
        const categoryText = category ? ` (${category})` : '';
        showAlert(`📦 Preparing ${format.toUpperCase()} export${categoryText}...`, 'info');
        
        // Trigger download
        window.location.href = url;
        
        setTimeout(() => {
            showAlert(`✅ ${format.toUpperCase()} export download started!`, 'success');
        }, 500);
    } catch (error) {
        console.error('Export error:', error);
        showAlert('❌ Error initiating export. Please try again.', 'error');
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    let bgColor, textColor, icon;
    
    switch(type) {
        case 'success':
            bgColor = 'bg-green-100 border-green-500';
            textColor = 'text-green-900';
            icon = '<svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            break;
        case 'error':
            bgColor = 'bg-red-100 border-red-500';
            textColor = 'text-red-900';
            icon = '<svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            break;
        case 'info':
            bgColor = 'bg-blue-100 border-blue-500';
            textColor = 'text-blue-900';
            icon = '<svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            break;
        default:
            bgColor = 'bg-gray-100 border-gray-500';
            textColor = 'text-gray-900';
            icon = '<svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    }
    
    alertContainer.innerHTML = `
        <div class="${bgColor} ${textColor} px-6 py-4 rounded-2xl border-l-4 shadow-lg mb-4 animate-slideIn flex items-center">
            <div class="flex-shrink-0 mr-3">
                ${icon}
            </div>
            <div class="flex-1 font-semibold">
                ${message}
            </div>
        </div>
    `;
    
    setTimeout(() => {
        const alert = alertContainer.firstElementChild;
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            alert.style.transition = 'all 0.3s ease-out';
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 300);
        }
    }, 4000);
}
</script>
@endsection
