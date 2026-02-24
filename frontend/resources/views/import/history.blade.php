@extends('layouts.app')

@section('title', 'Import History')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Import History</h1>
        <p class="mt-2 text-sm text-gray-600">View all import logs and details</p>
    </div>

    <!-- History Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody id="history-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading history...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow"></div>

    <!-- Document Upload History Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Document Upload History</h2>
            <p class="text-sm text-gray-600">View uploaded document records</p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody id="document-history-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading document upload history...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Document Pagination -->
    <div id="document-pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow"></div>
</div>

<script>
let currentPage = 1;
let currentLimit = 10;
let currentDocumentPage = 1;
let currentDocumentLimit = 10;

document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
    loadDocumentHistory();
});

async function loadHistory(page = 1) {
    currentPage = page;
    
    try {
        const response = await fetch(`http://localhost:8080/upload/history?page=${currentPage}&limit=${currentLimit}`);
        const data = await response.json();
        
        displayHistory(data.data || []);
        displayPagination(data);
    } catch (error) {
        console.error('Error loading history:', error);
        document.getElementById('history-tbody').innerHTML = 
            '<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Error loading history</td></tr>';
    }
}

function displayHistory(logs) {
    const tbody = document.getElementById('history-tbody');
    
    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No import history found</td></tr>';
        return;
    }
    
    tbody.innerHTML = logs.map(log => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.id}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${log.file_name}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${log.import_type === 'csv' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                    ${log.import_type.toUpperCase()}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.total_records}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">${log.success_count}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm ${log.failure_count > 0 ? 'text-red-600' : 'text-gray-500'}">${log.failure_count}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(log.status)}">
                    ${log.status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(log.created_at).toLocaleString()}</td>
        </tr>
    `).join('');
}

function getStatusColor(status) {
    switch(status) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'failed': return 'bg-red-100 text-red-800';
        case 'processing': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function displayPagination(data) {
    const pagination = document.getElementById('pagination');
    const totalPages = data.total_pages || 1;
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '<div class="flex items-center justify-between w-full">';
    paginationHTML += `<div class="text-sm text-gray-700">Showing page ${currentPage} of ${totalPages} (${data.total} total imports)</div>`;
    paginationHTML += '<div class="flex space-x-2">';
    
    if (currentPage > 1) {
        paginationHTML += `<button onclick="loadHistory(${currentPage - 1})" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Previous</button>`;
    }
    
    if (currentPage < totalPages) {
        paginationHTML += `<button onclick="loadHistory(${currentPage + 1})" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Next</button>`;
    }
    
    paginationHTML += '</div></div>';
    pagination.innerHTML = paginationHTML;
}

async function loadDocumentHistory(page = 1) {
    currentDocumentPage = page;

    try {
        const response = await fetch(`http://localhost:8080/documents?page=${currentDocumentPage}&limit=${currentDocumentLimit}`);
        const data = await response.json();

        displayDocumentHistory(data.data || []);
        displayDocumentPagination(data);
    } catch (error) {
        console.error('Error loading document history:', error);
        document.getElementById('document-history-tbody').innerHTML =
            '<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Error loading document upload history</td></tr>';
    }
}

function displayDocumentHistory(documents) {
    const tbody = document.getElementById('document-history-tbody');

    if (documents.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No document upload history found</td></tr>';
        return;
    }

    tbody.innerHTML = documents.map(doc => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${doc.id}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${doc.file_name || doc.original_name || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                    ${(doc.document_type || 'other').toUpperCase()}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${doc.category || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatFileSize(doc.file_size || 0)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${doc.uploaded_by || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(doc.status || 'active')}">
                    ${doc.status || 'active'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(doc.created_at).toLocaleString()}</td>
        </tr>
    `).join('');
}

function displayDocumentPagination(data) {
    const pagination = document.getElementById('document-pagination');
    const totalPages = data.total_pages || 1;

    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let paginationHTML = '<div class="flex items-center justify-between w-full">';
    paginationHTML += `<div class="text-sm text-gray-700">Showing page ${currentDocumentPage} of ${totalPages} (${data.total || 0} total uploads)</div>`;
    paginationHTML += '<div class="flex space-x-2">';

    if (currentDocumentPage > 1) {
        paginationHTML += `<button onclick="loadDocumentHistory(${currentDocumentPage - 1})" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Previous</button>`;
    }

    if (currentDocumentPage < totalPages) {
        paginationHTML += `<button onclick="loadDocumentHistory(${currentDocumentPage + 1})" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Next</button>`;
    }

    paginationHTML += '</div></div>';
    pagination.innerHTML = paginationHTML;
}

function formatFileSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(2)} KB`;
    if (bytes < 1024 * 1024 * 1024) return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    return `${(bytes / (1024 * 1024 * 1024)).toFixed(2)} GB`;
}
</script>
@endsection
