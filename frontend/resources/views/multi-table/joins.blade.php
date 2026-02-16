@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Table Joins</h1>
                <p class="mt-1 text-sm text-gray-600">Configure joins between tables to combine data</p>
            </div>
            <button onclick="openAddJoinModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors shadow-md">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Table Join
            </button>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Joins List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Left Table</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Right Table</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Join Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Join Condition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Table</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="joinsTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <p class="text-sm">No table joins configured</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Join Modal -->
<div id="joinModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Add Table Join</h3>
            <button onclick="closeJoinModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="joinForm" class="mt-4 space-y-4">
            <input type="hidden" id="joinId" value="">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Join Name</label>
                <input type="text" id="joinName" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="e.g., Users with Orders">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Left Table</label>
                    <select id="leftTableId" required onchange="updateTargetTableOptions()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select left table...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Right Table</label>
                    <select id="rightTableId" required onchange="updateTargetTableOptions()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select right table...</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Join Type</label>
                <select id="joinType" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="INNER">INNER JOIN - Returns matching rows from both tables</option>
                    <option value="LEFT">LEFT JOIN - Returns all rows from left table with matches from right</option>
                    <option value="RIGHT">RIGHT JOIN - Returns all rows from right table with matches from left</option>
                    <option value="FULL">FULL OUTER JOIN - Returns all rows from both tables</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Join Condition</label>
                <input type="text" id="joinCondition" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="e.g., users.id = orders.user_id">
                <p class="mt-1 text-xs text-gray-500">Specify the condition to join the tables (e.g., table1.column = table2.column)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Table (Optional)</label>
                <select id="targetTableId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select target table for combined data...</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Optionally specify a table where combined data can be exported</p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeJoinModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Join
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let allJoins = [];
let allTables = [];

// Load joins and tables on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    loadJoins();
});

async function loadTables() {
    try {
        const response = await fetch('/api/multi-table/table-configs');
        if (!response.ok) throw new Error('Failed to load tables');
        
        const data = await response.json();
        allTables = data.table_configs || [];
        
        updateTableSelects();
    } catch (error) {
        showAlert('Error loading tables: ' + error.message, 'error');
    }
}

function updateTableSelects() {
    const leftSelect = document.getElementById('leftTableId');
    const rightSelect = document.getElementById('rightTableId');
    
    const options = allTables.map(table => 
        `<option value="${table.id}">${table.database_config_name} - ${table.table_name}</option>`
    ).join('');
    
    leftSelect.innerHTML = '<option value="">Select left table...</option>' + options;
    rightSelect.innerHTML = '<option value="">Select right table...</option>' + options;
    
    updateTargetTableOptions();
}

function updateTargetTableOptions() {
    const targetSelect = document.getElementById('targetTableId');
    const leftTableId = parseInt(document.getElementById('leftTableId').value);
    const rightTableId = parseInt(document.getElementById('rightTableId').value);
    
    // Only show tables from the same databases as the selected tables
    const leftTable = allTables.find(t => t.id === leftTableId);
    const rightTable = allTables.find(t => t.id === rightTableId);
    
    let filteredTables = allTables;
    if (leftTable && rightTable) {
        const dbNames = new Set([leftTable.database_config_name, rightTable.database_config_name]);
        filteredTables = allTables.filter(t => dbNames.has(t.database_config_name));
    }
    
    const options = filteredTables.map(table => 
        `<option value="${table.id}">${table.database_config_name} - ${table.table_name}</option>`
    ).join('');
    
    targetSelect.innerHTML = '<option value="">Select target table for combined data...</option>' + options;
}

async function loadJoins() {
    try {
        const response = await fetch('/api/multi-table/table-joins');
        if (!response.ok) throw new Error('Failed to load joins');
        
        const data = await response.json();
        allJoins = data.joins || [];
        
        renderJoinsTable();
    } catch (error) {
        showAlert('Error loading joins: ' + error.message, 'error');
    }
}

function renderJoinsTable() {
    const tbody = document.getElementById('joinsTableBody');
    
    if (allJoins.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center py-8">
                        <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <p class="text-sm">No table joins configured</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = allJoins.map(join => {
        const leftTable = allTables.find(t => t.id === join.left_table_id);
        const rightTable = allTables.find(t => t.id === join.right_table_id);
        const targetTable = join.target_table_id ? allTables.find(t => t.id === join.target_table_id) : null;
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${join.name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">${leftTable ? `${leftTable.database_config_name}.${leftTable.table_name}` : 'N/A'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">${rightTable ? `${rightTable.database_config_name}.${rightTable.table_name}` : 'N/A'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        ${join.join_type}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600 font-mono">${join.join_condition}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">${targetTable ? `${targetTable.database_config_name}.${targetTable.table_name}` : '-'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <button onclick="editJoin(${join.id})" class="text-blue-600 hover:text-blue-900">Edit</button>
                    <button onclick="deleteJoin(${join.id})" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            </tr>
        `;
    }).join('');
}

function openAddJoinModal() {
    document.getElementById('modalTitle').textContent = 'Add Table Join';
    document.getElementById('joinForm').reset();
    document.getElementById('joinId').value = '';
    updateTableSelects();
    document.getElementById('joinModal').classList.remove('hidden');
}

function closeJoinModal() {
    document.getElementById('joinModal').classList.add('hidden');
}

function editJoin(id) {
    const join = allJoins.find(j => j.id === id);
    if (!join) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Table Join';
    document.getElementById('joinId').value = join.id;
    document.getElementById('joinName').value = join.name;
    document.getElementById('leftTableId').value = join.left_table_id;
    document.getElementById('rightTableId').value = join.right_table_id;
    document.getElementById('joinType').value = join.join_type;
    document.getElementById('joinCondition').value = join.join_condition;
    document.getElementById('targetTableId').value = join.target_table_id || '';
    
    updateTargetTableOptions();
    document.getElementById('joinModal').classList.remove('hidden');
}

document.getElementById('joinForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const joinId = document.getElementById('joinId').value;
    const joinData = {
        name: document.getElementById('joinName').value,
        left_table_id: parseInt(document.getElementById('leftTableId').value),
        right_table_id: parseInt(document.getElementById('rightTableId').value),
        join_type: document.getElementById('joinType').value,
        join_condition: document.getElementById('joinCondition').value,
        target_table_id: document.getElementById('targetTableId').value ? parseInt(document.getElementById('targetTableId').value) : null
    };
    
    try {
        const url = joinId ? `/api/multi-table/table-joins/${joinId}` : '/api/multi-table/table-joins';
        const method = joinId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(joinData)
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to save join');
        }
        
        showAlert(joinId ? 'Table join updated successfully' : 'Table join added successfully', 'success');
        closeJoinModal();
        loadJoins();
    } catch (error) {
        showAlert('Error: ' + error.message, 'error');
    }
});

async function deleteJoin(id) {
    if (!confirm('Are you sure you want to delete this table join?')) return;
    
    try {
        const response = await fetch(`/api/multi-table/table-joins/${id}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to delete join');
        }
        
        showAlert('Table join deleted successfully', 'success');
        loadJoins();
    } catch (error) {
        showAlert('Error: ' + error.message, 'error');
    }
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
