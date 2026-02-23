@extends('layouts.app')

@section('title', 'Import & Export Data')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-green-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-5xl font-extrabold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent mb-3">
                Import & Export Data
            </h1>
            <p class="text-xl text-gray-600">Simple, unified data transfer between your database tables</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer" class="mb-6"></div>

        <!-- Main Tabs -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex">
                    <button onclick="switchTab('import')" id="tab-import"
                        class="flex-1 py-6 px-8 text-center font-bold text-lg border-b-4 border-blue-500 text-blue-600 bg-blue-50 transition-all">
                        <svg class="inline h-8 w-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Import Data
                    </button>
                    <button onclick="switchTab('export')" id="tab-export"
                        class="flex-1 py-6 px-8 text-center font-bold text-lg border-b-4 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-all">
                        <svg class="inline h-8 w-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export Data
                    </button>
                </nav>
            </div>

            <!-- Import Panel -->
            <div id="panel-import" class="p-10">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Import Your Data
                </h2>
                <p class="text-gray-600 mb-8 text-lg">Upload multiple CSV or JSON files into different tables in one run</p>

                <form id="import-form" class="space-y-6">
                    <div id="import-items-container" class="space-y-4"></div>

                    <button type="button" onclick="addImportItem()"
                        class="w-full py-4 border-2 border-dashed border-blue-300 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all text-gray-700 hover:text-blue-700 flex items-center justify-center group">
                        <svg class="h-7 w-7 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span class="font-semibold text-lg">Add Another File + Table</span>
                    </button>

                    <!-- Import Button -->
                    <button type="submit" id="import-btn"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-5 px-8 rounded-xl text-xl hover:from-blue-700 hover:to-indigo-700 transition-all transform hover:scale-[1.02] hover:shadow-2xl flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span id="import-btn-text">Start Multi-Table Import</span>
                    </button>
                </form>

                <!-- Import Progress -->
                <div id="import-progress" class="hidden mt-8">
                    <div class="bg-blue-100 rounded-full h-6 overflow-hidden">
                        <div id="import-progress-bar" class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full transition-all duration-300 flex items-center justify-center" style="width: 0%">
                            <span class="text-white text-xs font-bold"></span>
                        </div>
                    </div>
                    <p id="import-progress-text" class="text-center mt-3 text-base text-gray-700 font-semibold"></p>
                </div>
            </div>

            <template id="import-item-template">
                <div class="import-item bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border-2 border-blue-200">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Import Item</h3>
                        <button type="button" onclick="removeImportItem(this)"
                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-all">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Target Table *</label>
                            <select class="import-table-select w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-semibold" required>
                                <option value="">Loading tables...</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Upload File *</label>
                            <input type="file" class="import-file-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500"
                                accept=".csv,.json" onchange="onImportFileSelected(this)" required>
                            <p class="import-file-label mt-2 text-xs text-gray-600">CSV or JSON</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Export Panel -->
            <div id="panel-export" class="p-10 hidden">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Your Data
                </h2>
                <p class="text-gray-600 mb-8 text-lg">Select tables and specific columns. Multiple tables will be automatically joined into one unified table.</p>

                <form id="export-form" class="space-y-6">
                    <!-- Selected Tables Display -->
                    <div id="selected-tables-container" class="space-y-4">
                        <!-- Table selection items will be added here -->
                    </div>

                    <!-- Add Table Button -->
                    <button type="button" onclick="addTableSelection()" 
                        class="w-full py-4 border-2 border-dashed border-green-300 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all text-gray-700 hover:text-green-700 flex items-center justify-center group">
                        <svg class="h-7 w-7 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span class="font-semibold text-lg">Add Another Table</span>
                    </button>

                    <!-- Format Selection -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border-2 border-blue-200">
                        <label class="block text-lg font-bold text-gray-800 mb-3">
                            <svg class="inline h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Format
                        </label>
                        <div class="flex items-center space-x-6 mt-3">
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" name="exportFormat" value="csv" checked class="w-5 h-5 text-green-600 focus:ring-green-500">
                                <span class="ml-3 text-lg font-semibold text-gray-700 group-hover:text-green-600 transition-colors">CSV (Excel)</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" name="exportFormat" value="json" class="w-5 h-5 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-lg font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">JSON (API)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Export Button -->
                    <button type="submit" id="export-btn"
                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold py-5 px-8 rounded-xl text-xl hover:from-green-700 hover:to-emerald-700 transition-all transform hover:scale-[1.02] hover:shadow-2xl flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span id="export-btn-text">Export Unified Data</span>
                    </button>
                </form>

                <!-- Export Info -->
                <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg">
                    <div class="flex">
                        <svg class="h-7 w-7 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-bold text-blue-900 mb-2">Smart Table Joining</h3>
                            <p class="text-base text-blue-800">When you select columns from multiple tables, they will be automatically joined based on their relationships (foreign keys or common ID columns). The result will be a single unified table with all selected columns in one row.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const USER_ROLE = '{{ session("user")["role"] ?? "user" }}';
