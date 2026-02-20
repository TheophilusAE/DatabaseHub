@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Selective Data Export</h1>
            <p class="mt-1 text-sm text-gray-600">Choose specific tables, columns, and filters to export your data</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Export Configuration -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-2">Select Tables and Data to Export</h2>
                <div class="flex items-center space-x-4 mb-4">
                    <label class="text-sm font-medium text-gray-700">Export Format:</label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="exportFormat" value="csv" checked class="text-blue-600">
                        <span class="ml-2">CSV</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="exportFormat" value="json" class="text-blue-600">
                        <span class="ml-2">JSON</span>
                    </label>
                </div>
            </div>

            <!-- Table Selection Items -->
            <div id="exportItemsContainer" class="space-y-4 mb-6"></div>

            <!-- Add Table Button -->
            <button onclick="addExportItem()" class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-gray-600 hover:text-blue-600 flex items-center justify-center">
                <svg class="h-6 w-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Another Table
            </button>

            <!-- Export Button -->
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="clearAll()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Clear All
                </button>
                <button onclick="startExport()" id="exportBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span id="exportBtnText">Export Data</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export Item Template (Hidden) -->
<template id="exportItemTemplate">
    <div class="export-item border border-gray-200 rounded-lg p-4 bg-gray-50">
        <div class="flex items-start justify-between mb-4">
            <h3 class="text-md font-semibold text-gray-900">Table Selection</h3>
            <button onclick="removeExportItem(this)" class="p-1 text-red-600 hover:bg-red-50 rounded">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="space-y-4">
            <!-- Table Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Table</label>
                <select class="table-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                        required onchange="loadTableColumns(this)">
                    <option value="">Select table...</option>
                </select>
            </div>

            <!-- Column Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Columns
                    <span class="text-xs text-gray-500">(leave empty to select all)</span>
                </label>
                <div class="columns-container border border-gray-200 rounded-lg p-3 max-h-40 overflow-y-auto bg-white">
                    <p class="text-sm text-gray-500 italic">Select a table first</p>
                </div>
            </div>

            <!-- Filter Conditions -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Filter Conditions
                    <span class="text-xs text-gray-500">(optional SQL WHERE clause)</span>
                </label>
                <input type="text" class="filter-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                       placeholder="e.g., status = 'active' AND created_at > '2024-01-01'">
                <p class="mt-1 text-xs text-gray-500">Enter SQL conditions without the WHERE keyword</p>
            </div>
        </div>
    </div>
</template>

<script>
let allTables = [];
let tableColumns = {}; // Cache for table columns
const SESSION_USER_ID = {{ session('user')['id'] ?? 'null' }};
const SESSION_USER_ROLE = '{{ strtolower(session('user')['role'] ?? 'user') }}';

function getCurrentUser() {
    return {
        userId: SESSION_USER_ID || localStorage.getItem('user_id') || sessionStorage.getItem('user_id'),
        userRole: SESSION_USER_ROLE || localStorage.getItem('user_role') || 'user'
    };
}

function buildApiUrl(path) {
    const { userId, userRole } = getCurrentUser();
    const url = new URL(`http://localhost:8080${path}`);
    if (userId) url.searchParams.append('user_id', String(userId));
    if (userRole) url.searchParams.append('user_role', String(userRole).toLowerCase());
    return url;
}

document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    addExportItem(); // Add initial export item
});

async function loadTables() {
    try {
        const { userRole } = getCurrentUser();
        const response = await fetch(buildApiUrl('/simple-multi/tables').toString(), {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-User-Role': String(userRole || 'user').toLowerCase(),
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        if (!response.ok) throw new Error('Failed to load tables');
        
        const data = await response.json();
        allTables = data.tables || [];
        
        // Update all existing table selects
        updateAllTableSelects();
    } catch (error) {
        showAlert('Error loading tables: ' + error.message, 'error');
    }
}

function updateAllTableSelects() {
    document.querySelectorAll('.table-select').forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '<option value="">Select table...</option>' + 
            allTables.map(table => `<option value="${table.table_name}">${table.table_name} (${table.row_count} rows)</option>`).join('');
        if (currentValue) {
            select.value = currentValue;
        }
    });
}

function addExportItem() {
    const template = document.getElementById('exportItemTemplate');
    const clone = template.content.cloneNode(true);
    
    // Update table select with loaded tables
    const select = clone.querySelector('.table-select');
    select.innerHTML = '<option value="">Select table...</option>' + 
        allTables.map(table => `<option value="${table.table_name}">${table.table_name} (${table.row_count} rows)</option>`).join('');
    
    document.getElementById('exportItemsContainer').appendChild(clone);
}

