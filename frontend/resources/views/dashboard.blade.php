@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8 animate-slide-in">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-4xl font-extrabold bg-gradient-to-r from-blue-700 via-blue-600 to-green-600 bg-clip-text text-transparent">
            Welcome to Dashboard
        </h1>
        <p class="mt-3 text-lg text-gray-600">Monitor your data ecosystem in real-time</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Records -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-400 to-blue-600 opacity-10 rounded-bl-full"></div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-lg shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Records</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1" id="total-records">
                            <span class="inline-block animate-pulse">...</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Documents -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 opacity-10 rounded-bl-full"></div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-lg shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Documents</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1" id="total-documents">
                            <span class="inline-block animate-pulse">...</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Imports -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-purple-400 to-purple-600 opacity-10 rounded-bl-full"></div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-3 rounded-lg shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Imports</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1" id="total-imports">
                            <span class="inline-block animate-pulse">...</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Server Status -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-orange-400 to-orange-600 opacity-10 rounded-bl-full"></div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div id="status-icon-container" class="bg-gradient-to-br from-gray-500 to-gray-600 p-3 rounded-lg shadow-lg group-hover:scale-110 transition-all">
                            <svg class="h-7 w-7 text-white animate-pulse" id="status-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Server Status</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1" id="server-status">Checking...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 shadow-xl rounded-2xl overflow-hidden">
        <div class="px-6 py-8">
            <h3 class="text-2xl font-bold text-white mb-6 flex items-center">
                <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Quick Actions
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('admin.data-records.create') }}" class="group bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm rounded-xl p-4 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-30 p-3 rounded-lg group-hover:bg-opacity-40 transition-all">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="ml-3 text-white font-semibold">Add Record</span>
                    </div>
                </a>
                <a href="{{ route('admin.documents.create') }}" class="group bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm rounded-xl p-4 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-30 p-3 rounded-lg group-hover:bg-opacity-40 transition-all">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <span class="ml-3 text-white font-semibold">Upload File</span>
                    </div>
                </a>
                <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.import.index' : 'user.import.index') }}" class="group bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm rounded-xl p-4 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-30 p-3 rounded-lg group-hover:bg-opacity-40 transition-all">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <span class="ml-3 text-white font-semibold">Import Data</span>
                    </div>
                </a>
                <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.export.index' : 'user.export.index') }}" class="group bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm rounded-xl p-4 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-30 p-3 rounded-lg group-hover:bg-opacity-40 transition-all">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <span class="ml-3 text-white font-semibold">Export Data</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-xl font-bold text-gray-900 flex items-center">
                <svg class="h-6 w-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Recent Import Activity
            </h3>
        </div>
        <div class="px-6 py-5">
            <div id="recent-activity" class="space-y-4">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent"></div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.import.history' : 'user.import.history') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center group">
                View All Import History
                <svg class="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

async function loadDashboardData() {
    try {
        // Check server health
        const healthResponse = await fetch('http://localhost:8080/health');
        const healthData = await healthResponse.json();
        
        const statusIconContainer = document.getElementById('status-icon-container');
        const statusText = document.getElementById('server-status');
        
        if (healthData.status === 'ok') {
            statusIconContainer.className = 'bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-lg shadow-lg group-hover:scale-110 transition-all';
            statusText.textContent = 'Online';
            statusText.className = 'text-3xl font-bold text-green-600 mt-1';
        } else {
            statusIconContainer.className = 'bg-gradient-to-br from-red-500 to-red-600 p-3 rounded-lg shadow-lg group-hover:scale-110 transition-all';
            statusText.textContent = 'Offline';
            statusText.className = 'text-3xl font-bold text-red-600 mt-1';
        }

        // Animate counters
        animateCounter('total-records', 0);
        animateCounter('total-documents', 0);
        animateCounter('total-imports', 0);

        // Load total records
        const recordsResponse = await fetch('http://localhost:8080/data?page=1&limit=1');
        const recordsData = await recordsResponse.json();
        animateCounter('total-records', recordsData.total || 0);

        // Load total documents
        const docsResponse = await fetch('http://localhost:8080/documents?page=1&limit=1');
        const docsData = await docsResponse.json();
        animateCounter('total-documents', docsData.total || 0);

        // Load import history
        const historyResponse = await fetch('http://localhost:8080/upload/history?page=1&limit=5');
        const historyData = await historyResponse.json();
        animateCounter('total-imports', historyData.total || 0);

        // Display recent activity
        const activityContainer = document.getElementById('recent-activity');
        if (historyData.data && historyData.data.length > 0) {
            activityContainer.innerHTML = historyData.data.map((log, index) => `
                <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0 hover:bg-gray-50 rounded-lg px-4 transition-colors" style="animation: fadeIn 0.5s ease-out ${index * 0.1}s both">
                    <div class="flex items-center space-x-4 flex-1">
                        <div class="${log.import_type === 'csv' ? 'bg-green-100' : 'bg-blue-100'} p-3 rounded-lg">
                            <svg class="h-5 w-5 ${log.import_type === 'csv' ? 'text-green-600' : 'text-blue-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">${log.file_name}</p>
                            <p class="text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${log.import_type === 'csv' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                                    ${log.import_type.toUpperCase()}
                                </span>
                                <span class="mx-2">•</span>
                                <span class="text-green-600 font-medium">${log.success_count} imported</span>
                                ${log.failure_count > 0 ? `<span class="mx-1">•</span><span class="text-red-600">${log.failure_count} failed</span>` : ''}
                            </p>
                        </div>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-sm text-gray-500">${new Date(log.created_at).toLocaleDateString()}</p>
                        <p class="text-xs text-gray-400">${new Date(log.created_at).toLocaleTimeString()}</p>
                    </div>
                </div>
            `).join('');
        } else {
            activityContainer.innerHTML = '<div class="text-center py-12"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg><p class="mt-4 text-gray-500 font-medium">No recent import activity</p><p class="mt-1 text-sm text-gray-400">Start by importing your first CSV or JSON file</p></div>';
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        document.getElementById('server-status').textContent = 'Error';
        document.getElementById('server-status').className = 'text-3xl font-bold text-red-600 mt-1';
        document.getElementById('status-icon-container').className = 'bg-gradient-to-br from-red-500 to-red-600 p-3 rounded-lg shadow-lg group-hover:scale-110 transition-all';
    }
}

function animateCounter(elementId, target) {
    const element = document.getElementById(elementId);
    const duration = 1000;
    const steps = 30;
    const increment = target / steps;
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString();
        }
    }, duration / steps);
}
</script>
@endsection
