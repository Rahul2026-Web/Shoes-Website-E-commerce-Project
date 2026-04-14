// Main JavaScript file for ShoesShop E-commerce

// Hamburger menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = '#e74c3c';
                showValidationError(this, 'Please enter a valid email address');
            } else {
                this.style.borderColor = '#ddd';
                hideValidationError(this);
            }
        });
    });
    
    // Password validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.style.borderColor = '#e74c3c';
                showValidationError(this, 'Passwords do not match');
            } else {
                this.style.borderColor = '#27ae60';
                hideValidationError(this);
            }
        });
        
        password.addEventListener('input', function() {
            if (this.value.length < 6) {
                this.style.borderColor = '#e74c3c';
                showValidationError(this, 'Password must be at least 6 characters');
            } else {
                this.style.borderColor = '#27ae60';
                hideValidationError(this);
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
});

// Add to cart function
function addToCart(productId) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart', 'error');
    });
}

// Add to cart with custom quantity
function addToCartWithQuantity(productId) {
    const quantity = document.getElementById('quantity').value;
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
            setTimeout(() => {
                window.location.href = 'cart.php';
            }, 1000);
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart', 'error');
    });
}

// Update cart count badge
function updateCartCount() {
    // This would typically fetch from the server
    // For now, we'll reload the page to update the count
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Show notification
function showNotification(message, type = 'success') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Validation error helper functions
function showValidationError(element, message) {
    hideValidationError(element);
    const error = document.createElement('span');
    error.className = 'validation-error';
    error.textContent = message;
    error.style.cssText = 'color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem; display: block;';
    element.parentNode.appendChild(error);
}

function hideValidationError(element) {
    const existingError = element.parentNode.querySelector('.validation-error');
    if (existingError) {
        existingError.remove();
    }
}

// Image preview for file inputs
const fileInputs = document.querySelectorAll('input[type="file"]');
fileInputs.forEach(input => {
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // You can add preview functionality here
                console.log('Image selected:', file.name);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Confirm before delete
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-delete')) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    }
});

// Quantity input validation
const quantityInputs = document.querySelectorAll('input[type="number"]');
quantityInputs.forEach(input => {
    input.addEventListener('change', function() {
        const min = parseInt(this.min) || 1;
        const max = parseInt(this.max) || Infinity;
        let value = parseInt(this.value) || min;
        
        if (value < min) value = min;
        if (value > max) value = max;
        
        this.value = value;
    });
});

// Product search with debounce
const searchInputs = document.querySelectorAll('.search-input');
searchInputs.forEach(input => {
    let debounceTimer;
    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            // Search functionality can be enhanced with AJAX
            console.log('Searching for:', this.value);
        }, 500);
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Loading state for forms
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn && !this.classList.contains('no-loading')) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
        }
    });
});

console.log('The Shoe Vault E-commerce initialized');
