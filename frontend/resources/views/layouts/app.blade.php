<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Data Import Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        .animate-slide-in {
            animation: slideIn 0.4s ease-out;
        }
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #0058A3, #8CC63F);
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-green-50 to-blue-100 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="glass-effect sticky top-0 z-50 shadow-lg backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ session()->has('user') ? (session('user')['role'] === 'admin' ? route('admin.dashboard') : route('user.dashboard')) : route('login') }}" class="flex items-center space-x-3 group">
                            <div class="bg-gradient-to-r from-blue-700 to-green-600 p-2 rounded-lg shadow-md group-hover:shadow-lg transition-shadow">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <span class="text-xl font-bold bg-gradient-to-r from-blue-700 to-green-600 bg-clip-text text-transparent">
                                Data Import Dashboard
                            </span>
                        </a>
                    </div>
                    
                    @if(session()->has('user'))
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-1">
                        <a href="{{ session('user')['role'] === 'admin' ? route('admin.dashboard') : route('user.dashboard') }}" 
                           class="nav-link text-gray-700 hover:text-blue-600 inline-flex items-center px-3 pt-1 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </a>
                        
                        @if(session('user')['role'] === 'admin')
                        <a href="{{ route('admin.users.index') }}" 
                           class="nav-link text-gray-700 hover:text-blue-600 inline-flex items-center px-3 pt-1 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Users
                        </a>
                        @endif
                        
                        <a href="{{ route('data-records.index') }}" 
                           class="nav-link text-gray-700 hover:text-blue-600 inline-flex items-center px-3 pt-1 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Data Records
                        </a>
                        <a href="{{ route('documents.index') }}" 
                           class="nav-link text-gray-700 hover:text-blue-600 inline-flex items-center px-3 pt-1 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Documents
                        </a>
                        
                        <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.import.index' : 'user.import.index') }}" 
                           class="nav-link text-gray-700 hover:text-blue-600 inline-flex items-center px-3 pt-1 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Import
                        </a>
                        <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.export.index' : 'user.export.index') }}" 
                           class="nav-link text-gray-700 hover:text-blue-600 inline-flex items-center px-3 pt-1 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export
                        </a>
                        
                        <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.import.history' : 'user.import.history') }}" 
                           class="nav-link text-gray-700 hover:text-blue-600 inline-flex items-center px-3 pt-1 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            History
                        </a>
                    </div>
                    @endif
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center">
                    @if(session()->has('user'))
                    <div class="relative group">
                        <button class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-2">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr(session('user')['name'], 0, 1)) }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-bold text-gray-900">{{ session('user')['name'] }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if(session('user')['role'] === 'admin')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                            👑 Admin
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                                            👤 User
                                        </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-56 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 -translate-y-2">
                            <div class="p-3 border-b border-gray-100">
                                <p class="text-sm font-bold text-gray-900">{{ session('user')['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ session('user')['email'] }}</p>
                            </div>
                            <div class="p-2">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center font-semibold">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-blue-700 to-green-600 rounded-lg hover:from-blue-800 hover:to-green-700 transition-all shadow-md hover:shadow-lg">
                            Register
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <div id="alert-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4"></div>

    <!-- Page Content -->
    <main class="py-10 animate-fade-in flex-grow">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="glass-effect border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                <p class="text-center text-gray-600 text-sm font-medium">
                    Data Import Dashboard &copy; {{ date('Y') }}
                </p>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                    <span class="flex items-center">
                        <span class="h-2 w-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        System Online
                    </span>
                    <span>v1.0.0</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
