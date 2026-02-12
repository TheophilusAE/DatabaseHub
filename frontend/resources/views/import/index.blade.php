@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
<div class="space-y-6">
    <!-- Alert Container -->
    <div id="alert-container"></div>
    
    <div class="bg-gradient-to-r from-blue-800 to-green-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm p-3 rounded-xl">
                    <img src="{{ asset('Logo/Upload.svg') }}" alt="Company Logo" class="h-8 w-8">
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Import Data</h1>
                    <p class="text-white opacity-90 mt-1">Import data from CSV or JSON files into your database</p>
                </div>
            </div>
            <div>
                <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.import.history' : 'user.import.history') }}" class="inline-flex items-center px-4 py-2.5 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded-xl text-sm font-bold text-blue-600 hover:bg-opacity-30 transition-all transform hover:-translate-y-0.5 shadow-lg">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Import History
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- CSV Import -->
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-white rounded-xl shadow-sm">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">CSV Import</h3>
                        <p class="text-sm text-gray-600 mt-1">Comma-separated values file</p>
                    </div>
                </div>
            </div>
            
            <form id="csv-form" class="space-y-6 p-6">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 flex items-center">
                        <svg class="h-4 w-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Choose CSV File
                    </label>
                    <input type="file" id="csv-file" accept=".csv" required
                           class="block w-full text-sm text-gray-700 border-2 border-gray-200 rounded-xl cursor-pointer focus:outline-none hover:border-green-400 transition-colors file:mr-4 file:py-3 file:px-6 file:rounded-l-xl file:border-0 file:text-sm file:font-bold file:bg-gradient-to-r file:from-green-600 file:to-emerald-600 file:text-white hover:file:from-green-700 hover:file:to-emerald-700 file:cursor-pointer">
                    <p class="text-xs text-gray-500 ml-1 mt-2 flex items-start">
                        <svg class="h-4 w-4 mr-1 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span>Required headers: <strong>name, description, category, value, status</strong></span>
                    </p>
                </div>
                
                <button type="submit" class="w-full inline-flex justify-center items-center py-3.5 px-6 border border-transparent rounded-xl shadow-lg text-base font-bold text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 transition-all transform hover:-translate-y-0.5 hover:shadow-xl">
                    <svg class="-ml-1 mr-2 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Import CSV Data
                </button>
            </form>
            
            <div id="csv-progress" class="hidden px-6 pb-6 space-y-3">
                <div class="relative">
                    <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div id="csv-progress-bar" class="h-full rounded-full transition-all duration-500 bg-gradient-to-r from-green-600 to-emerald-600 relative" style="width: 0%">
                            <div class="absolute inset-0 bg-white opacity-30 animate-progressFlow"></div>
                        </div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="csv-percent" class="text-xs font-bold text-gray-700">0%</span>
                    </div>
                </div>
                <p id="csv-status" class="text-sm text-gray-600 text-center font-semibold"></p>
            </div>
        </div>

        <!-- JSON Import -->
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="bg-gradient-to-br from-blue-50 to-green-50 p-6 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-white rounded-xl shadow-sm">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">JSON Import</h3>
                        <p class="text-sm text-gray-600 mt-1">JavaScript Object Notation file</p>
                    </div>
                </div>
            </div>
            
            <form id="json-form" class="space-y-6 p-6">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 flex items-center">
                        <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        Choose JSON File
                    </label>
                    <input type="file" id="json-file" accept=".json" required
                           class="block w-full text-sm text-gray-700 border-2 border-gray-200 rounded-xl cursor-pointer focus:outline-none hover:border-blue-400 transition-colors file:mr-4 file:py-3 file:px-6 file:rounded-l-xl file:border-0 file:text-sm file:font-bold file:bg-gradient-to-r file:from-blue-700 file:to-green-600 file:text-white hover:file:from-blue-800 hover:file:to-green-700 file:cursor-pointer">
                    <p class="text-xs text-gray-500 ml-1 mt-2 flex items-start">
                        <svg class="h-4 w-4 mr-1 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span>JSON array with data objects containing record properties</span>
                    </p>
                </div>
                
                <button type="submit" class="w-full inline-flex justify-center items-center py-3.5 px-6 border border-transparent rounded-xl shadow-lg text-base font-bold text-white bg-gradient-to-r from-blue-700 to-green-600 hover:from-blue-800 hover:to-green-700 transition-all transform hover:-translate-y-0.5 hover:shadow-xl">
                    <svg class="-ml-1 mr-2 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Import JSON Data
                </button>
            </form>
            
            <div id="json-progress" class="hidden px-6 pb-6 space-y-3">
                <div class="relative">
                    <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div id="json-progress-bar" class="h-full rounded-full transition-all duration-500 bg-gradient-to-r from-blue-700 to-green-600 relative" style="width: 0%">
                            <div class="absolute inset-0 bg-white opacity-30 animate-progressFlow"></div>
                        </div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="json-percent" class="text-xs font-bold text-gray-700">0%</span>
                    </div>
                </div>
                <p id="json-status" class="text-sm text-gray-600 text-center font-semibold"></p>
            </div>
        </div>
    </div>

    <!-- Import Guidelines -->
    <div class="bg-gradient-to-r from-blue-50 to-green-50 border-l-4 border-blue-500 rounded-r-2xl p-6 shadow-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                    <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-base font-bold text-gray-900 flex items-center">
                    💡 Import Guidelines & Requirements
                </h3>
                <div class="mt-3 text-sm text-gray-700">
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>CSV Files:</strong> Must include headers: name, description, category, value, status</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>JSON Files:</strong> Should contain an array of objects with record properties</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>Required fields:</strong> name, category, value, status</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>Optional fields:</strong> description, metadata (JSON string)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>Maximum file size:</strong> 500MB per import (supports large datasets)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-600 font-bold mr-2">✓</span>
                            <span><strong>Processing time:</strong> Up to 15 minutes for very large files</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Imports -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 flex items-center">
                <svg class="h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Recent Imports
            </h3>
        </div>
        <div id="recent-imports" class="space-y-3">
            <div class="flex items-center justify-center py-8">
                <div class="text-center">
                    <div class="inline-block h-8 w-8 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin mb-4"></div>
                    <p class="text-gray-500 font-semibold">Loading recent imports...</p>
                </div>
            </div>
        </div>
        <div class="mt-6 pt-4 border-t border-gray-200">
            <a href="{{ route(session('user')['role'] === 'admin' ? 'admin.import.history' : 'user.import.history') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-bold hover:underline">
                View All Import History
                <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadRecentImports();
    
    // Add file size validation
    const csvFileInput = document.getElementById('csv-file');
    const jsonFileInput = document.getElementById('json-file');
    
    csvFileInput.addEventListener('change', function() {
        validateFileSize(this, 'csv');
    });
    
    jsonFileInput.addEventListener('change', function() {
        validateFileSize(this, 'json');
    });
    
    // CSV form handler
    document.getElementById('csv-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        await handleImport('csv');
    });
    
    // JSON form handler
    document.getElementById('json-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        await handleImport('json');
    });
});

