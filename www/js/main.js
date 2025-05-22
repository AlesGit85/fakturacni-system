/**
 * Fakturační systém - Hlavní JS soubor
 * 
 * Obsahuje obecné funkce používané napříč systémem
 */
document.addEventListener('DOMContentLoaded', function() {
    // Obecná inicializace pro celou aplikaci
    console.log('Fakturační systém - inicializován');
    
    // Inicializace tooltipů (pokud používáte Bootstrap tooltips)
    initTooltips();
    
    // Inicializace potvrzovacích dialogů
    initConfirmDialogs();
    
    // Automatické skrývání flash zpráv
    initAutoHideFlashes();
});

/**
 * Inicializace Bootstrap tooltipů
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Inicializace potvrzovacích dialogů
 */
function initConfirmDialogs() {
    // Automatické potvrzení pro odkazy s data-confirm atributem
    const confirmLinks = document.querySelectorAll('a[data-confirm], button[data-confirm]');
    
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Automatické skrývání flash zpráv po 5 sekundách
 */
function initAutoHideFlashes() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // Pouze pro success a info zprávy
        if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        }
    });
}

/**
 * Obecná funkce pro AJAX požadavky
 * @param {string} url URL pro požadavek
 * @param {Object} options Možnosti pro fetch
 * @returns {Promise} Promise s odpovědí
 */
function makeAjaxRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    return fetch(url, {...defaultOptions, ...options})
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            throw error;
        });
}