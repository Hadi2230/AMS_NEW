/**
 * Aala Niroo AMS - Main JavaScript File
 */

// ===============================================
// Global Variables
// ===============================================

const APP = {
    name: 'سامانه مدیریت اعلا نیرو',
    version: '2.0.0',
    debug: false
};

// ===============================================
// Theme Management
// ===============================================

function toggleTheme() {
    const body = document.body;
    const icon = document.querySelector('.theme-switch i');
    
    if (body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        icon.classList.replace('fa-sun', 'fa-moon');
        document.cookie = "theme=light; path=/; max-age=31536000";
        localStorage.setItem('theme', 'light');
    } else {
        body.classList.add('dark-mode');
        icon.classList.replace('fa-moon', 'fa-sun');
        document.cookie = "theme=dark; path=/; max-age=31536000";
        localStorage.setItem('theme', 'dark');
    }
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 
                      document.cookie.match('(^|;)\\s*theme\\s*=\\s*([^;]+)')?.pop() || 'light';
    
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        const icon = document.querySelector('.theme-switch i');
        if (icon) {
            icon.classList.replace('fa-moon', 'fa-sun');
        }
    }
});

// ===============================================
// Clock Management
// ===============================================

function updateClock() {
    const clockElement = document.getElementById('clockTime');
    if (clockElement) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('fa-IR');
        clockElement.textContent = timeString;
    }
}

// Update clock every second
setInterval(updateClock, 1000);

// ===============================================
// Notification System
// ===============================================

function showNotification(message, type = 'info', duration = 5000) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification alert alert-${type} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after duration
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
    
    // Add close functionality
    const closeBtn = notification.querySelector('.btn-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            notification.remove();
        });
    }
}

// ===============================================
// AJAX Utilities
// ===============================================

const AJAX = {
    /**
     * Make a GET request
     */
    get: function(url, options = {}) {
        return this.request(url, 'GET', null, options);
    },
    
    /**
     * Make a POST request
     */
    post: function(url, data = null, options = {}) {
        return this.request(url, 'POST', data, options);
    },
    
    /**
     * Make a PUT request
     */
    put: function(url, data = null, options = {}) {
        return this.request(url, 'PUT', data, options);
    },
    
    /**
     * Make a DELETE request
     */
    delete: function(url, options = {}) {
        return this.request(url, 'DELETE', null, options);
    },
    
    /**
     * Make a generic request
     */
    request: function(url, method = 'GET', data = null, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            finalOptions.headers['X-CSRF-Token'] = csrfToken;
        }
        
        // Prepare request data
        if (data && finalOptions.headers['Content-Type'] === 'application/json') {
            finalOptions.body = JSON.stringify(data);
        } else if (data) {
            finalOptions.body = data;
        }
        
        finalOptions.method = method;
        
        return fetch(url, finalOptions)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                showNotification('خطا در ارتباط با سرور', 'danger');
                throw error;
            });
    }
};

// ===============================================
// Form Validation
// ===============================================

const Validator = {
    /**
     * Validate required fields
     */
    required: function(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    },
    
    /**
     * Validate email format
     */
    email: function(value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value);
    },
    
    /**
     * Validate phone number (Iranian format)
     */
    phone: function(value) {
        const phoneRegex = /^[0-9]{10,11}$/;
        return phoneRegex.test(value.replace(/[^0-9]/g, ''));
    },
    
    /**
     * Validate minimum length
     */
    minLength: function(value, min) {
        return value.toString().length >= min;
    },
    
    /**
     * Validate maximum length
     */
    maxLength: function(value, max) {
        return value.toString().length <= max;
    },
    
    /**
     * Validate form
     */
    validateForm: function(formElement, rules) {
        const errors = {};
        const formData = new FormData(formElement);
        
        for (const [field, fieldRules] of Object.entries(rules)) {
            const value = formData.get(field);
            
            for (const rule of fieldRules) {
                if (rule.type === 'required' && !this.required(value)) {
                    errors[field] = rule.message || `${field} الزامی است`;
                    break;
                } else if (rule.type === 'email' && !this.email(value)) {
                    errors[field] = rule.message || 'فرمت ایمیل نامعتبر است';
                    break;
                } else if (rule.type === 'phone' && !this.phone(value)) {
                    errors[field] = rule.message || 'فرمت شماره تلفن نامعتبر است';
                    break;
                } else if (rule.type === 'minLength' && !this.minLength(value, rule.value)) {
                    errors[field] = rule.message || `حداقل ${rule.value} کاراکتر الزامی است`;
                    break;
                } else if (rule.type === 'maxLength' && !this.maxLength(value, rule.value)) {
                    errors[field] = rule.message || `حداکثر ${rule.value} کاراکتر مجاز است`;
                    break;
                }
            }
        }
        
        return errors;
    }
};

// ===============================================
// DataTable Utilities
// ===============================================

