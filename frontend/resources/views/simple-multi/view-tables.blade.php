@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent mb-3">
                View All Tables
            </h1>
            <p class="text-lg text-gray-600">Browse and view data from all database tables</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer" class="mb-6"></div>

        <!-- Tables List -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                    <svg class="w-7 h-7 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    Available Tables
                </h2>
                <button onclick="loadTables()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 transition-all transform hover:scale-105 shadow-lg font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </div>
            <div id="tablesListContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <div class="text-center py-12 col-span-full">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-4 border-t-4 border-blue-600"></div>
                    <p class="mt-4 text-gray-600 font-medium">Loading tables...</p>
                </div>
            </div>
        </div>

        <!-- Table Data Viewer -->
        <div id="tableDataViewer" class="bg-white rounded-2xl shadow-xl hidden border-t-4 border-purple-500">
            <div class="p-6 border-b-2 border-gray-200 flex justify-between items-center bg-gradient-to-r from-purple-50 to-blue-50">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800" id="currentTableName">Table Data</h2>
                    <p class="text-sm text-gray-600 font-medium" id="tableRowCount"></p>
                </div>
                <button onclick="closeTableViewer()" class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-lg transition-all transform hover:scale-110">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <!-- Table Navigation -->
                <div class="mb-6 flex flex-wrap justify-between items-center gap-4 bg-gray-50 p-4 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <label class="text-sm font-semibold text-gray-700">Rows per page:</label>
                        <select id="pageSizeSelect" onchange="changePageSize()" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 font-medium">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span id="pageInfo" class="text-sm font-semibold text-gray-700"></span>
                        <button onclick="previousPage()" id="prevBtn" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed font-semibold transform hover:scale-105 transition-all">
                            Previous
                        </button>
                        <button onclick="nextPage()" id="nextBtn" class="px-5 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:from-purple-700 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold transform hover:scale-105 transition-all shadow-lg">
                            Next
                        </button>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto rounded-xl border-2 border-gray-200 shadow-lg">
                    <table id="dataTable" class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                            <tr id="tableHeaders"></tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let allTables = [];
let currentTable = '';
let currentPage = 1;
let pageSize = 50;
let totalPages = 1;
let totalCount = 0;
const userId = {{ session('user')['id'] ?? 'null' }};
const userRole = '{{ session('user')['role'] ?? '' }}';

document.addEventListener('DOMContentLoaded', function() {
    loadTables();
});

async function loadTables() {
    try {
        // Include user_id and role in the request if user is not admin
        let url = 'http://localhost:8080/simple-multi/tables';
        if (userId && userRole !== 'admin') {
            url += `?user_id=${userId}&user_role=${userRole}`;
        }
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to load tables');
        
        const data = await response.json();
        allTables = data.tables || [];
        
        renderTablesList();
    } catch (error) {
        showAlert('Error loading tables: ' + error.message, 'error');
        document.getElementById('tablesListContainer').innerHTML = `
            <div class="col-span-full text-center py-8 text-red-600">
                <p>Failed to load tables. Please check your connection.</p>
            </div>
        `;
    }
}

function renderTablesList() {
    const container = document.getElementById('tablesListContainer');
    
    if (allTables.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-12 text-gray-500">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <p class="text-lg font-semibold">No tables found in database</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = allTables.map(table => `
        <div class="group relative border-2 border-gray-200 rounded-xl p-5 hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer bg-gradient-to-br from-blue-50 via-white to-purple-50 hover:border-blue-400 overflow-hidden"
             onclick="viewTable('${table.table_name}')">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-purple-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <svg class="h-10 w-10 text-blue-600 group-hover:text-purple-600 transition-colors group-hover:scale-110 transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold group-hover:bg-purple-100 group-hover:text-purple-800 transition-colors">${table.row_count.toLocaleString()} rows</span>
                </div>
                <h3 class="font-bold text-gray-900 text-lg truncate mb-1 group-hover:text-blue-600 transition-colors">${table.table_name}</h3>
                <p class="text-sm text-gray-500 group-hover:text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Click to view data
                </p>
            </div>
        </div>
    `).join('');
}

async function viewTable(tableName) {
    currentTable = tableName;
    currentPage = 1;
    
    document.getElementById('currentTableName').textContent = tableName;
    document.getElementById('tableDataViewer').classList.remove('hidden');
    
    // Scroll to viewer
    document.getElementById('tableDataViewer').scrollIntoView({ behavior: 'smooth' });
    
    await loadTableData();
}

async function loadTableData() {
    try {
        const response = await fetch(`http://localhost:8080/simple-multi/tables/${currentTable}?page=${currentPage}&page_size=${pageSize}`);
        if (!response.ok) throw new Error('Failed to load table data');
        
        const result = await response.json();
        totalPages = result.total_pages;
        totalCount = result.total_count;
        
        renderTableData(result.data);
        updatePagination();
    } catch (error) {
        showAlert('Error loading table data: ' + error.message, 'error');
    }
}

function renderTableData(data) {
    const headersRow = document.getElementById('tableHeaders');
    const tbody = document.getElementById('tableBody');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td class="px-6 py-4 text-center text-gray-500" colspan="100">No data found</td>
            </tr>
        `;
        headersRow.innerHTML = '';
        return;
    }
    
    // Render headers
    const columns = Object.keys(data[0]);
    headersRow.innerHTML = columns.map(col => `
        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-300">${col}</th>
    `).join('');
    
    // Render rows
    tbody.innerHTML = data.map((row, idx) => `
        <tr class="${idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors">
            ${columns.map(col => {
                let value = row[col];
                let displayValue;
                if (value === null || value === undefined) {
                    displayValue = '<span class="text-gray-400 italic font-medium">null</span>';
                } else if (typeof value === 'object') {
                    displayValue = '<span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">' + JSON.stringify(value) + '</span>';
                } else {
                    displayValue = String(value);
                }
                return `<td class="px-6 py-4 text-sm text-gray-900">${displayValue}</td>`;
            }).join('')}
        </tr>
    `).join('');
    
    // Update row count
    document.getElementById('tableRowCount').textContent = `Total: ${totalCount.toLocaleString()} rows`;
}

function updatePagination() {
    document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
    document.getElementById('prevBtn').disabled = currentPage <= 1;
    document.getElementById('nextBtn').disabled = currentPage >= totalPages;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        loadTableData();
    }
}

function nextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        loadTableData();
    }
}

function changePageSize() {
    pageSize = parseInt(document.getElementById('pageSizeSelect').value);
    currentPage = 1;
    loadTableData();
}

function closeTableViewer() {
    document.getElementById('tableDataViewer').classList.add('hidden');
    currentTable = '';
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
    
    alertContainer.innerHTML = `
        <div class="${alertClass} border-l-4 p-4 mb-4 rounded" role="alert">
            <p class="font-medium">${message}</p>
        </div>
    `;
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}
</script>
@endsection
