@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Export Configurations</h1>
                <p class="mt-1 text-sm text-gray-600">Define export formats with filters, sorting, and column selection</p>
            </div>
            <button onclick="openAddConfigModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors shadow-md">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Export Config
            </button>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Configs List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Config Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Export Format</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filters/Settings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="configsTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-sm">No export configurations</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Config Modal -->
<div id="configModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-lg bg-white mb-10">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Add Export Configuration</h3>
            <button onclick="closeConfigModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="configForm" class="mt-4 space-y-4">
            <input type="hidden" id="configId" value="">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Config Name</label>
                    <input type="text" id="configName" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Orders Export">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Export Format</label>
                    <select id="exportFormat" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Source</label>
                <div class="flex space-x-4 mb-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="sourceType" value="table" checked onchange="toggleSourceOptions()">
                        <span class="ml-2 text-sm">Single Table</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="sourceType" value="join" onchange="toggleSourceOptions()">
                        <span class="ml-2 text-sm">Table Join</span>
                    </label>
                </div>
                
                <select id="tableConfigId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select table...</option>
                </select>
                
                <select id="joinId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent hidden">
                    <option value="">Select join...</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Filter Conditions (Optional)
                </label>
                <textarea id="filterConditions" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                          placeholder="e.g., status = 'active' AND created_at > '2024-01-01'"></textarea>
                <p class="mt-1 text-xs text-gray-500">SQL WHERE conditions (without the WHERE keyword)</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort By (Optional)</label>
                    <input type="text" id="sortBy"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., created_at">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <select id="sortOrder"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="ASC">Ascending</option>
                        <option value="DESC">Descending</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Selected Columns (Optional)
                </label>
                <textarea id="selectedColumns" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                          placeholder="Leave empty to export all columns, or specify comma-separated: id, name, email, created_at"></textarea>
                <p class="mt-1 text-xs text-gray-500">Comma-separated list of columns to export (leave empty for all)</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Limit Records (Optional)</label>
                    <input type="number" id="limitRecords" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Leave empty for no limit">
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="includeHeaders" checked class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Include headers in CSV export</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeConfigModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let allConfigs = [];
let allTables = [];
let allJoins = [];

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    loadJoins();
    loadConfigs();
});

async function loadTables() {
    try {
        const response = await fetch('/api/multi-table/table-configs');
        if (!response.ok) throw new Error('Failed to load tables');
        
        const data = await response.json();
        allTables = data.table_configs || [];
        
        updateTableSelect();
    } catch (error) {
        showAlert('Error loading tables: ' + error.message, 'error');
    }
}

async function loadJoins() {
    try {
        const response = await fetch('/api/multi-table/table-joins');
        if (!response.ok) throw new Error('Failed to load joins');
        
        const data = await response.json();
        allJoins = data.joins || [];
        
        updateJoinSelect();
    } catch (error) {
        showAlert('Error loading joins: ' + error.message, 'error');
    }
}

function updateTableSelect() {
    const select = document.getElementById('tableConfigId');
    const options = allTables.map(table => 
        `<option value="${table.id}">${table.database_config_name} - ${table.table_name}</option>`
    ).join('');
    
    select.innerHTML = '<option value="">Select table...</option>' + options;
}

function updateJoinSelect() {
    const select = document.getElementById('joinId');
    const options = allJoins.map(join => 
        `<option value="${join.id}">${join.name}</option>`
    ).join('');
    
    select.innerHTML = '<option value="">Select join...</option>' + options;
}

function toggleSourceOptions() {
    const sourceType = document.querySelector('input[name="sourceType"]:checked').value;
    const tableSelect = document.getElementById('tableConfigId');
    const joinSelect = document.getElementById('joinId');
    
    if (sourceType === 'table') {
        tableSelect.classList.remove('hidden');
        tableSelect.required = true;
        joinSelect.classList.add('hidden');
        joinSelect.required = false;
    } else {
        tableSelect.classList.add('hidden');
        tableSelect.required = false;
        joinSelect.classList.remove('hidden');
        joinSelect.required = true;
    }
}

async function loadConfigs() {
    try {
        const response = await fetch('/api/multi-table/export-configs');
        if (!response.ok) throw new Error('Failed to load configurations');
        
        const data = await response.json();
        allConfigs = data.configs || [];
        
        renderConfigsTable();
    } catch (error) {
        showAlert('Error loading configurations: ' + error.message, 'error');
    }
}

