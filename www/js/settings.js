/**
 * Fakturační systém - Nastavení
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace přepínání zobrazení DIČ
    initVatPayerToggle();
});

/**
 * Inicializace přepínání zobrazení pole DIČ podle stavu checkboxu
 */
function initVatPayerToggle() {
    const vatPayerCheckbox = document.getElementById('vat-payer-checkbox');
    const dicContainer = document.getElementById('dic-container');
    
    if (!vatPayerCheckbox || !dicContainer) {
        return; // Nic neděláme, pokud nejsme na správné stránce
    }
    
    // Funkce pro zobrazení/skrytí pole DIČ
    function toggleDicField() {
        if (vatPayerCheckbox.checked) {
            dicContainer.style.display = 'block';
        } else {
            dicContainer.style.display = 'none';
            document.getElementById('dic-field').value = '';
        }
    }
    
    // Přidání posluchače události na změnu checkboxu
    vatPayerCheckbox.addEventListener('change', toggleDicField);
    
    // Inicializace stavu při načtení stránky
    toggleDicField();
}