const DataTable = {
    /**
     * Initialize a data table
     */
    init: function(tableId, options = {}) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const defaultOptions = {
            pageSize: 10,
            searchable: true,
            sortable: true,
            responsive: true
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        // Add search functionality
        if (finalOptions.searchable) {
            this.addSearch(table, finalOptions);
        }
        
        // Add pagination
        if (finalOptions.pageSize) {
            this.addPagination(table, finalOptions);
        }
        
        // Add sorting
        if (finalOptions.sortable) {
            this.addSorting(table);
        }
    },
    
    /**
     * Add search functionality
     */
    addSearch: function(table, options) {
        const searchContainer = document.createElement('div');
        searchContainer.className = 'mb-3';
        searchContainer.innerHTML = `
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" placeholder="جستجو..." id="search-${table.id}">
            </div>
        `;
        
        table.parentNode.insertBefore(searchContainer, table);
        
        const searchInput = document.getElementById(`search-${table.id}`);
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    },
    
    /**
     * Add pagination
     */
    addPagination: function(table, options) {
        const rows = table.querySelectorAll('tbody tr');
        const pageSize = options.pageSize;
        const totalPages = Math.ceil(rows.length / pageSize);
        
        // Hide all rows initially
        rows.forEach((row, index) => {
            row.style.display = index < pageSize ? '' : 'none';
        });
        
        // Create pagination controls
        if (totalPages > 1) {
            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'd-flex justify-content-between align-items-center mt-3';
            paginationContainer.innerHTML = `
                <div class="pagination-info">
                    نمایش <span id="start-${table.id}">1</span> تا <span id="end-${table.id}">${Math.min(pageSize, rows.length)}</span> از ${rows.length} مورد
                </div>
                <nav>
                    <ul class="pagination" id="pagination-${table.id}">
                        ${this.generatePaginationHTML(totalPages, 1)}
                    </ul>
                </nav>
            `;
            
            table.parentNode.appendChild(paginationContainer);
            
            // Add pagination event listeners
            this.addPaginationListeners(table, rows, pageSize, totalPages);
        }
    },
    
    /**
     * Generate pagination HTML
     */
    generatePaginationHTML: function(totalPages, currentPage) {
        let html = '';
        
        for (let i = 1; i <= totalPages; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        return html;
    },
    
    /**
     * Add pagination event listeners
     */
    addPaginationListeners: function(table, rows, pageSize, totalPages) {
        const pagination = document.getElementById(`pagination-${table.id}`);
        
        pagination.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (e.target.classList.contains('page-link')) {
                const page = parseInt(e.target.dataset.page);
                const start = (page - 1) * pageSize;
                const end = start + pageSize;
                
                // Hide all rows
                rows.forEach((row, index) => {
                    row.style.display = (index >= start && index < end) ? '' : 'none';
                });
                
                // Update pagination info
                document.getElementById(`start-${table.id}`).textContent = start + 1;
                document.getElementById(`end-${table.id}`).textContent = Math.min(end, rows.length);
                
                // Update active page
                pagination.querySelectorAll('.page-item').forEach(item => {
                    item.classList.remove('active');
                });
                e.target.parentElement.classList.add('active');
            }
        });
    },
    
    /**
     * Add sorting functionality
     */
    addSorting: function(table) {
        const headers = table.querySelectorAll('thead th');
        
        headers.forEach(header => {
            if (header.dataset.sortable !== 'false') {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    const column = Array.from(headers).indexOf(this);
                    const rows = Array.from(table.querySelectorAll('tbody tr'));
                    const isAscending = this.classList.contains('sort-asc');
                    
                    // Remove sort classes from all headers
                    headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                    
                    // Add sort class to current header
                    this.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
                    
                    // Sort rows
                    rows.sort((a, b) => {
                        const aValue = a.cells[column]?.textContent || '';
                        const bValue = b.cells[column]?.textContent || '';
                        
                        if (isAscending) {
                            return bValue.localeCompare(aValue, 'fa');
                        } else {
                            return aValue.localeCompare(bValue, 'fa');
                        }
                    });
                    
                    // Reorder rows in table
                    const tbody = table.querySelector('tbody');
                    rows.forEach(row => tbody.appendChild(row));
                });
            }
        });
    }
};

// ===============================================
// Modal Utilities
// ===============================================

const Modal = {
    /**
     * Show a modal
     */
    show: function(title, content, options = {}) {
        const modalId = 'dynamic-modal-' + Date.now();
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog ${options.size || 'modal-lg'}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        ${options.footer ? `
                            <div class="modal-footer">
                                ${options.footer}
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modal = document.getElementById(modalId);
        const bootstrapModal = new bootstrap.Modal(modal);
        
        bootstrapModal.show();
        
        // Clean up after modal is hidden
        modal.addEventListener('hidden.bs.modal', function() {
            modal.remove();
        });
        
        return bootstrapModal;
    },
    
    /**
     * Show confirmation dialog
     */
    confirm: function(message, callback, options = {}) {
        const title = options.title || 'تأیید';
        const confirmText = options.confirmText || 'تأیید';
        const cancelText = options.cancelText || 'انصراف';
        
        const footer = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${cancelText}</button>
            <button type="button" class="btn btn-danger" id="confirm-btn">${confirmText}</button>
        `;
        
        const modal = this.show(title, message, { footer });
        
        document.getElementById('confirm-btn').addEventListener('click', function() {
            modal.hide();
            if (callback) callback(true);
        });
        
        return modal;
    }
};

// ===============================================
// Utility Functions
// ===============================================

/**
 * Format number with Persian digits
 */
function formatNumber(number) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return number.toString().replace(/\d/g, x => persianDigits[x]);
}

/**
 * Format date to Persian format
 */
function formatDate(date) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        calendar: 'persian'
    };
    return new Intl.DateTimeFormat('fa-IR', options).format(new Date(date));
}

/**
 * Debounce function
 */
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

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ===============================================
// Event Listeners
// ===============================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Add loading states to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>در حال پردازش...';
            }
        });
    });
    
    // Add confirmation to delete buttons
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.dataset.confirm || 'آیا از حذف این مورد اطمینان دارید؟';
            Modal.confirm(message, (confirmed) => {
                if (confirmed) {
                    window.location.href = this.href;
                }
            });
        });
    });
});

// ===============================================
// Export to global scope
// ===============================================

window.APP = APP;
window.AJAX = AJAX;
window.Validator = Validator;
window.DataTable = DataTable;
window.Modal = Modal;
window.showNotification = showNotification;
window.toggleTheme = toggleTheme;
window.formatNumber = formatNumber;
window.formatDate = formatDate;