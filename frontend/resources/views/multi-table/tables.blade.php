@extends('layouts.app')

@section('title', 'Table Configurations - Multi-Table')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Table Configurations</h1>
        <p class="text-gray-600">Define table structures for import and export operations</p>
    </div>

    <!-- Alert Container -->
    <div id="alert-container"></div>

    @if(session('user')['role'] === 'admin')
    <!-- Database Selection and Discovery (ADMIN ONLY) -->
    <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center mb-4">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <h2 class="text-2xl font-bold">🔍 Auto-Discover Tables</h2>
            <span class="ml-3 px-3 py-1 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">ADMIN ONLY</span>
        </div>
        <p class="mb-4 text-indigo-100">Select a database to automatically discover and sync tables</p>
        
        <div class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium mb-2">Select Database</label>
                <select id="discovery-database" class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-300 border-0">
                    <option value="">Loading databases...</option>
                </select>
            </div>
            <button onclick="discoverTables()" class="px-6 py-3 bg-white text-purple-600 font-semibold rounded-lg hover:bg-indigo-50 transition shadow-md">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Discover Tables
            </button>
        </div>
    </div>

    <!-- Discovered Tables (ADMIN ONLY) -->
    <div id="discovered-tables-section" class="hidden bg-white rounded-xl shadow-lg p-6 mb-8 border-t-4 border-green-500">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Discovered Tables
            </h2>
            <button onclick="syncAllTables()" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Sync All Tables
            </button>
        </div>
        <div id="discovered-tables-list" class="space-y-3"></div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @if(session('user')['role'] === 'admin')
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow cursor-pointer" onclick="showAddModal()">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Add Manually</h3>
                    <p class="text-sm text-blue-100">Configure table manually</p>
                </div>
                <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
        </div>
        @else
        <div class="bg-gradient-to-br from-gray-400 to-gray-500 text-white p-6 rounded-xl shadow-lg opacity-60 cursor-not-allowed">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Add Manually</h3>
                    <p class="text-sm text-gray-100">Admin only</p>
                </div>
                <svg class="w-12 h-12 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
        </div>
        @endif

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='{{ route(session('user')['role'] . '.multi-table.joins') }}'">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Configure Joins</h3>
                    <p class="text-sm text-purple-100">Combine tables</p>
                </div>
                <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='{{ route(session('user')['role'] . '.multi-table.import-mappings') }}'">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Import Mappings</h3>
                    <p class="text-sm text-green-100">Configure imports</p>
                </div>
                <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Table Configurations List -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-blue-500">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Configured Tables
            </h2>
            <button onclick="loadTables()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>

        <div id="tables-list" class="space-y-4">
            <div class="text-center py-8 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Loading table configurations...
            </div>
        </div>
    </div>
</div>

<!-- Add Table Modal -->
<div id="add-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-2xl font-semibold text-gray-800">Add Table Configuration</h3>
        </div>
        <form id="add-table-form" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Configuration Name *</label>
                    <input type="text" id="table-name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Connection *</label>
                    <select id="table-database" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Loading...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Table Name *</label>
                    <input type="text" id="table-tablename" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="table-description" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Columns JSON *</label>
                    <textarea id="table-columns" required rows="6" placeholder='[{"name":"id","type":"int","is_primary":true}]'
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"></textarea>
                    <p class="text-xs text-gray-500 mt-1">JSON array of column definitions</p>
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeAddModal()"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    Add Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ✅ FIXED: Configure your Go backend API URL here
const API_BASE = 'http://localhost:8080';

let discoveredTablesData = [];
let selectedDatabase = '';
const userRole = '{{ session('user')['role'] ?? 'user' }}';
const isAdmin = userRole === 'admin';

