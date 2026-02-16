@extends('layouts.app')

@section('title', 'Multi-Table Import - Data Import Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Multi-Table Import</h1>
        <p class="text-gray-600">Import data to any configured table across multiple databases</p>
    </div>

    <!-- Alert Container -->
    <div id="alert-container"></div>

    <!-- Import Form -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8 border-t-4 border-blue-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Import Data
        </h2>

        <form id="import-form" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Import Mapping *</label>
                <select id="mapping-select" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Loading mappings...</option>
                </select>
                <p class="mt-2 text-sm text-gray-500">Select which table and mapping to use for import</p>
            </div>

            <div id="mapping-details" class="hidden bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <h3 class="font-semibold text-blue-800 mb-2">Mapping Details</h3>
                <div class="text-sm text-gray-700 space-y-1">
                    <p><strong>Table:</strong> <span id="detail-table"></span></p>
                    <p><strong>Database:</strong> <span id="detail-database"></span></p>
                    <p><strong>Format:</strong> <span id="detail-format"></span></p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload File *</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>Upload a file</span>
                                <input id="file-upload" name="file-upload" type="file" class="sr-only" accept=".csv,.json">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">CSV or JSON files up to 500MB</p>
                    </div>
                </div>
                <div id="file-info" class="mt-2 text-sm text-gray-600 hidden"></div>
            </div>

            <div class="flex space-x-4">
                <button type="submit" id="import-btn"
                    class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Data
                </button>
            </div>
        </form>

        <!-- Progress Bar -->
        <div id="import-progress" class="hidden mt-6">
            <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                <div id="progress-bar" class="bg-gradient-to-r from-blue-500 to-green-500 h-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="progress-text" class="text-center mt-2 text-sm text-gray-600"></p>
        </div>
    </div>

    <!-- Recent Imports -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-green-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Import History
        </h2>
        <div id="history-list" class="space-y-3">
            <p class="text-gray-500 text-center py-4">No recent imports</p>
        </div>
    </div>
</div>

<script>
let mappingsData = [];
let tableConfigsData = [];

document.addEventListener('DOMContentLoaded', function() {
    loadMappings();
    loadHistory();

    // File input handling
    const fileInput = document.getElementById('file-upload');
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            document.getElementById('file-info').innerHTML = `
                <div class="flex items-center text-green-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Selected: ${file.name} (${(file.size / (1024*1024)).toFixed(2)} MB)
                </div>
            `;
            document.getElementById('file-info').classList.remove('hidden');
        }
    });

    // Mapping selection
    document.getElementById('mapping-select').addEventListener('change', function() {
        const mappingId = this.value;
        if (mappingId) {
            const mapping = mappingsData.find(m => m.id == mappingId);
            if (mapping && mapping.table_config) {
                document.getElementById('detail-table').textContent = mapping.table_config.table_name;
                document.getElementById('detail-database').textContent = mapping.table_config.database_name;
                document.getElementById('detail-format').textContent = mapping.source_format.toUpperCase();
                document.getElementById('mapping-details').classList.remove('hidden');
            }
        } else {
            document.getElementById('mapping-details').classList.add('hidden');
        }
    });

    // Form submission
    document.getElementById('import-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const mappingSelect = document.getElementById('mapping-select');
        const fileInput = document.getElementById('file-upload');
        
        if (!mappingSelect.value) {
            showAlert('Please select an import mapping', 'error');
            return;
        }

        if (!fileInput.files.length) {
            showAlert('Please select a file to import', 'error');
            return;
        }

        const mapping = mappingsData.find(m => m.id == mappingSelect.value);
        const formData = new FormData();
        formData.append('mapping_name', mapping.name);
        formData.append('file', fileInput.files[0]);

        // Show progress
        document.getElementById('import-progress').classList.remove('hidden');
        document.getElementById('progress-bar').style.width = '30%';
        document.getElementById('progress-text').textContent = 'Uploading and processing...';
        document.getElementById('import-btn').disabled = true;

        try {
            const response = await fetch(`${window.API_BASE_URL}/multi-import/table`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Import failed');

            const result = await response.json();
            
            document.getElementById('progress-bar').style.width = '100%';
            document.getElementById('progress-text').textContent = 'Import complete!';

            showAlert(`Import successful! ${result.success_records} records imported.`, 'success');
            
            // Reset form
            setTimeout(() => {
                this.reset();
                document.getElementById('import-progress').classList.add('hidden');
                document.getElementById('progress-bar').style.width = '0%';
                document.getElementById('file-info').classList.add('hidden');
                document.getElementById('mapping-details').classList.add('hidden');
                document.getElementById('import-btn').disabled = false;
                loadHistory();
            }, 2000);
        } catch (error) {
            document.getElementById('progress-bar').style.width = '0%';
            document.getElementById('progress-text').textContent = 'Import failed';
            showAlert(error.message || 'Import failed', 'error');
            document.getElementById('import-btn').disabled = false;
        }
    });
});

async function loadMappings() {
    try {
        const response = await apiRequest('/multi-import/mappings');
        const data = await response.json();
        mappingsData = data.mappings || [];

        const select = document.getElementById('mapping-select');
        if (mappingsData.length > 0) {
            select.innerHTML = '<option value="">Select a mapping...</option>' +
                mappingsData.map(m => `<option value="${m.id}">${m.name} - ${m.description || 'No description'}</option>`).join('');
        } else {
            select.innerHTML = '<option value="">No mappings configured yet</option>';
        }
    } catch (error) {
        console.error('Failed to load mappings:', error);
    }
}

async function loadHistory() {
    try {
        const response = await apiRequest('/upload/history?limit=5');
        const data = await response.json();

        const container = document.getElementById('history-list');
        if (data.logs && data.logs.length > 0) {
            container.innerHTML = data.logs.map(log => {
                const statusColor = log.status === 'completed' ? 'green' : log.status === 'failed' ? 'red' : 'yellow';
                return `
                    <div class="border-l-4 border-${statusColor}-500 bg-${statusColor}-50 p-4 rounded-r">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-gray-800">${log.file_name}</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    ${log.total_records || 0} records | 
                                    Status: <span class="font-semibold">${log.status}</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">${new Date(log.created_at).toLocaleString()}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                                ${log.import_type.toUpperCase()}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<p class="text-gray-500 text-center py-4">No recent imports</p>';
        }
    } catch (error) {
        console.error('Failed to load history:', error);
    }
}
</script>
@endsection
