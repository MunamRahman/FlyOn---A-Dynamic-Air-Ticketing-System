// FlyOn Main JavaScript

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    initMobileMenu();
    
    // Form validation
    initFormValidation();
    
    // Smooth scroll
    initSmoothScroll();
    
    // Toast notifications
    initToastNotifications();
    
    // Airport autocomplete
    initAirportAutocomplete();
});

// Mobile menu functionality
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showError(input, 'This field is required');
            isValid = false;
        } else {
            clearError(input);
        }
        
        // Email validation
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                showError(input, 'Please enter a valid email');
                isValid = false;
            }
        }
        
        // Phone validation
        if (input.type === 'tel' && input.value) {
            const phoneRegex = /^[+]?[0-9]{10,15}$/;
            if (!phoneRegex.test(input.value.replace(/\s/g, ''))) {
                showError(input, 'Please enter a valid phone number');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

function showError(input, message) {
    clearError(input);
    
    input.classList.add('border-red-500');
    const error = document.createElement('p');
    error.className = 'text-red-500 text-sm mt-1';
    error.textContent = message;
    input.parentNode.appendChild(error);
}

function clearError(input) {
    input.classList.remove('border-red-500');
    const error = input.parentNode.querySelector('.text-red-500');
    if (error) {
        error.remove();
    }
}

// Smooth scroll
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Toast notifications
function initToastNotifications() {
    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast bg-${type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue'}-500 text-white`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-3"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };
}

// Loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// AJAX helper
function ajax(url, method = 'GET', data = null) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data ? JSON.stringify(data) : null
    })
    .then(response => response.json())
    .catch(error => {
        console.error('AJAX Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// Date formatting
function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

// Price formatting
function formatPrice(amount, currency = 'BDT') {
    if (currency === 'BDT') {
        return '৳ ' + new Intl.NumberFormat('en-BD').format(amount);
    }
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Debounce function
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

// Airport Autocomplete
function initAirportAutocomplete() {
    const fromInput = document.getElementById('from-input');
    const toInput = document.getElementById('to-input');
    const fromSuggestions = document.getElementById('from-suggestions');
    const toSuggestions = document.getElementById('to-suggestions');
    
    if (fromInput && fromSuggestions) {
        setupAutocomplete(fromInput, fromSuggestions);
    }
    
    if (toInput && toSuggestions) {
        setupAutocomplete(toInput, toSuggestions);
    }
}

function setupAutocomplete(input, suggestionsDiv) {
    let selectedIndex = -1;
    
    const searchAirports = debounce(async function(query) {
        if (query.length < 2) {
            suggestionsDiv.classList.add('hidden');
            return;
        }
        
        try {
            const response = await fetch(`api/search_airports.php?q=${encodeURIComponent(query)}`);
            const airports = await response.json();
            
            if (airports.length > 0) {
                displaySuggestions(airports, suggestionsDiv, input);
            } else {
                suggestionsDiv.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error fetching airports:', error);
        }
    }, 300);
    
    input.addEventListener('input', function() {
        searchAirports(this.value);
        selectedIndex = -1;
    });
    
    input.addEventListener('keydown', function(e) {
        const items = suggestionsDiv.querySelectorAll('.suggestion-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelection(items, selectedIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelection(items, selectedIndex);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            items[selectedIndex].click();
        } else if (e.key === 'Escape') {
            suggestionsDiv.classList.add('hidden');
        }
    });
    
    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.add('hidden');
        }
    });
}

function displaySuggestions(airports, suggestionsDiv, input) {
    suggestionsDiv.innerHTML = '';
    
    airports.forEach((airport, index) => {
        const item = document.createElement('div');
        item.className = 'suggestion-item px-4 py-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0';
        item.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-plane text-primary mr-3"></i>
                <div>
                    <div class="font-semibold text-gray-800">${airport.city} (${airport.code})</div>
                    <div class="text-sm text-gray-600">${airport.name}</div>
                    <div class="text-xs text-gray-500">${airport.country}</div>
                </div>
            </div>
        `;
        
        item.addEventListener('click', function() {
            input.value = `${airport.city} (${airport.code})`;
            input.dataset.airportId = airport.id;
            input.dataset.airportCode = airport.code;
            suggestionsDiv.classList.add('hidden');
        });
        
        suggestionsDiv.appendChild(item);
    });
    
    suggestionsDiv.classList.remove('hidden');
}

function updateSelection(items, selectedIndex) {
    items.forEach((item, index) => {
        if (index === selectedIndex) {
            item.classList.add('bg-blue-50');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('bg-blue-50');
        }
    });
}

// Export functions
window.FlyOn = {
    showLoading,
    hideLoading,
    showToast,
    ajax,
    formatDate,
    formatPrice,
    debounce
};
