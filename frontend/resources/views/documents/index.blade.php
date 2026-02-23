@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="space-y-6">
    <!-- Alert Container -->
    <div id="alert-container"></div>
    
    <div class="bg-gradient-to-r from-blue-800 to-green-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm p-3 rounded-xl">
                     <img src="{{ asset('Logo/Document.svg') }}" alt="Company Logo" class="h-8 w-8">
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Documents</h1>
                    <p class="mt-1 text-white opacity-90">Manage your uploaded documents and files</p>
                </div>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.documents.create' : 'user.documents.create') }}" class="inline-flex items-center px-6 py-3 bg-white text-blue-600 rounded-xl text-sm font-bold hover:bg-blue-50 transition-all transform hover:-translate-y-0.5 shadow-lg">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Upload Document
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 p-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div class="space-y-2">
                <label for="search" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Search Documents
                </label>
                <div class="relative">
                    <input type="text" id="search" placeholder="Search by filename or description..." 
                           class="block w-full px-4 py-3 pl-11 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-base">
                    <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-2">
                <label for="category-filter" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Filter by Category
                </label>
                <select id="category-filter" 
                        class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all text-base">
                    <option value="">📂 All Categories</option>
                </select>
            </div>
            <div class="space-y-2">
                <label for="type-filter" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h8a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                    Filter by Type
                </label>
                <select id="type-filter"
                        class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all text-base">
                    <option value="">🗂️ All Types</option>
                    <option value="pdf">📄 pdf</option>
                    <option value="doc">📝 doc</option>
                    <option value="docx">📝 docx</option>
                    <option value="xls">📊 xls</option>
                    <option value="xlsx">📊 xlsx</option>
                    <option value="ppt">📽️ ppt</option>
                    <option value="pptx">📽️ pptx</option>
                    <option value="csv">📋 csv</option>
                    <option value="json">🔧 json</option>
                    <option value="txt">🗒️ txt</option>
                    <option value="jpg">🖼️ jpg</option>
                    <option value="jpeg">🖼️ jpeg</option>
                    <option value="png">🖼️ png</option>
                    <option value="gif">🎞️ gif</option>
                    <option value="zip">🗜️ zip</option>
                    <option value="rar">🗜️ rar</option>
                    <option value="other">📁 other</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Documents Grid -->
    <div id="documents-grid" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div class="col-span-full flex flex-col items-center justify-center py-16">
            <div class="relative">
                <div class="h-16 w-16 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"></div>
            </div>
            <p class="text-gray-600 font-semibold text-lg mt-6">Loading documents...</p>
            <p class="text-gray-400 text-sm mt-2">Please wait while we fetch your files</p>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow"></div>
</div>

<!-- Delete Modal -->
<div id="delete-modal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-slideIn" onclick="event.stopPropagation()">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-full bg-red-100 sm:mx-0 sm:h-12 sm:w-12 animate-pulse">
                        <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-xl leading-6 font-bold text-gray-900">Delete Document</h3>
                        <div class="mt-3">
                            <p class="text-sm text-gray-600 leading-relaxed">Are you sure you want to delete this document? The file will be permanently removed from the server and cannot be recovered.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                <button onclick="confirmDelete()" type="button" class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-base font-bold text-white hover:from-red-700 hover:to-red-800 sm:w-auto sm:text-sm transition-all transform hover:-translate-y-0.5">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Document
                </button>
                <button onclick="closeDeleteModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentLimit = 12;
let deleteDocumentId = null;
const csrfToken = '{{ csrf_token() }}';

// Helper function to get user role
function getUserRole() {
    return '{{ session("user")["role"] ?? "user" }}';
}

