@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Alert Container -->
    <div id="alert-container" class="mb-4"></div>
    
    <div class="bg-gradient-to-r from-blue-800 to-green-700 rounded-2xl shadow-xl mb-6 p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm p-3 rounded-xl">
                    <img src="{{ asset('Logo/document_upload.svg') }}" alt="Company Logo" class="h-8 w-8">
                </div>
                <div>
                    <h2 class="text-3xl font-bold">
                        Upload Document
                    </h2>
                    <p class="text-white opacity-90 mt-1">Add a new file to your document library</p>
                </div>
            </div>
            <div>
                <a href="{{ route('documents.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded-xl text-sm font-medium text-black hover:bg-opacity-30 transition-all transform hover:-translate-y-0.5 shadow-lg">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
        <form id="upload-form" class="space-y-8 p-8">
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Select File <span class="text-red-500 ml-1">*</span>
                </label>
                <div id="drop-zone" class="flex justify-center px-8 pt-10 pb-10 border-3 border-gray-300 border-dashed rounded-2xl hover:border-blue-500 hover:bg-gradient-to-br hover:from-blue-50 hover:to-green-50 transition-all duration-300 cursor-pointer group">
                    <div class="space-y-3 text-center">
                            <div class="mx-auto h-20 w-20 rounded-full bg-gradient-to-br from-blue-100 to-green-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="h-10 w-10 text-blue-600" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div class="flex justify-center text-base text-gray-600">
                            <label for="file-upload" class="relative cursor-pointer rounded-md font-bold text-blue-600 hover:text-blue-700 focus-within:outline-none transition-colors">
                                <span class="text-lg">Click to upload</span>
                                <input id="file-upload" name="file" type="file" class="sr-only" required onchange="handleFileSelect(event)">
                            </label>
                            <p class="pl-2 text-lg">or drag and drop</p>
                        </div>
                        <p class="text-sm text-gray-500 font-semibold">📄 Any file type • Maximum 10GB</p>
                        <div id="selected-file" class="mt-4"></div>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label for="category" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Category <span class="text-red-500 ml-1">*</span>
                </label>
                <select id="category" required
                        class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all text-base">
                    <option value="">📂 Select a category</option>
                </select>
                <div id="admin-category-controls" class="hidden mt-2">
                    <div class="flex gap-2">
                        <input type="text" id="new-category-name" placeholder="Only Admin Can Create Categories"
                               class="flex-1 px-4 py-2 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-sm">
                        <button type="button" onclick="createCategory()"
                                class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-700 to-green-600 hover:from-blue-800 hover:to-green-700 transition-all">
                            Add Category
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label for="document-type" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h8a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                    Document Type <span class="text-red-500 ml-1">*</span>
                </label>
                <select id="document-type" required
                        class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all text-base">
                    <option value="">🗂️ Select a document type</option>
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

            <div class="space-y-2">
                <label for="description" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    Description
                </label>
                <textarea id="description" rows="4" placeholder="Add a detailed description for this document..."
                          class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all text-base"></textarea>
                <p class="text-xs text-gray-500 mt-1 ml-1">Optional: Help others understand what this document contains</p>
            </div>

            <div class="space-y-2">
                <label for="uploaded-by" class="block text-sm font-bold text-gray-700 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Uploaded By
                </label>
                <input type="text" id="uploaded-by" readonly
                       class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm bg-gray-50 text-gray-700 text-base cursor-not-allowed">
                <p class="text-xs text-gray-500 mt-1 ml-1">Automatically filled with your account name</p>
            </div>

            <!-- Progress Bar -->
            <div id="upload-progress" class="hidden space-y-3">
                <div class="relative">
                    <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div id="progress-bar" class="h-full rounded-full transition-all duration-500 bg-gradient-to-r from-blue-700 to-green-600 relative" style="width: 0%">
                            <div class="absolute inset-0 bg-white opacity-30 animate-progressFlow"></div>
                        </div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="progress-percent" class="text-xs font-bold text-gray-700">0%</span>
                    </div>
                </div>
                <p id="progress-text" class="text-sm text-gray-600 text-center font-semibold"></p>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t-2 border-gray-100">
                <a href="{{ route('documents.index') }}" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 rounded-xl shadow-sm text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all transform hover:-translate-y-0.5">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="inline-flex items-center px-8 py-3 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-blue-700 to-green-600 hover:from-blue-800 hover:to-green-700 transition-all transform hover:-translate-y-0.5 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Upload Document
                </button>
            </div>
        </form>
    </div>

    <!-- Upload Tips -->
    <div class="mt-6 bg-gradient-to-r from-blue-50 to-green-50 border-l-4 border-blue-500 rounded-r-2xl p-6 shadow-lg">
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
                    💡 Upload Tips & Guidelines
                </h3>
                <div class="mt-3 text-sm text-gray-700">
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">✓</span>
                            <span><strong>All file types supported:</strong> PDF, images, videos, documents, archives, and more</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">✓</span>
                            <span><strong>Maximum file size:</strong> 10GB per upload</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">✓</span>
                            <span><strong>Secure storage:</strong> Files are encrypted and safely stored on the server</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-600 font-bold mr-2">✓</span>
                            <span><strong>Easy retrieval:</strong> Download your files anytime from the documents list</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const userRole = '{{ session("user")["role"] ?? "user" }}';
