@extends('layouts.app')

@section('title', 'Manage Table Permissions')

@section('content')
<div class="container mx-auto px-4 max-w-4xl">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
        </a>
        <h1 class="text-3xl font-bold text-gray-900">🔐 Manage Table Permissions</h1>
        <p class="text-gray-600 mt-1">Control which tables <strong>{{ $user['name'] }}</strong> can access</p>
    </div>

    <!-- User Info Card -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-6 text-white mb-6 shadow-lg">
        <div class="flex items-center">
            <div class="h-16 w-16 rounded-full bg-gradient-to-r from-blue-600 to-green-500 bg-opacity-20 flex items-center justify-center text-2xl font-bold">
                {{ strtoupper(substr($user['name'], 0, 1)) }}
            </div>
            <div class="ml-4">
                <h2 class="text-xl font-bold">{{ $user['name'] }}</h2>
                <p class="text-blue-100">{{ $user['email'] }}</p>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs text-green-600 font-bold bg-white bg-opacity-20 mt-2">
                    @if ($user['role'] === 'admin')
                        👑 Administrator
                    @else
                        👤 Regular User
                    @endif
                </span>
            </div>
        </div>
    </div>

    @if ($user['role'] === 'admin')
        <!-- Admin Notice -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-2xl mb-6">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-yellow-400 mr-3 mt-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="text-yellow-800 font-bold text-lg">Administrator Access</p>
                    <p class="text-yellow-700 mt-1">This user is an administrator and has automatic access to all tables. Table permissions are not applicable for admin users.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Success Message -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-2xl">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-green-700 font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Table Permissions -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Available Tables</h2>
                        <p class="text-sm text-gray-600 mt-1">Select which tables this user can view</p>
                    </div>
                    <button onclick="toggleAllPermissions()" id="toggleAllBtn" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        Select All
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div id="permissions-container" class="space-y-3">
                    <!-- Loading state -->
                    <div id="loading" class="text-center py-8">
                        <svg class="animate-spin h-10 w-10 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-600 mt-3">Loading tables...</p>
                    </div>

                    <!-- Tables will be loaded here via JavaScript -->
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <button onclick="savePermissions()" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg">
                    💾 Save Permissions
                </button>
            </div>
        </div>
    @endif
</div>

<script>
const userId = {{ $user['id'] }};
const apiUrl = 'http://localhost:8080';
const currentUserId = {{ session('user')['id'] ?? 'null' }};
const currentUserRole = '{{ session('user')['role'] ?? '' }}';
let allTables = [];
let userPermissions = [];
let allSelected = false;

function buildPermissionsUrl(path) {
    const url = new URL(`${apiUrl}/simple-multi/permissions${path}`);
    if (currentUserId) url.searchParams.set('user_id', String(currentUserId));
    if (currentUserRole) url.searchParams.set('user_role', currentUserRole);
    return url.toString();
}

function authHeaders() {
    return {
        'Content-Type': 'application/json',
        'X-User-ID': currentUserId ? String(currentUserId) : '',
        'X-User-Role': currentUserRole || ''
    };
}

async function loadData() {
    try {
        // ✅ No credentials needed - Go backend returns all tables for admin management
        const tablesResponse = await fetch(buildPermissionsUrl(`/users/${userId}/tables`), {
            headers: authHeaders()
        });
        
        if (!tablesResponse.ok) {
            throw new Error(`HTTP ${tablesResponse.status}: ${tablesResponse.statusText}`);
        }
        
        const tablesData = await tablesResponse.json();
        allTables = tablesData.data || [];

        const permResponse = await fetch(buildPermissionsUrl(`/users/${userId}`), {
            headers: authHeaders()
        });
        
        if (!permResponse.ok) {
            throw new Error(`HTTP ${permResponse.status}: ${permResponse.statusText}`);
        }
        
        const permData = await permResponse.json();
        userPermissions = permData.data || [];

        renderTables();
    } catch (error) {
        console.error('Error loading data:', error);
        document.getElementById('loading').innerHTML = `
            <div class="text-center py-8 text-red-600">
                <p class="font-bold">Error loading tables</p>
                <p class="text-sm mt-2">${error.message}</p>
                <button onclick="location.reload()" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Retry</button>
            </div>
        `;
    }
}

