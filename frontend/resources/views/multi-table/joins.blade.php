@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Table Joins</h1>
                <p class="mt-1 text-sm text-gray-600">Configure joins between tables to combine data</p>
            </div>
            <button onclick="openAddJoinModal()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center transition-colors shadow-md">
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
            <div class="overflow-x-auto table-responsive">
                <table class="min-w-[960px] w-full divide-y divide-gray-200">
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
    <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Left Table</label>
                    <select id="leftTableId" required onchange="updateTargetTableOptions(); updateJoinColumnSelects();"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select left table...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Right Table</label>
                    <select id="rightTableId" required onchange="updateTargetTableOptions(); updateJoinColumnSelects();"
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
                <input type="hidden" id="joinCondition" required>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <select id="leftJoinColumn" onchange="updateJoinConditionValue()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select left column...</option>
                    </select>
                    <select id="joinOperator" onchange="updateJoinConditionValue()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="=">=</option>
                        <option value="!=">!=</option>
                        <option value=">">&gt;</option>
                        <option value="<">&lt;</option>
                        <option value=">=">&gt;=</option>
                        <option value="<=">&lt;=</option>
                    </select>
                    <select id="rightJoinColumn" onchange="updateJoinConditionValue()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select right column...</option>
                    </select>
                </div>
                <div id="joinConditionPreview" class="mt-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded text-sm font-mono text-gray-700">
                    Condition will appear here...
                </div>
                <p class="mt-1 text-xs text-gray-500">Choose columns from each table. The join condition is generated automatically.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Table (Optional)</label>
                <select id="targetTableId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select target table for combined data...</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Optionally specify a table where combined data can be exported</p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeJoinModal()" 
                        class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Join
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// : Configure your Go backend API URL here
const API_BASE = 'http://localhost:8080';

let allJoins = [];
let allTables = [];
const tableColumnsCache = {};
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

// Load joins and tables on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    loadJoins();
});

async function loadTables() {
    try {
        const response = await fetch(buildApiUrl('/tables'), {
            headers: authHeaders()
        });
        if (!response.ok) throw new Error('Failed to load tables');
        
        const data = await response.json();
        allTables = data.table_configs || data.data || [];
        
        updateTableSelects();
    } catch (error) {
        showAlert('Error loading tables: ' + error.message, 'error');
    }
}

function updateTableSelects() {
    const leftSelect = document.getElementById('leftTableId');
    const rightSelect = document.getElementById('rightTableId');
    
    const options = allTables.map(table => 
        `<option value="${table.id}">${getTableDatabaseName(table)} - ${table.table_name}</option>`
    ).join('');
    
    leftSelect.innerHTML = '<option value="">Select left table...</option>' + options;
    rightSelect.innerHTML = '<option value="">Select right table...</option>' + options;
    
    updateTargetTableOptions();
    updateJoinColumnSelects();
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
        const dbNames = new Set([getTableDatabaseName(leftTable), getTableDatabaseName(rightTable)]);
        filteredTables = allTables.filter(t => dbNames.has(getTableDatabaseName(t)));
    }
    
    const options = filteredTables.map(table => 
        `<option value="${table.id}">${getTableDatabaseName(table)} - ${table.table_name}</option>`
    ).join('');
    
    targetSelect.innerHTML = '<option value="">Select target table for combined data...</option>' + options;
}

function getTableDatabaseName(table) {
    return table?.database_name || table?.database_config_name || 'default';
}

function getTableDatabaseKey(table) {
    return table?.database_name || table?.database_config_name || 'default';
}

function getTableColumns(table) {
    if (!table || !table.columns) return [];

    try {
        const parsed = typeof table.columns === 'string' ? JSON.parse(table.columns) : table.columns;
        if (!Array.isArray(parsed)) return [];

        return parsed
            .map(col => (typeof col === 'string' ? col : col?.name))
            .filter(Boolean)
            .map(name => String(name));
    } catch (error) {
        return [];
    }
}

async function fetchTableColumns(table) {
    if (!table || !table.table_name) {
        return [];
    }

    const databaseKey = getTableDatabaseKey(table);
    const cacheKey = `${databaseKey}::${table.table_name}`;
    if (tableColumnsCache[cacheKey]) {
        return tableColumnsCache[cacheKey];
    }

    const localColumns = getTableColumns(table);
    if (localColumns.length > 0) {
        tableColumnsCache[cacheKey] = localColumns;
        return localColumns;
    }

    try {
        const response = await fetch(buildApiUrl(`/simple-multi/tables/${encodeURIComponent(table.table_name)}/columns`, {
            database: databaseKey,
        }), {
            headers: authHeaders(),
        });

        if (!response.ok) {
            return [];
        }

        const data = await response.json();
        const fetched = Array.isArray(data.columns)
            ? data.columns.map(col => String(col?.name || '')).filter(Boolean)
            : [];

        tableColumnsCache[cacheKey] = fetched;
        return fetched;
    } catch (error) {
        return [];
    }
}

async function updateJoinColumnSelects() {
    const leftTableId = parseInt(document.getElementById('leftTableId').value);
    const rightTableId = parseInt(document.getElementById('rightTableId').value);
    const leftColumnSelect = document.getElementById('leftJoinColumn');
    const rightColumnSelect = document.getElementById('rightJoinColumn');

    const leftTable = allTables.find(t => t.id === leftTableId);
    const rightTable = allTables.find(t => t.id === rightTableId);

    const [leftColumns, rightColumns] = await Promise.all([
        fetchTableColumns(leftTable),
        fetchTableColumns(rightTable),
    ]);

    leftColumnSelect.innerHTML = '<option value="">Select left column...</option>' +
        leftColumns.map(col => `<option value="${col}">${col}</option>`).join('');
    rightColumnSelect.innerHTML = '<option value="">Select right column...</option>' +
        rightColumns.map(col => `<option value="${col}">${col}</option>`).join('');

    suggestCommonJoinColumns(leftColumns, rightColumns);
    updateJoinConditionValue();
}

