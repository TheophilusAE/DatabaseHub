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
                <div class="flex items-center gap-3">
                    <div>
                        <label for="databaseSelect" class="sr-only">Database</label>
                        <select id="databaseSelect" onchange="onDatabaseChange(this.value)"
                                class="px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold text-gray-700 bg-white min-w-[220px]">
                            <option value="default">Default Database</option>
                        </select>
                    </div>
                    <button onclick="loadTables()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 transition-all transform hover:scale-105 shadow-lg font-semibold flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
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
                            <option value="1000">1000</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <input id="tableSearchInput" type="text" oninput="handleTableSearchInput()" placeholder="Search rows..."
                                   class="w-56 sm:w-72 px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 font-medium" />
                        </div>
                        <button onclick="clearTableSearch()" id="clearSearchBtn" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-all hidden">
                            Clear
                        </button>
                        <span id="pageInfo" class="text-sm font-semibold text-gray-700"></span>
                        <button onclick="previousPage()" id="prevBtn" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed font-semibold transform hover:scale-105 transition-all">
                            Previous
                        </button>
                        <button onclick="nextPage()" id="nextBtn" class="px-5 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:from-purple-700 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold transform hover:scale-105 transition-all shadow-lg">
                            Next
                        </button>
                    </div>
                </div>

                <div id="adminCrudBar" class="hidden mb-6 bg-blue-50 border border-blue-200 p-4 rounded-xl">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-blue-800 text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            No SQL needed: use this form to create, edit, or delete rows.
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="openCreateModal()" id="createRowBtn" class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-all">Create Row</button>
                            <button onclick="openEditModal()" id="editRowBtn" class="px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>Edit Selected</button>
                            <button onclick="deleteSelectedRow()" id="deleteRowBtn" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>Delete Selected</button>
                        </div>
                    </div>
                    <p id="selectedRowInfo" class="mt-2 text-xs text-blue-700">No row selected</p>
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

<div id="rowEditorModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeRowEditorModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h3 id="rowEditorTitle" class="text-xl font-bold text-gray-800">Create Row</h3>
                <button onclick="closeRowEditorModal()" class="text-gray-400 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="rowEditorForm" class="p-6">
                <div id="rowEditorFields" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeRowEditorModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-semibold">Save</button>
                </div>
            </form>
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
let currentColumns = [];
let currentRows = [];
let displayedRows = [];
let primaryKeyColumn = '';
let selectedRowData = null;
let editorMode = 'create';
let tableSearchQuery = '';
let searchDebounceTimer = null;
let availableDatabases = [];
let currentDatabase = 'default';

// ✅ FIXED: Point to Go backend API
const API_URL = 'http://localhost:8080';
const userId = {{ session('user')['id'] ?? 'null' }};
const userRole = '{{ session('user')['role'] ?? '' }}';

function isAdminUser() {
    return String(userRole).toLowerCase() === 'admin';
}

function getAuthHeaders() {
    return {
        'Accept': 'application/json',
        'X-User-ID': userId || '',
        'X-User-Role': userRole || ''
    };
}

document.addEventListener('DOMContentLoaded', async function() {
    await loadDatabases();
    await loadTables();
});

function appendDatabaseQuery(url) {
    const separator = url.includes('?') ? '&' : '?';
    return `${url}${separator}database=${encodeURIComponent(currentDatabase)}`;
}

async function loadDatabases() {
    try {
        const response = await fetch(`${API_URL}/simple-multi/databases`, {
            method: 'GET',
            credentials: 'include',
            headers: getAuthHeaders()
        });

        if (!response.ok) {
            throw new Error('Failed to load databases');
        }

        const data = await response.json();
        availableDatabases = data.databases || [];

        if (availableDatabases.length === 0) {
            availableDatabases = [{ name: 'default', db_name: 'default', type: 'default' }];
        }

        const hasCurrent = availableDatabases.some(db => db.name === currentDatabase);
        if (!hasCurrent) {
            currentDatabase = availableDatabases[0].name;
        }

        renderDatabaseSelector();
    } catch (error) {
        console.error('Load databases error:', error);
        availableDatabases = [{ name: 'default', db_name: 'default', type: 'default' }];
        currentDatabase = 'default';
        renderDatabaseSelector();
        showAlert('Using default database. Unable to load database list.', 'error');
    }
}