const USER_ID = '{{ session("user")["id"] ?? "" }}';

let allTables = [];
let tableColumns = {}; // Cache for table columns
let tableSelectionCounter = 0;
let importItemCounter = 0;

function buildApiUrl(endpoint, params = {}) {
    const url = new URL(endpoint, 'http://localhost:8080');

    if (USER_ROLE) {
        url.searchParams.append('user_role', USER_ROLE);
    }
    if (USER_ID) {
        url.searchParams.append('user_id', USER_ID);
    }

    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
            url.searchParams.append(key, params[key]);
        }
    });

    return url.toString();
}

function getAuthHeaders(includeJson = false) {
    const headers = {};

    if (includeJson) {
        headers['Content-Type'] = 'application/json';
    }

    if (USER_ID) {
        headers['X-User-ID'] = USER_ID;
    }
    if (USER_ROLE) {
        headers['X-User-Role'] = USER_ROLE;
    }

    const token = localStorage.getItem('token');
    if (token) {
        headers['Authorization'] = 'Bearer ' + token;
    }

    return headers;
}

document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const queryTab = params.get('tab');
    const hashTab = window.location.hash ? window.location.hash.replace('#', '') : '';
    const initialTab = (queryTab === 'export' || hashTab === 'export') ? 'export' : 'import';

    switchTab(initialTab);
    addImportItem();
    loadTables();
    setupFormHandlers();
    
    // Add initial table selection for export
    setTimeout(() => {
        if (allTables.length > 0) {
            addTableSelection();
        }
    }, 1000);
});

function switchTab(tab) {
    // Update tabs
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50', 'border-green-500', 'text-green-600', 'bg-green-50');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.querySelectorAll('[id^="panel-"]').forEach(panel => {
        panel.classList.add('hidden');
    });

    if (tab === 'import') {
        document.getElementById('tab-import').classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('tab-import').classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
        document.getElementById('panel-import').classList.remove('hidden');
    } else {
        document.getElementById('tab-export').classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('tab-export').classList.add('border-green-500', 'text-green-600', 'bg-green-50');
        document.getElementById('panel-export').classList.remove('hidden');
    }
}

async function loadTables() {
    try {
        const response = await fetch(buildApiUrl('/simple-multi/tables'), {
            headers: getAuthHeaders()
        });
        if (!response.ok) throw new Error('Failed to load tables');
        
        const data = await response.json();
        allTables = data.tables || [];
        
        updateImportTableSelects();
        
    } catch (error) {
        showAlert('Error loading tables: ' + error.message, 'error');
    }
}

function updateImportTableSelects() {
    document.querySelectorAll('.import-table-select').forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '<option value="">Select a table...</option>' +
            allTables.map(table => `<option value="${table.table_name}">${table.table_name} (${table.row_count} rows)</option>`).join('');
        if (currentValue) {
            select.value = currentValue;
        }
    });
}

function updateImportItemTitles() {
    document.querySelectorAll('.import-item').forEach((item, index) => {
        const title = item.querySelector('h3');
        if (title) {
            title.textContent = `Import Item ${index + 1}`;
        }
    });
}

