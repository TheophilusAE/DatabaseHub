@extends('layouts.app')

@section('title', 'Edit Data Record')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Alert Container -->
    <div id="alert-container" class="mb-4"></div>
    
    <div class="bg-gradient-to-r from-blue-800 to-green-700 rounded-2xl shadow-xl mb-6 p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm p-3 rounded-xl">
                    <img src="{{ asset('Logo/edit.svg') }}" alt="Company Logo" class="h-8 w-8">
                </div>
                <div>
                    <h2 class="text-3xl font-bold">
                        Edit Record #<span id="record-id">{{ $id }}</span>
                    </h2>
                    <p class="text-white opacity-90 mt-1">Update the details of this data record</p>
                </div>
            </div>
            <div>
                <a href="{{ route('data-records.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded-xl text-sm font-medium text-black hover:bg-opacity-30 transition-all transform hover:-translate-y-0.5 shadow-lg">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
        <div id="loading" class="p-12 text-center">
            <div class="flex flex-col items-center">
                <div class="relative">
                    <div class="h-16 w-16 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"></div>
                </div>
                <p class="text-gray-600 font-semibold text-lg mt-6">Loading record data...</p>
                <p class="text-gray-400 text-sm mt-2">Please wait while we fetch the details</p>
            </div>
        </div>
        
        <form id="edit-form" class="space-y-8 p-8 hidden">
            <div class="space-y-2">
                <label for="name" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Record Name <span class="text-red-500 ml-1">*</span>
                </label>
                <input type="text" id="name" required placeholder="Enter a descriptive name"
                       class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-base">
                <p class="text-xs text-gray-500 mt-1 ml-1">Choose a unique and descriptive name</p>
            </div>

            <div class="space-y-2">
                <label for="description" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    Description
                </label>
                <textarea id="description" rows="4" placeholder="Add a detailed description (optional)"
                          class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all text-base"></textarea>
                <p class="text-xs text-gray-500 mt-1 ml-1">Provide additional context about this record</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="category" class="block text-sm font-bold text-gray-700 flex items-center">
                        <svg class="h-4 w-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Category <span class="text-red-500 ml-1">*</span>
                    </label>
                    <select id="category" required
                            class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all text-base">
                        <option value="">📂 Select category</option>
                        <option value="electronics">📱 Electronics</option>
                        <option value="furniture">🪑 Furniture</option>
                        <option value="clothing">👕 Clothing</option>
                        <option value="books">📚 Books</option>
                        <option value="other">📦 Other</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="value" class="block text-sm font-bold text-gray-700 flex items-center">
                        <svg class="h-4 w-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Value <span class="text-red-500 ml-1">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 text-base font-semibold">$</span>
                        <input type="number" id="value" step="0.01" min="0" required placeholder="0.00"
                               class="block w-full pl-8 pr-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-yellow-500 focus:ring-4 focus:ring-yellow-100 transition-all text-base">
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label for="status" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Status <span class="text-red-500 ml-1">*</span>
                </label>
                <select id="status" required
                        class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-base">
                    <option value="active">✅ Active</option>
                    <option value="inactive">⭕ Inactive</option>
                    <option value="pending">⏳ Pending</option>
                </select>
            </div>

            <div class="space-y-2">
                <label for="metadata" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    Metadata (JSON)
                </label>
                <textarea id="metadata" rows="5" placeholder='{&#10;  "key": "value",&#10;  "example": "data"&#10;}'
                          class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all text-sm font-mono"></textarea>
                <p class="text-xs text-gray-500 mt-1 ml-1">Optional: Enter valid JSON data for additional attributes</p>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t-2 border-gray-100">
                <a href="{{ route('data-records.index') }}" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 rounded-xl shadow-sm text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all transform hover:-translate-y-0.5">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-8 py-3 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all transform hover:-translate-y-0.5 hover:shadow-xl">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const recordId = {{ $id }};

document.addEventListener('DOMContentLoaded', function() {
    loadRecord();
});

async function loadRecord() {
    try {
        const response = await fetch(`http://localhost:8080/data/${recordId}`);
        
        if (!response.ok) {
            throw new Error('Record not found');
        }
        
        const result = await response.json();
        const record = result.data;
        
        // Populate form
        document.getElementById('name').value = record.name;
        document.getElementById('description').value = record.description || '';
        document.getElementById('category').value = record.category;
        document.getElementById('value').value = record.value;
        document.getElementById('status').value = record.status;
        document.getElementById('metadata').value = record.metadata || '';
        
        // Show form, hide loading
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('edit-form').classList.remove('hidden');
    } catch (error) {
        console.error('Error loading record:', error);
        document.getElementById('loading').innerHTML = '<p class="text-red-500">Failed to load record</p>';
    }
}

document.getElementById('edit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('name').value,
        description: document.getElementById('description').value,
        category: document.getElementById('category').value,
        value: parseFloat(document.getElementById('value').value),
        status: document.getElementById('status').value,
        metadata: document.getElementById('metadata').value || ''
    };
    
    try {
        const response = await fetch(`http://localhost:8080/data/${recordId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        if (response.ok) {
            showAlert('Record updated successfully!', 'success');
            setTimeout(() => {
                window.location.href = '/data-records';
            }, 1500);
        } else {
            const error = await response.json();
            showAlert(error.message || 'Failed to update record', 'error');
        }
    } catch (error) {
        console.error('Error updating record:', error);
        showAlert('Error updating record', 'error');
    }
});

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