function removeExportItem(button) {
    const item = button.closest('.export-item');
    item.remove();
    
    // If no items left, add one
    if (document.querySelectorAll('.export-item').length === 0) {
        addExportItem();
    }
}

async function loadTableColumns(selectElement) {
    const tableName = selectElement.value;
    const item = selectElement.closest('.export-item');
    const columnsContainer = item.querySelector('.columns-container');
    
    if (!tableName) {
        columnsContainer.innerHTML = '<p class="text-sm text-gray-500 italic">Select a table first</p>';
        return;
    }
    
    // Check cache first
    if (tableColumns[tableName]) {
        renderColumnCheckboxes(columnsContainer, tableColumns[tableName]);
        return;
    }
    
    // Show loading
    columnsContainer.innerHTML = '<p class="text-sm text-gray-500">Loading columns...</p>';
    
    try {
        const { userRole } = getCurrentUser();
        const response = await fetch(buildApiUrl(`/simple-multi/tables/${encodeURIComponent(tableName)}/columns`).toString(), {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-User-Role': String(userRole || 'user').toLowerCase(),
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        if (!response.ok) throw new Error('Failed to load columns');
        
        const data = await response.json();
        tableColumns[tableName] = data.columns || [];
        
        renderColumnCheckboxes(columnsContainer, data.columns);
    } catch (error) {
        columnsContainer.innerHTML = '<p class="text-sm text-red-500">Error loading columns</p>';
    }
}

function renderColumnCheckboxes(container, columns) {
    if (!columns || columns.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500">No columns found</p>';
        return;
    }
    
    const html = `
        <div class="mb-2">
            <label class="inline-flex items-center text-sm text-blue-600 cursor-pointer">
                <input type="checkbox" class="select-all-columns rounded" onchange="toggleAllColumns(this)">
                <span class="ml-2 font-semibold">Select All</span>
            </label>
        </div>
        <div class="space-y-1">
            ${columns.map(col => `
                <label class="flex items-center text-sm cursor-pointer hover:bg-blue-50 p-1 rounded">
                    <input type="checkbox" class="column-checkbox rounded text-blue-600" value="${col.name}">
                    <span class="ml-2">${col.name}</span>
                    <span class="ml-auto text-xs text-gray-500">${col.type}</span>
                </label>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = html;
}

function toggleAllColumns(checkbox) {
    const item = checkbox.closest('.export-item');
    const columnCheckboxes = item.querySelectorAll('.column-checkbox');
    columnCheckboxes.forEach(cb => cb.checked = checkbox.checked);
}

function clearAll() {
    document.getElementById('exportItemsContainer').innerHTML = '';
    addExportItem();
}

async function startExport() {
    const { userRole } = getCurrentUser();
    const items = document.querySelectorAll('.export-item');
    const exportBtn = document.getElementById('exportBtn');
    const exportBtnText = document.getElementById('exportBtnText');
    const format = document.querySelector('input[name="exportFormat"]:checked').value;
    
    // Build export request
    const tables = [];
    let valid = true;
    
    items.forEach(item => {
        const tableSelect = item.querySelector('.table-select');
        const filterInput = item.querySelector('.filter-input');
        const columnCheckboxes = item.querySelectorAll('.column-checkbox:checked');
        
        if (!tableSelect.value) {
            showAlert('Please select a table for all export items', 'error');
            valid = false;
            return;
        }
        
        const selectedColumns = Array.from(columnCheckboxes).map(cb => cb.value);
        
        tables.push({
            table_name: tableSelect.value,
            columns: selectedColumns,  // empty array means all columns
            filters: filterInput.value.trim()
        });
    });
    
    if (!valid || tables.length === 0) return;
    
    // Disable button
    exportBtn.disabled = true;
    exportBtnText.textContent = 'Exporting...';
    
    try {
        const response = await fetch(buildApiUrl('/simple-multi/export-selected').toString(), {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-User-Role': String(userRole || 'user').toLowerCase(),
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                tables: tables,
                format: format
            })
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Export failed');
        }
        
        // Get filename from response headers or generate one
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = `export_${new Date().toISOString().replace(/[:.]/g, '-')}.${format}`;
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }
        
        // Download the file
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showAlert('Export completed successfully!', 'success');
        
    } catch (error) {
        showAlert('Error during export: ' + error.message, 'error');
    } finally {
        exportBtn.disabled = false;
        exportBtnText.textContent = 'Export Data';
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    let alertClass = 'bg-blue-100 border-blue-500 text-blue-700';
    
    if (type === 'success') {
        alertClass = 'bg-green-100 border-green-500 text-green-700';
    } else if (type === 'error') {
        alertClass = 'bg-red-100 border-red-500 text-red-700';
    } else if (type === 'warning') {
        alertClass = 'bg-yellow-100 border-yellow-500 text-yellow-700';
    }
    
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
