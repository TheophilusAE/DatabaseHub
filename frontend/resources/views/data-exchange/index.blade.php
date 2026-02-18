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
                <p class="text-gray-600 mb-8 text-lg">Upload a CSV or JSON file to import data into any table</p>

                <form id="import-form" class="space-y-6">
                    <!-- Table Selection -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border-2 border-blue-200">
                        <label class="block text-lg font-bold text-gray-800 mb-3">
                            <svg class="inline h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            Select Table *
                        </label>
                        <select id="import-table-select" required
                            class="w-full px-6 py-4 text-lg border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-300 focus:border-blue-500 transition-all">
                            <option value="">Loading tables...</option>
                        </select>
                        <p class="mt-3 text-sm text-gray-600">Choose which table to import your data into</p>
                    </div>

                    <!-- File Upload -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl border-2 border-green-200">
                        <label class="block text-lg font-bold text-gray-800 mb-3">
                            <svg class="inline h-6 w-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Upload File *
                        </label>
                        <div class="mt-2 flex justify-center px-8 pt-8 pb-8 border-4 border-dashed border-green-300 rounded-xl hover:border-green-500 hover:bg-white transition-all cursor-pointer" id="drop-zone">
                            <div class="space-y-2 text-center">
                                <svg class="mx-auto h-16 w-16 text-green-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="flex text-base text-gray-600 justify-center">
                                    <label for="import-file-input" class="relative cursor-pointer bg-white rounded-md font-bold text-green-600 hover:text-green-700 px-3 py-1">
                                        <span class="text-xl">Choose a file</span>
                                        <input id="import-file-input" name="file" type="file" class="sr-only" accept=".csv,.json" required>
                                    </label>
                                    <p class="pl-2 text-xl">or drag and drop</p>
                                </div>
                                <p class="text-base text-gray-500 font-semibold">CSV or JSON files up to 500MB</p>
                            </div>
                        </div>
                        <div id="import-file-info" class="mt-4 text-base text-gray-700 hidden"></div>
                    </div>

                    <!-- Import Button -->
                    <button type="submit" id="import-btn"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-5 px-8 rounded-xl text-xl hover:from-blue-700 hover:to-indigo-700 transition-all transform hover:scale-[1.02] hover:shadow-2xl flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span id="import-btn-text">Start Import</span>
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
let allTables = [];
let tableColumns = {}; // Cache for table columns
let tableSelectionCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    setupFileDropZone();
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
        const response = await fetch('http://localhost:8080/simple-multi/tables');
        if (!response.ok) throw new Error('Failed to load tables');
        
        const data = await response.json();
        allTables = data.tables || [];
        
        // Update import select
        const importSelect = document.getElementById('import-table-select');
        importSelect.innerHTML = '<option value="">Select a table...</option>' + 
            allTables.map(table => `<option value="${table.table_name}">${table.table_name} (${table.row_count} rows)</option>`).join('');
        
    } catch (error) {
        showAlert('Error loading tables: ' + error.message, 'error');
    }
}

async function loadTableColumns(tableName) {
    if (tableColumns[tableName]) {
        return tableColumns[tableName];
    }
    
    try {
        const response = await fetch(`http://localhost:8080/simple-multi/tables/${tableName}/columns`);
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
                    Filter (Optional)
                    <span class="text-xs font-normal text-gray-500">- SQL WHERE condition</span>
                </label>
                <input type="text" class="filter-input w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 font-mono text-sm"
                       placeholder="e.g., status = 'active' AND created_at > '2024-01-01'">
                <p class="mt-1 text-xs text-gray-600">Leave empty to export all rows</p>
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
    } else {
        columnsList.innerHTML = '<p class="text-red-500 col-span-full text-center">No columns found</p>';
    }
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

function setupFileDropZone() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('import-file-input');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        }, false);
    });
    
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        handleFileSelect();
    }, false);
    
    fileInput.addEventListener('change', handleFileSelect);
}

function handleFileSelect() {
    const fileInput = document.getElementById('import-file-input');
    const fileInfo = document.getElementById('import-file-info');
    
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        fileInfo.innerHTML = `
            <div class="flex items-center bg-green-100 border-2 border-green-500 rounded-lg p-4">
                <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="font-bold text-green-900">${file.name}</p>
                    <p class="text-sm text-green-700">${(file.size / (1024*1024)).toFixed(2)} MB</p>
                </div>
            </div>
        `;
        fileInfo.classList.remove('hidden');
    }
}

function setupFormHandlers() {
    // Import form
    document.getElementById('import-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const table = document.getElementById('import-table-select').value;
        const fileInput = document.getElementById('import-file-input');
        
        if (!table || !fileInput.files.length) {
            showAlert('Please select a table and file', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('table', table);
        formData.append('file', fileInput.files[0]);
        
        const importBtn = document.getElementById('import-btn');
        const importBtnText = document.getElementById('import-btn-text');
        const progress = document.getElementById('import-progress');
        const progressBar = document.getElementById('import-progress-bar');
        const progressText = document.getElementById('import-progress-text');
        
        importBtn.disabled = true;
        importBtnText.textContent = 'Importing...';
        progress.classList.remove('hidden');
        progressBar.style.width = '50%';
        progressText.textContent = 'Uploading and processing data...';
        
        try {
            const response = await fetch('http://localhost:8080/unified/import', {
                method: 'POST',
                body: formData,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            });
            
            if (!response.ok) throw new Error('Import failed');
            
            const result = await response.json();
            
            progressBar.style.width = '100%';
            progressText.textContent = 'Import completed!';
            
            setTimeout(() => {
                showAlert(`Import successful! ${result.success_count} records imported${result.failure_count > 0 ? ', ' + result.failure_count + ' failed' : ''}`, 'success');
                progress.classList.add('hidden');
                document.getElementById('import-form').reset();
                document.getElementById('import-file-info').classList.add('hidden');
            }, 1000);
            
        } catch (error) {
            showAlert('Import failed: ' + error.message, 'error');
            progress.classList.add('hidden');
        } finally {
            importBtn.disabled = false;
            importBtnText.textContent = 'Start Import';
        }
    });
    
    // Export form
    document.getElementById('export-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Collect all table selections
        const tableSelections = [];
        document.querySelectorAll('.table-selection').forEach(container => {
            const tableName = container.querySelector('.table-select').value;
            if (!tableName) return;
            
            const selectedColumns = Array.from(container.querySelectorAll('.column-checkbox:checked'))
                .map(cb => cb.value);
            
            const filter = container.querySelector('.filter-input').value.trim();
            
            tableSelections.push({
                table_name: tableName,
                columns: selectedColumns,
                filters: filter
            });
        });
        
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
            
            const response = await fetch('http://localhost:8080/unified/export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
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