document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    
    // Only load discovery features for admins
    if (isAdmin) {
        loadDatabases();
        loadDiscoveryDatabases();
    }

    document.getElementById('add-table-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!isAdmin) {
            showAlert('Only administrators can add table configurations', 'error');
            return;
        }

        const config = {
            name: document.getElementById('table-name').value,
            database_name: document.getElementById('table-database').value,
            table_name: document.getElementById('table-tablename').value,
            description: document.getElementById('table-description').value,
            columns: document.getElementById('table-columns').value,
            primary_key: 'id'
        };

        try {
            const response = await fetch(`${API_BASE}/tables?user_role=${userRole}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(config)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to add table configuration');
            }

            showAlert('Table configuration added successfully!', 'success');
            closeAddModal();
            this.reset();
            loadTables();
        } catch (error) {
            showAlert(error.message || 'Failed to add table configuration', 'error');
        }
    });
});

async function loadDiscoveryDatabases() {
    if (!isAdmin) return;
    
    try {
        const response = await fetch(`${API_BASE}/discovery/databases?user_role=${userRole}`);
        if (!response.ok) throw new Error('Failed to load databases');
        const data = await response.json();

        const select = document.getElementById('discovery-database');
        if (data.databases && data.databases.length > 0) {
            select.innerHTML = '<option value="">Select a database...</option>' +
                data.databases.map(db => `<option value="${db.name}">${db.name} (${db.type} - ${db.db_name})</option>`).join('');
        } else {
            select.innerHTML = '<option value="">No databases available - Add one first</option>';
        }
    } catch (error) {
        console.error('Failed to load databases:', error);
        if (error.message && error.message.includes('Access denied')) {
            showAlert('Access denied: Admin privileges required', 'error');
        }
        const select = document.getElementById('discovery-database');
        if (select) {
            select.innerHTML = '<option value="">Access denied - Admin only</option>';
        }
    }
}

async function discoverTables() {
    if (!isAdmin) {
        showAlert('Only administrators can discover tables', 'error');
        return;
    }

    const database = document.getElementById('discovery-database').value;
    
    if (!database) {
        showAlert('Please select a database first', 'error');
        return;
    }

    selectedDatabase = database;

    try {
        showAlert('Discovering tables...', 'info');
        
        const response = await apiRequest(`/discovery/tables?database=${encodeURIComponent(database)}&user_role=${userRole}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to discover tables');
        }

        discoveredTablesData = data.tables || [];
        
        if (discoveredTablesData.length === 0) {
            showAlert('No tables found in this database', 'warning');
            document.getElementById('discovered-tables-section').classList.add('hidden');
            return;
        }

        displayDiscoveredTables(discoveredTablesData);
        showAlert(`Found ${discoveredTablesData.length} tables in ${database}`, 'success');
        
    } catch (error) {
        console.error('Discovery error:', error);
        if (error.message && error.message.includes('Access denied')) {
            showAlert('Access denied: Admin privileges required for table discovery', 'error');
        } else {
            showAlert(error.message || 'Failed to discover tables', 'error');
        }
    }
}

function displayDiscoveredTables(tables) {
    const container = document.getElementById('discovered-tables-list');
    const section = document.getElementById('discovered-tables-section');
    
    section.classList.remove('hidden');
    
    container.innerHTML = tables.map((table, index) => `
        <div class="border ${table.is_configured ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white'} rounded-lg p-4 flex items-center justify-between hover:shadow-md transition">
            <div class="flex items-center space-x-4 flex-1">
                <input type="checkbox" id="table-check-${index}" 
                    ${table.is_configured ? '' : 'checked'}
                    class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800 flex items-center">
                        ${table.table_name}
                        ${table.is_configured ? '<span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Already Synced</span>' : ''}
                    </h4>
                    <p class="text-sm text-gray-600">
                        ${table.row_count.toLocaleString()} rows • ${table.columns.length} columns
                        ${table.primary_keys.length > 0 ? ' • PK: ' + table.primary_keys.join(', ') : ''}
                    </p>
                </div>
            </div>
            <button onclick="syncSingleTable('${table.table_name}')" 
                class="px-4 py-2 ${table.is_configured ? 'bg-blue-500' : 'bg-green-500'} text-white rounded-lg hover:opacity-90 transition text-sm font-medium">
                ${table.is_configured ? 'Re-sync' : 'Sync Now'}
            </button>
        </div>
    `).join('');
}