function validateFileSize(input, type) {
    const file = input.files[0];
    if (file) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        const maxSizeMB = 500;
        
        // Create or update file size display
        let sizeDisplay = document.getElementById(`${type}-file-size`);
        if (!sizeDisplay) {
            sizeDisplay = document.createElement('p');
            sizeDisplay.id = `${type}-file-size`;
            sizeDisplay.className = 'text-sm font-semibold mt-2 ml-1';
            input.parentElement.appendChild(sizeDisplay);
        }
        
        if (sizeMB > maxSizeMB) {
            sizeDisplay.innerHTML = `<span class="text-red-600">⚠️ File size: ${sizeMB} MB (exceeds ${maxSizeMB} MB limit)</span>`;
            showAlert(`File is too large (${sizeMB} MB). Maximum allowed is ${maxSizeMB} MB.`, 'error');
            input.value = '';
        } else if (sizeMB > 100) {
            sizeDisplay.innerHTML = `<span class="text-orange-600">📊 Large file: ${sizeMB} MB (may take several minutes to process)</span>`;
        } else if (sizeMB > 10) {
            sizeDisplay.innerHTML = `<span class="text-blue-600">📁 File size: ${sizeMB} MB</span>`;
        } else {
            sizeDisplay.innerHTML = `<span class="text-green-600">✓ File size: ${sizeMB} MB</span>`;
        }
    }
}