function addImportItem() {
    const container = document.getElementById('import-items-container');
    const template = document.getElementById('import-item-template');
    if (!container || !template) return;

    const clone = template.content.cloneNode(true);
    container.appendChild(clone);
    importItemCounter++;
    updateImportTableSelects();
    updateImportItemTitles();
}

function removeImportItem(button) {
    const item = button.closest('.import-item');
    if (item) {
        item.remove();
    }

    if (document.querySelectorAll('.import-item').length === 0) {
        addImportItem();
    }

    updateImportItemTitles();
}

function onImportFileSelected(input) {
    const item = input.closest('.import-item');
    if (!item) return;

    const label = item.querySelector('.import-file-label');
    if (!label) return;

    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        label.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
    } else {
        label.textContent = 'CSV or JSON';
    }
}

async function loadTableColumns(tableName) {
    if (tableColumns[tableName]) {
        return tableColumns[tableName];
    }
    
    try {
        const response = await fetch(buildApiUrl(`/simple-multi/tables/${encodeURIComponent(tableName)}/columns`), {
            headers: getAuthHeaders()
        });
        if (!response.ok) throw new Error('Failed to load columns');
        
        const data = await response.json();
        tableColumns[tableName] = data.columns || [];
        return tableColumns[tableName];
    } catch (error) {
        showAlert('Error loading columns: ' + error.message, 'error');
        return [];
    }
}

function addTableSelection() {
    const container = document.getElementById('selected-tables-container');
    const id = ++tableSelectionCounter;
    
    const div = document.createElement('div');
    div.className = 'table-selection bg-white border-2 border-gray-200 rounded-xl p-6 shadow-sm';
    div.dataset.id = id;
    
    div.innerHTML = `
        <div class="flex items-start justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">Table ${id}</h3>
            <button type="button" onclick="removeTableSelection(${id})" 
                class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-all">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Select Table *</label>
                <select class="table-select w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 font-semibold" 
                        required onchange="onTableChange(${id}, this.value)">
                    <option value="">Choose a table...</option>
                    ${allTables.map(table => `<option value="${table.table_name}">${table.table_name} (${table.row_count} rows)</option>`).join('')}
                </select>
            </div>
            
            <div class="columns-section hidden">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-bold text-gray-700">Select Columns</label>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="selectAllColumns(${id})" 
                            class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-all font-semibold">
                            Select All
                        </button>
                        <button type="button" onclick="deselectAllColumns(${id})" 
                            class="text-xs px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-all font-semibold">
                            Deselect All
                        </button>
                    </div>
                </div>
                <div class="columns-list grid grid-cols-2 md:grid-cols-3 gap-2 p-4 bg-gray-50 rounded-lg border-2 border-gray-200 max-h-60 overflow-y-auto">
                    <p class="text-gray-500 col-span-full text-center">Loading columns...</p>
                </div>
                <p class="mt-2 text-xs text-gray-600">Select specific columns to export, or select all for the entire table</p>
            </div>
            
            <div class="filter-section hidden">
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Filter Conditions (Optional)
                    <span class="text-xs font-normal text-gray-500">- No SQL needed</span>
                </label>
                <div class="filter-rows space-y-2"></div>
                <div class="mt-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Match</label>
                        <select class="filter-logic px-2 py-1 border border-gray-300 rounded text-xs">
                            <option value="AND">All conditions (AND)</option>
                            <option value="OR">Any condition (OR)</option>
                        </select>
                    </div>
                    <button type="button" onclick="addFilterCondition(${id})"
                        class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-all font-semibold">
                        + Add Condition
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-600">Add one or more conditions, or leave empty to export all rows</p>
            </div>
        </div>
    `;
    
    container.appendChild(div);
}

function removeTableSelection(id) {
    const element = document.querySelector(`[data-id="${id}"]`);
    if (element) {
        element.remove();
    }
}

