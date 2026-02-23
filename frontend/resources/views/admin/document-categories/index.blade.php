@extends('layouts.app')

@section('title', 'Document Categories')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <div id="alert-container"></div>

    <div class="bg-gradient-to-r from-blue-800 to-green-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm p-3 rounded-xl">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Document Categories</h1>
                    <p class="mt-1 text-white opacity-90">Create and manage categories used by all users</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-900">Create New Category</h2>
        <div class="flex gap-3">
            <input type="text" id="new-category-name" placeholder="e.g. Class A"
                   class="flex-1 px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-base">
            <button type="button" onclick="createCategory()"
                    class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-700 to-green-600 hover:from-blue-800 hover:to-green-700 transition-all">
                Add Category
            </button>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Existing Categories</h2>
            <p class="text-sm text-gray-500 mt-1">Renaming updates existing documents. Deleting moves documents to Other.</p>
        </div>

        <div id="categories-list" class="divide-y divide-gray-100">
            <div class="p-6 text-gray-500">Loading categories...</div>
        </div>
    </div>
</div>

<script>
const userRole = '{{ session("user")["role"] ?? "user" }}';

document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});

async function loadCategories() {
    const list = document.getElementById('categories-list');
    list.innerHTML = '<div class="p-6 text-gray-500">Loading categories...</div>';

    try {
        const response = await fetch('http://localhost:8080/document-categories');
        const result = await response.json();
        const categories = result.data || [];

        if (categories.length === 0) {
            list.innerHTML = '<div class="p-6 text-gray-500">No categories found.</div>';
            return;
        }

        list.innerHTML = categories.map((category) => `
            <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex-1">
                    <div class="text-sm font-bold text-gray-900">${escapeHtml(category.name)}</div>
                    <div class="text-xs text-gray-500 mt-1">ID: ${category.id}</div>
                </div>
                <div class="flex gap-2">
                    <input type="text" id="rename-${category.id}" value="${escapeHtml(category.name)}"
                           class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
                    <button type="button" onclick="renameCategory(${category.id})"
                            class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition-all">
                        Rename
                    </button>
                    <button type="button" onclick="deleteCategory(${category.id}, '${escapeJs(category.name)}')"
                            class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
                        Delete
                    </button>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading categories:', error);
        list.innerHTML = '<div class="p-6 text-red-600">Unable to load categories.</div>';
    }
}

async function createCategory() {
    const input = document.getElementById('new-category-name');
    const name = input.value.trim();

    if (!name) {
        showAlert('Category name is required', 'error');
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
            body: JSON.stringify({ name })
        });

        const result = await response.json();
        if (!response.ok) {
            showAlert(result.error || 'Failed to create category', 'error');
            return;
        }

        input.value = '';
        showAlert('Category created successfully', 'success');
        loadCategories();
    } catch (error) {
        console.error('Error creating category:', error);
        showAlert('Unable to create category', 'error');
    }
}

async function renameCategory(categoryId) {
    const input = document.getElementById(`rename-${categoryId}`);
    const name = input.value.trim();

    if (!name) {
        showAlert('New category name is required', 'error');
        return;
    }

    try {
        const response = await fetch(`http://localhost:8080/document-categories/${categoryId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-User-Role': userRole,
            },
            body: JSON.stringify({ name })
        });

        const result = await response.json();
        if (!response.ok) {
            showAlert(result.error || 'Failed to rename category', 'error');
            return;
        }

        showAlert('Category renamed successfully', 'success');
        loadCategories();
    } catch (error) {
        console.error('Error renaming category:', error);
        showAlert('Unable to rename category', 'error');
    }
}

async function deleteCategory(categoryId, categoryName) {
    if (!confirm(`Delete category "${categoryName}"? Documents in this category will be moved to "Other".`)) {
        return;
    }

    try {
        const response = await fetch(`http://localhost:8080/document-categories/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-User-Role': userRole,
            }
        });

        const result = await response.json();
        if (!response.ok) {
            showAlert(result.error || 'Failed to delete category', 'error');
            return;
        }

        showAlert(`Category deleted. ${result.reassigned_documents || 0} documents moved to Other.`, 'success');
        loadCategories();
    } catch (error) {
        console.error('Error deleting category:', error);
        showAlert('Unable to delete category', 'error');
    }
}

function escapeHtml(text) {
    if (typeof text !== 'string') return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeJs(text) {
    if (typeof text !== 'string') return '';
    return text.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
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
            <div class="flex-shrink-0">${icon}</div>
            <div class="flex-1 font-semibold">${message}</div>
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
    }, 4000);
}
</script>
@endsection
