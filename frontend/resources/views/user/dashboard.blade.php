@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')
<div class="space-y-8 animate-fadeIn">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-800 to-green-700 rounded-3xl shadow-2xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-extrabold flex items-center text-white">
                    <span class="mr-3">👤</span> My Dashboard
                </h1>
                <p class="mt-3 text-lg text-white opacity-90">Your personal data overview</p>
                <p class="mt-2 text-sm text-white opacity-85">Welcome, <strong>{{ session('user')['name'] }}</strong>!</p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white bg-opacity-25 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white border-opacity-40">
                    <p class="text-sm font-semibold text-white opacity-90">Role</p>
                    <p class="text-2xl font-bold text-white">User</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-6 shadow-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-blue-900">📖 User Access Information</h3>
                <div class="mt-2 text-sm text-blue-800">
                    <p>As a regular user, you can <strong>view all data records and documents</strong>, as well as <strong>import and export data</strong>. For create, edit, and delete operations, please contact an administrator.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- Total Records -->
        <div class="group relative bg-white overflow-hidden shadow-xl rounded-2xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400 to-blue-600 opacity-10 rounded-bl-full"></div>
            <div class="p-8 relative">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-4 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Total Records</p>
                        <p class="text-4xl font-extrabold text-gray-900 mt-2">{{ $totalRecords }}</p>
                        <a href="{{ route('user.data-records.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-semibold mt-2 inline-block">
                            View All Records →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Documents -->
        <div class="group relative bg-white overflow-hidden shadow-xl rounded-2xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-400 to-green-600 opacity-10 rounded-bl-full"></div>
            <div class="p-8 relative">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="bg-gradient-to-br from-green-600 to-green-700 p-4 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Documents</p>
                        <p class="text-4xl font-extrabold text-gray-900 mt-2">{{ $totalDocuments }}</p>
                        <a href="{{ route('user.documents.index') }}" class="text-sm text-green-600 hover:text-green-700 font-semibold mt-2 inline-block">
                            Browse Documents →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Available Actions -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Quick Access
            </h2>
            <div class="space-y-3">
                <a href="{{ route('user.data-records.index') }}" class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-200 rounded-xl hover:shadow-lg transition-all group">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-blue-700 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div>
                            <p class="font-bold text-blue-900">View Data Records</p>
                            <p class="text-xs text-blue-700">Browse all available records</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('user.documents.index') }}" class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-200 rounded-xl hover:shadow-lg transition-all group">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-green-700 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <p class="font-bold text-green-900">Browse Documents</p>
                            <p class="text-xs text-green-700">View and download documents</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('user.import.index') }}" class="flex items-center justify-between p-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-2 border-teal-200 rounded-xl hover:shadow-lg transition-all group">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-teal-700 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <div>
                            <p class="font-bold text-teal-900">Import Data</p>
                            <p class="text-xs text-teal-700">Upload CSV or JSON files</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('user.export.index') }}" class="flex items-center justify-between p-4 bg-gradient-to-r from-orange-50 to-amber-50 border-2 border-orange-200 rounded-xl hover:shadow-lg transition-all group">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-orange-600 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <div>
                            <p class="font-bold text-orange-900">Export Data</p>
                            <p class="text-xs text-orange-600">Download data as CSV or JSON</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('user.import.history') }}" class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-slate-50 border-2 border-gray-200 rounded-xl hover:shadow-lg transition-all group">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-gray-600 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-bold text-gray-900">Import History</p>
                            <p class="text-xs text-gray-600">View your import logs</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- User Permissions -->
        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl shadow-xl p-6 border border-blue-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Your Permissions
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex items-start">
                    <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                    <span class="text-gray-700"><strong>View Records:</strong> Browse and search all data records</span>
                </div>
                <div class="flex items-start">
                    <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                    <span class="text-gray-700"><strong>View Documents:</strong> Access and download available documents</span>
                </div>
                <div class="flex items-start">
                    <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                    <span class="text-gray-700"><strong>Import Data:</strong> Upload CSV and JSON files to import data</span>
                </div>
                <div class="flex items-start">
                    <span class="text-green-600 font-bold mr-2 text-lg">✓</span>
                    <span class="text-gray-700"><strong>Export Data:</strong> Download data in CSV or JSON format</span>
                </div>
                <div class="flex items-start">
                    <span class="text-red-500 font-bold mr-2 text-lg">✗</span>
                    <span class="text-gray-500"><strong>Create/Edit/Delete:</strong> Requires administrator access</span>
                </div>
                <div class="flex items-start">
                    <span class="text-red-500 font-bold mr-2 text-lg">✗</span>
                    <span class="text-gray-500"><strong>User Management:</strong> Requires administrator access</span>
                </div>
            </div>
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-xs text-blue-800">
                    <strong>💡 Tip:</strong> You can import and export data freely. Contact an administrator if you need full CRUD access.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