async function onTableChange(id, tableName) {
    const container = document.querySelector(`[data-id="${id}"]`);
    if (!container || !tableName) return;
    
    const columnsSection = container.querySelector('.columns-section');
    const filterSection = container.querySelector('.filter-section');
    const columnsList = container.querySelector('.columns-list');
    
    // Show sections
    columnsSection.classList.remove('hidden');
    filterSection.classList.remove('hidden');
    
    // Load columns
    columnsList.innerHTML = '<p class="text-gray-500 col-span-full text-center">Loading columns...</p>';
    
    const columns = await loadTableColumns(tableName);
    
    if (columns.length > 0) {
        columnsList.innerHTML = columns.map(col => `
            <label class="flex items-center p-2 bg-white rounded hover:bg-green-50 cursor-pointer border border-transparent hover:border-green-300 transition-all">
                <input type="checkbox" value="${col.name}" class="column-checkbox w-4 h-4 text-green-600 rounded focus:ring-green-500" checked>
                <span class="ml-2 text-sm font-medium text-gray-700">${col.name}</span>
            </label>
        `).join('');

        const filterRows = container.querySelector('.filter-rows');
        if (filterRows) {
            filterRows.innerHTML = '';
        }
    } else {
        columnsList.innerHTML = '<p class="text-red-500 col-span-full text-center">No columns found</p>';
    }
}

function createOperatorOptions() {
    return `
        <option value="eq">is equal to</option>
        <option value="neq">is not equal to</option>
        <option value="contains">contains</option>
        <option value="starts_with">starts with</option>
        <option value="ends_with">ends with</option>
        <option value="gt">is greater than</option>
        <option value="gte">is greater than or equal to</option>
        <option value="lt">is less than</option>
        <option value="lte">is less than or equal to</option>
        <option value="is_empty">is empty</option>
        <option value="is_not_empty">is not empty</option>
    `;
}

function addFilterCondition(id) {
    const container = document.querySelector(`[data-id="${id}"]`);
    if (!container) return;

    const tableName = container.querySelector('.table-select')?.value;
    if (!tableName) {
        showAlert('Please select a table first before adding filter conditions', 'error');
        return;
    }

    const columns = tableColumns[tableName] || [];
    if (columns.length === 0) {
        showAlert('No columns available for this table', 'error');
        return;
    }

    const filterRows = container.querySelector('.filter-rows');
    if (!filterRows) return;

    const row = document.createElement('div');
    row.className = 'filter-row grid grid-cols-1 md:grid-cols-12 gap-2 items-center';

    row.innerHTML = `
        <select class="filter-column md:col-span-4 px-3 py-2 border border-gray-300 rounded text-sm">
            ${columns.map(col => `<option value="${col.name}" data-type="${(col.type || '').toLowerCase()}">${col.name}</option>`).join('')}
        </select>
        <select class="filter-operator md:col-span-4 px-3 py-2 border border-gray-300 rounded text-sm" onchange="onFilterOperatorChange(this)">
            ${createOperatorOptions()}
        </select>
        <input type="text" class="filter-value md:col-span-3 px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Value">
        <button type="button" class="md:col-span-1 px-2 py-2 text-red-600 hover:bg-red-100 rounded" onclick="this.closest('.filter-row').remove()" title="Remove condition">
            ✕
        </button>
    `;

    filterRows.appendChild(row);
}

function onFilterOperatorChange(selectEl) {
    const row = selectEl.closest('.filter-row');
    if (!row) return;

    const valueInput = row.querySelector('.filter-value');
    if (!valueInput) return;

    const noValueOps = ['is_empty', 'is_not_empty'];
    const operator = selectEl.value;

    if (noValueOps.includes(operator)) {
        valueInput.value = '';
        valueInput.disabled = true;
        valueInput.placeholder = 'No value needed';
    } else {
        valueInput.disabled = false;
        valueInput.placeholder = 'Value';
    }
}

