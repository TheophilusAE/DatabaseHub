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
                                    <p class="text-sm">Loading configurations...</p>
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
// : Configure your Go backend API URL here
const API_BASE = 'http://localhost:8080'; // Change this to your Go backend port

let allConfigs = [];
let allTables = [];
let allJoins = [];
const currentUserId = {{ session('user')['id'] ?? 'null' }};
const currentUserRole = '{{ session('user')['role'] ?? '' }}';

function buildApiUrl(path, query = {}) {
    const url = new URL(`${API_BASE}${path}`);
    if (currentUserId) url.searchParams.set('user_id', String(currentUserId));
    if (currentUserRole) url.searchParams.set('user_role', currentUserRole);
    Object.entries(query).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            url.searchParams.set(key, String(value));
        }
    });
    return url.toString();
}

function authHeaders(includeJson = false) {
    const headers = {
        'Accept': 'application/json',
        'X-User-ID': currentUserId ? String(currentUserId) : '',
        'X-User-Role': currentUserRole || ''
    };
    if (includeJson) {
        headers['Content-Type'] = 'application/json';
    }
    return headers;
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    loadJoins();
    loadConfigs();
});

// 🔁 FIXED: Load tables from your Go route /tables
async function loadTables() {
    try {
        const response = await fetch(buildApiUrl('/tables'), {
            headers: authHeaders()
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        
        const data = await response.json();
        // Handle both array and object responses from Go
        allTables = Array.isArray(data) ? data : (data.table_configs || data.configs || data || []);
        updateTableSelect();
    } catch (error) {
        console.error('Load tables error:', error);
        showAlert('Error loading tables: ' + error.message, 'error');
    }
}

// 🔁 FIXED: Load joins from your Go route /joins
async function loadJoins() {
    try {
        const response = await fetch(buildApiUrl('/joins'), {
            headers: authHeaders()
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        
        const data = await response.json();
        allJoins = Array.isArray(data) ? data : (data.joins || data.configs || data || []);
        updateJoinSelect();
    } catch (error) {
        console.error('Load joins error:', error);
        showAlert('Error loading joins: ' + error.message, 'error');
    }
}

// 🔁 FIXED: Load configs from your Go route /multi-export/configs
async function loadConfigs() {
    try {
        const response = await fetch(buildApiUrl('/multi-export/configs'), {
            headers: authHeaders()
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        
        const data = await response.json();
        allConfigs = Array.isArray(data) ? data : (data.configs || data.export_configs || data || []);
        renderConfigsTable();
    } catch (error) {
        console.error('Load configs error:', error);
        showAlert('Error loading configurations: ' + error.message, 'error');
    }
}

function updateTableSelect() {
    const select = document.getElementById('tableConfigId');
    if (!Array.isArray(allTables) || allTables.length === 0) {
        select.innerHTML = '<option value="">No tables available</option>';
        return;
    }
    
    const options = allTables.map(table => {
        const label = table.name || table.table_name || `Table ${table.id}`;
        const db = table.database_config_name || table.database || '';
        return `<option value="${table.id}">${db ? db + '.' : ''}${label}</option>`;
    }).join('');
    
    select.innerHTML = '<option value="">Select table...</option>' + options;
}

function updateJoinSelect() {
    const select = document.getElementById('joinId');
    if (!Array.isArray(allJoins) || allJoins.length === 0) {
        select.innerHTML = '<option value="">No joins available</option>';
        return;
    }
    
    const options = allJoins.map(join => {
        const label = join.name || join.join_name || `Join ${join.id}`;
        return `<option value="${join.id}">${label}</option>`;
    }).join('');
    
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

function renderConfigsTable() {
    const tbody = document.getElementById('configsTableBody');
    
    if (!Array.isArray(allConfigs) || allConfigs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center py-8">
                        <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm">No export configurations</p>
                        <button onclick="openAddConfigModal()" class="mt-3 text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Create your first config →
                        </button>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = allConfigs.map(config => {
        // Find source info
        const table = config.table_config_id ? allTables.find(t => t.id == config.table_config_id || t.id === config.table_config_id) : null;
        const join = config.join_id ? allJoins.find(j => j.id == config.join_id || j.id === config.join_id) : null;
        
        let source = 'N/A';
        if (table) {
            source = `${table.database_config_name || table.database || 'db'}.${table.table_name || table.name || 'table'}`;
        } else if (join) {
            source = `Join: ${join.name || join.join_name || join.id}`;
        }
        
        const formatBadge = config.export_format === 'csv' ? 
            'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
        
        const filterInfo = [];
        if (config.filter_conditions) filterInfo.push('Filtered');
        if (config.sort_by) filterInfo.push('Sorted');
        if (config.selected_columns) filterInfo.push('Columns');
        if (config.limit_records) filterInfo.push(`Limit:${config.limit_records}`);
        
        const createdDate = config.created_at ? new Date(config.created_at).toLocaleDateString() : 'N/A';
        const configId = config.id || config.config_id;
        
        return `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${config.name || config.config_name || 'Unnamed'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600" title="${source}">${source.length > 25 ? source.substring(0,22)+'...' : source}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${formatBadge}">
                        ${(config.export_format || 'CSV').toUpperCase()}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">
                        ${filterInfo.length > 0 ? filterInfo.join(', ') : '<span class="text-gray-400">None</span>'}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">${createdDate}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <button onclick="viewConfig(${configId})" class="text-green-600 hover:text-green-900 font-medium">View</button>
                    <button onclick="editConfig(${configId})" class="text-blue-600 hover:text-blue-900 font-medium">Edit</button>
                    <button onclick="deleteConfig(${configId})" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
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
    const config = allConfigs.find(c => (c.id == id || c.id === id || c.config_id == id));
    if (!config) {
        showAlert('Configuration not found', 'error');
        return;
    }
    
    document.getElementById('modalTitle').textContent = 'Edit Export Configuration';
    document.getElementById('configId').value = config.id || config.config_id || '';
    document.getElementById('configName').value = config.name || config.config_name || '';
    document.getElementById('exportFormat').value = config.export_format || 'csv';
    
    // Set source type and values
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
    document.getElementById('sortOrder').value = config.sort_order || config.sortOrder || 'ASC';
    document.getElementById('selectedColumns').value = config.selected_columns || '';
    document.getElementById('limitRecords').value = config.limit_records || config.limitRecords || '';
    document.getElementById('includeHeaders').checked = config.include_headers !== false;
    
    document.getElementById('configModal').classList.remove('hidden');
}

function viewConfig(id) {
    const config = allConfigs.find(c => (c.id == id || c.id === id || c.config_id == id));
    if (!config) {
        showAlert('Configuration not found', 'error');
        return;
    }
    
    const table = config.table_config_id ? allTables.find(t => t.id == config.table_config_id) : null;
    const join = config.join_id ? allJoins.find(j => j.id == config.join_id) : null;
    const source = table ? `${table.database_config_name || table.database}.${table.table_name || table.name}` : 
                   join ? `Join: ${join.name || join.join_name}` : 'N/A';
    
    const details = `
        <div class="space-y-2 text-sm">
            <div><strong class="text-gray-700">Name:</strong> <span class="text-gray-900">${config.name || config.config_name}</span></div>
            <div><strong class="text-gray-700">Source:</strong> <span class="text-gray-900">${source}</span></div>
            <div><strong class="text-gray-700">Format:</strong> <span class="text-gray-900">${(config.export_format || 'CSV').toUpperCase()}</span></div>
            ${config.filter_conditions ? `<div><strong class="text-gray-700">Filters:</strong> <code class="bg-gray-100 px-1 rounded text-xs">${config.filter_conditions}</code></div>` : ''}
            ${config.sort_by ? `<div><strong class="text-gray-700">Sort:</strong> <span class="text-gray-900">${config.sort_by} ${config.sort_order || config.sortOrder || 'ASC'}</span></div>` : ''}
            ${config.selected_columns ? `<div><strong class="text-gray-700">Columns:</strong> <span class="text-gray-900">${config.selected_columns}</span></div>` : ''}
            ${config.limit_records ? `<div><strong class="text-gray-700">Limit:</strong> <span class="text-gray-900">${config.limit_records} records</span></div>` : ''}
            <div><strong class="text-gray-700">Headers:</strong> <span class="text-gray-900">${config.include_headers !== false ? 'Yes' : 'No'}</span></div>
        </div>
    `;
    
    showAlert(details, 'info');
}

// 🔁 FIXED: Form submit handler using your Go routes
document.getElementById('configForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const configId = document.getElementById('configId').value;
    const sourceType = document.querySelector('input[name="sourceType"]:checked').value;
    
    const configData = {
        name: document.getElementById('configName').value,
        export_format: document.getElementById('exportFormat').value,
        table_config_id: sourceType === 'table' ? (document.getElementById('tableConfigId').value ? parseInt(document.getElementById('tableConfigId').value) : null) : null,
        join_id: sourceType === 'join' ? (document.getElementById('joinId').value ? parseInt(document.getElementById('joinId').value) : null) : null,
        filter_conditions: document.getElementById('filterConditions').value || null,
        sort_by: document.getElementById('sortBy').value || null,
        sort_order: document.getElementById('sortOrder').value,
        selected_columns: document.getElementById('selectedColumns').value || null,
        limit_records: document.getElementById('limitRecords').value ? parseInt(document.getElementById('limitRecords').value) : null,
        include_headers: document.getElementById('includeHeaders').checked
    };
    
    // Remove null values to keep JSON clean
    Object.keys(configData).forEach(key => configData[key] === null && delete configData[key]);
    
    try {
        // ✅ Use your Go routes: /multi-export/configs
        const url = configId 
            ? buildApiUrl(`/multi-export/configs/${configId}`)  // PUT for update
            : buildApiUrl('/multi-export/configs');              // POST for create
        
        const method = configId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: authHeaders(true),
            body: JSON.stringify(configData)
        });
        
        if (!response.ok) {
            let errorText = 'Failed to save configuration';
            try {
                const error = await response.json();
                errorText = error.error || error.message || errorText;
            } catch (e) {
                errorText = await response.text() || errorText;
            }
            throw new Error(errorText);
        }
        
        showAlert(configId ? '✓ Configuration updated!' : '✓ Configuration created!', 'success');
        closeConfigModal();
        loadConfigs();
    } catch (error) {
        console.error('Save config error:', error);
        showAlert('✗ Error: ' + error.message, 'error');
    }
});

// 🔁 FIXED: Delete function using your Go route
async function deleteConfig(id) {
    if (!confirm('⚠️ Are you sure you want to delete this export configuration?\n\nThis action cannot be undone.')) return;
    
    try {
        // ✅ Use your Go route: DELETE /multi-export/configs/:id
        const response = await fetch(buildApiUrl(`/multi-export/configs/${id}`), {
            method: 'DELETE',
            headers: authHeaders(true)
        });
        
        if (!response.ok) {
            let errorText = 'Failed to delete configuration';
            try {
                const error = await response.json();
                errorText = error.error || error.message || errorText;
            } catch (e) {
                errorText = await response.text() || errorText;
            }
            throw new Error(errorText);
        }
        
        showAlert('✓ Configuration deleted!', 'success');
        loadConfigs();
    } catch (error) {
        console.error('Delete config error:', error);
        showAlert('✗ Error: ' + error.message, 'error');
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    
    const alertClasses = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    
    const icons = {
        success: '✓',
        error: '✗',
        info: 'ℹ'
    };
    
    alertContainer.innerHTML = `
        <div class="${alertClasses[type] || alertClasses.info} border-l-4 p-4 mb-4 rounded shadow-sm flex items-start" role="alert">
            <span class="font-bold mr-2">${icons[type] || icons.info}</span>
            <div class="flex-1">${message}</div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-gray-500 hover:text-gray-700">&times;</button>
        </div>
    `;
    
    // Auto-dismiss after 5 seconds (except info alerts)
    if (type !== 'info') {
        setTimeout(() => {
            const alert = alertContainer.firstChild;
            if (alert) {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
}

// Close modal when clicking outside
document.getElementById('configModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfigModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConfigModal();
    }
});
</script>
@endsection