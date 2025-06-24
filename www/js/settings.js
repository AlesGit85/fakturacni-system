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
    console.log('Inicializace VAT payer toggle');
    
    // Zkusíme najít checkbox různými způsoby
    let vatPayerCheckbox = document.getElementById('vat-payer-checkbox');
    
    // Pokud nenajdeme podle ID, zkusíme podle name atributu
    if (!vatPayerCheckbox) {
        vatPayerCheckbox = document.querySelector('input[name="vat_payer"]');
        console.log('Checkbox nalezen podle name atributu:', !!vatPayerCheckbox);
    }
    
    // Pokud stále nenajdeme, zkusíme podle CSS třídy
    if (!vatPayerCheckbox) {
        vatPayerCheckbox = document.querySelector('input.modern-checkbox');
        console.log('Checkbox nalezen podle CSS třídy:', !!vatPayerCheckbox);
    }
    
    // Pokud stále nenajdeme, zkusíme všechny checkboxy typu checkbox
    if (!vatPayerCheckbox) {
        const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
        console.log('Nalezeno checkboxů celkem:', allCheckboxes.length);
        
        // Vypíšeme všechny checkboxy pro debug
        allCheckboxes.forEach((cb, index) => {
            console.log(`Checkbox ${index + 1}: id="${cb.id}", name="${cb.name}", class="${cb.className}"`);
            
            // Pokud najdeme checkbox s name obsahujícím "vat"
            if (cb.name && cb.name.includes('vat')) {
                vatPayerCheckbox = cb;
                console.log('Nalezen VAT checkbox podle name obsahujícího "vat"');
            }
        });
    }
    
    const dicContainer = document.getElementById('dic-container');
    
    console.log('VAT checkbox nalezen:', !!vatPayerCheckbox);
    console.log('DIC container nalezen:', !!dicContainer);
    
    if (vatPayerCheckbox) {
        console.log('VAT checkbox ID:', vatPayerCheckbox.id);
        console.log('VAT checkbox name:', vatPayerCheckbox.name);
        console.log('VAT checkbox class:', vatPayerCheckbox.className);
    }
    
    if (!vatPayerCheckbox || !dicContainer) {
        console.log('VAT toggle prvky nenalezeny, ukončuji inicializaci');
        
        // Pro debug - vypíšeme všechny elementy s ID obsahujícím "vat" nebo "dic"
        const allElements = document.querySelectorAll('*[id*="vat"], *[id*="dic"], *[name*="vat"], *[name*="dic"]');
        console.log('Elementy obsahující "vat" nebo "dic":', allElements.length);
        allElements.forEach((el, index) => {
            console.log(`Element ${index + 1}: tag="${el.tagName}", id="${el.id}", name="${el.name || 'N/A'}", class="${el.className}"`);
        });
        
        return;
    }
    
    // Funkce pro zobrazení/skrytí pole DIČ
    function toggleDicField() {
        console.log('Toggle DIC field, checkbox checked:', vatPayerCheckbox.checked);
        
        if (vatPayerCheckbox.checked) {
            dicContainer.style.display = 'block';
            console.log('DIC container zobrazen');
        } else {
            dicContainer.style.display = 'none';
            const dicField = document.getElementById('dic-field') || 
                           document.querySelector('input[name="dic"]') ||
                           dicContainer.querySelector('input');
            if (dicField) {
                dicField.value = '';
            }
            console.log('DIC container skryt');
        }
    }
    
    // Přidání posluchače události na změnu checkboxu
    vatPayerCheckbox.addEventListener('change', toggleDicField);
    console.log('Event listener přidán');
    
    // Inicializace stavu při načtení stránky
    toggleDicField();
}

/**
 * Inicializace color pickerů s možností ručního zadání HEX kódu
 */
function initColorPickers() {
    console.log('Inicializace color pickerů');
    
    // Najdeme všechny color pickery
    const colorPickers = document.querySelectorAll('input[type="color"]');
    console.log('Nalezeno color pickerů:', colorPickers.length);
    
    // Pro každý color picker vytvoříme text pole pro zadání HEX kódu
    colorPickers.forEach((picker, index) => {
        console.log(`Zpracovávám color picker ${index + 1}`);
        
        // Kontrola, zda už není textové pole vytvořeno
        const existingHexInput = picker.parentElement.querySelector('.hex-input');
        if (existingHexInput) {
            console.log(`Textové pole pro color picker ${index + 1} už existuje, přeskakuji`);
            return;
        }
        
        // Vytvoření textového pole pro zadání HEX kódu
        const hexInput = document.createElement('input');
        hexInput.type = 'text';
        hexInput.className = 'form-control form-control-sm ms-2 hex-input';
        hexInput.placeholder = 'HEX kód (#cacaca)';
        hexInput.value = picker.value;
        hexInput.style.width = '110px';
        
        // Vložení textového pole vedle color pickeru
        const parentElement = picker.parentElement;
        parentElement.insertBefore(hexInput, picker.nextSibling);
        
        console.log(`Textové pole pro color picker ${index + 1} vytvořeno`);
        
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
    console.log('Inicializace color preview');
    
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