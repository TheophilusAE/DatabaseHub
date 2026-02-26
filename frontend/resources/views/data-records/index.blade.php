@extends('layouts.app')

@section('title', 'Data Records')

@section('content')
<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out forwards;
    opacity: 0;
}

.tab-content {
    animation: fadeIn 0.3s ease-out;
}
</style>

<!-- Alert Container -->
<div id="alert-container"></div>

<div class="space-y-6 animate-slide-in">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-4xl font-extrabold bg-gradient-to-r from-blue-700 to-green-600 bg-clip-text text-transparent">
                Database Tables
            </h1>
            <p class="mt-2 text-base text-gray-600">View and manage data from all your database tables</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <!-- Import & Export Button (Available to all users) -->
            <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.data-exchange' : 'user.data-exchange') }}" 
               class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-green-600 rounded-xl shadow-lg text-sm font-bold text-white hover:from-blue-700 hover:to-green-700 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Import
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </a>

            <!-- Database Selector -->
            <select id="database-select" onchange="onDatabaseChange(this.value)"
                    class="px-4 py-3 border-2 border-gray-300 rounded-xl bg-white text-gray-700 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[220px]">
                <option value="default">Default Database</option>
            </select>
            
            <!-- Refresh Button -->
            <button onclick="loadDatabaseTables()" class="inline-flex items-center px-4 py-3 bg-gradient-to-r from-blue-600 to-green-600 text-white rounded-xl shadow-lg hover:from-blue-700 hover:to-green-700 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Tables
            </button>
        </div>
    </div>

    <!-- All Database Tables Section -->
    <div class="bg-white shadow-lg rounded-2xl p-6 border border-gray-100">
        <div id="tables-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="col-span-full flex justify-center py-12">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent mx-auto mb-4"></div>
                    <p class="text-gray-500 font-medium">Loading tables...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Data Viewer Modal -->
    <div id="table-viewer" class="hidden fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-green-700 bg-opacity-75 transition-opacity" onclick="closeTableViewer()"></div>
            
            <!-- Centering trick -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full z-50">
                <div class="bg-gradient-to-r from-blue-600 to-green-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white flex items-center space-x-3">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span id="viewer-table-name">Table Data</span>
                        </h3>
                        <button onclick="closeTableViewer()" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <label class="text-sm font-semibold text-gray-700">Rows per page:</label>
                            <select id="page-size" onchange="changePageSize()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                        <div id="table-pagination" class="flex items-center space-x-2"></div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table id="table-data" class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4">
                    <button onclick="closeTableViewer()" class="w-full sm:w-auto px-6 py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-all duration-200 shadow-md hover:shadow-lg font-semibold">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
// Get user identity from Laravel session
const USER_ROLE = '{{ session("user")["role"] ?? "user" }}';
const USER_ID = '{{ session("user")["id"] ?? "" }}';

// Database tables functionality
let currentTableName = '';
let currentPageNum = 1;
let currentPageSize = 25;
let selectedDatabase = 'default';
let availableDatabases = [];

// Helper function to build authenticated API URL
function buildApiUrl(endpoint, params = {}) {
    const url = new URL(endpoint, 'http://localhost:8080');
    
    // Add authentication parameters
    url.searchParams.append('user_role', USER_ROLE);
    if (USER_ID) {
        url.searchParams.append('user_id', USER_ID);
    }
    
    // Add custom parameters
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
            url.searchParams.append(key, params[key]);
        }
    });

    if (!Object.prototype.hasOwnProperty.call(params, 'database') && selectedDatabase) {
        url.searchParams.append('database', selectedDatabase);
    }
    
    return url.toString();
}

