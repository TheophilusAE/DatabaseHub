@extends('layouts.app')

@section('title', 'Multi-Table Export - DataBridge')

@section('content')
<div class="container mx-auto px-3 sm:px-4 py-6 sm:py-8 max-w-7xl">
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 mb-2">Multi-Table Export</h1>
        <p class="text-sm sm:text-base text-gray-600">Export data from any table or combine multiple tables</p>
    </div>

    <!-- Alert Container -->
    <div id="alert-container"></div>

    <!-- Export Tabs -->
    <div class="bg-white rounded-xl shadow-lg mb-8">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="flex -mb-px min-w-max sm:min-w-0">
                <button onclick="switchTab('single')" id="tab-single"
                    class="tab-btn border-b-2 border-blue-500 py-3 sm:py-4 px-4 sm:px-6 text-center text-sm sm:text-base font-semibold text-blue-600 whitespace-nowrap">
                    Single Table Export
                </button>
                <button onclick="switchTab('joined')" id="tab-joined"
                    class="tab-btn border-b-2 border-transparent py-3 sm:py-4 px-4 sm:px-6 text-center text-sm sm:text-base font-semibold text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    Combined Tables Export
                </button>
            </nav>
        </div>

        <!-- Single Table Export -->
        <div id="panel-single" class="p-4 sm:p-8">
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-5 sm:mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export from Single Table
            </h2>

            <form id="single-export-form" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Export Configuration *</label>
                    <select id="export-config-select" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Loading configurations...</option>
                    </select>
                </div>

                <div id="export-details" class="hidden bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <h3 class="font-semibold text-blue-800 mb-2">Export Details</h3>
                    <div class="text-sm text-gray-700 space-y-1">
                        <p><strong>Format:</strong> <span id="detail-format"></span></p>
                        <p><strong>Source:</strong> <span id="detail-source"></span></p>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Export
                </button>
            </form>
        </div>

        <!-- Combined Tables Export -->
        <div id="panel-joined" class="p-4 sm:p-8 hidden">
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-5 sm:mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Export Combined Data
            </h2>

            <form id="joined-export-form" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Table Join *</label>
                    <select id="join-select" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Loading joins...</option>
                    </select>
                </div>

                <div id="join-details" class="hidden bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                    <h3 class="font-semibold text-purple-800 mb-2">Join Details</h3>
                    <div class="text-sm text-gray-700 space-y-1">
                        <p><strong>Left Table:</strong> <span id="join-left-table"></span></p>
                        <p><strong>Right Table:</strong> <span id="join-right-table"></span></p>
                        <p><strong>Join Type:</strong> <span id="join-type"></span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                    <button type="button" onclick="exportJoinToFile()"
                        class="bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download as File
                    </button>
                    <button type="button" onclick="exportJoinToTable()"
                        class="bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/>
                        </svg>
                        Export to Table
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Formats Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-green-50 border-l-4 border-green-500 p-5 sm:p-6 rounded-lg">
            <div class="flex items-center mb-3">
                <svg class="w-8 h-8 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800">CSV Export</h3>
            </div>
            <p class="text-sm text-gray-600">Download data in CSV format for Excel and other spreadsheet applications</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-5 sm:p-6 rounded-lg">
            <div class="flex items-center mb-3">
                <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800">JSON Export</h3>
            </div>
            <p class="text-sm text-gray-600">Export data in JSON format for APIs and web applications</p>
        </div>

        <div class="bg-purple-50 border-l-4 border-purple-500 p-5 sm:p-6 rounded-lg">
            <div class="flex items-center mb-3">
                <svg class="w-8 h-8 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800">Table Export</h3>
            </div>
            <p class="text-sm text-gray-600">Export combined data directly to a target table in any database</p>
        </div>
    </div>
</div>

<script>
let exportConfigsData = [];
let joinsData = [];

document.addEventListener('DOMContentLoaded', function() {
    loadExportConfigs();
    loadJoins();
});

function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById(`tab-${tab}`).classList.remove('border-transparent', 'text-gray-500');
    document.getElementById(`tab-${tab}`).classList.add('border-blue-500', 'text-blue-600');

    // Update panels
    document.getElementById('panel-single').classList.add('hidden');
    document.getElementById('panel-joined').classList.add('hidden');
    document.getElementById(`panel-${tab}`).classList.remove('hidden');
}