function suggestCommonJoinColumns(leftColumns, rightColumns) {
    if (!leftColumns.length || !rightColumns.length) return;

    const leftSelect = document.getElementById('leftJoinColumn');
    const rightSelect = document.getElementById('rightJoinColumn');

    if (leftSelect.value && rightSelect.value) return;

    const rightLower = new Map(rightColumns.map(col => [col.toLowerCase(), col]));
    let suggestedLeft = '';
    let suggestedRight = '';

    for (const leftCol of leftColumns) {
        const exact = rightLower.get(leftCol.toLowerCase());
        if (exact) {
            suggestedLeft = leftCol;
            suggestedRight = exact;
            break;
        }
    }

    if (!suggestedLeft) {
        const commonPairs = [
            ['id', 'id'],
            ['user_id', 'id'],
            ['customer_id', 'id'],
            ['product_id', 'id'],
        ];

        for (const [leftCandidate, rightCandidate] of commonPairs) {
            const leftFound = leftColumns.find(c => c.toLowerCase() === leftCandidate);
            const rightFound = rightColumns.find(c => c.toLowerCase() === rightCandidate);
            if (leftFound && rightFound) {
                suggestedLeft = leftFound;
                suggestedRight = rightFound;
                break;
            }
        }
    }

    if (suggestedLeft && !leftSelect.value) leftSelect.value = suggestedLeft;
    if (suggestedRight && !rightSelect.value) rightSelect.value = suggestedRight;
}

function updateJoinConditionValue() {
    const leftTableId = parseInt(document.getElementById('leftTableId').value);
    const rightTableId = parseInt(document.getElementById('rightTableId').value);
    const leftColumn = document.getElementById('leftJoinColumn').value;
    const rightColumn = document.getElementById('rightJoinColumn').value;
    const operator = document.getElementById('joinOperator').value;
    const joinConditionInput = document.getElementById('joinCondition');
    const preview = document.getElementById('joinConditionPreview');

    const leftTable = allTables.find(t => t.id === leftTableId);
    const rightTable = allTables.find(t => t.id === rightTableId);

    if (!leftTable || !rightTable || !leftColumn || !rightColumn) {
        joinConditionInput.value = '';
        preview.textContent = 'Condition will appear here...';
        return;
    }

    const condition = `${leftTable.table_name}.${leftColumn} ${operator} ${rightTable.table_name}.${rightColumn}`;
    joinConditionInput.value = condition;
    preview.textContent = condition;
}

function parseConditionParts(condition) {
    if (!condition) return null;

    const pattern = /^\s*([a-zA-Z_][\w]*)\.([a-zA-Z_][\w]*)\s*(=|!=|>=|<=|>|<)\s*([a-zA-Z_][\w]*)\.([a-zA-Z_][\w]*)\s*$/;
    const match = String(condition).match(pattern);
    if (!match) return null;

    return {
        leftTable: match[1],
        leftColumn: match[2],
        operator: match[3],
        rightTable: match[4],
        rightColumn: match[5],
    };
}

async function loadJoins() {
    try {
        const response = await fetch(buildApiUrl('/joins'), {
            headers: authHeaders()
        });
        if (!response.ok) throw new Error('Failed to load joins');
        
        const data = await response.json();
        allJoins = data.joins || data.data || [];
        
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
                    <div class="text-sm text-gray-600">${leftTable ? `${getTableDatabaseName(leftTable)}.${leftTable.table_name}` : 'N/A'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">${rightTable ? `${getTableDatabaseName(rightTable)}.${rightTable.table_name}` : 'N/A'}</div>
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
                    <div class="text-sm text-gray-600">${targetTable ? `${getTableDatabaseName(targetTable)}.${targetTable.table_name}` : '-'}</div>
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
    document.getElementById('joinCondition').value = '';
    document.getElementById('joinConditionPreview').textContent = 'Condition will appear here...';
    updateTableSelects();
    document.getElementById('joinModal').classList.remove('hidden');
}

function closeJoinModal() {
    document.getElementById('joinModal').classList.add('hidden');
}

async function editJoin(id) {
    const join = allJoins.find(j => j.id === id);
    if (!join) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Table Join';
    document.getElementById('joinId').value = join.id;
    document.getElementById('joinName').value = join.name;
    document.getElementById('leftTableId').value = join.left_table_id;
    document.getElementById('rightTableId').value = join.right_table_id;
    document.getElementById('joinType').value = join.join_type;
    document.getElementById('targetTableId').value = join.target_table_id || '';
    
    updateTargetTableOptions();
    await updateJoinColumnSelects();

    const parsed = parseConditionParts(join.join_condition);
    if (parsed) {
        document.getElementById('leftJoinColumn').value = parsed.leftColumn;
        document.getElementById('joinOperator').value = parsed.operator;
        document.getElementById('rightJoinColumn').value = parsed.rightColumn;
    }
    updateJoinConditionValue();

    if (!parsed) {
        document.getElementById('joinCondition').value = join.join_condition;
        document.getElementById('joinConditionPreview').textContent = join.join_condition || 'Condition will appear here...';
    }

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

    if (!joinData.join_condition) {
        showAlert('Please select left and right join columns to build the join condition', 'error');
        return;
    }
    
    try {
        const url = joinId ? buildApiUrl(`/joins/${joinId}`) : buildApiUrl('/joins');
        const method = joinId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: authHeaders(true),
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
        const response = await fetch(buildApiUrl(`/joins/${id}`), {
            method: 'DELETE',
            headers: authHeaders()
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
