/**
 * Fakturační systém - Funkce pro tabulky
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace dropdown menu v tabulkách
    initTableDropdowns();
    
    // Inicializace vyhledávání v tabulkách
    initTableSearch();
});

/**
 * Inicializace dropdown menu v tabulkách
 */
function initTableDropdowns() {
    const dropdownToggles = document.querySelectorAll('.data-table .dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const row = this.closest('tr');
            const tableBody = this.closest('tbody');
            const allRows = Array.from(tableBody.querySelectorAll('tr'));
            const rowIndex = allRows.indexOf(row);
            const totalRows = allRows.length;
            
            // Pokud je to jeden z posledních dvou řádků, přidáme CSS třídu
            if (rowIndex >= totalRows - 2) {
                row.classList.add('show-dropdown-up');
            } else {
                row.classList.remove('show-dropdown-up');
            }
        });
        
        // Při zavření dropdown odebereme třídu
        toggle.addEventListener('hidden.bs.dropdown', function() {
            const row = this.closest('tr');
            row.classList.remove('show-dropdown-up');
        });
    });
    
    // Odebereme třídu při kliknutí mimo
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('tr.show-dropdown-up').forEach(row => {
                row.classList.remove('show-dropdown-up');
            });
        }
    });
}

/**
 * Inicializace vyhledávání v tabulkách
 */
function initTableSearch() {
    // Vyhledávání v tabulce klientů
    const clientSearch = document.getElementById('clientSearch');
    if (clientSearch) {
        clientSearch.addEventListener('input', function() {
            searchInTable(this.value, '.data-table tbody tr');
        });
    }
    
    // Vyhledávání v tabulce faktur
    const invoiceSearch = document.getElementById('invoiceSearch');
    if (invoiceSearch) {
        invoiceSearch.addEventListener('input', function() {
            searchInTable(this.value, '.data-table tbody tr');
        });
    }
}

/**
 * Funkce pro vyhledávání v tabulce
 * @param {string} searchText Hledaný text
 * @param {string} rowSelector Selektor pro řádky tabulky
 */
function searchInTable(searchText, rowSelector) {
    const searchTextLower = searchText.toLowerCase();
    const rows = document.querySelectorAll(rowSelector);
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTextLower) ? '' : 'none';
    });
}