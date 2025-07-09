/**
 * Super Admin Users Management - JavaScript
 * Funkcionalita pro správu uživatelů super adminem
 */

/**
 * Načte modal pro přesunutí uživatele do jiného tenanta
 * @param {number} userId - ID uživatele k přesunutí
 */
function loadUserForMove(userId) {
    try {
        console.log('🔄 Loading move modal for user ID:', userId);
        
        // Ověřme, že máme platné ID
        if (!userId || userId <= 0) {
            console.error('❌ Invalid user ID:', userId);
            alert('Chyba: Neplatné ID uživatele');
            return;
        }

        // Nastavíme user_id do hidden inputu
        const userIdInput = document.querySelector('input[name="user_id"]');
        if (userIdInput) {
            userIdInput.value = userId;
            console.log('✅ User ID set to hidden input:', userId);
        } else {
            console.error('❌ Hidden input user_id not found');
            alert('Chyba: Formulář nebyl správně načten');
            return;
        }

        // Zobrazíme modal
        const modalElement = document.getElementById('moveTenantModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('✅ Modal displayed');
        } else {
            console.error('❌ Modal element not found');
            alert('Chyba: Modal dialog nebyl nalezen');
        }
    } catch (error) {
        console.error('❌ Error loading move modal:', error);
        alert('Nastala chyba při otevírání dialogu. Zkuste to prosím znovu.');
    }
}

/**
 * Potvrzovací dialog pro smazání uživatele
 * @param {string} username - Jméno uživatele
 * @param {string} deleteUrl - URL pro smazání
 * @returns {boolean}
 */
function confirmUserDelete(username, deleteUrl) {
    const confirmed = confirm(`Opravdu chcete smazat uživatele ${username}?\n\nTato akce je nevratná!`);

    if (confirmed) {
        window.location.href = deleteUrl;
    }

    return false; // Zabránit výchozímu chování linku
}

/**
 * Inicializace po načtení DOM
 */
document.addEventListener('DOMContentLoaded', function () {
    console.log('Super Admin Users Management JS loaded');
    
    // Kontrola, zda existuje modal pro přesunutí uživatelů
    const moveTenantModal = document.getElementById('moveTenantModal');
    if (moveTenantModal) {
        console.log('Move tenant modal found');
        
        // Vyčištění formuláře při zavření modalu
        moveTenantModal.addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
            }
        });
    }

    // Auto-focus na vyhledávací pole
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput && searchInput.value === '') {
        searchInput.focus();
    }

    // Inicializace tooltipů
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Animace statistických karet
    animateStatsCards();
    
    // Inicializace accordion funkcionalita
    initAccordionControls();
});

/**
 * Animace statistických karet při načtení
 */
function animateStatsCards() {
    const statCards = document.querySelectorAll('.stats-cards .stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.4s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
}

/**
 * Inicializace ovládání accordion
 */
function initAccordionControls() {
    // Přidání kontrolních tlačítek pro accordion (pokud by bylo potřeba)
    const accordionContainer = document.querySelector('.tenants-accordion');
    if (accordionContainer) {
        console.log('Tenants accordion found');
        
        // Zde můžeme přidat další funkcionalitu pro accordion
        // například ctrl+click pro otevření všech sekcí najednou
        
        const accordionButtons = accordionContainer.querySelectorAll('.accordion-button');
        accordionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Ctrl+click pro otevření/zavření všech
                if (e.ctrlKey) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const allCollapsed = Array.from(accordionButtons).every(btn => btn.classList.contains('collapsed'));
                    
                    if (allCollapsed) {
                        TenantsAccordion.expandAll();
                    } else {
                        TenantsAccordion.collapseAll();
                    }
                }
            });
        });
    }
}

/**
 * Helpers pro práci s accordion
 */
const TenantsAccordion = {
    /**
     * Rozbalí všechny tenanty
     */
    expandAll: function () {
        const collapseElements = document.querySelectorAll('.tenants-accordion .accordion-collapse');
        collapseElements.forEach(collapse => {
            if (!collapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(collapse, { show: true });
            }
        });
    },

    /**
     * Sbalí všechny tenanty
     */
    collapseAll: function () {
        const collapseElements = document.querySelectorAll('.tenants-accordion .accordion-collapse.show');
        collapseElements.forEach(collapse => {
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.hide();
            }
        });
    },

    /**
     * Přepne stav všech tenantů
     */
    toggleAll: function() {
        const collapseElements = document.querySelectorAll('.tenants-accordion .accordion-collapse');
        const expandedCount = document.querySelectorAll('.tenants-accordion .accordion-collapse.show').length;
        
        if (expandedCount > collapseElements.length / 2) {
            this.collapseAll();
        } else {
            this.expandAll();
        }
    }
};

/**
 * Vylepšená vyhledávací funkcionalita
 */
const SuperAdminSearch = {
    /**
     * Zvýrazní vyhledávané termíny v tabulce
     */
    highlightSearchTerms: function(searchTerm) {
        if (!searchTerm) return;
        
        const tableRows = document.querySelectorAll('.data-table tbody tr');
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                const text = cell.textContent;
                if (text.toLowerCase().includes(searchTerm.toLowerCase())) {
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    cell.innerHTML = cell.innerHTML.replace(regex, '<mark>$1</mark>');
                }
            });
        });
    },

    /**
     * Odstraní zvýraznění
     */
    removeHighlight: function() {
        const marks = document.querySelectorAll('.data-table mark');
        marks.forEach(mark => {
            mark.outerHTML = mark.innerHTML;
        });
    }
};

/**
 * Utility funkce pro práci s uživateli
 */
const UserUtils = {
    /**
     * Zkopíruje email uživatele do schránky
     */
    copyEmailToClipboard: function(email) {
        navigator.clipboard.writeText(email).then(() => {
            // Zobrazit krátkou notifikaci
            const toast = this.showToast('Email zkopírován do schránky', 'success');
        }).catch(err => {
            console.error('Nepodařilo se zkopírovat email:', err);
        });
    },

    /**
     * Zobrazí toast notifikaci
     */
    showToast: function(message, type = 'info') {
        // Vytvoří dočasnou toast notifikaci
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed`;
        toast.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
            min-width: 250px;
        `;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Fade in
        setTimeout(() => toast.style.opacity = '1', 10);
        
        // Fade out and remove
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
        
        return toast;
    }
};

// Exportujeme funkce pro globální použití
window.loadUserForMove = loadUserForMove;
window.confirmUserDelete = confirmUserDelete;
window.TenantsAccordion = TenantsAccordion;
window.SuperAdminSearch = SuperAdminSearch;
window.UserUtils = UserUtils;