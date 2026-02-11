@extends('layouts.app')

@section('title', 'Data Records')

@section('content')
<div class="space-y-6 animate-slide-in">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-4xl font-extrabold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Data Records
            </h1>
            <p class="mt-2 text-base text-gray-600">Manage and organize your data records efficiently</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('data-records.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Record
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-lg rounded-2xl p-6 border border-gray-100">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div class="relative">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search Records</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="search" placeholder="Search by name or description..." 
                           class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
            </div>
            <div>
                <label for="category-filter" class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                <select id="category-filter" 
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">All Categories</option>
                    <option value="electronics">📱 Electronics</option>
                    <option value="furniture">🪑 Furniture</option>
                    <option value="clothing">👕 Clothing</option>
                    <option value="books">📚 Books</option>
                    <option value="other">📦 Other</option>
                </select>
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select id="status-filter" 
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">All Status</option>
                    <option value="active">✅ Active</option>
                    <option value="inactive">⭕ Inactive</option>
                    <option value="pending">⏳ Pending</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                ID
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Name
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Category
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Value
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Status
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="records-tbody" class="bg-white divide-y divide-gray-100">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
                                <p class="mt-4 text-gray-500 font-medium">Loading records...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200 rounded-2xl shadow-lg"></div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity modal-backdrop"></div>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-fade-in">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-full bg-red-100 sm:mx-0 sm:h-12 sm:w-12 animate-pulse">
                        <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-xl leading-6 font-bold text-gray-900">Delete Record</h3>
                        <div class="mt-3">
                            <p class="text-sm text-gray-600">
                                Are you sure you want to delete this record? This action cannot be undone and all associated data will be permanently removed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button onclick="confirmDelete()" type="button" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-5 py-3 bg-gradient-to-r from-red-600 to-red-700 text-base font-semibold text-white hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-all transform hover:-translate-y-0.5">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
                <button onclick="closeDeleteModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-5 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentLimit = 10;
let deleteRecordId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadRecords();
    
    // Filter listeners
    document.getElementById('category-filter').addEventListener('change', loadRecords);
    document.getElementById('status-filter').addEventListener('change', loadRecords);
    document.getElementById('search').addEventListener('keyup', debounce(loadRecords, 500));
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

async function loadRecords(page = 1) {
    currentPage = page;
    const category = document.getElementById('category-filter').value;
    const search = document.getElementById('search').value;
    
    let url = `http://localhost:8080/data?page=${currentPage}&limit=${currentLimit}`;
    
    if (category) {
        url = `http://localhost:8080/data/category/${category}?page=${currentPage}&limit=${currentLimit}`;
    }
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        displayRecords(data.data || []);
        displayPagination(data);
    } catch (error) {
        console.error('Error loading records:', error);
        showAlert('Error loading records', 'error');
    }
}

function displayRecords(records) {
    const tbody = document.getElementById('records-tbody');
    
    if (records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 font-semibold text-lg">No records found</p>
                        <p class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = records.map((record, index) => `
        <tr class="hover:bg-blue-50 transition-all duration-200 cursor-pointer group" style="animation: fadeIn 0.3s ease-out ${index * 0.05}s both">
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-500 text-white text-sm font-bold shadow-md">
                    ${record.id}
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div>
                        <div class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors">${record.name}</div>
                        <div class="text-sm text-gray-500 truncate max-w-xs">${record.description || '<span class="italic">No description</span>'}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm ${getCategoryStyle(record.category)}">
                    ${getCategoryIcon(record.category)} ${record.category}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-base font-bold text-gray-900">$${parseFloat(record.value).toFixed(2)}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm ${getStatusColor(record.status)}">
                    ${getStatusIcon(record.status)} ${record.status.toUpperCase()}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <a href="/data-records/${record.id}/edit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <button onclick="showDeleteModal(${record.id})" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all transform hover:-translate-y-0.5">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </td>
        </tr>
    `).join('');
}

function getCategoryIcon(category) {
    const icons = {
        'electronics': '📱',
        'furniture': '🪑',
        'clothing': '👕',
        'books': '📚',
        'other': '📦'
    };
    return icons[category] || '📦';
}

function getCategoryStyle(category) {
    const styles = {
        'electronics': 'bg-blue-100 text-blue-800 border border-blue-200',
        'furniture': 'bg-green-100 text-green-800 border border-green-200',
        'clothing': 'bg-purple-100 text-purple-800 border border-purple-200',
        'books': 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        'other': 'bg-gray-100 text-gray-800 border border-gray-200'
    };
    return styles[category] || 'bg-gray-100 text-gray-800 border border-gray-200';
}

function getStatusColor(status) {
    switch(status) {
        case 'active': return 'bg-green-100 text-green-800 border border-green-200';
        case 'inactive': return 'bg-gray-100 text-gray-800 border border-gray-200';
        case 'pending': return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
        default: return 'bg-gray-100 text-gray-800 border border-gray-200';
    }
}

function getStatusIcon(status) {
    const icons = {
        'active': '✅',
        'inactive': '⭕',
        'pending': '⏳'
    };
    return icons[status] || '⭕';
}

function displayPagination(data) {
    const pagination = document.getElementById('pagination');
    const totalPages = data.total_pages || 1;
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '<div class="flex items-center justify-between w-full">';
    paginationHTML += `<div class="text-sm text-gray-700">Showing page ${currentPage} of ${totalPages} (${data.total} total records)</div>`;
    paginationHTML += '<div class="flex space-x-2">';
    
    if (currentPage > 1) {
        paginationHTML += `<button onclick="loadRecords(${currentPage - 1})" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Previous</button>`;
    }
    
    if (currentPage < totalPages) {
        paginationHTML += `<button onclick="loadRecords(${currentPage + 1})" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Next</button>`;
    }
    
    paginationHTML += '</div></div>';
    pagination.innerHTML = paginationHTML;
}

function showDeleteModal(id) {
    deleteRecordId = id;
    document.getElementById('delete-modal').classList.remove('hidden');
}

function closeDeleteModal() {
    deleteRecordId = null;
    document.getElementById('delete-modal').classList.add('hidden');
}

async function confirmDelete() {
    if (!deleteRecordId) return;
    
    try {
        const response = await fetch(`http://localhost:8080/data/${deleteRecordId}`, {
            method: 'DELETE'
        });
        
        if (response.ok) {
            showAlert('Record deleted successfully', 'success');
            loadRecords(currentPage);
        } else {
            showAlert('Failed to delete record', 'error');
        }
    } catch (error) {
        console.error('Error deleting record:', error);
        showAlert('Error deleting record', 'error');
    }
    
    closeDeleteModal();
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    
    alertContainer.innerHTML = `
        <div class="${alertClass} px-4 py-3 rounded relative mb-4">
            ${message}
        </div>
    `;
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 3000);
}
</script>
@endsection
