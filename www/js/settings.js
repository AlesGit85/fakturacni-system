/**
 * Fakturační systém - Nastavení
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace přepínání zobrazení DIČ
    initVatPayerToggle();
    
    // Inicializace color pickerů s možností zadání HEX kódu
    initColorPickers();
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

/**
 * Inicializace color pickerů s možností ručního zadání HEX kódu
 */
function initColorPickers() {
    // Najdeme všechny color pickery
    const colorPickers = document.querySelectorAll('input[type="color"]');
    
    // Pro každý color picker vytvoříme text pole pro zadání HEX kódu
    colorPickers.forEach(picker => {
        // Vytvoření textového pole pro zadání HEX kódu
        const hexInput = document.createElement('input');
        hexInput.type = 'text';
        hexInput.className = 'form-control form-control-sm ms-2';
        hexInput.placeholder = 'HEX kód (#cacaca)';
        hexInput.value = picker.value;
        hexInput.style.width = '110px';
        
        // Vložení textového pole vedle color pickeru
        const parentElement = picker.parentElement;
        parentElement.insertBefore(hexInput, picker.nextSibling);
        
        // Synchronizace barvy mezi color pickerem a textovým polem
        picker.addEventListener('input', function() {
            hexInput.value = picker.value;
        });
        
        hexInput.addEventListener('input', function() {
            // Validace HEX kódu a přidání # na začátek, pokud chybí
            let hexCode = hexInput.value;
            if (hexCode && !hexCode.startsWith('#')) {
                hexCode = '#' + hexCode;
                hexInput.value = hexCode;
            }
            
            // Kontrola, zda jde o platný HEX kód (musí být #RGB nebo #RRGGBB)
            const hexRegex = /^#([A-Fa-f0-9]{3}){1,2}$/;
            if (hexRegex.test(hexCode)) {
                picker.value = hexCode;
            }
        });
    });
}