async function handleImport(type) {
    const fileInput = document.getElementById(`${type}-file`);
    const progressDiv = document.getElementById(`${type}-progress`);
    const progressBar = document.getElementById(`${type}-progress-bar`);
    const progressPercent = document.getElementById(`${type}-percent`);
    const statusText = document.getElementById(`${type}-status`);
    
    if (!fileInput.files[0]) {
        showAlert('Please select a file to import', 'error');
        return;
    }
    
    // Show progress
    progressDiv.classList.remove('hidden');
    progressBar.style.width = '20%';
    progressPercent.textContent = '20%';
    statusText.textContent = 'Uploading file...';
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    
    try {
        progressBar.style.width = '50%';
        progressPercent.textContent = '50%';
        statusText.textContent = 'Processing import...';
        
        // Create AbortController for timeout (15 minutes)
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 900000); // 15 minutes
        
        const response = await fetch(`http://localhost:8080/upload/${type}`, {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        const result = await response.json();
        
        progressBar.style.width = '100%';
        progressPercent.textContent = '100%';
        
        if (response.ok) {
            if (result.success > 0) {
                statusText.textContent = `✅ Success! Imported ${result.success} of ${result.total} records`;
                statusText.classList.add('text-green-600', 'font-bold');
                showAlert(`Successfully imported ${result.success} records from ${type.toUpperCase()} file!`, 'success');
            } else if (result.total > 0) {
                // All records failed to import
                statusText.textContent = `⚠️ Failed: 0 of ${result.total} records imported`;
                statusText.classList.add('text-red-600', 'font-bold');
                showAlert(`Import failed: ${result.message || 'No records were imported. Check your CSV headers (name, category, value, status) and data format.'}`, 'error');
            } else {
                statusText.textContent = `✅ File processed (no data rows found)`;
                statusText.classList.add('text-yellow-600', 'font-bold');
                showAlert('File processed but contained no data rows.', 'error');
            }
            
            // Reset form
            fileInput.value = '';
            setTimeout(() => {
                progressDiv.classList.add('hidden');
                progressBar.style.width = '0%';
                progressPercent.textContent = '0%';
                statusText.textContent = '';
                statusText.classList.remove('text-green-600', 'text-red-600', 'text-yellow-600', 'font-bold');
                loadRecentImports();
            }, result.success > 0 ? 3000 : 8000); // Show error message longer
        } else {
            statusText.textContent = `❌ Failed: ${result.error || result.message || 'Import error'}`;
            statusText.classList.add('text-red-600', 'font-bold');
            showAlert(result.error || result.message || 'Import failed', 'error');
            setTimeout(() => progressDiv.classList.add('hidden'), 5000);
        }
    } catch (error) {
        console.error('Import error:', error);
        progressBar.style.width = '100%';
        progressPercent.textContent = '100%';
        
        if (error.name === 'AbortError') {
            statusText.textContent = '⏱️ Import timed out - File may be too large or complex';
            showAlert('Import timed out after 15 minutes. Try splitting your file into smaller chunks.', 'error');
        } else {
            statusText.textContent = '❌ Error during import';
            showAlert('Network error: Unable to import data. Check your connection and try again.', 'error');
        }
        
        statusText.classList.add('text-red-600', 'font-bold');
        setTimeout(() => progressDiv.classList.add('hidden'), 5000);
    }
}

async function loadRecentImports() {
    try {
        const response = await fetch('http://localhost:8080/upload/history?page=1&limit=5');
        const data = await response.json();
        
        const container = document.getElementById('recent-imports');
        
        if (data.data && data.data.length > 0) {
            container.innerHTML = data.data.map((log, index) => `
                <div class="flex items-center justify-between py-4 px-4 rounded-xl hover:bg-gray-50 transition-colors" style="animation: fadeIn 0.3s ease-out ${index * 0.1}s both">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1.5 text-xs font-bold rounded-full shadow-sm ${log.import_type === 'csv' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-blue-100 text-blue-800 border border-blue-200'}">
                                ${log.import_type === 'csv' ? '📄' : '📊'} ${log.import_type.toUpperCase()}
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">${log.file_name || 'Import'}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="text-green-600 font-semibold">✔ ${log.success_count} imported</span>
                                    ${log.failure_count > 0 ? `<span class="text-red-600 ml-2">✖ ${log.failure_count} failed</span>` : ''}
                                    <span class="ml-2">• ${new Date(log.created_at).toLocaleString()}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12">
                    <svg class="h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 font-semibold">No import history yet</p>
                    <p class="text-gray-400 text-sm mt-2">Start importing CSV or JSON files to see your history</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading recent imports:', error);
        document.getElementById('recent-imports').innerHTML = `
            <p class="text-red-500 text-center py-4">Error loading import history</p>
        `;
    }
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
