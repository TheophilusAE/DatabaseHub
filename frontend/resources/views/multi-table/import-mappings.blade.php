@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Import Mappings</h1>
                <p class="mt-1 text-sm text-gray-600">Define how data columns map to table columns for imports</p>
            </div>
            <button onclick="openAddMappingModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors shadow-md">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Import Mapping
            </button>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Mappings List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mapping Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Table</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Column Mappings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="mappingsTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-sm">Loading mappings...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Mapping Modal -->
<div id="mappingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Add Import Mapping</h3>
            <button onclick="closeMappingModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="mappingForm" class="mt-4 space-y-4">
            <input type="hidden" id="mappingId" value="">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mapping Name</label>
                    <input type="text" id="mappingName" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., User CSV Import">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Table</label>
                    <select id="tableConfigId" required onchange="loadTableColumns()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select target table...</option>
                    </select>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700">Column Mappings</label>
                    <button type="button" onclick="addMappingRow()" class="text-blue-600 hover:text-blue-800 text-sm inline-flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Mapping
                    </button>
                </div>
                
                <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 max-h-96 overflow-y-auto">
                    <div id="columnMappings" class="space-y-3">
                        <!-- Dynamic mapping rows will be added here -->
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">Map source columns (from your CSV/JSON) to destination columns in the table</p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeMappingModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Mapping
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// : Configure your Go backend API URL here
const API_BASE_URL = 'http://localhost:8080'; // Change this to your Go backend port

let allMappings = [];
let allTables = [];
let currentTableColumns = [];
let mappingRowCounter = 0;

// Load mappings and tables on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    loadMappings();
});

// : Uses API_BASE_URL instead of relative path
async function loadTables() {
    try {
        const response = await fetch(`${API_BASE_URL}/tables`);
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        
        const data = await response.json();
        console.log('Tables response:', data);
        // Response: { "table_configs": [...], "count": N }
        allTables = data.table_configs || data.data || [];
        
        updateTableSelect();
    } catch (error) {
        showAlert('Error loading tables: ' + error.message, 'error');
        console.error('Load tables failed:', error);
    }
}

// : Use correct field names (database_name || name)
function updateTableSelect() {
    const select = document.getElementById('tableConfigId');
    const options = allTables.map(table => 
        `<option value="${table.id}">${table.database_name || table.name} - ${table.table_name}</option>`
    ).join('');
    
    select.innerHTML = '<option value="">Select target table...</option>' + options;
}

// : Handle columns as JSON string or array
async function loadTableColumns() {
    const tableId = document.getElementById('tableConfigId').value;
    if (!tableId) {
        currentTableColumns = [];
        return;
    }
    
    const table = allTables.find(t => t.id == tableId);
    if (!table) {
        currentTableColumns = [];
        return;
    }
    
    // Columns is stored as JSON string in Go model
    try {
        currentTableColumns = typeof table.columns === 'string' 
            ? JSON.parse(table.columns) 
            : table.columns || [];
    } catch (e) {
        console.error('Failed to parse columns:', e);
        currentTableColumns = [];
    }
    
    // Update existing mapping row dropdowns
    document.querySelectorAll('.dest-column-select').forEach(select => {
        const currentValue = select.value;
        const options = currentTableColumns.map(col => 
            `<option value="${col.name}">${col.name} (${col.type})</option>`
        ).join('');
        select.innerHTML = '<option value="">Select destination column...</option>' + options;
        select.value = currentValue;
    });
}

// : Uses API_BASE_URL
async function loadMappings() {
    try {
        const response = await fetch(`${API_BASE_URL}/multi-import/mappings`);
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        
        const data = await response.json();
        console.log('Mappings response:', data);
        allMappings = data.mappings || [];
        
        renderMappingsTable();
    } catch (error) {
        showAlert('Error loading mappings: ' + error.message, 'error');
        console.error('Load mappings failed:', error);
    }
}