function renderDatabaseSelector() {
    const select = document.getElementById('databaseSelect');
    if (!select) return;

    select.innerHTML = availableDatabases.map(db => {
        const label = getDatabaseDisplayName(db);
        return `<option value="${db.name}">${label}</option>`;
    }).join('');

    select.value = currentDatabase;
}

function getDatabaseDisplayName(databaseItem) {
    const rawName = String(databaseItem?.db_name || databaseItem?.name || 'default').trim();
    const matchInParentheses = rawName.match(/\(([^)]+)\)\s*$/);
    if (matchInParentheses && matchInParentheses[1]) {
        return matchInParentheses[1].trim();
    }
    return rawName;
}

function onDatabaseChange(databaseName) {
    currentDatabase = databaseName || 'default';
    closeTableViewer();
    loadTables();
}

async function loadTables() {
    try {
        let url = appendDatabaseQuery(`${API_URL}/simple-multi/tables`);
        if (userId && userRole !== 'admin') {
            url += `&user_id=${userId}&user_role=${userRole}`;
        }
        
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-User-ID': userId || '',
                'X-User-Role': userRole || ''
            }
        });
        
        if (response.status === 401) {
            throw new Error('Unauthorized. Please login again.');
        }
        
        if (!response.ok) throw new Error('Failed to load tables');
        
        const data = await response.json();
        allTables = data.tables || [];
        
        renderTablesList();
    } catch (error) {
        console.error('Load tables error:', error);
        showAlert('Error loading tables: ' + error.message, 'error');
        document.getElementById('tablesListContainer').innerHTML = `
            <div class="col-span-full text-center py-8 text-red-600">
                <p>Failed to load tables. Please check your connection.</p>
                <button onclick="loadTables()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Try Again
                </button>
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
    selectedRowData = null;
    tableSearchQuery = '';
    displayedRows = [];

    const searchInput = document.getElementById('tableSearchInput');
    if (searchInput) {
        searchInput.value = '';
    }
    updateSearchUi();
    
    document.getElementById('currentTableName').textContent = tableName;
    document.getElementById('tableDataViewer').classList.remove('hidden');
    document.getElementById('tableDataViewer').scrollIntoView({ behavior: 'smooth' });

    await loadTableColumns();
    document.getElementById('adminCrudBar').classList.remove('hidden');
    document.getElementById('editRowBtn').classList.toggle('hidden', !isAdminUser());
    document.getElementById('deleteRowBtn').classList.toggle('hidden', !isAdminUser());
    updateSelectedRowInfo();
    
    await loadTableData();
}

async function loadTableColumns() {
    const response = await fetch(appendDatabaseQuery(`${API_URL}/simple-multi/tables/${currentTable}/columns`), {
        method: 'GET',
        credentials: 'include',
        headers: getAuthHeaders()
    });

    if (!response.ok) {
        throw new Error('Failed to load table columns');
    }

    const result = await response.json();
    currentColumns = result.columns || [];
    const primary = currentColumns.find(col => col.is_primary_key);
    primaryKeyColumn = primary ? primary.name : '';
}

async function loadTableData() {
    try {
        let dataUrl = `${API_URL}/simple-multi/tables/${currentTable}?page=${currentPage}&page_size=${pageSize}`;
        dataUrl = appendDatabaseQuery(dataUrl);
        if (tableSearchQuery) {
            dataUrl += `&search=${encodeURIComponent(tableSearchQuery)}`;
        }
        if (userId && userRole && userRole !== 'admin') {
            dataUrl += `&user_id=${encodeURIComponent(userId)}&user_role=${encodeURIComponent(userRole)}`;
        }

        const response = await fetch(dataUrl, {
            method: 'GET',
            credentials: 'include',
            headers: getAuthHeaders()
        });
        
        if (response.status === 401) {
            throw new Error('Unauthorized. Please login again.');
        }
        
        if (!response.ok) throw new Error('Failed to load table data');
        
        const result = await response.json();
        totalPages = result.total_pages;
        totalCount = result.total_count;
        currentRows = result.data || [];
        
        renderTableData(currentRows);
        updatePagination();
    } catch (error) {
        console.error('Load table data error:', error);
        showAlert('Error loading table data: ' + error.message, 'error');
    }
}

function renderTableData(data) {
    const headersRow = document.getElementById('tableHeaders');
    const tbody = document.getElementById('tableBody');
    const filteredData = applyClientSideFilter(data || []);
    displayedRows = filteredData;
    
    if (!filteredData || filteredData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td class="px-6 py-4 text-center text-gray-500" colspan="100">${tableSearchQuery ? 'No matching rows found' : 'No data found'}</td>
            </tr>
        `;
        headersRow.innerHTML = isAdminUser() ? '<th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-300">Select</th>' : '';
        return;
    }
    
    const columns = currentColumns.length ? currentColumns.map(col => col.name) : Object.keys(filteredData[0]);
    const selectHeader = isAdminUser() ? '<th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-300">Select</th>' : '';
    headersRow.innerHTML = selectHeader + columns.map(col => `
        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-300">${col}</th>
    `).join('');
    
    tbody.innerHTML = filteredData.map((row, idx) => `
        <tr class="${idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors">
            ${isAdminUser() ? `<td class="px-4 py-4 text-sm text-gray-900"><input type="radio" name="selectedRow" onclick="selectRow(${idx})" ${selectedRowData === row ? 'checked' : ''}></td>` : ''}
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
    
    document.getElementById('tableRowCount').textContent = `Total: ${totalCount.toLocaleString()} rows`;
}

function selectRow(index) {
    selectedRowData = displayedRows[index] || null;
    updateSelectedRowInfo();
}

function applyClientSideFilter(rows) {
    if (!tableSearchQuery) {
        return rows;
    }

    const searchTerm = tableSearchQuery.toLowerCase();
    const columns = currentColumns.length ? currentColumns.map(col => col.name) : (rows[0] ? Object.keys(rows[0]) : []);

    return rows.filter(row => columns.some(col => {
        const value = row[col];
        if (value === null || value === undefined) return false;
        if (typeof value === 'object') {
            return JSON.stringify(value).toLowerCase().includes(searchTerm);
        }
        return String(value).toLowerCase().includes(searchTerm);
    }));
}

function updateSearchUi() {
    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) {
        clearBtn.classList.toggle('hidden', !tableSearchQuery);
    }
}

function handleTableSearchInput() {
    const input = document.getElementById('tableSearchInput');
    tableSearchQuery = (input?.value || '').trim();
    currentPage = 1;
    selectedRowData = null;
    updateSelectedRowInfo();
    updateSearchUi();

    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        loadTableData();
    }, 300);
}

function clearTableSearch() {
    tableSearchQuery = '';
    const input = document.getElementById('tableSearchInput');
    if (input) {
        input.value = '';
    }
    currentPage = 1;
    selectedRowData = null;
    updateSelectedRowInfo();
    updateSearchUi();
    loadTableData();
}

function updateSelectedRowInfo() {
    const info = document.getElementById('selectedRowInfo');
    const editBtn = document.getElementById('editRowBtn');
    const deleteBtn = document.getElementById('deleteRowBtn');

    if (!isAdminUser()) {
        info.textContent = 'Create new rows using the form button.';
        return;
    }

    const hasSelection = !!selectedRowData;

    editBtn.disabled = !hasSelection;
    deleteBtn.disabled = !hasSelection;

    if (!hasSelection) {
        info.textContent = 'No row selected';
        return;
    }

    const pkValue = primaryKeyColumn ? selectedRowData[primaryKeyColumn] : '(no primary key)';
    info.textContent = `Selected row ${primaryKeyColumn ? `${primaryKeyColumn}=${pkValue}` : ''}`;
}

function getInputType(dataType) {
    const type = String(dataType || '').toLowerCase();
    if (type.includes('int') || type.includes('numeric') || type.includes('decimal') || type.includes('double') || type.includes('real')) return 'number';
    if (type.includes('date') || type.includes('timestamp')) return 'datetime-local';
    if (type.includes('bool')) return 'checkbox';
    return 'text';
}

function openCreateModal() {
    editorMode = 'create';
    renderRowEditorFields();
    document.getElementById('rowEditorTitle').textContent = `Create Row in ${currentTable}`;
    document.getElementById('rowEditorModal').classList.remove('hidden');
}

function openEditModal() {
    if (!selectedRowData) {
        showAlert('Select a row first', 'error');
        return;
    }
    if (!primaryKeyColumn) {
        showAlert('This table has no primary key, update is not supported', 'error');
        return;
    }

    editorMode = 'edit';
    renderRowEditorFields(selectedRowData);
    document.getElementById('rowEditorTitle').textContent = `Edit Row in ${currentTable}`;
    document.getElementById('rowEditorModal').classList.remove('hidden');
}

function closeRowEditorModal() {
    document.getElementById('rowEditorModal').classList.add('hidden');
}

function renderRowEditorFields(rowData = {}) {
    const container = document.getElementById('rowEditorFields');

    const editableColumns = currentColumns.filter(col => {
        const colName = String(col.name || '').toLowerCase();
        if (colName === 'created_at' || colName === 'updated_at') {
            return false;
        }

        if (editorMode === 'create') {
            return !col.is_identity && !(col.is_primary_key && col.has_default);
        }
        return !col.is_primary_key && !col.is_identity;
    });

    container.innerHTML = editableColumns.map(col => {
        const inputType = getInputType(col.type);
        const rawValue = rowData[col.name];
        const safeValue = rawValue === null || rawValue === undefined ? '' : String(rawValue);
        const required = col.nullable === 'NO' ? 'required' : '';

        if (inputType === 'checkbox') {
            const checked = String(rawValue).toLowerCase() === 'true' || rawValue === 1 ? 'checked' : '';
            return `
                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                    <input type="checkbox" data-column="${col.name}" ${checked} class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">${col.name}</span>
                </label>
            `;
        }

        return `
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">${col.name} <span class="text-gray-400">(${col.type})</span></label>
                <input type="${inputType}" data-column="${col.name}" value="${safeValue.replace(/"/g, '&quot;')}" ${required}
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        `;
    }).join('');
}

document.getElementById('rowEditorForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const fields = Array.from(document.querySelectorAll('#rowEditorFields [data-column]'));
    const payloadData = {};

    fields.forEach(field => {
        const colName = field.getAttribute('data-column');
        if (field.type === 'checkbox') {
            payloadData[colName] = field.checked;
        } else {
            payloadData[colName] = field.value === '' ? null : field.value;
        }
    });

    try {
        const isCreate = editorMode === 'create';
        const method = isCreate ? 'POST' : 'PUT';
        const requestBody = isCreate
            ? { data: payloadData }
            : {
                primary_key_column: primaryKeyColumn,
                primary_key_value: selectedRowData[primaryKeyColumn],
                data: payloadData,
            };

        const responseWithDatabase = await fetch(appendDatabaseQuery(`${API_URL}/simple-multi/tables/${currentTable}/rows`), {
            method,
            credentials: 'include',
            headers: {
                ...getAuthHeaders(),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestBody),
        });

        const result = await responseWithDatabase.json().catch(() => ({}));
        if (!responseWithDatabase.ok) {
            throw new Error(result.error || 'Operation failed');
        }

        showAlert(isCreate ? 'Row created successfully' : 'Row updated successfully', 'success');
        closeRowEditorModal();
        selectedRowData = null;
        updateSelectedRowInfo();
        await loadTableData();
        await loadTables();
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

async function deleteSelectedRow() {
    if (!selectedRowData) {
        showAlert('Select a row first', 'error');
        return;
    }
    if (!primaryKeyColumn) {
        showAlert('This table has no primary key, delete is not supported', 'error');
        return;
    }

    const pkValue = selectedRowData[primaryKeyColumn];
    if (!confirm(`Delete selected row (${primaryKeyColumn}=${pkValue})? This cannot be undone.`)) {
        return;
    }

    try {
        const response = await fetch(appendDatabaseQuery(`${API_URL}/simple-multi/tables/${currentTable}/rows`), {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                ...getAuthHeaders(),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                primary_key_column: primaryKeyColumn,
                primary_key_value: pkValue,
            }),
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(result.error || 'Delete failed');
        }

        showAlert('Row deleted successfully', 'success');
        selectedRowData = null;
        updateSelectedRowInfo();
        await loadTableData();
        await loadTables();
    } catch (error) {
        showAlert(error.message, 'error');
    }
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
    currentColumns = [];
    currentRows = [];
    displayedRows = [];
    primaryKeyColumn = '';
    selectedRowData = null;
    tableSearchQuery = '';

    const input = document.getElementById('tableSearchInput');
    if (input) {
        input.value = '';
    }
    updateSearchUi();
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