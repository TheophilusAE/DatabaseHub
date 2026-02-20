@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-green-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent mb-3">
                Multi-Table Upload
            </h1>
            <p class="text-lg text-gray-600">Upload multiple CSV or JSON files to different tables simultaneously</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer" class="mb-6"></div>

        <!-- Upload Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6 border-t-4 border-green-500">
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Files to Upload
                    </h2>
                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold" id="fileCounter">0 files</span>
                </div>
                <p class="text-gray-600">Add one or more files and select their target tables. Supported formats: CSV and JSON</p>
            </div>

            <!-- File Upload Items -->
            <div id="uploadItemsContainer" class="space-y-4 mb-6"></div>

            <!-- Add File Button -->
            <button onclick="addUploadItem()" class="w-full py-4 border-2 border-dashed border-green-300 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all transform hover:scale-[1.01] text-gray-700 hover:text-green-700 flex items-center justify-center group">
                <svg class="h-7 w-7 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="font-semibold">Add Another File</span>
            </button>

            <!-- Upload Button -->
            <div class="mt-8 flex justify-end space-x-3">
                <button onclick="clearAll()" class="px-8 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all font-semibold transform hover:scale-105">
                    Clear All
                </button>
                <button onclick="startUpload()" id="uploadBtn" class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all font-semibold transform hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    <span id="uploadBtnText" class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13l-3-3m0 0l-3 3m3-3v12M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2h-3M3 10V8a2 2 0 012-2h2"/>
                        </svg>
                        Start Upload
                    </span>
                </button>
            </div>
        </div>

        <!-- Upload Results -->
        <div id="uploadResults" class="bg-white rounded-2xl shadow-xl p-8 hidden border-t-4 border-blue-500">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                <svg class="w-7 h-7 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Upload Results
            </h2>
            <div id="resultsContainer"></div>
        </div>
    </div>
</div>

<!-- File Upload Item Template (Hidden) -->
<template id="uploadItemTemplate">
    <div class="upload-item border-2 border-gray-200 rounded-xl p-6 bg-gradient-to-br from-white to-gray-50 hover:shadow-lg transition-all">
        <div class="flex items-start space-x-4">
            <!-- File Input Area -->
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select File</label>
                <div class="relative">
                    <input type="file" accept=".csv,.json" class="file-input hidden" onchange="handleFileSelect(this)">
                    <button onclick="this.previousElementSibling.click()" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg hover:bg-white hover:border-green-500 text-left flex items-center transition-all group">
                        <svg class="h-6 w-6 mr-2 text-gray-400 group-hover:text-green-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span class="file-name text-gray-500 group-hover:text-gray-700 font-medium">Choose a file...</span>
                    </button>
                </div>
            </div>

            <!-- Table Selection -->
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Target Table</label>
                <select class="table-select w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors font-medium" required>
                    <option value="">Select table...</option>
                </select>
            </div>

            <!-- Remove Button -->
            <div class="pt-7">
                <button onclick="removeUploadItem(this)" class="p-3 text-red-600 hover:bg-red-100 rounded-lg transition-all transform hover:scale-110" title="Remove this file">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- File Info -->
        <div class="file-info mt-4 p-3 bg-green-50 border-l-4 border-green-500 rounded text-sm text-gray-700 hidden">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="file-details font-medium"></span>
            </div>
        </div>
    </div>
</template>

<script>
let allTables = [];
let uploadItemCounter = 0;
const SESSION_USER_ID = {{ session('user')['id'] ?? 'null' }};
const SESSION_USER_ROLE = '{{ strtolower(session('user')['role'] ?? 'user') }}';

document.addEventListener('DOMContentLoaded', function() {
    loadTables();
    addUploadItem(); // Add initial upload item
});

function getCurrentUser() {
    return {
        userId: SESSION_USER_ID || localStorage.getItem('user_id') || sessionStorage.getItem('user_id'),
        userRole: SESSION_USER_ROLE || localStorage.getItem('user_role') || 'user'
    };
}


async function loadTables() {
    try {
        const { userId, userRole } = getCurrentUser();
        
        const url = new URL('http://localhost:8080/simple-multi/tables');
        if (userId) url.searchParams.append('user_id', userId);
        if (userRole) url.searchParams.append('user_role', userRole);

        const response = await fetch(url.toString(), {
            method: 'GET',
            credentials: 'include', // Send cookies for session auth
            headers: {
                'Accept': 'application/json',
                'X-User-Role': String(userRole || 'user').toLowerCase(),
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                showAlert('Session expired. Please login again.', 'error');
                window.location.href = '/login';
                throw new Error('Unauthorized');
            }
            throw new Error('Failed to load tables');
        }
        
        const data = await response.json();
        allTables = data.tables || [];
        updateAllTableSelects();
    } catch (error) {
        if (error.message !== 'Unauthorized') {
            showAlert('Error loading tables: ' + error.message, 'error');
        }
    }
}

function updateAllTableSelects() {
    document.querySelectorAll('.table-select').forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '<option value="">Select table...</option>' + 
            allTables.map(table => `<option value="${table.table_name}">${table.table_name} (${table.row_count} rows)</option>`).join('');
        if (currentValue) {
            select.value = currentValue;
        }
    });
}

function addUploadItem() {
    const template = document.getElementById('uploadItemTemplate');
    const clone = template.content.cloneNode(true);
    
    // Update table select with loaded tables
    const select = clone.querySelector('.table-select');
    select.innerHTML = '<option value="">Select table...</option>' + 
        allTables.map(table => `<option value="${table.table_name}">${table.table_name} (${table.row_count} rows)</option>`).join('');
    
    document.getElementById('uploadItemsContainer').appendChild(clone);
    uploadItemCounter++;
    updateFileCounter();
}

