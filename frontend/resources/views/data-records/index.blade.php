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
                Data Records
            </h1>
            <p class="mt-2 text-base text-gray-600">Manage and organize your data records efficiently</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <!-- Export Button (Available to all users) -->
            <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.export.index' : 'user.export.index') }}" 
               class="inline-flex items-center px-4 py-3 border border-gray-300 rounded-xl shadow-md text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                <svg class="-ml-1 mr-2 h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </a>
            
            <!-- Import Button (Available to all users) -->
            <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.import.index' : 'user.import.index') }}" 
               class="inline-flex items-center px-4 py-3 border border-gray-300 rounded-xl shadow-md text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                <svg class="-ml-1 mr-2 h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Import
            </a>
            
            <!-- Add New Record Button (Available to all users) -->
            <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.data-records.create' : 'user.data-records.create') }}" 
               class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-blue-700 to-green-600 hover:from-blue-800 hover:to-green-700 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Record
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white shadow-lg rounded-2xl border border-gray-100 overflow-hidden">
        <div class="flex border-b border-gray-200">
            <button onclick="switchTab('records')" id="records-tab" 
                    class="flex-1 px-6 py-4 text-sm font-bold transition-all duration-200 border-b-2 border-blue-600 text-blue-600 bg-blue-50">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Data Records</span>
                </div>
            </button>
            <button onclick="switchTab('tables')" id="tables-tab" 
                    class="flex-1 px-6 py-4 text-sm font-bold transition-all duration-200 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    <span>All Database Tables</span>
                </div>
            </button>
        </div>
    </div>

    <!-- Records Tab Content -->
    <div id="records-content" class="tab-content">
        <!-- Filters -->
        <div class="bg-white shadow-lg rounded-2xl p-6 border border-gray-100">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div class="relative">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search Records</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="search" placeholder="Search by name or description..." 
                           class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
            </div>
            <div>
                <label for="category-filter" class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                <select id="category-filter" 
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">All Categories</option>
                    <option value="electronics">📱 Electronics</option>
                    <option value="furniture">🪑 Furniture</option>
                    <option value="clothing">👕 Clothing</option>
                    <option value="books">📚 Books</option>
                    <option value="other">📦 Other</option>
                </select>
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select id="status-filter" 
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">All Status</option>
                    <option value="active">✅ Active</option>
                    <option value="inactive">⭕ Inactive</option>
                    <option value="pending">⏳ Pending</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                ID
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Name
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Category
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Value
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Status
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="records-tbody" class="bg-white divide-y divide-gray-100">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
                                <p class="mt-4 text-gray-500 font-medium">Loading records...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200 rounded-2xl shadow-lg"></div>
    </div>

    <!-- All Tables Tab Content -->
<div id="tables-content" class="tab-content hidden">
    <div class="bg-white shadow-lg rounded-2xl p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Database Tables</h3>
            <button onclick="loadDatabaseTables()" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-green-600 text-white rounded-lg hover:from-blue-700 hover:to-green-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <div class="flex items-center space-x-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Refresh Tables</span>
                </div>
            </button>
        </div>
        
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
</div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity modal-backdrop"></div>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-fade-in">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-full bg-red-100 sm:mx-0 sm:h-12 sm:w-12 animate-pulse">
                        <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-xl leading-6 font-bold text-gray-900">Delete Record</h3>
                        <div class="mt-3">
                            <p class="text-sm text-gray-600">
                                Are you sure you want to delete this record? This action cannot be undone and all associated data will be permanently removed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button onclick="confirmDelete()" type="button" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-5 py-3 bg-gradient-to-r from-red-600 to-red-700 text-base font-semibold text-white hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-all transform hover:-translate-y-0.5">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
                <button onclick="closeDeleteModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-5 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentLimit = 10;
let deleteRecordId = null;

// Tab switching functionality
function switchTab(tab) {
    const recordsTab = document.getElementById('records-tab');
    const tablesTab = document.getElementById('tables-tab');
    const recordsContent = document.getElementById('records-content');
    const tablesContent = document.getElementById('tables-content');
    
    if (tab === 'records') {
        recordsTab.classList.add('border-blue-600', 'text-blue-600', 'bg-blue-50');
        recordsTab.classList.remove('border-transparent', 'text-gray-500');
        tablesTab.classList.remove('border-blue-600', 'text-blue-600', 'bg-blue-50');
        tablesTab.classList.add('border-transparent', 'text-gray-500');
        recordsContent.classList.remove('hidden');
        tablesContent.classList.add('hidden');
    } else {
        tablesTab.classList.add('border-blue-600', 'text-blue-600', 'bg-blue-50');
        tablesTab.classList.remove('border-transparent', 'text-gray-500');
        recordsTab.classList.remove('border-blue-600', 'text-blue-600', 'bg-blue-50');
        recordsTab.classList.add('border-transparent', 'text-gray-500');
        tablesContent.classList.remove('hidden');
        recordsContent.classList.add('hidden');
        loadDatabaseTables();
    }
}

// Database tables functionality
let currentTableName = '';
let currentPageNum = 1;
let currentPageSize = 25;