async function syncSingleTable(tableName) {
    if (!isAdmin) {
        showAlert('Only administrators can sync tables', 'error');
        return;
    }

    if (!selectedDatabase) {
        showAlert('No database selected', 'error');
        return;
    }

    try {
        showAlert(`Syncing ${tableName}...`, 'info');
        
        const response = await fetch(`${API_BASE}/discovery/sync?user_role=${userRole}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                database: selectedDatabase,
                tables: [tableName]
            })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to sync table');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to sync table');
        }

        showAlert(`✓ ${tableName} synced successfully!`, 'success');
        
        // Reload discovered tables and configured tables
        await discoverTables();
        await loadTables();
        
    } catch (error) {
        console.error('Sync error:', error);
        if (error.message && error.message.includes('Access denied')) {
            showAlert('Access denied: Admin privileges required', 'error');
        } else {
            showAlert(error.message || 'Failed to sync table', 'error');
        }
    }
}

async function syncAllTables() {
    if (!isAdmin) {
        showAlert('Only administrators can sync tables', 'error');
        return;
    }

    if (!selectedDatabase) {
        showAlert('No database selected', 'error');
        return;
    }

    // Get selected tables
    const selectedTables = [];
    discoveredTablesData.forEach((table, index) => {
        const checkbox = document.getElementById(`table-check-${index}`);
        if (checkbox && checkbox.checked) {
            selectedTables.push(table.table_name);
        }
    });

    if (selectedTables.length === 0) {
        showAlert('Please select at least one table to sync', 'warning');
        return;
    }

    try {
        showAlert(`Syncing ${selectedTables.length} tables...`, 'info');
        
        const response = await fetch(`${API_BASE}/discovery/sync?user_role=${userRole}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                database: selectedDatabase,
                tables: selectedTables
            })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to sync tables');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to sync tables');
        }

        showAlert(`✓ Successfully synced ${data.synced_count} tables!`, 'success');
        
        // Reload discovered tables and configured tables
        await discoverTables();
        await loadTables();
        
    } catch (error) {
        console.error('Sync error:', error);
        if (error.message && error.message.includes('Access denied')) {
            showAlert('Access denied: Admin privileges required', 'error');
        } else {
            showAlert(error.message || 'Failed to sync tables', 'error');
        }
    }
}

async function loadTables() {
    try {
        const response = await fetch(`${API_BASE}/tables`);
        if (!response.ok) throw new Error('Failed to load tables');
        const data = await response.json();

        const container = document.getElementById('tables-list');
        if (data.configs && data.configs.length > 0) {
            container.innerHTML = data.configs.map(config => `
                <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition duration-300">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h3 class="text-lg font-semibold text-gray-800">${config.name}</h3>
                                <span class="ml-3 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">${config.database_name}</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">${config.description || 'No description'}</p>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Table: ${config.table_name}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <p>No table configurations yet</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load tables:', error);
    }
}

async function loadDatabases() {
    try {
        const response = await fetch(`${API_BASE}/databases`);
        if (!response.ok) throw new Error('Failed to load databases');
        const data = await response.json();

        const select = document.getElementById('table-database');
        if (data.connections && data.connections.length > 0) {
            select.innerHTML = '<option value="">Select database...</option>' +
                data.connections.map(name => `<option value="${name}">${name}</option>`).join('');
        } else {
            select.innerHTML = '<option value="">No databases configured</option>';
        }
    } catch (error) {
        console.error('Failed to load databases:', error);
    }
}

function showAddModal() {
    document.getElementById('add-modal').classList.remove('hidden');
}

function closeAddModal() {
    document.getElementById('add-modal').classList.add('hidden');
}
</script>
@endsection