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
    // Hledáme checkbox různými způsoby
    let vatPayerCheckbox = document.getElementById('vat-payer-checkbox') || 
                          document.querySelector('input[name="vat_payer"]') ||
                          document.querySelector('input[id*="vat_payer"]');
    
    const dicContainer = document.getElementById('dic-container');
    
    if (!vatPayerCheckbox || !dicContainer) {
        console.log('VatPayer checkbox nebo DIC container nenalezen');
        console.log('Checkbox:', vatPayerCheckbox);
        console.log('DIC container:', dicContainer);
        return;
    }
    
    console.log('VatPayer toggle inicializován pro:', vatPayerCheckbox);
    
    // Funkce pro zobrazení/skrytí pole DIČ
    function toggleDicField() {
        if (vatPayerCheckbox.checked) {
            dicContainer.style.display = 'block';
            dicContainer.style.opacity = '1';
            dicContainer.style.transform = 'translateY(0)';
        } else {
            dicContainer.style.display = 'none';
            dicContainer.style.opacity = '0';
            dicContainer.style.transform = 'translateY(-10px)';
            // Vymažeme hodnotu DIČ pole
            const dicField = document.getElementById('dic-field') || 
                           document.querySelector('input[name="dic"]');
            if (dicField) {
                dicField.value = '';
            }
        }
        
        // Aktualizujeme styl container podle stavu
        updateCheckboxContainerStyle();
    }
    
    // Funkce pro aktualizaci stylu container podle stavu checkboxu
    function updateCheckboxContainerStyle() {
        const container = vatPayerCheckbox.closest('.form-check-container');
        if (!container) return;
        
        if (vatPayerCheckbox.checked) {
            container.style.borderColor = '#B1D235';
            container.style.background = 'rgba(177, 210, 53, 0.15)';
        } else {
            container.style.borderColor = '#e0e0e0';
            container.style.background = '#f8f9fa';
        }
    }
    
    // Přidání posluchače události na změnu checkboxu
    vatPayerCheckbox.addEventListener('change', toggleDicField);
    
    // Přidání hover efektů
    const container = vatPayerCheckbox.closest('.form-check-container');
    if (container) {
        container.addEventListener('mouseenter', function() {
            if (!vatPayerCheckbox.checked) {
                container.style.borderColor = '#B1D235';
                container.style.background = 'rgba(177, 210, 53, 0.1)';
            }
        });
        
        container.addEventListener('mouseleave', function() {
            if (!vatPayerCheckbox.checked) {
                container.style.borderColor = '#e0e0e0';
                container.style.background = '#f8f9fa';
            }
        });
    }
    
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
        // Ověříme, zda už nemá HEX input (kvůli případnému dvojímu volání)
        const existingHexInput = picker.parentElement.querySelector('.hex-input');
        if (existingHexInput) {
            return;
        }
        
        // Vytvoření textového pole pro zadání HEX kódu
        const hexInput = document.createElement('input');
        hexInput.type = 'text';
        hexInput.className = 'form-control form-control-sm hex-input';
        hexInput.placeholder = '#B1D235';
        hexInput.value = picker.value;
        hexInput.style.width = '120px';
        hexInput.style.fontSize = '0.875rem';
        
        // Vložení textového pole do color-input-group
        const colorInputGroup = picker.closest('.color-input-group');
        if (colorInputGroup) {
            colorInputGroup.insertBefore(hexInput, picker.nextSibling);
        }
        
        // Synchronizace barvy mezi color pickerem a textovým polem
        picker.addEventListener('input', function() {
            hexInput.value = picker.value;
            updateColorPreview(picker);
        });
        
        hexInput.addEventListener('input', function() {
            // Validace HEX kódu a přidání # na začátek, pokud chybí
            let hexCode = hexInput.value.trim();
            
            // Odebereme # pokud tam je, pak ho zase přidáme
            if (hexCode.startsWith('#')) {
                hexCode = hexCode.substring(1);
            }
            
            // Přidáme # na začátek
            hexCode = '#' + hexCode;
            
            // Kontrola, zda jde o platný HEX kód (musí být #RGB nebo #RRGGBB)
            const hexRegex = /^#([A-Fa-f0-9]{3}){1,2}$/;
            if (hexRegex.test(hexCode)) {
                hexInput.value = hexCode;
                picker.value = hexCode;
                updateColorPreview(picker);
                
                // Odebereme error styling
                hexInput.classList.remove('is-invalid');
                hexInput.style.borderColor = '';
            } else {
                // Přidáme error styling
                hexInput.classList.add('is-invalid');
                hexInput.style.borderColor = '#dc3545';
            }
        });
        
        // Přidáme blur event pro úpravu formátu
        hexInput.addEventListener('blur', function() {
            let hexCode = hexInput.value.trim();
            
            // Pokud je hodnota prázdná, nenastavujeme nic
            if (!hexCode) return;
            
            // Odebereme # pokud tam je
            if (hexCode.startsWith('#')) {
                hexCode = hexCode.substring(1);
            }
            
            // Rozšíříme 3-znak HEX na 6-znak
            if (hexCode.length === 3) {
                hexCode = hexCode.split('').map(char => char + char).join('');
            }
            
            // Přidáme # a nastavíme
            const finalHex = '#' + hexCode;
            const hexRegex = /^#([A-Fa-f0-9]{6})$/;
            
            if (hexRegex.test(finalHex)) {
                hexInput.value = finalHex;
                picker.value = finalHex;
                updateColorPreview(picker);
                hexInput.classList.remove('is-invalid');
                hexInput.style.borderColor = '';
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
    if (picker.name && picker.name.includes('text_color')) {
        // Pro barvu textu nastavíme barvu textu
        preview.style.color = color;
        preview.style.backgroundColor = '#f8f9fa';
        preview.style.border = '1px solid #dee2e6';
    } else {
        // Pro ostatní nastavíme barvu pozadí
        preview.style.backgroundColor = color;
        preview.style.border = '1px solid ' + color;
        // Automaticky určíme barvu textu na základě světlosti pozadí
        preview.style.color = getContrastColor(color);
    }
    
    // Přidáme hover efekt
    preview.style.transition = 'all 0.2s ease';
}

/**
 * Určí kontrastní barvu textu na základě barvy pozadí
 * @param {string} hexColor HEX barva pozadí
 * @returns {string} Černá nebo bílá barva pro optimální kontrast
 */
function getContrastColor(hexColor) {
    // Převod HEX na RGB
    const hex = hexColor.replace('#', '');
    
    // Ošetření pro 3-char HEX
    const fullHex = hex.length === 3 
        ? hex.split('').map(char => char + char).join('')
        : hex;
    
    const r = parseInt(fullHex.substr(0, 2), 16);
    const g = parseInt(fullHex.substr(2, 2), 16);
    const b = parseInt(fullHex.substr(4, 2), 16);
    
    // Výpočet relativní luminance podle W3C algoritmu
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    
    // Vrátíme černou pro světlé pozadí, bílou pro tmavé
    return luminance > 0.5 ? '#212529' : '#ffffff';
}

/**
 * Pomocná funkce pro debug
 */
function debugColorPickers() {
    console.log('Color pickers debug info:');
    const colorPickers = document.querySelectorAll('input[type="color"]');
    colorPickers.forEach((picker, index) => {
        console.log(`Picker ${index + 1}:`, {
            name: picker.name,
            value: picker.value,
            hasPreview: !!picker.closest('.color-input-group')?.querySelector('.color-preview'),
            hasHexInput: !!picker.parentElement.querySelector('.hex-input')
        });
    });
}