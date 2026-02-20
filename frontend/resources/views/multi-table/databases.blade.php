@extends('layouts.app')

@section('title', 'Database Connections - Multi-Table')

@section('content')
@php
    $isAdmin = session('user')['role'] === 'admin';
@endphp

@if(!$isAdmin)
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg shadow-md">
        <div class="flex items-center mb-4">
            <svg class="w-8 h-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <h1 class="text-2xl font-bold text-red-800">Access Restricted</h1>
        </div>
        <p class="text-red-700 mb-4">Database connection management requires administrator privileges.</p>
        <p class="text-red-600 text-sm">Please contact your administrator if you need access to this feature.</p>
        <div class="mt-6">
            <a href="{{ route(session('user')['role'] . '.multi-table.hub') }}" 
               class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Multi-Table Hub
            </a>
        </div>
    </div>
</div>
@else
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2 flex items-center">
                    Database Connections
                    <span class="ml-3 px-3 py-1 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">ADMIN ONLY</span>
                </h1>
                <p class="text-gray-600">Manage connections to multiple databases for import/export operations</p>
            </div>
            <svg class="w-16 h-16 text-blue-500 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alert-container"></div>

    <!-- Add Database Connection Card -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8  border-t-4 border-blue-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add New Connection
        </h2>

        <form id="add-connection-form" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Connection Name *</label>
                <input type="text" id="conn-name" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g., warehouse_db">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Database Type *</label>
                <select id="conn-type" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="postgres">PostgreSQL</option>
                    <option value="mysql">MySQL</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Host *</label>
                <input type="text" id="conn-host" required value="localhost"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Port *</label>
                <input type="text" id="conn-port" required value="5432"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Database Name *</label>
                <input type="text" id="conn-dbname" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="database_name">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                <input type="text" id="conn-user" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="conn-password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="md:col-span-2">
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Connection
                </button>
            </div>
        </form>
    </div>

    <!-- Existing Connections -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-green-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/>
            </svg>
            Active Connections
        </h2>

        <div id="connections-list" class="space-y-4">
            <div class="text-center py-8 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                Loading connections...
            </div>
        </div>
    </div>
</div>

<script>
// : Configure your Go backend API URL here
const API_BASE = 'http://localhost:8080';

document.addEventListener('DOMContentLoaded', function() {
    // Update port based on database type
    document.getElementById('conn-type').addEventListener('change', function() {
        const port = this.value === 'postgres' ? '5432' : '3306';
        document.getElementById('conn-port').value = port;
    });

    // Load connections on page load
    loadConnections();

    // Add connection form submission
    document.getElementById('add-connection-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const connection = {
            name: document.getElementById('conn-name').value,
            type: document.getElementById('conn-type').value,
            host: document.getElementById('conn-host').value,
            port: document.getElementById('conn-port').value,
            user: document.getElementById('conn-user').value,
            password: document.getElementById('conn-password').value,
            dbname: document.getElementById('conn-dbname').value,
        };

        try {
            const response = await fetch(`${API_BASE}/databases?user_role=admin`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(connection)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to add connection');
            }

            showAlert('Database connection added successfully!', 'success');
            this.reset();
            loadConnections();
        } catch (error) {
            showAlert(error.message || 'Failed to add connection', 'error');
        }
    });
});

async function loadConnections() {
    try {
        const response = await fetch(`${API_BASE}/databases?user_role=admin`);
        if (!response.ok) throw new Error('Failed to load connections');
        const data = await response.json();

        const container = document.getElementById('connections-list');
        
        if (data.connections && data.connections.length > 0) {
            container.innerHTML = data.connections.map(name => `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">${name}</h3>
                                <p class="text-sm text-gray-500">Status: <span class="connection-status-${name}">Checking...</span></p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="testConnection('${name}')" 
                                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-300 text-sm">
                                Test
                            </button>
                            <button onclick="removeConnection('${name}')" 
                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300 text-sm">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            // Test each connection
            data.connections.forEach(name => testConnection(name, true));
        } else {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p>No database connections configured yet</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load connections:', error);
    }
}

async function testConnection(name, silent = false) {
    const statusEl = document.querySelector(`.connection-status-${name}`);
    if (statusEl) statusEl.textContent = 'Testing...';

    try {
        const response = await fetch(`${API_BASE}/databases/test?name=${name}&user_role=admin`);
        if (!response.ok) throw new Error('Test failed');
        const data = await response.json();
        
        if (statusEl) {
            statusEl.textContent = '✓ Connected';
            statusEl.className = 'text-green-600 font-semibold';
        }
        if (!silent) showAlert(`Connection "${name}" is working!`, 'success');
    } catch (error) {
        if (statusEl) {
            statusEl.textContent = '✗ Failed';
            statusEl.className = 'text-red-600 font-semibold';
        }
        if (!silent) showAlert(`Connection "${name}" test failed`, 'error');
    }
}

async function removeConnection(name) {
    if (!confirm(`Are you sure you want to remove connection "${name}"?`)) return;

    try {
        const response = await fetch(`${API_BASE}/databases?name=${name}&user_role=admin`, { method: 'DELETE' });
        if (!response.ok) throw new Error('Failed to remove connection');
        showAlert('Connection removed successfully', 'success');
        loadConnections();
    } catch (error) {
        showAlert('Failed to remove connection', 'error');
    }
}
</script>
@endif
@endsection