function removeUploadItem(button) {
    const item = button.closest('.upload-item');
    item.remove();
    
    // If no items left, add one
    if (document.querySelectorAll('.upload-item').length === 0) {
        addUploadItem();
    }
    updateFileCounter();
}

function updateFileCounter() {
    const count = document.querySelectorAll('.upload-item').length;
    const counter = document.getElementById('fileCounter');
    counter.textContent = `${count} file${count !== 1 ? 's' : ''}`;
}

function handleFileSelect(input) {
    const item = input.closest('.upload-item');
    const fileNameSpan = item.querySelector('.file-name');
    const fileInfo = item.querySelector('.file-info');
    const fileDetails = item.querySelector('.file-details');
    
    if (input.files.length > 0) {
        const file = input.files[0];
        fileNameSpan.textContent = file.name;
        fileNameSpan.classList.remove('text-gray-500');
        fileNameSpan.classList.add('text-gray-900', 'font-medium');
        
        const sizeInMB = (file.size / 1024 / 1024).toFixed(2);
        fileDetails.textContent = `${file.name} (${sizeInMB} MB)`;
        fileInfo.classList.remove('hidden');
    } else {
        fileNameSpan.textContent = 'Choose a file...';
        fileNameSpan.classList.add('text-gray-500');
        fileNameSpan.classList.remove('text-gray-900', 'font-medium');
        fileInfo.classList.add('hidden');
    }
}

function clearAll() {
    document.getElementById('uploadItemsContainer').innerHTML = '';
    addUploadItem();
    document.getElementById('uploadResults').classList.add('hidden');
}

async function startUpload() {
    const { userId, userRole } = getCurrentUser();
    const items = document.querySelectorAll('.upload-item');
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadBtnText = document.getElementById('uploadBtnText');
    
    // Validation
    let valid = true;
    const formData = new FormData();
    const tableNames = [];
    
    items.forEach((item, index) => {
        const fileInput = item.querySelector('.file-input');
        const tableSelect = item.querySelector('.table-select');
        
        if (!fileInput.files.length) {
            showAlert('Please select a file for all upload items', 'error');
            valid = false;
            return;
        }
        
        if (!tableSelect.value) {
            showAlert('Please select a target table for all upload items', 'error');
            valid = false;
            return;
        }
        
        formData.append('files', fileInput.files[0]);
        tableNames.push(tableSelect.value);
    });
    
    if (!valid || tableNames.length === 0) return;
    
    tableNames.forEach(name => formData.append('table_names', name));
    
    // Disable button
    uploadBtn.disabled = true;
    uploadBtnText.textContent = 'Uploading...';
    
    try {
        const url = new URL('http://localhost:8080/simple-multi/upload-multiple');
        if (userId) url.searchParams.append('user_id', userId);
        if (userRole) url.searchParams.append('user_role', userRole);

        const response = await fetch(url.toString(), {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-User-Role': String(userRole || 'user').toLowerCase(),
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: formData
            // Don't set Content-Type - browser sets it with boundary for FormData
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Session expired. Please login again.');
            }
            const error = await response.json().catch(() => ({}));
            throw new Error(error.error || 'Upload failed');
        }
        
        const result = await response.json();
        
        displayResults(result);
        showAlert(`Upload completed! Success: ${result.total_success}, Failed: ${result.total_failed}`, 
                  result.total_failed === 0 ? 'success' : 'warning');
        
        loadTables(); // Refresh table list
        
    } catch (error) {
        showAlert('Error during upload: ' + error.message, 'error');
        if (error.message.includes('Session expired')) {
            window.location.href = '/login';
        }
    } finally {
        uploadBtn.disabled = false;
        uploadBtnText.textContent = 'Start Upload';
    }
}

function displayResults(result) {
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsDiv = document.getElementById('uploadResults');
    
    resultsDiv.classList.remove('hidden');
    
    const resultsHTML = result.results.map(res => {
        const statusColor = res.status === 'completed' ? 'green' : res.status === 'failed' ? 'red' : 'yellow';
        const statusIcon = res.status === 'completed' ? 
            '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' :
            '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
        
        return `
            <div class="border-l-4 border-${statusColor}-500 bg-${statusColor}-50 p-4 mb-3 rounded">
                <div class="flex items-start">
                    <div class="flex-shrink-0 text-${statusColor}-600">
                        ${statusIcon}
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-${statusColor}-800">${res.table} - ${res.filename}</h3>
                        <div class="mt-2 text-sm text-${statusColor}-700">
                            <p>Status: <span class="font-semibold">${res.status}</span></p>
                            <p>Success: <span class="font-semibold">${res.success_count}</span> records</p>
                            <p>Failed: <span class="font-semibold">${res.failure_count}</span> records</p>
                            ${res.error ? `<p class="mt-1 text-red-600">Error: ${res.error}</p>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    resultsContainer.innerHTML = resultsHTML;
    
    // Scroll to results
    resultsDiv.scrollIntoView({ behavior: 'smooth' });
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    let alertClass = 'bg-blue-100 border-blue-500 text-blue-700';
    
    if (type === 'success') {
        alertClass = 'bg-green-100 border-green-500 text-green-700';
    } else if (type === 'error') {
        alertClass = 'bg-red-100 border-red-500 text-red-700';
    } else if (type === 'warning') {
        alertClass = 'bg-yellow-100 border-yellow-500 text-yellow-700';
    }
    
    alertContainer.innerHTML = `
        <div class="${alertClass} border-l-4 p-4 mb-4 rounded" role="alert">
            <p class="font-medium">${message}</p>
        </div>
    `;
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}
</script>
@endsection
