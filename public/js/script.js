/**
 * Online Supermarket - Main JavaScript File
 * Handles notifications, modals, and interactive features
 */

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove();">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Modal functions
function viewDetails(orderId) {
    fetch(`admin/get_order_details.php?id=${orderId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('details-content').innerHTML = html;
            document.getElementById('modal-details').style.display = 'block';
        })
        .catch(error => showNotification('Error loading details', 'error'));
}

function closeModal() {
    document.getElementById('modal-details').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('modal-details');
    if (modal && event.target === modal) {
        modal.style.display = 'none';
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Smooth scroll to top
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Add scroll-to-top button
document.addEventListener('DOMContentLoaded', function() {
    // Create scroll-to-top button
    const scrollBtn = document.createElement('button');
    scrollBtn.id = 'scroll-to-top';
    scrollBtn.innerHTML = '↑';
    scrollBtn.style.display = 'none';
    document.body.appendChild(scrollBtn);
    
    // Show button when scrolled
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    });
    
    scrollBtn.addEventListener('click', scrollToTop);
    
    // Quantity input validation
    const quantityInputs = document.querySelectorAll('input[type="number"][name="quantity"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            if (this.value > this.max) this.value = this.max;
        });
    });
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    for (let input of inputs) {
        if (!input.value.trim()) {
            showNotification(`Please fill in ${input.name}`, 'error');
            input.focus();
            return false;
        }
    }
    return true;
}

// Debounce function for search
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Search autocomplete (optional enhancement)
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('keyup', debounce(function() {
        // Could add autocomplete suggestions here
    }, 300));
}

// Cart update handler
function updateCartItem(productId, newQuantity) {
    if (newQuantity <= 0) {
        removeCartItem(productId);
        return;
    }
    
    // You could use AJAX here to update without page reload
    console.log(`Cart: Product ${productId} quantity changed to ${newQuantity}`);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    // Press '/' to focus search
    if (event.key === '/' && document.activeElement.tagName !== 'INPUT') {
        event.preventDefault();
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) searchInput.focus();
    }
    
    // Press 'Escape' to close modals
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Smooth animations on page load
window.addEventListener('load', function() {
    const cards = document.querySelectorAll('.product-card, .stat-card');
    cards.forEach((card, index) => {
        card.style.animation = `fadeInUp 0.6s ease forwards`;
        card.style.animationDelay = `${index * 0.1}s`;
    });
});

// DEBUG: Log all form submissions
document.addEventListener('submit', function(e) {
    console.log('FORM SUBMITTED:', e.target);
    console.log('Form method:', e.target.method);
    console.log('Form action:', e.target.action);
}, true);

