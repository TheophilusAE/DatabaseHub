import './bootstrap';

// API Configuration
window.API_BASE_URL = 'http://localhost:8080';

// Global alert function
window.showAlert = function(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    let alertClass;
    switch(type) {
        case 'success':
            alertClass = 'bg-green-100 text-green-800 border-green-400';
            break;
        case 'error':
            alertClass = 'bg-red-100 text-red-800 border-red-400';
            break;
        case 'warning':
            alertClass = 'bg-yellow-100 text-yellow-800 border-yellow-400';
            break;
        case 'info':
        default:
            alertClass = 'bg-blue-100 text-blue-800 border-blue-400';
    }
    
    const alertHTML = `
        <div class="${alertClass} px-4 py-3 rounded relative mb-4 border-l-4 flex items-center justify-between" role="alert">
            <span class="block sm:inline">${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHTML;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
};

// Global API helper
window.apiRequest = async function(endpoint, options = {}) {
    const url = `${window.API_BASE_URL}${endpoint}`;
    
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                ...options.headers,
            }
        });
        
        return response;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
};

// Check API health on page load
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch(`${window.API_BASE_URL}/health`);
        const data = await response.json();
        
        if (data.status !== 'ok') {
            console.warn('Backend API is not responding correctly');
        }
    } catch (error) {
        console.error('Backend API is not available:', error);
    }
});

