@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8 animate-fadeIn">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-800 to-green-700 rounded-3xl shadow-2xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-extrabold flex items-center text-white">
                    <span class="mr-3">👑</span> Admin Dashboard
                </h1>
                <p class="mt-3 text-lg text-white opacity-90">Full control and management panel</p>
                <p class="mt-2 text-sm text-white opacity-85">Welcome back, <strong>{{ session('user')['name'] }}</strong>!</p>
            </div>
            <div class="hidden md:block">
                <div class="bg-orange-50 bg-opacity-25 backdrop-blur-sm rounded-2xl px-6 py-4 border border-red-400 border-opacity-40">
                    <p class="text-sm font-semibold text-red-500 opacity-90">Role</p>
                    <p class="text-2xl font-bold text-red-500">Administrator</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <svg class="h-6 w-6 mr-2 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Quick Actions
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.data-records.create') }}" class="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-200 rounded-xl p-4 hover:shadow-lg transition-all text-center group">
                <svg class="h-8 w-8 text-green-700 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <p class="text-sm font-bold text-green-900">Create Record</p>
            </a>
            <a href="{{ route('admin.documents.create') }}" class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200 rounded-xl p-4 hover:shadow-lg transition-all text-center group">
                <svg class="h-8 w-8 text-blue-700 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="text-sm font-bold text-blue-900">Upload Document</p>
            </a>
            <a href="{{ route('admin.data-exchange', ['tab' => 'import']) }}" class="bg-gradient-to-br from-teal-50 to-cyan-50 border-2 border-teal-200 rounded-xl p-4 hover:shadow-lg transition-all text-center group">
                <svg class="h-8 w-8 text-teal-700 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <p class="text-sm font-bold text-teal-900">Import Data</p>
            </a>
            <a href="{{ route('admin.data-exchange', ['tab' => 'export']) }}" class="bg-gradient-to-br from-orange-50 to-amber-50 border-2 border-orange-200 rounded-xl p-4 hover:shadow-lg transition-all text-center group">
                <svg class="h-8 w-8 text-orange-600 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <p class="text-sm font-bold text-orange-900">Export Data</p>
            </a>
        </div>
    </div>

   <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-2">
        <!-- Total Records -->
        <div class="group relative bg-white overflow-hidden shadow-xl rounded-2xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-400 to-blue-600 opacity-10 rounded-bl-full"></div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Total Records</p>
                        <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalRecords }}</p>
                        <a href="{{ route('admin.data-records.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold mt-1 inline-block">
                            Manage →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Documents -->
        <div class="group relative bg-white overflow-hidden shadow-xl rounded-2xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 opacity-10 rounded-bl-full"></div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="bg-gradient-to-br from-green-600 to-green-700 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Documents</p>
                        <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalDocuments }}</p>
                        <a href="{{ route('admin.documents.index') }}" class="text-xs text-green-600 hover:text-green-700 font-semibold mt-1 inline-block">
                            View All →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Capabilities -->
    <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl shadow-xl p-6 border border-purple-200">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <svg class="h-6 w-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Admin Capabilities
        </h2>
        <div class="space-y-2 text-sm">
            <div class="flex items-start">
                <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                <span class="text-gray-700"><strong>Full CRUD Access:</strong> Create, Read, Update, Delete all records</span>
            </div>
            <div class="flex items-start">
                <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                <span class="text-gray-700"><strong>Document Management:</strong> Upload, download, and manage all documents</span>
            </div>
            <div class="flex items-start">
                <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                <span class="text-gray-700"><strong>Data Import/Export:</strong> Import CSV/JSON files and export data in multiple formats</span>
            </div>
            <div class="flex items-start">
                <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                <span class="text-gray-700"><strong>Analytics Dashboard:</strong> View comprehensive statistics and category breakdowns</span>
            </div>
            <div class="flex items-start">
                <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                <span class="text-gray-700"><strong>System Management:</strong> Full administrative control over the platform</span>
            </div>
        </div>
    </div>
</div>
@endsection