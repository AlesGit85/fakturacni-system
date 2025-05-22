/**
 * Fakturační systém - Nastavení
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace přepínání zobrazení DIČ
    initVatPayerToggle();
    
    // Inicializace color pickerů s možností zadání HEX kódu
    initColorPickers();
    
    // Inicializace náhledu barev
    initColorPreviews();
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
            const dicField = document.getElementById('dic-field');
            if (dicField) {
                dicField.value = '';
            }
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
            updateColorPreview(picker);
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
                updateColorPreview(picker);
            }
        });
        
        // Inicializace náhledu při načtení
        updateColorPreview(picker);
    });
}

/**
 * Inicializace náhledů barev
 */
function initColorPreviews() {
    const colorPickers = document.querySelectorAll('input[type="color"]');
    
    colorPickers.forEach(picker => {
        updateColorPreview(picker);
    });
}

/**
 * Aktualizace náhledu barvy
 * @param {HTMLElement} picker Color picker element
 */
function updateColorPreview(picker) {
    const colorInputGroup = picker.closest('.color-input-group');
    if (!colorInputGroup) return;
    
    const preview = colorInputGroup.querySelector('.color-preview');
    if (!preview) return;
    
    const color = picker.value;
    
    // Nastavení barvy pozadí nebo textu podle typu
    if (picker.name.includes('text_color')) {
        // Pro barvu textu nastavíme barvu textu
        preview.style.color = color;
        preview.style.backgroundColor = '#f8f9fa';
    } else {
        // Pro ostatní nastavíme barvu pozadí
        preview.style.backgroundColor = color;
        // Automaticky určíme barvu textu na základě světlosti pozadí
        preview.style.color = getContrastColor(color);
    }
}

/**
 * Určí kontrastní barvu textu na základě barvy pozadí
 * @param {string} hexColor HEX barva pozadí
 * @returns {string} Černá nebo bílá barva pro optimální kontrast
 */
function getContrastColor(hexColor) {
    // Převod HEX na RGB
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    
    // Výpočet relativní luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    
    // Vrátíme černou pro světlé pozadí, bílou pro tmavé
    return luminance > 0.5 ? '#000000' : '#ffffff';
}