async function loadExportConfigs() {
    try {
        const response = await apiRequest('/multi-export/configs');
        const data = await response.json();
        exportConfigsData = data.configs || [];

        const select = document.getElementById('export-config-select');
        if (exportConfigsData.length > 0) {
            select.innerHTML = '<option value="">Select a configuration...</option>' +
                exportConfigsData.map(c => `<option value="${c.name}">${c.name} - ${c.description || 'No description'}</option>`).join('');
        } else {
            select.innerHTML = '<option value="">No configurations available</option>';
        }

        // Handle selection
        select.addEventListener('change', function() {
            if (this.value) {
                const config = exportConfigsData.find(c => c.name === this.value);
                if (config) {
                    document.getElementById('detail-format').textContent = config.target_format.toUpperCase();
                    document.getElementById('detail-source').textContent = config.source_type === 'table' ? 'Single Table' : 'Joined Tables';
                    document.getElementById('export-details').classList.remove('hidden');
                }
            } else {
                document.getElementById('export-details').classList.add('hidden');
            }
        });

    } catch (error) {
        console.error('Failed to load export configs:', error);
    }
}

async function loadJoins() {
    try {
        const response = await apiRequest('/joins');
        const data = await response.json();
        joinsData = data.joins || [];

        const select = document.getElementById('join-select');
        if (joinsData.length > 0) {
            select.innerHTML = '<option value="">Select a join...</option>' +
                joinsData.map(j => `<option value="${j.name}">${j.name} - ${j.description || 'No description'}</option>`).join('');
        } else {
            select.innerHTML = '<option value="">No joins configured yet</option>';
        }

        // Handle selection
        select.addEventListener('change', function() {
            if (this.value) {
                const join = joinsData.find(j => j.name === this.value);
                if (join) {
                    const leftTable = join.left_table?.table_name || join.left_table_name || 'N/A';
                    const rightTable = join.right_table?.table_name || join.right_table_name || 'N/A';
                    document.getElementById('join-left-table').textContent = leftTable;
                    document.getElementById('join-right-table').textContent = rightTable;
                    document.getElementById('join-type').textContent = join.join_type;
                    document.getElementById('join-details').classList.remove('hidden');
                }
            } else {
                document.getElementById('join-details').classList.add('hidden');
            }
        });

    } catch (error) {
        console.error('Failed to load joins:', error);
    }
}

// Single table export
document.getElementById('single-export-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const configName = document.getElementById('export-config-select').value;
    
    if (!configName) {
        showAlert('Please select an export configuration', 'error');
        return;
    }

    try {
        window.open(`${window.API_BASE_URL}/multi-export/table?config_name=${encodeURIComponent(configName)}`, '_blank');
        showAlert('Export started! Check your downloads folder.', 'success');
    } catch (error) {
        showAlert('Export failed', 'error');
    }
});

// Export joined data to file
async function exportJoinToFile() {
    const joinName = document.getElementById('join-select').value;
    
    if (!joinName) {
        showAlert('Please select a table join', 'error');
        return;
    }

    const join = joinsData.find(j => j.name === joinName);
    if (!join) return;

    try {
        window.open(`${window.API_BASE_URL}/multi-export/join-file?join_name=${encodeURIComponent(joinName)}`, '_blank');
        showAlert('Export started! Check your downloads folder.', 'success');
    } catch (error) {
        showAlert('Export failed', 'error');
    }
}

// Export joined data to table
async function exportJoinToTable() {
    const joinName = document.getElementById('join-select').value;
    
    if (!joinName) {
        showAlert('Please select a table join', 'error');
        return;
    }

    if (!confirm('This will insert the combined data into the target table. Continue?')) {
        return;
    }

    try {
        const response = await apiRequest(`/multi-export/join-to-table?join_name=${encodeURIComponent(joinName)}`);
        const result = await response.json();
        
        showAlert(`Successfully exported ${result.total_records} records to ${result.target_table}`, 'success');
    } catch (error) {
        showAlert(error.message || 'Export to table failed', 'error');
    }
}
</script>
@endsection