function renderTables() {
    const container = document.getElementById('permissions-container');
    if (!container) return;
    
    if (allTables.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <p class="font-bold">No tables available</p>
                <p class="text-sm mt-2">This user has no permitted tables or no tables are configured</p>
            </div>
        `;
        return;
    }

    const permissionMap = {};
    userPermissions.forEach(perm => {
        permissionMap[perm.table_config_id] = perm;
    });

    container.innerHTML = allTables.map(table => {
        const hasPermission = permissionMap[table.id];
        return `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="table-${table.id}" 
                        data-table-id="${table.id}"
                        ${hasPermission ? 'checked' : ''}
                        class="h-5 w-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                    >
                    <label for="table-${table.id}" class="ml-3 cursor-pointer">
                        <div class="text-sm font-bold text-gray-900">${escapeHtml(table.name)}</div>
                        <div class="text-xs text-gray-600">${escapeHtml(table.database_name)} - ${escapeHtml(table.table_name)}</div>
                        ${table.description ? `<div class="text-xs text-gray-500 mt-1">${escapeHtml(table.description)}</div>` : ''}
                    </label>
                </div>
                <div class="flex items-center space-x-2">
                    ${hasPermission ? '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">Granted</span>' : '<span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs font-bold rounded">No Access</span>'}
                </div>
            </div>
        `;
    }).join('');

    const loading = document.getElementById('loading');
    if (loading) loading.remove();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleAllPermissions() {
    allSelected = !allSelected;
    const checkboxes = document.querySelectorAll('[data-table-id]');
    checkboxes.forEach(cb => cb.checked = allSelected);
    const btn = document.getElementById('toggleAllBtn');
    if (btn) btn.textContent = allSelected ? 'Deselect All' : 'Select All';
}

async function savePermissions(event) {
    if (event) event.preventDefault();
    
    const checkboxes = document.querySelectorAll('[data-table-id]');
    const selectedTableIds = [];
    checkboxes.forEach(cb => {
        if (cb.checked) selectedTableIds.push(parseInt(cb.dataset.tableId));
    });

    try {
        const btn = document.querySelector('button[onclick="savePermissions(event)"]') || 
                   document.querySelector('button[onclick="savePermissions()"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '⏳ Saving...';
        }

        if (!currentUserRole || currentUserRole.toLowerCase() !== 'admin') {
            throw new Error('Current session is not admin. Please re-login as admin.');
        }

        // Delete all existing permissions with admin credentials
        const revokeResponse = await fetch(buildPermissionsUrl(`/users/${userId}/all`), {
            method: 'DELETE',
            headers: authHeaders()
        });
        if (!revokeResponse.ok) {
            throw new Error(`Failed to clear old permissions: HTTP ${revokeResponse.status}`);
        }

        // Assign new permissions with admin credentials
        if (selectedTableIds.length > 0) {
            const assignResponse = await fetch(buildPermissionsUrl('/bulk-assign'), {
                method: 'POST',
                headers: authHeaders(),
                body: JSON.stringify({
                    user_id: userId,
                    table_config_ids: selectedTableIds,
                    can_view: true,
                    can_edit: false,
                    can_delete: false,
                    can_export: false,
                    can_import: false
                })
            });
            if (!assignResponse.ok) {
                throw new Error(`Failed to assign permissions: HTTP ${assignResponse.status}`);
            }
        }

        if (btn) {
            btn.innerHTML = '✓ Saved!';
            btn.classList.remove('from-blue-600', 'to-purple-600');
            btn.classList.add('from-green-600', 'to-green-700');
        }
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        console.error('Error saving permissions:', error);
        alert('Error: ' + error.message);
        location.reload();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('permissions-container')) {
        loadData();
    }
});
</script>
@endsection