document.addEventListener('DOMContentLoaded', function() {
    loadCategoryFilterOptions();
    loadDocuments();

    document.getElementById('category-filter').addEventListener('change', () => loadDocuments(1));
    document.getElementById('type-filter').addEventListener('change', () => loadDocuments(1));
    document.getElementById('search').addEventListener('keyup', debounce(() => loadDocuments(1), 500));
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

async function loadCategoryFilterOptions() {
    const categoryFilter = document.getElementById('category-filter');
    try {
        const response = await fetch('http://localhost:8080/document-categories');
        const result = await response.json();
        const categories = result.data || [];

        categoryFilter.innerHTML = '<option value="">📂 All Categories</option>';
        categories.forEach((category) => {
            const option = document.createElement('option');
            option.value = category.name;
            option.textContent = `📁 ${category.name}`;
            categoryFilter.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading category filters:', error);
    }
}

async function loadDocuments(page = 1) {
    currentPage = page;
    const category = document.getElementById('category-filter').value;
    const documentType = document.getElementById('type-filter').value;

    let url = `http://localhost:8080/documents?page=${currentPage}&limit=${currentLimit}`;

    if (category) {
        url += `&category=${encodeURIComponent(category)}`;
    }
    if (documentType) {
        url += `&document_type=${encodeURIComponent(documentType)}`;
    }

    try {
        const response = await fetch(url);
        const data = await response.json();

        displayDocuments(data.data || []);
        displayPagination(data);
    } catch (error) {
        console.error('Error loading documents:', error);
        showAlert('Error loading documents', 'error');
    }
}

function displayDocuments(documents) {
    const grid = document.getElementById('documents-grid');
    
    if (documents.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full flex flex-col items-center justify-center py-16">
                <svg class="h-24 w-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 font-semibold text-lg">No documents found</p>
                <p class="text-gray-400 text-sm mt-2">Try adjusting your filters or upload a new document</p>
            </div>
        `;
        return;
    }
    
    grid.innerHTML = documents.map((doc, index) => `
        <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 group" style="animation: fadeIn 0.3s ease-out ${index * 0.05}s both">
            <div class="bg-gradient-to-br from-blue-50 to-green-50 p-6 border-b border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="p-2.5 bg-white rounded-xl shadow-sm group-hover:shadow-md transition-shadow">
                            ${getFileIcon(doc.file_type)}
                        </div>
                        <span class="px-3 py-1 text-xs font-bold rounded-full text-white bg-gradient-to-r from-blue-600 to-green-600 shadow-sm">
                            ${doc.file_type.toUpperCase().replace('.', '')}
                        </span>
                    </div>
                    <span class="px-3 py-1.5 text-xs font-semibold rounded-full shadow-sm ${getCategoryColor(doc.category)}">
                        ${getCategoryEmoji(doc.category)} ${doc.category}
                    </span>
                </div>
                <div class="h-16 flex items-center">
                    <h3 class="text-base font-bold text-gray-900 line-clamp-2 group-hover:text-blue-600 transition-colors" title="${doc.original_name}">
                        ${doc.original_name}
                    </h3>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="text-xs font-semibold text-purple-700 bg-purple-100 rounded-lg px-3 py-2 inline-block">
                    Type: ${getDocumentTypeEmoji(doc.document_type)} ${(doc.document_type || 'other').toLowerCase()}
                </div>
                <p class="text-sm text-gray-600 line-clamp-2 min-h-[2.5rem]">
                    ${doc.description || '<span class="italic text-gray-400">No description provided</span>'}
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="flex items-center space-x-2 text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="font-semibold">${formatFileSize(doc.file_size)}</span>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-semibold">${new Date(doc.created_at).toLocaleDateString()}</span>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="font-semibold truncate" title="${doc.uploaded_by || 'anonymous'}">${doc.uploaded_by || 'anonymous'}</span>
                    </div>
                </div>
                <div class="flex space-x-2 pt-2">
                    <a href="http://localhost:8080/documents/${doc.id}/download" 
                       class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border-2 border-blue-200 rounded-xl text-sm font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 hover:border-blue-300 transition-all transform hover:-translate-y-0.5">
                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download
                    </a>
                    <button onclick="showDeleteModal(${doc.id})" type="button"
                            class="px-4 py-2.5 border-2 border-red-200 rounded-xl text-sm font-bold text-red-700 bg-red-50 hover:bg-red-100 hover:border-red-300 transition-all transform hover:-translate-y-0.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function getCategoryEmoji(category) {
    const emojis = {
        'reports': '📊',
        'images': '🖼️',
        'videos': '🎥',
        'pdfs': '📄',
        'other': '📦'
    };
    return emojis[category] || '📦';
}

function getFileIcon(fileType) {
    const icons = {
        '.pdf': '<svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12V6h-4V2H4v16zm-2 1V0h12l4 4v16H2z"/></svg>',
        '.jpg': '<svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        '.png': '<svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        '.mp4': '<svg class="h-6 w-6 text-pink-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 6v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm12.553 1.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>',
    };
    return icons[fileType] || '<svg class="h-6 w-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>';
}

function getDocumentTypeEmoji(documentType) {
    const type = (documentType || 'other').toLowerCase();
    const icons = {
        pdf: '📄',
        doc: '📝',
        docx: '📝',
        xls: '📊',
        xlsx: '📊',
        ppt: '📽️',
        pptx: '📽️',
        csv: '📋',
        json: '🔧',
        txt: '🗒️',
        jpg: '🖼️',
        jpeg: '🖼️',
        png: '🖼️',
        gif: '🎞️',
        zip: '🗜️',
        rar: '🗜️',
        other: '📁',
    };
    return icons[type] || '📁';
}

function getCategoryColor(category) {
    const colors = {
        'reports': 'bg-blue-100 text-blue-800',
        'images': 'bg-green-100 text-green-800',
        'videos': 'bg-pink-100 text-pink-800',
        'pdfs': 'bg-red-100 text-red-800',
    };
    return colors[category] || 'bg-gray-100 text-gray-800';
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
}

function displayPagination(data) {
    const pagination = document.getElementById('pagination');
    const totalPages = data.total_pages || 1;
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '<div class="flex items-center justify-between w-full p-4">';
    paginationHTML += `<div class="text-sm font-semibold text-gray-700 flex items-center space-x-2">
        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span>Page ${currentPage} of ${totalPages} <span class="text-gray-500">(${data.total} total documents)</span></span>
    </div>`;
    paginationHTML += '<div class="flex space-x-3">';
    
    if (currentPage > 1) {
        paginationHTML += `<button onclick="loadDocuments(${currentPage - 1})" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 to-green-600 text-white font-bold shadow-lg hover:from-blue-800 hover:to-green-700 transition-all transform hover:-translate-y-0.5">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Previous
        </button>`;
    }
    
    if (currentPage < totalPages) {
        paginationHTML += `<button onclick="loadDocuments(${currentPage + 1})" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 to-green-600 text-white font-bold shadow-lg hover:from-blue-800 hover:to-green-700 transition-all transform hover:-translate-y-0.5">
            Next
            <svg class="h-4 w-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>`;
    }
    
    paginationHTML += '</div></div>';
    pagination.innerHTML = paginationHTML;
}

function showDeleteModal(id) {
    deleteDocumentId = id;
    document.getElementById('delete-modal').classList.remove('hidden');
}

function closeDeleteModal() {
    deleteDocumentId = null;
    document.getElementById('delete-modal').classList.add('hidden');
}

async function confirmDelete() {
    if (!deleteDocumentId) return;

    const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/user';
    const deleteUrl = `${basePath}/documents/${deleteDocumentId}/delete`;

    try {
        const response = await fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({}),
        });

        const result = await response.json().catch(() => ({}));

        if (response.ok) {
            showAlert('Document deleted successfully', 'success');
            loadDocuments(currentPage);
        } else {
            showAlert(result.error || `Failed to delete document (${response.status})`, 'error');
        }
    } catch (error) {
        showAlert('Network error: Unable to delete document', 'error');
    }

    closeDeleteModal();
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const icon = type === 'success' 
        ? '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
        : '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    const alertClass = type === 'success' 
        ? 'bg-green-50 text-green-800 border-green-200' 
        : 'bg-red-50 text-red-800 border-red-200';
    
    alertContainer.innerHTML = `
        <div class="${alertClass} px-6 py-4 rounded-xl border-2 shadow-lg animate-slideIn flex items-center space-x-3">
            <div class="flex-shrink-0">
                ${icon}
            </div>
            <div class="flex-1 font-semibold">
                ${message}
            </div>
            <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    `;
    
    setTimeout(() => {
        const alert = alertContainer.querySelector('div');
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            alert.style.transition = 'all 0.3s ease-out';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
}
</script>
@endsection
