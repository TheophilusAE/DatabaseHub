@extends('layouts.app')

@section('title', 'Multi-Table Operations')

@section('content')
@php
    if (!session()->has('authenticated') || !session()->has('user')) {
        abort(403, 'Unauthorized access');
    }
    
    $userRole = session('user')['role'] ?? null;
    
    if (!$userRole) {
        abort(403, 'User role not defined');
    }
@endphp

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                Multi-Table Operations
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Unified hub for simple operations and advanced configurations
            </p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-t-xl overflow-hidden shadow-sm">
            <div class="flex border-b border-gray-200">
                <button onclick="switchTab('quick')" id="tab-quick" class="tab-button flex-1 py-4 px-6 text-center font-medium transition-all duration-200 border-b-2 border-blue-600 text-blue-600 hover:bg-blue-50">
                    <div class="flex items-center justify-center">
                        <span>Quick Actions</span>
                    </div>
                </button>
                @if($userRole === 'admin')
                <button onclick="switchTab('import')" id="tab-import" class="tab-button flex-1 py-4 px-6 text-center font-medium transition-all duration-200 border-b-2 border-transparent text-gray-600 hover:text-blue-600 hover:bg-gray-50">
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span>Import & Export</span>
                    </div>
                </button>
                <button onclick="switchTab('config')" id="tab-config" class="tab-button flex-1 py-4 px-6 text-center font-medium transition-all duration-200 border-b-2 border-transparent text-gray-600 hover:text-purple-600 hover:bg-gray-50">
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Configuration</span>
                    </div>
                </button>
                @endif
            </div>
        </div>

        <!-- Tab Contents -->
        <div class="bg-white rounded-b-xl shadow-sm p-6">
            <!-- Quick Actions Tab -->
            <div id="content-quick" class="tab-content">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Multi-Table Operations </h2>
                <p class="text-gray-600 mb-6">Direct access to database tables - no configuration required</p>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- View All Tables Card -->
                    <a href="{{ route($userRole . '.simple-multi.view-tables') }}" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-blue-700">Simple</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">View All Tables</h3>
                        <p class="text-blue-100 text-sm">Browse and view data from all database tables with pagination and filtering</p>
                        <div class="flex items-center text-xs text-blue-200 mt-3">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to access
                        </div>
                    </a>

                    <!-- Multi-Table Upload Card -->
                    <a href="{{ route($userRole . '.simple-multi.multi-upload') }}" class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-green-700">Simple</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Multi-Table Upload</h3>
                        <p class="text-green-100 text-sm">Upload multiple CSV or JSON files to different tables simultaneously</p>
                        <div class="flex items-center text-xs text-green-200 mt-3">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to access
                        </div>
                    </a>

                    <!-- Selective Export Card -->
                    <a href="{{ route($userRole . '.simple-multi.selective-export') }}" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-purple-700">Simple</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Selective Export</h3>
                        <p class="text-purple-100 text-sm">Choose specific tables, columns, and apply filters to export custom data</p>
                        <div class="flex items-center text-xs text-purple-200 mt-3">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to access
                        </div>
                    </a>
                </div>
            </div>

            <!-- Import & Export Tab -->
            @if($userRole === 'admin')
            <div id="content-import" class="tab-content hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Import & Export Operations</h2>
                <p class="text-gray-600 mb-6">Configured import and export operations using mappings</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Multi-Table Import -->
                    <a href="{{ route($userRole . '.multi-table.import') }}" class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-blue-500">Configured</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Multi-Table Import</h3>
                        <p class="text-blue-100 text-sm mb-4">Import data using pre-configured mappings</p>
                        <div class="flex items-center text-xs text-blue-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to access
                        </div>
                    </a>

                    <!-- Multi-Table Export -->
                    <a href="{{ route($userRole . '.multi-table.export') }}" class="bg-gradient-to-br from-orange-500 to-pink-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-orange-700">Configured</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Multi-Table Export</h3>
                        <p class="text-orange-100 text-sm mb-4">Export data using pre-configured settings</p>
                        <div class="flex items-center text-xs text-orange-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to access
                        </div>
                    </a>
                </div>
            </div>
            @endif

            <!-- Configuration Tab -->
            @if($userRole === 'admin')
            <div id="content-config" class="tab-content hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Advanced Configuration</h2>
                <p class="text-gray-600 mb-6">Configure databases, tables, joins, and mappings for advanced operations</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Database Connections -->
                    <a href="{{ route($userRole . '.multi-table.databases') }}" class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                        <div class="flex items-center justify-between mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-indigo-700">Config</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Databases</h3>
                        <p class="text-indigo-100 text-sm mb-3">Manage database connections</p>
                        <div class="flex items-center text-xs text-indigo-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to configure
                        </div>
                    </a>

                    <!-- Table Configurations -->
                    <a href="{{ route($userRole . '.multi-table.tables') }}" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                        <div class="flex items-center justify-between mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-purple-700">Config</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Tables</h3>
                        <p class="text-purple-100 text-sm mb-3">Configure table structures</p>
                        <div class="flex items-center text-xs text-purple-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to configure
                        </div>
                    </a>

                    <!-- Table Joins -->
                    <a href="{{ route($userRole . '.multi-table.joins') }}" class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                        <div class="flex items-center justify-between mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-pink-700">Config</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Joins</h3>
                        <p class="text-pink-100 text-sm mb-3">Configure table relationships</p>
                        <div class="flex items-center text-xs text-pink-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to configure
                        </div>
                    </a>

                    <!-- Import Mappings -->
                    <a href="{{ route($userRole . '.multi-table.import-mappings') }}" class="bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                        <div class="flex items-center justify-between mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-teal-700">Config</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Import Mappings</h3>
                        <p class="text-teal-100 text-sm mb-3">Configure import settings</p>
                        <div class="flex items-center text-xs text-teal-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to configure
                        </div>
                    </a>

                    <!-- Export Configurations -->
                    <a href="{{ route($userRole . '.multi-table.export-configs') }}" class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                        <div class="flex items-center justify-between mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span class="bg-white bg-opacity-30 px-3 py-1 rounded-full text-xs font-bold text-amber-700">Config</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Export Configs</h3>
                        <p class="text-amber-100 text-sm mb-3">Configure export settings</p>
                        <div class="flex items-center text-xs text-amber-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Click to configure
                        </div>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Tab Switching
function switchTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Reset all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-b-2', 'border-blue-600', 'text-blue-600', 'bg-blue-50', 'border-purple-600', 'text-purple-600', 'bg-purple-50');
        button.classList.add('border-transparent', 'text-gray-600', 'hover:text-blue-600', 'hover:bg-gray-50');
    });
    
    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Highlight selected tab
    const selectedButton = document.getElementById('tab-' + tabName);
    selectedButton.classList.remove('border-transparent', 'text-gray-600');
    
    if (tabName === 'quick') {
        selectedButton.classList.add('border-b-2', 'border-blue-600', 'text-blue-600', 'bg-blue-50');
    } else if (tabName === 'config') {
        selectedButton.classList.add('border-b-2', 'border-purple-600', 'text-purple-600', 'bg-purple-50');
    } else {
        selectedButton.classList.add('border-b-2', 'border-blue-600', 'text-blue-600', 'bg-blue-50');
    }
}
</script>
@endsection