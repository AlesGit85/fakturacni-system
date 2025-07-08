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
        // Nastavíme user_id do hidden inputu
        const userIdInput = document.querySelector('input[name="user_id"]');
        if (userIdInput) {
            userIdInput.value = userId;
        }

        // Zobrazíme modal
        const modalElement = document.getElementById('moveTenantModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Modal element not found');
        }
    } catch (error) {
        console.error('Chyba při načítání modalu pro přesunutí uživatele:', error);
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
    // Kontrola, zda existuje modal pro přesunutí uživatelů
    const moveTenantModal = document.getElementById('moveTenantModal');
    if (moveTenantModal) {
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
});

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
            const bsCollapse = new bootstrap.Collapse(collapse, { show: true });
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
    }
};

// Exportujeme funkce pro globální použití
window.loadUserForMove = loadUserForMove;
window.confirmUserDelete = confirmUserDelete;
window.TenantsAccordion = TenantsAccordion;