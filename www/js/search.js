/**
 * Fakturační systém - Vyhledávání
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace obecného vyhledávání
    initGeneralSearch();
});

/**
 * Inicializace obecného vyhledávání
 */
function initGeneralSearch() {
    // Obecná funkce pro live search ve všech input polích s class="search-input"
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const targetSelector = this.getAttribute('data-search-target') || '.data-table tbody tr';
            const rows = document.querySelectorAll(targetSelector);
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    });
}

/**
 * Funkce pro zvýraznění výsledků vyhledávání
 * @param {string} text Původní text
 * @param {string} searchTerm Hledaný výraz
 * @returns {string} Text se zvýrazněným hledaným výrazem
 */
function highlightSearchTerm(text, searchTerm) {
    if (!searchTerm) return text;
    
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}