function escapeSqlString(value) {
    return String(value).replace(/'/g, "''");
}

function isNumericType(columnType) {
    return /(int|numeric|decimal|float|double|real|serial)/i.test(columnType || '');
}

function isBooleanType(columnType) {
    return /(bool)/i.test(columnType || '');
}

function formatSqlValue(rawValue, columnType) {
    const value = String(rawValue).trim();

    if (isBooleanType(columnType)) {
        const normalized = value.toLowerCase();
        if (normalized === 'true' || normalized === '1' || normalized === 'yes') return 'TRUE';
        if (normalized === 'false' || normalized === '0' || normalized === 'no') return 'FALSE';
    }

    if (isNumericType(columnType) && value !== '' && !isNaN(Number(value))) {
        return String(Number(value));
    }

    return `'${escapeSqlString(value)}'`;
}

function buildSqlFilterForTable(container) {
    const tableName = container.querySelector('.table-select')?.value;
    const filterRows = Array.from(container.querySelectorAll('.filter-row'));
    if (!tableName || filterRows.length === 0) {
        return '';
    }

    const logic = container.querySelector('.filter-logic')?.value || 'AND';
    const noValueOps = ['is_empty', 'is_not_empty'];
    const conditions = [];

    for (const row of filterRows) {
        const columnSelect = row.querySelector('.filter-column');
        const operatorSelect = row.querySelector('.filter-operator');
        const valueInput = row.querySelector('.filter-value');

        const column = columnSelect?.value;
        const columnType = columnSelect?.selectedOptions?.[0]?.dataset?.type || '';
        const operator = operatorSelect?.value;
        const value = valueInput?.value?.trim() || '';

        if (!column || !operator) {
            continue;
        }

        if (!noValueOps.includes(operator) && value === '') {
            throw new Error(`Please enter a value for condition on column "${column}"`);
        }

        const qualifiedColumn = `${tableName}.${column}`;
        let sql = '';

        switch (operator) {
            case 'eq':
                sql = `${qualifiedColumn} = ${formatSqlValue(value, columnType)}`;
                break;
            case 'neq':
                sql = `${qualifiedColumn} <> ${formatSqlValue(value, columnType)}`;
                break;
            case 'gt':
                sql = `${qualifiedColumn} > ${formatSqlValue(value, columnType)}`;
                break;
            case 'gte':
                sql = `${qualifiedColumn} >= ${formatSqlValue(value, columnType)}`;
                break;
            case 'lt':
                sql = `${qualifiedColumn} < ${formatSqlValue(value, columnType)}`;
                break;
            case 'lte':
                sql = `${qualifiedColumn} <= ${formatSqlValue(value, columnType)}`;
                break;
            case 'contains':
                sql = `${qualifiedColumn}::text ILIKE '%${escapeSqlString(value)}%'`;
                break;
            case 'starts_with':
                sql = `${qualifiedColumn}::text ILIKE '${escapeSqlString(value)}%'`;
                break;
            case 'ends_with':
                sql = `${qualifiedColumn}::text ILIKE '%${escapeSqlString(value)}'`;
                break;
            case 'is_empty':
                sql = `(${qualifiedColumn} IS NULL OR ${qualifiedColumn}::text = '')`;
                break;
            case 'is_not_empty':
                sql = `(${qualifiedColumn} IS NOT NULL AND ${qualifiedColumn}::text <> '')`;
                break;
            default:
                continue;
        }

        conditions.push(`(${sql})`);
    }

    return conditions.join(` ${logic} `);
}

function selectAllColumns(id) {
    const container = document.querySelector(`[data-id="${id}"]`);
    if (!container) return;
    
    container.querySelectorAll('.column-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllColumns(id) {
    const container = document.querySelector(`[data-id="${id}"]`);
    if (!container) return;
    
    container.querySelectorAll('.column-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function setupFormHandlers() {
    // Import form
    document.getElementById('import-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const importItems = document.querySelectorAll('.import-item');
        if (importItems.length === 0) {
            showAlert('Please add at least one import item', 'error');
            return;
        }

        const formData = new FormData();
        let valid = true;

        importItems.forEach(item => {
            if (!valid) return;

            const tableSelect = item.querySelector('.import-table-select');
            const fileInput = item.querySelector('.import-file-input');

            if (!tableSelect || !tableSelect.value) {
                showAlert('Please select a target table for each import item', 'error');
                valid = false;
                return;
            }

            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                showAlert('Please select a file for each import item', 'error');
                valid = false;
                return;
            }

            formData.append('files', fileInput.files[0]);
            formData.append('table_names', tableSelect.value);
        });

        if (!valid) {
            return;
        }
        
        const importBtn = document.getElementById('import-btn');
        const importBtnText = document.getElementById('import-btn-text');
        const progress = document.getElementById('import-progress');
        const progressBar = document.getElementById('import-progress-bar');
        const progressText = document.getElementById('import-progress-text');
        
        importBtn.disabled = true;
        importBtnText.textContent = 'Importing...';
        progress.classList.remove('hidden');
        progressBar.style.width = '35%';
        progressText.textContent = 'Uploading files to selected tables...';
        
        try {
            const response = await fetch(buildApiUrl('/simple-multi/upload-multiple'), {
                method: 'POST',
                body: formData,
                headers: getAuthHeaders()
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || 'Import failed');
            }

            const result = await response.json();
            
            progressBar.style.width = '100%';
            progressText.textContent = 'Import completed!';
            
            setTimeout(() => {
                const successCount = result.total_success || 0;
                const failedCount = result.total_failed || 0;
                showAlert(`Import finished. Success: ${successCount}, Failed: ${failedCount}`, failedCount > 0 ? 'error' : 'success');
                progress.classList.add('hidden');
                document.getElementById('import-form').reset();
                document.getElementById('import-items-container').innerHTML = '';
                addImportItem();
                loadTables();
            }, 1000);
            
        } catch (error) {
            showAlert('Import failed: ' + error.message, 'error');
            progress.classList.add('hidden');
        } finally {
            importBtn.disabled = false;
            importBtnText.textContent = 'Start Multi-Table Import';
        }
    });
    
    // Export form
    document.getElementById('export-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Collect all table selections
        const tableSelections = [];
        let hasFilterError = false;
        document.querySelectorAll('.table-selection').forEach(container => {
            if (hasFilterError) return;

            const tableName = container.querySelector('.table-select').value;
            if (!tableName) return;
            
            const selectedColumns = Array.from(container.querySelectorAll('.column-checkbox:checked'))
                .map(cb => cb.value);
            
            let filter = '';
            try {
                filter = buildSqlFilterForTable(container);
            } catch (filterError) {
                showAlert(filterError.message, 'error');
                hasFilterError = true;
                return;
            }
            
            tableSelections.push({
                table_name: tableName,
                columns: selectedColumns,
                filters: filter
            });
        });

        if (hasFilterError) {
            return;
        }
        
        if (tableSelections.length === 0) {
            showAlert('Please add and configure at least one table', 'error');
            return;
        }
        
        // Check if any table has no columns selected
        const hasEmptyColumns = tableSelections.some(t => t.columns.length === 0);
        if (hasEmptyColumns) {
            showAlert('Please select at least one column for each table', 'error');
            return;
        }
        
        const format = document.querySelector('input[name="exportFormat"]:checked').value;
        
        const exportBtn = document.getElementById('export-btn');
        const exportBtnText = document.getElementById('export-btn-text');
        
        exportBtn.disabled = true;
        exportBtnText.textContent = 'Exporting...';
        
        try {
            const requestBody = {
                tables: tableSelections,
                format: format
            };
            
            const response = await fetch(buildApiUrl('/unified/export'), {
                method: 'POST',
                headers: getAuthHeaders(true),
                body: JSON.stringify(requestBody)
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Export failed');
            }
            
            // Download the file
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `unified_export_${new Date().toISOString().slice(0,10)}.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showAlert(`Export successful! ${tableSelections.length} table(s) joined and exported`, 'success');
            
        } catch (error) {
            showAlert('Export failed: ' + error.message, 'error');
        } finally {
            exportBtn.disabled = false;
            exportBtnText.textContent = 'Export Unified Data';
        }
    });
}

function showAlert(message, type) {
    const container = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'bg-green-100 border-green-500 text-green-900' : 'bg-red-100 border-red-500 text-red-900';
    const iconPath = type === 'success' 
        ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        : 'M6 18L18 6M6 6l12 12';
    
    const alert = document.createElement('div');
    alert.className = `${alertClass} border-l-4 p-4 rounded-lg mb-4 animate-fade-in`;
    alert.innerHTML = `
        <div class="flex items-center">
            <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"/>
            </svg>
            <div class="flex-1 font-semibold">${message}</div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
    
    container.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>

<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
@endsection