const userName = '{{ session("user")["name"] ?? "Anonymous" }}';
const isAdmin = userRole === 'admin';
const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024 * 1024;
const MAX_FILE_SIZE_LABEL = '10GB';

document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    // Auto-fill uploaded by field with current user's name
    document.getElementById('uploaded-by').value = userName;
    if (isAdmin) {
        document.getElementById('admin-category-controls').classList.remove('hidden');
    }
});

async function loadCategories(selectedValue = '') {
    try {
        const response = await fetch('http://localhost:8080/document-categories');
        const result = await response.json();
        const categories = result.data || [];

        const categorySelect = document.getElementById('category');
        categorySelect.innerHTML = '<option value="">📂 Select a category</option>';

        categories.forEach((category) => {
            const option = document.createElement('option');
            option.value = category.name;
            option.textContent = `📁 ${category.name}`;
            categorySelect.appendChild(option);
        });

        if (selectedValue) {
            categorySelect.value = selectedValue;
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        showAlert('Unable to load categories', 'error');
    }
}

async function createCategory() {
    const input = document.getElementById('new-category-name');
    const name = input.value.trim();

    if (!name) {
        showAlert('Enter a category name first', 'error');
        return;
    }

    try {
        const response = await fetch('http://localhost:8080/document-categories', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-User-Role': userRole,
            },
            body: JSON.stringify({ name }),
        });

        const result = await response.json();
        if (!response.ok) {
            showAlert(result.error || 'Failed to create category', 'error');
            return;
        }

        input.value = '';
        showAlert('Category created successfully', 'success');
        await loadCategories(result.category?.name || name);
    } catch (error) {
        console.error('Error creating category:', error);
        showAlert('Unable to create category', 'error');
    }
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        if (!isFileSizeValid(file)) {
            clearFileSelection();
            return;
        }

        const fileSize = formatFileSize(file.size);
        const fileType = file.type || 'Unknown type';
        const icon = getFileTypeIcon(file.name);
        const inferredDocumentType = inferDocumentType(file.name);
        const documentTypeSelect = document.getElementById('document-type');
        if (documentTypeSelect && inferredDocumentType) {
            const match = Array.from(documentTypeSelect.options).find(o => o.value === inferredDocumentType);
            documentTypeSelect.value = match ? inferredDocumentType : 'other';
        }
        
        document.getElementById('selected-file').innerHTML = `
            <div class="inline-flex items-center space-x-3 px-6 py-4 bg-white rounded-xl shadow-md border-2 border-blue-200 animate-slideIn">
                <div class="text-3xl">${icon}</div>
                <div class="text-left">
                    <p class="text-sm font-bold text-gray-900 truncate max-w-xs">${file.name}</p>
                    <p class="text-xs text-gray-500 mt-1">${fileSize} • ${fileType}</p>
                </div>
                <button type="button" onclick="clearFileSelection()" class="ml-4 text-red-500 hover:text-red-700 transition-colors">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        `;
    }
}

function inferDocumentType(filename) {
    if (!filename || !filename.includes('.')) return 'other';
    return filename.split('.').pop().toLowerCase();
}

function getFileTypeIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        pdf: '📄',
        doc: '📃', docx: '📃',
        xls: '📊', xlsx: '📊',
        ppt: '📊', pptx: '📊',
        jpg: '🖼️', jpeg: '🖼️', png: '🖼️', gif: '🖼️',
        mp4: '🎥', avi: '🎥', mov: '🎥',
        mp3: '🎵', wav: '🎵',
        zip: '🗄️', rar: '🗄️',
        txt: '📝',
    };
    return icons[ext] || '📁';
}

function clearFileSelection() {
    document.getElementById('file-upload').value = '';
    document.getElementById('selected-file').innerHTML = '';
}

function isFileSizeValid(file) {
    if (file.size <= MAX_FILE_SIZE_BYTES) {
        return true;
    }

    showAlert(`File "${file.name}" is ${formatFileSize(file.size)}. Maximum allowed size is ${MAX_FILE_SIZE_LABEL}.`, 'error');
    return false;
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
}

document.getElementById('upload-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('file-upload');
    const selectedFile = fileInput.files[0];
    const category = document.getElementById('category').value;
    const documentType = document.getElementById('document-type').value;
    const description = document.getElementById('description').value;
    const uploadedBy = document.getElementById('uploaded-by').value || userName || 'anonymous';
    
    if (!selectedFile) {
        showAlert('Please select a file to upload', 'error');
        return;
    }

    if (!isFileSizeValid(selectedFile)) {
        clearFileSelection();
        return;
    }

    if (!category) {
        showAlert('Please select a category', 'error');
        return;
    }

    if (!documentType) {
        showAlert('Please select a document type', 'error');
        return;
    }
    
    // Show progress
    const progressDiv = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressPercent = document.getElementById('progress-percent');
    const submitBtn = document.getElementById('submit-btn');
    
    progressDiv.classList.remove('hidden');
    submitBtn.disabled = true;
    progressBar.style.width = '0%';
    progressPercent.textContent = '0%';
    progressText.textContent = 'Preparing upload...';
    
    // Create form data
    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('category', category);
    formData.append('document_type', documentType);
    formData.append('description', description);
    formData.append('uploaded_by', uploadedBy);
    
    try {
        // Simulate progress stages
        progressBar.style.width = '20%';
        progressPercent.textContent = '20%';
        progressText.textContent = 'Uploading file to server...';
        
        await new Promise(resolve => setTimeout(resolve, 200));
        
        progressBar.style.width = '40%';
        progressPercent.textContent = '40%';
        
        const response = await fetch('http://localhost:8080/documents', {
            method: 'POST',
            body: formData
        });
        
        progressBar.style.width = '80%';
        progressPercent.textContent = '80%';
        progressText.textContent = 'Processing file...';
        
        const result = await response.json();
        
        progressBar.style.width = '100%';
        progressPercent.textContent = '100%';
        
        if (response.ok) {
            progressText.textContent = '✅ Upload complete!';
            progressText.classList.add('text-green-600', 'font-bold');
            showAlert('Document uploaded successfully! Redirecting...', 'success');
            
            setTimeout(() => {
                window.location.href = '/documents';
            }, 1500);
        } else {
            progressText.textContent = '❌ Upload failed';
            progressText.classList.add('text-red-600', 'font-bold');
            showAlert(result.error || 'Failed to upload document', 'error');
            submitBtn.disabled = false;
            setTimeout(() => progressDiv.classList.add('hidden'), 3000);
        }
    } catch (error) {
        console.error('Upload error:', error);
        progressBar.style.width = '100%';
        progressPercent.textContent = '100%';
        progressText.textContent = '❌ Error during upload';
        progressText.classList.add('text-red-600', 'font-bold');
        showAlert('Network error: Unable to upload document', 'error');
        submitBtn.disabled = false;
        setTimeout(() => progressDiv.classList.add('hidden'), 3000);
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

// Drag and drop support
const dropZone = document.getElementById('drop-zone');
const fileUploadInput = document.getElementById('file-upload');

dropZone.addEventListener('click', (e) => {
    if (e.target.closest('button') || e.target.closest('label[for="file-upload"]')) {
        return;
    }
    fileUploadInput.click();
});

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-gradient-to-br', 'from-blue-50', 'to-purple-50', 'scale-105');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-500', 'scale-105');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'scale-105');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        if (!isFileSizeValid(files[0])) {
            clearFileSelection();
            return;
        }

        document.getElementById('file-upload').files = files;
        handleFileSelect({ target: { files: files } });
    }
});
</script>
@endsection