async function loadDatabases() {
    try {
        const response = await fetch(buildApiUrl('/simple-multi/databases'));
        if (!response.ok) {
            throw new Error(`Failed to load databases (HTTP ${response.status})`);
        }

        const data = await response.json();
        availableDatabases = data.databases || [];

        if (availableDatabases.length === 0) {
            availableDatabases = [{ name: 'default', db_name: 'default' }];
        }

        if (!availableDatabases.some(db => db.name === selectedDatabase)) {
            selectedDatabase = availableDatabases[0].name;
        }

        renderDatabaseOptions();
    } catch (error) {
        console.error('Error loading databases:', error);
        availableDatabases = [{ name: 'default', db_name: 'default' }];
        selectedDatabase = 'default';
        renderDatabaseOptions();
    }
}

function renderDatabaseOptions() {
    const select = document.getElementById('database-select');
    if (!select) return;

    select.innerHTML = availableDatabases.map(db => {
        const dbLabel = db.db_name || db.name;
        return `<option value="${db.name}">${dbLabel}</option>`;
    }).join('');

    select.value = selectedDatabase;
}

function onDatabaseChange(databaseName) {
    selectedDatabase = databaseName || 'default';
    closeTableViewer();
    loadDatabaseTables();
}

async function loadDatabaseTables() {
    const tablesList = document.getElementById('tables-list');
    
    try {
        // Build authenticated URL with user identity
        const apiUrl = buildApiUrl('/simple-multi/tables');
        
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ error: 'Unknown error' }));
            throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error('Server returned non-JSON response');
        }
        
        const data = await response.json();
        
        if (data.tables && data.tables.length > 0) {
            tablesList.innerHTML = data.tables.map((table, index) => `
                <div class="bg-white border-2 border-gray-200 rounded-xl p-5 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer animate-fade-in" 
                     style="animation-delay: ${index * 0.05}s"
                     onclick="viewTableData('${table.table_name}')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-green-500 rounded-lg shadow-md">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900">${table.table_name}</h4>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 font-medium">Rows:</span>
                        <span class="px-3 py-1 bg-gradient-to-r from-blue-100 to-green-100 text-blue-700 rounded-full font-bold">${table.row_count.toLocaleString()}</span>
                    </div>
                </div>
            `).join('');
        } else {
            tablesList.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500 font-semibold text-lg">No tables found</p>
                    <p class="text-gray-400 mt-2">${USER_ROLE === 'admin' ? 'Database is empty' : "You don't have access to any tables"}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading tables:', error);
        tablesList.innerHTML = `
            <div class="col-span-full text-center py-12">
                <svg class="h-16 w-16 text-red-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-red-500 font-semibold text-lg">Error loading tables</p>
                <p class="text-sm text-gray-600 mt-2">${error.message}</p>
                <p class="text-xs text-gray-500 mt-2">Make sure the backend is running on localhost:8080</p>
            </div>
        `;
    }
}

async function viewTableData(tableName) {
    currentTableName = tableName;
    currentPageNum = 1;
    document.getElementById('viewer-table-name').textContent = tableName;
    document.getElementById('table-viewer').classList.remove('hidden');
    await loadTableData();
}