function renderConfigsTable() {
    const tbody = document.getElementById('configsTableBody');
    
    if (allConfigs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center py-8">
                        <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm">No export configurations</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = allConfigs.map(config => {
        const table = config.table_config_id ? allTables.find(t => t.id === config.table_config_id) : null;
        const join = config.join_id ? allJoins.find(j => j.id === config.join_id) : null;
        
        const source = table ? `${table.database_config_name}.${table.table_name}` : 
                       join ? `Join: ${join.name}` : 'N/A';
        
        const formatBadge = config.export_format === 'csv' ? 
            'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
        
        const hasFilters = config.filter_conditions || config.sort_by || config.selected_columns;
        const filterInfo = [];
        if (config.filter_conditions) filterInfo.push('Filtered');
        if (config.sort_by) filterInfo.push('Sorted');
        if (config.selected_columns) filterInfo.push('Select columns');
        if (config.limit_records) filterInfo.push(`Limit ${config.limit_records}`);
        
        const createdDate = new Date(config.created_at).toLocaleDateString();
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${config.name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">${source}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${formatBadge}">
                        ${config.export_format.toUpperCase()}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">
                        ${filterInfo.length > 0 ? filterInfo.join(', ') : 'None'}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">${createdDate}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <button onclick="viewConfig(${config.id})" class="text-green-600 hover:text-green-900">View</button>
                    <button onclick="editConfig(${config.id})" class="text-blue-600 hover:text-blue-900">Edit</button>
                    <button onclick="deleteConfig(${config.id})" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            </tr>
        `;
    }).join('');
}

function openAddConfigModal() {
    document.getElementById('modalTitle').textContent = 'Add Export Configuration';
    document.getElementById('configForm').reset();
    document.getElementById('configId').value = '';
    document.querySelector('input[name="sourceType"][value="table"]').checked = true;
    toggleSourceOptions();
    updateTableSelect();
    updateJoinSelect();
    document.getElementById('configModal').classList.remove('hidden');
}

function closeConfigModal() {
    document.getElementById('configModal').classList.add('hidden');
}

function editConfig(id) {
    const config = allConfigs.find(c => c.id === id);
    if (!config) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Export Configuration';
    document.getElementById('configId').value = config.id;
    document.getElementById('configName').value = config.name;
    document.getElementById('exportFormat').value = config.export_format;
    
    if (config.table_config_id) {
        document.querySelector('input[name="sourceType"][value="table"]').checked = true;
        document.getElementById('tableConfigId').value = config.table_config_id;
    } else if (config.join_id) {
        document.querySelector('input[name="sourceType"][value="join"]').checked = true;
        document.getElementById('joinId').value = config.join_id;
    }
    toggleSourceOptions();
    
    document.getElementById('filterConditions').value = config.filter_conditions || '';
    document.getElementById('sortBy').value = config.sort_by || '';
    document.getElementById('sortOrder').value = config.sort_order || 'ASC';
    document.getElementById('selectedColumns').value = config.selected_columns || '';
    document.getElementById('limitRecords').value = config.limit_records || '';
    document.getElementById('includeHeaders').checked = config.include_headers;
    
    document.getElementById('configModal').classList.remove('hidden');
}

function viewConfig(id) {
    const config = allConfigs.find(c => c.id === id);
    if (!config) return;
    
    const table = config.table_config_id ? allTables.find(t => t.id === config.table_config_id) : null;
    const join = config.join_id ? allJoins.find(j => j.id === config.join_id) : null;
    const source = table ? `${table.database_config_name}.${table.table_name}` : 
                   join ? `Join: ${join.name}` : 'N/A';
    
    const details = `
        <div class="space-y-3">
            <div><strong>Name:</strong> ${config.name}</div>
            <div><strong>Source:</strong> ${source}</div>
            <div><strong>Format:</strong> ${config.export_format.toUpperCase()}</div>
            ${config.filter_conditions ? `<div><strong>Filters:</strong> <code class="text-sm">${config.filter_conditions}</code></div>` : ''}
            ${config.sort_by ? `<div><strong>Sort:</strong> ${config.sort_by} ${config.sort_order}</div>` : ''}
            ${config.selected_columns ? `<div><strong>Columns:</strong> ${config.selected_columns}</div>` : ''}
            ${config.limit_records ? `<div><strong>Limit:</strong> ${config.limit_records} records</div>` : ''}
            <div><strong>Include Headers:</strong> ${config.include_headers ? 'Yes' : 'No'}</div>
        </div>
    `;
    
    showAlert(details, 'info');
}

document.getElementById('configForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const configId = document.getElementById('configId').value;
    const sourceType = document.querySelector('input[name="sourceType"]:checked').value;
    
    const configData = {
        name: document.getElementById('configName').value,
        export_format: document.getElementById('exportFormat').value,
        table_config_id: sourceType === 'table' ? parseInt(document.getElementById('tableConfigId').value) || null : null,
        join_id: sourceType === 'join' ? parseInt(document.getElementById('joinId').value) || null : null,
        filter_conditions: document.getElementById('filterConditions').value || null,
        sort_by: document.getElementById('sortBy').value || null,
        sort_order: document.getElementById('sortOrder').value,
        selected_columns: document.getElementById('selectedColumns').value || null,
        limit_records: parseInt(document.getElementById('limitRecords').value) || null,
        include_headers: document.getElementById('includeHeaders').checked
    };
    
    try {
        const url = configId ? `/api/multi-table/export-configs/${configId}` : '/api/multi-table/export-configs';
        const method = configId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(configData)
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to save configuration');
        }
        
        showAlert(configId ? 'Export configuration updated successfully' : 'Export configuration added successfully', 'success');
        closeConfigModal();
        loadConfigs();
    } catch (error) {
        showAlert('Error: ' + error.message, 'error');
    }
});

async function deleteConfig(id) {
    if (!confirm('Are you sure you want to delete this export configuration?')) return;
    
    try {
        const response = await fetch(`/api/multi-table/export-configs/${id}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to delete configuration');
        }
        
        showAlert('Export configuration deleted successfully', 'success');
        loadConfigs();
    } catch (error) {
        showAlert('Error: ' + error.message, 'error');
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