async function loadDatabaseTables() {
    const tablesList = document.getElementById('tables-list');
    
    try {
        const response = await fetch('http://localhost:8080/simple-multi/tables');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
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
        const response = await fetch(`http://localhost:8080/simple-multi/tables/${currentTableName}?page=${currentPageNum}&page_size=${currentPageSize}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const text = await response.text();
            console.error('Response is not JSON:', text);
            throw new Error('Server returned non-JSON response');
        }
        
        const data = await response.json();
        
        // Backend returns { data: [...], page, page_size, table, total_count, total_pages }
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
    return '{{ session("user")["role"] ?? "user" }}';
}

document.addEventListener('DOMContentLoaded', function() {
    loadRecords();
    
    // Filter listeners
    document.getElementById('category-filter').addEventListener('change', loadRecords);
    document.getElementById('status-filter').addEventListener('change', loadRecords);
    document.getElementById('search').addEventListener('keyup', debounce(loadRecords, 500));
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

async function loadRecords(page = 1) {
    currentPage = page;
    const category = document.getElementById('category-filter').value;
    const search = document.getElementById('search').value;
    
    let url = `http://localhost:8080/data?page=${currentPage}&limit=${currentLimit}`;
    
    if (category) {
        url = `http://localhost:8080/data/category/${category}?page=${currentPage}&limit=${currentLimit}`;
    }
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        displayRecords(data.data || []);
        displayPagination(data);
    } catch (error) {
        console.error('Error loading records:', error);
        showAlert('Error loading records', 'error');
    }
}

function displayRecords(records) {
    const tbody = document.getElementById('records-tbody');
    
    if (records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 font-semibold text-lg">No records found</p>
                        <p class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = records.map((record, index) => `
        <tr class="hover:bg-blue-50 transition-all duration-200 cursor-pointer group" style="animation: fadeIn 0.3s ease-out ${index * 0.05}s both">
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-gradient-to-br from-blue-600 to-green-600 text-white text-sm font-bold shadow-md">
                    ${record.id}
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div>
                        <div class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors">${record.name}</div>
                        <div class="text-sm text-gray-500 truncate max-w-xs">${record.description || '<span class="italic">No description</span>'}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm ${getCategoryStyle(record.category)}">
                    ${getCategoryIcon(record.category)} ${record.category}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-base font-bold text-gray-900">$${parseFloat(record.value).toFixed(2)}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm ${getStatusColor(record.status)}">
                    ${getStatusIcon(record.status)} ${record.status.toUpperCase()}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <a href="${getUserRole() === 'admin' ? '/admin' : '/user'}/data-records/${record.id}/edit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <button onclick="event.preventDefault(); event.stopPropagation(); showDeleteModal(${record.id}); return false;" type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all transform hover:-translate-y-0.5">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </td>
        </tr>
    `).join('');
}

function getCategoryIcon(category) {
    const icons = {
        'electronics': '📱',
        'furniture': '🪑',
        'clothing': '👕',
        'books': '📚',
        'other': '📦'
    };
    return icons[category] || '📦';
}

function getCategoryStyle(category) {
    const styles = {
        'electronics': 'bg-blue-100 text-blue-800 border border-blue-200',
        'furniture': 'bg-green-100 text-green-800 border border-green-200',
        'clothing': 'bg-purple-100 text-purple-800 border border-purple-200',
        'books': 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        'other': 'bg-gray-100 text-gray-800 border border-gray-200'
    };
    return styles[category] || 'bg-gray-100 text-gray-800 border border-gray-200';
}

function getStatusColor(status) {
    switch(status) {
        case 'active': return 'bg-green-100 text-green-800 border border-green-200';
        case 'inactive': return 'bg-gray-100 text-gray-800 border border-gray-200';
        case 'pending': return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
        default: return 'bg-gray-100 text-gray-800 border border-gray-200';
    }
}

function getStatusIcon(status) {
    const icons = {
        'active': '✅',
        'inactive': '⭕',
        'pending': '⏳'
    };
    return icons[status] || '⭕';
}

function displayPagination(data) {
    const pagination = document.getElementById('pagination');
    const totalPages = data.total_pages || 1;
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '<div class="flex items-center justify-between w-full">';
    paginationHTML += `<div class="text-sm text-gray-700">Showing page ${currentPage} of ${totalPages} (${data.total} total records)</div>`;
    paginationHTML += '<div class="flex space-x-2">';
    
    if (currentPage > 1) {
        paginationHTML += `<button onclick="loadRecords(${currentPage - 1})" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Previous</button>`;
    }
    
    if (currentPage < totalPages) {
        paginationHTML += `<button onclick="loadRecords(${currentPage + 1})" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Next</button>`;
    }
    
    paginationHTML += '</div></div>';
    pagination.innerHTML = paginationHTML;
}

function showDeleteModal(id) {
    deleteRecordId = id;
    document.getElementById('delete-modal').classList.remove('hidden');
}

function closeDeleteModal() {
    deleteRecordId = null;
    document.getElementById('delete-modal').classList.add('hidden');
}

async function confirmDelete() {
    if (!deleteRecordId) return;
    
    try {
        const response = await fetch(`http://localhost:8080/data/${deleteRecordId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            showAlert('Record deleted successfully', 'success');
            loadRecords(currentPage);
        } else {
            showAlert('Failed to delete record', 'error');
        }
    } catch (error) {
        console.error('Error deleting record:', error);
        showAlert('Error deleting record', 'error');
    }
    
    closeDeleteModal();
}

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