async function loadTableData() {
    // Validate table name before loading
    if (!currentTableName || currentTableName === 'undefined') {
        console.error('Invalid table name:', currentTableName);
        return;
    }
    
    const tableData = document.getElementById('table-data');
    const tbody = tableData.querySelector('tbody');
    const thead = tableData.querySelector('thead');
    
    tbody.innerHTML = `
        <tr>
            <td colspan="100" class="px-6 py-8 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
                    <p class="mt-4 text-gray-500 font-medium">Loading data...</p>
                </div>
            </td>
        </tr>
    `;
    
    try {
        // Build authenticated URL with pagination
        const apiUrl = buildApiUrl(`/simple-multi/tables/${currentTableName}`, {
            page: currentPageNum,
            page_size: currentPageSize
        });
        
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ error: 'Unknown error' }));
            throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const text = await response.text();
            console.error('Response is not JSON:', text);
            throw new Error('Server returned non-JSON response');
        }
        
        const data = await response.json();
        
        // Backend returns {  [...], page, page_size, table, total_count, total_pages }
        // Extract column names from the first row of data
        if (data.data && data.data.length > 0) {
            const columns = Object.keys(data.data[0]);
            
            // Build table header
            thead.innerHTML = `
                <tr>
                    ${columns.map(col => `
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            ${col}
                        </th>
                    `).join('')}
                </tr>
            `;
            
            // Build table body
            tbody.innerHTML = data.data.map((row, index) => `
                <tr class="hover:bg-blue-50 transition-colors" style="animation: fadeIn 0.3s ease-out ${index * 0.03}s both">
                    ${columns.map(col => {
                        let value = row[col];
                        if (value === null) value = '<span class="text-gray-400 italic">null</span>';
                        else if (typeof value === 'boolean') value = value ? '✅ true' : '❌ false';
                        else if (typeof value === 'object') value = JSON.stringify(value);
                        return `<td class="px-4 py-3 text-sm text-gray-900">${value}</td>`;
                    }).join('')}
                </tr>
            `).join('');
            
            // Update pagination using total_count from backend
            updateTablePagination(data.total_count, data.total_pages);
        } else {
            // No data in table
            tbody.innerHTML = `
                <tr>
                    <td colspan="100" class="px-6 py-12 text-center">
                        <p class="text-gray-500 font-semibold">No data found in this table</p>
                    </td>
                </tr>
            `;
            thead.innerHTML = '';
        }
    } catch (error) {
        console.error('Error loading table data:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="100" class="px-6 py-12 text-center">
                    <div class="text-red-500 font-semibold mb-2">Error loading table data</div>
                    <div class="text-sm text-gray-600">${error.message}</div>
                    <div class="text-xs text-gray-500 mt-2">Make sure the Go backend is running on localhost:8080</div>
                </td>
            </tr>
        `;
    }
}

function updateTablePagination(totalCount, totalPages) {
    const pagination = document.getElementById('table-pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = `<span class="text-sm text-gray-600 mr-3">Page ${currentPageNum} of ${totalPages} (${totalCount.toLocaleString()} total rows)</span>`;
    
    if (currentPageNum > 1) {
        html += `<button onclick="changePage(${currentPageNum - 1})" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm font-medium">Previous</button>`;
    }
    
    if (currentPageNum < totalPages) {
        html += `<button onclick="changePage(${currentPageNum + 1})" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm font-medium ml-2">Next</button>`;
    }
    
    pagination.innerHTML = html;
}

function changePage(page) {
    currentPageNum = page;
    loadTableData();
}

function changePageSize() {
    currentPageSize = parseInt(document.getElementById('page-size').value);
    currentPageNum = 1;
    loadTableData();
}

function closeTableViewer() {
    document.getElementById('table-viewer').classList.add('hidden');
}

// Helper function to get user role
function getUserRole() {
    return USER_ROLE;
}

document.addEventListener('DOMContentLoaded', async function() {
    console.log('User Role:', USER_ROLE);
    console.log('User ID:', USER_ID);
    await loadDatabases();
    loadDatabaseTables(); // Load database tables on page load
});

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const icon = type === 'success' 
        ? '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
        : '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    const alertClass = type === 'success' 
        ? 'bg-green-50 text-green-800 border-green-200' 
        : 'bg-red-50 text-red-800 border-red-200';
    
    alertContainer.innerHTML = `
        <div class="${alertClass} px-6 py-4 rounded-xl border-2 shadow-lg animate-slideIn flex items-center space-x-3 mb-4">
            <div class="flex-shrink-0">
                ${icon}
            </div>
            <div class="flex-1 font-semibold">
                ${message}
            </div>
            <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    `;
    
    setTimeout(() => {
        const alert = alertContainer.querySelector('div');
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            alert.style.transition = 'all 0.3s ease-out';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
}
</script>
@endsection