function renderMappingsTable() {
    const tbody = document.getElementById('mappingsTableBody');
    
    if (allMappings.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center py-8">
                        <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm">No import mappings configured</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = allMappings.map(mapping => {
        // : Use correct field names
        const table = allTables.find(t => t.id === mapping.table_config_id);
        const tableName = table ? `${table.database_name || table.name}.${table.table_name}` : 'N/A';
        
        let columnMappings = {};
        try {
            columnMappings = typeof mapping.column_mapping === 'string' 
                ? JSON.parse(mapping.column_mapping) 
                : mapping.column_mapping || {};
        } catch (e) {}
        
        const mappingCount = Object.keys(columnMappings).length;
        const mappingPreview = Object.entries(columnMappings).slice(0, 3).map(([src, dest]) => 
            `<span class="inline-block bg-gray-100 rounded px-2 py-1 text-xs mr-1 mb-1">${src} → ${dest}</span>`
        ).join('');
        
        const createdDate = new Date(mapping.created_at).toLocaleDateString();
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${mapping.name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">${tableName}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">
                        ${mappingPreview}
                        ${mappingCount > 3 ? `<span class="text-xs text-gray-500">+${mappingCount - 3} more</span>` : ''}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">${createdDate}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <button onclick="viewMapping(${mapping.id})" class="text-green-600 hover:text-green-900">View</button>
                    <button onclick="editMapping(${mapping.id})" class="text-blue-600 hover:text-blue-900">Edit</button>
                    <button onclick="deleteMapping(${mapping.id})" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            </tr>
        `;
    }).join('');
}

function openAddMappingModal() {
    document.getElementById('modalTitle').textContent = 'Add Import Mapping';
    document.getElementById('mappingForm').reset();
    document.getElementById('mappingId').value = '';
    document.getElementById('columnMappings').innerHTML = '';
    mappingRowCounter = 0;
    
    addMappingRow();
    updateTableSelect();
    document.getElementById('mappingModal').classList.remove('hidden');
}

function closeMappingModal() {
    document.getElementById('mappingModal').classList.add('hidden');
}

function addMappingRow() {
    const container = document.getElementById('columnMappings');
    const rowId = `mapping-row-${mappingRowCounter++}`;
    
    const destOptions = currentTableColumns.map(col => 
        `<option value="${col.name}">${col.name} (${col.type})</option>`
    ).join('');
    
    const rowHtml = `
        <div id="${rowId}" class="flex items-center space-x-2 bg-white p-3 rounded border border-gray-200">
            <div class="flex-1">
                <input type="text" class="source-column w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                       placeholder="Source column name (from CSV/JSON)">
            </div>
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
            <div class="flex-1">
                <select class="dest-column-select w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="">Select destination column...</option>
                    ${destOptions}
                </select>
            </div>
            <button type="button" onclick="removeMappingRow('${rowId}')" class="text-red-600 hover:text-red-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', rowHtml);
}

function removeMappingRow(rowId) {
    document.getElementById(rowId).remove();
}

function editMapping(id) {
    const mapping = allMappings.find(m => m.id === id);
    if (!mapping) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Import Mapping';
    document.getElementById('mappingId').value = mapping.id;
    document.getElementById('mappingName').value = mapping.name;
    document.getElementById('tableConfigId').value = mapping.table_config_id;
    
    loadTableColumns();
    
    document.getElementById('columnMappings').innerHTML = '';
    mappingRowCounter = 0;
    
    let columnMappings = {};
    try {
        columnMappings = typeof mapping.column_mapping === 'string' 
            ? JSON.parse(mapping.column_mapping) 
            : mapping.column_mapping || {};
    } catch (e) {}
    
    Object.entries(columnMappings).forEach(([source, dest]) => {
        addMappingRow();
        const lastRow = document.getElementById('columnMappings').lastElementChild;
        lastRow.querySelector('.source-column').value = source;
        lastRow.querySelector('.dest-column-select').value = dest;
    });
    
    if (Object.keys(columnMappings).length === 0) {
        addMappingRow();
    }
    
    document.getElementById('mappingModal').classList.remove('hidden');
}

function viewMapping(id) {
    const mapping = allMappings.find(m => m.id === id);
    if (!mapping) return;
    
    let columnMappings = {};
    try {
        columnMappings = typeof mapping.column_mapping === 'string' 
            ? JSON.parse(mapping.column_mapping) 
            : mapping.column_mapping || {};
    } catch (e) {}
    
    const table = allTables.find(t => t.id === mapping.table_config_id);
    const tableName = table ? `${table.database_name || table.name}.${table.table_name}` : 'N/A';
    
    const mappingsList = Object.entries(columnMappings).map(([src, dest]) => 
        `<li class="flex items-center py-2 border-b border-gray-200">
            <span class="flex-1 font-mono text-sm">${src}</span>
            <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
            <span class="flex-1 font-mono text-sm text-right">${dest}</span>
        </li>`
    ).join('');
    
    showAlert(`
        <div class="mb-2"><strong>${mapping.name}</strong></div>
        <div class="text-sm mb-2">Target: ${tableName}</div>
        <div class="bg-white rounded p-3 max-h-64 overflow-y-auto">
            <ul class="divide-y divide-gray-200">${mappingsList || '<li class="py-2 text-gray-500">No mappings</li>'}</ul>
        </div>
    `, 'info');
}

// : Uses API_BASE_URL and correct field names
document.getElementById('mappingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const mappingId = document.getElementById('mappingId').value;
    
    const columnMappings = {};
    document.querySelectorAll('#columnMappings > div').forEach(row => {
        const source = row.querySelector('.source-column').value.trim();
        const dest = row.querySelector('.dest-column-select').value;
        if (source && dest) {
            columnMappings[source] = dest;
        }
    });
    
    if (Object.keys(columnMappings).length === 0) {
        showAlert('Please add at least one column mapping', 'error');
        return;
    }
    
    const mappingData = {
        name: document.getElementById('mappingName').value,
        table_config_id: parseInt(document.getElementById('tableConfigId').value),
        column_mapping: JSON.stringify(columnMappings),
        source_format: 'csv'
    };
    
    try {
        // : Uses API_BASE_URL
        const url = mappingId 
            ? `${API_BASE_URL}/multi-import/mappings/${mappingId}` 
            : `${API_BASE_URL}/multi-import/mappings`;
        const method = mappingId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(mappingData)
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || error.message || 'Failed to save mapping');
        }
        
        const result = await response.json();
        console.log('Save response:', result);
        
        showAlert(mappingId ? 'Import mapping updated successfully' : 'Import mapping added successfully', 'success');
        closeMappingModal();
        loadMappings();
    } catch (error) {
        showAlert('Error: ' + error.message, 'error');
        console.error('Save mapping failed:', error);
    }
});

// : Uses API_BASE_URL
async function deleteMapping(id) {
    if (!confirm('Are you sure you want to delete this import mapping?')) return;
    
    try {
        const response = await fetch(`${API_BASE_URL}/multi-import/mappings/${id}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || error.message || 'Failed to delete mapping');
        }
        
        showAlert('Import mapping deleted successfully', 'success');
        loadMappings();
    } catch (error) {
        showAlert('Error: ' + error.message, 'error');
        console.error('Delete mapping failed:', error);
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    let alertClass = 'bg-blue-100 border-blue-500 text-blue-700';
    
    if (type === 'success') {
        alertClass = 'bg-green-100 border-green-500 text-green-700';
    } else if (type === 'error') {
        alertClass = 'bg-red-100 border-red-500 text-red-700';
    }
    
    alertContainer.innerHTML = `
        <div class="${alertClass} border-l-4 p-4 mb-4 rounded" role="alert">
            <div>${message}</div>
        </div>
    `;
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}
</script>
@endsection