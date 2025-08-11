/**
 * Fakturační systém - Načítání dat z ARESu
 */
document.addEventListener('DOMContentLoaded', function() {
    initAresLookup();
});

/**
 * Inicializace načítání z ARESu
 */
function initAresLookup() {
    console.log('Inicializace ARES lookup');
    
    const aresButton = document.getElementById('load-from-ares');
    console.log('ARES tlačítko nalezeno:', !!aresButton);
    
    if (!aresButton) return;
    
    aresButton.addEventListener('click', function(e) {
        console.log('ARES tlačítko kliknuto');
        e.preventDefault();
        
        // Najdeme IČO pole - zkusíme různé možné ID
        const icoInput = document.getElementById('frm-clientForm-ic') || 
                        document.querySelector('input[name="ic"]') ||
                        document.querySelector('#ic');
        
        if (!icoInput || icoInput.value.trim() === '') {
            showErrorMessage('Prosím zadejte IČO');
            if (icoInput) icoInput.focus();
            return;
        }
        
        // Validace IČO
        const icoValue = icoInput.value.trim().replace(/\s/g, '');
        if (!/^\d{7,8}$/.test(icoValue)) {
            showErrorMessage('IČO musí obsahovat 7 nebo 8 číslic');
            icoInput.focus();
            return;
        }
        
        // Zobrazíme načítací indikátor
        const originalText = aresButton.innerHTML;
        aresButton.disabled = true;
        aresButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Načítám z ARESu...';
        
        console.log('Odesílám AJAX požadavek pro IČO:', icoValue);
        
        // Vytvoříme URL pro Nette signál
        const baseUrl = window.location.href.split('?')[0];
        const url = baseUrl + '?do=aresLookup&ico=' + encodeURIComponent(icoValue);
        console.log('URL požadavku:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            }
        })
            .then(response => {
                console.log('ARES odpověď:', response.status, response.statusText);
                
                // Kontrola content type
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                if (!response.ok) {
                    throw new Error(`Chyba při komunikaci se serverem: ${response.status} ${response.statusText}`);
                }
                
                // Kontrola, zda je odpověď JSON
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Neočekávaná odpověď (není JSON):', text.substring(0, 500));
                        throw new Error('Server nevrátil JSON odpověď. Možná došlo k chybě na serveru.');
                    });
                }
                
                return response.json();
            })
            .then(responseData => {
                console.log('ARES data:', responseData);
                
                // OPRAVENO: Kontrola formátu odpovědi
                if (responseData.error) {
                    showErrorMessage(responseData.error);
                    return;
                }
                
                // OPRAVENO: Data jsou ve responseData.data, ne přímo v responseData
                const data = responseData.data;
                if (!data) {
                    showErrorMessage('Neočekávaný formát odpovědi z ARESu');
                    return;
                }
                
                // OPRAVENO: Kontrola názvu z data.name místo responseData.name
                if (!data.name || data.name.trim() === '') {
                    showErrorMessage('Z ARESu se nepodařilo načíst název firmy');
                    return;
                }
                
                console.log('Začínám vyplňování formuláře...');
                
                // Vyčistíme staré zprávy
                clearMessages();
                
                // Předvyplnění formuláře s kontrolou existence polí
                let filledFields = [];
                
                // Základní údaje společnosti (vždy přepíšeme)
                if (fillFormField('frm-clientForm-name', data.name) || fillFormField('name', data.name)) {
                    filledFields.push('název');
                }
                
                if (fillFormField('frm-clientForm-address', data.address) || fillFormField('address', data.address)) {
                    filledFields.push('adresa');
                }
                
                if (fillFormField('frm-clientForm-city', data.city) || fillFormField('city', data.city)) {
                    filledFields.push('město');
                }
                
                if (fillFormField('frm-clientForm-zip', data.zip) || fillFormField('zip', data.zip)) {
                    filledFields.push('PSČ');
                }
                
                if (fillFormField('frm-clientForm-country', data.country) || fillFormField('country', data.country)) {
                    filledFields.push('země');
                }
                
                if (fillFormField('frm-clientForm-dic', data.dic) || fillFormField('dic', data.dic)) {
                    filledFields.push('DIČ');
                }
                
                // Kontaktní údaje NEPŘEPISUJEME - ty nejsou v ARESu a uživatel je vyplnil ručně
                
                console.log('Formulář vyplněn, pole:', filledFields);
                
                // Zobrazíme informaci o úspěšném načtení
                if (filledFields.length > 0) {
                    showSuccessMessage(`Data úspěšně načtena z ARESu: ${filledFields.join(', ')}`);
                } else {
                    showWarningMessage('Data z ARESu byla načtena, ale nepodařilo se vyplnit žádné pole formuláře');
                }
            })
            .catch(error => {
                console.error('Chyba při načítání dat z ARES:', error);
                showErrorMessage('Nepodařilo se načíst data z ARESu: ' + error.message);
            })
            .finally(() => {
                // Obnovení tlačítka
                aresButton.disabled = false;
                aresButton.innerHTML = originalText;
                console.log('AJAX požadavek dokončen');
            });
    });
}

/**
 * Bezpečně vyplní pole formuláře
 * Vrací true, pokud bylo pole nalezeno a vyplněno
 */
function fillFormField(fieldId, value) {
    // Zkusíme najít pole podle ID
    let field = document.getElementById(fieldId);
    
    // Pokud se nepodařilo najít podle ID, zkusíme podle name atributu
    if (!field) {
        const name = fieldId.replace('frm-clientForm-', '');
        field = document.querySelector(`input[name="${name}"], textarea[name="${name}"], select[name="${name}"]`);
    }
    
    if (field && value && value.toString().trim() !== '') {
        const trimmedValue = value.toString().trim();
        field.value = trimmedValue;
        
        // Spustíme event pro případ, že na poli jsou navěšené listenery
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        
        console.log(`Vyplněno pole ${fieldId}:`, trimmedValue);
        return true;
    } else if (!field) {
        console.warn(`Pole ${fieldId} nebylo nalezeno`);
        return false;
    } else {
        console.log(`Pole ${fieldId} má prázdnou hodnotu nebo nebyla poskytnuta`);
        return false;
    }
}

/**
 * Vyčistí všechny zprávy
 */
function clearMessages() {
    const existingMessages = document.querySelectorAll('[id^="ares-message"]');
    existingMessages.forEach(message => message.remove());
}

/**
 * Zobrazí zprávu o úspěšném načtení
 */
function showSuccessMessage(message) {
    showMessage(message, 'success');
}

/**
 * Zobrazí varovnou zprávu
 */
function showWarningMessage(message) {
    showMessage(message, 'warning');
}

/**
 * Zobrazí chybovou zprávu
 */
function showErrorMessage(message) {
    showMessage(message, 'danger');
}

/**
 * Zobrazí zprávu daného typu
 */
function showMessage(message, type) {
    // Vyčistíme staré zprávy
    clearMessages();
    
    const messageElement = document.createElement('div');
    messageElement.id = `ares-message-${Date.now()}`;
    messageElement.className = `alert alert-${type} mt-3`;
    
    let icon;
    switch (type) {
        case 'success':
            icon = 'check-circle-fill';
            break;
        case 'warning':
            icon = 'exclamation-triangle-fill';
            break;
        case 'danger':
            icon = 'x-circle-fill';
            break;
        default:
            icon = 'info-circle-fill';
    }
    
    messageElement.innerHTML = `<i class="bi bi-${icon} me-2"></i>${message}`;
    
    // Najdeme místo, kam zprávu vložit
    const insertionPoint = findInsertionPoint();
    if (insertionPoint) {
        insertionPoint.appendChild(messageElement);
    }
    
    // Nastavíme automatické zmizení po 8 sekundách (pro úspěch a varování)
    if (type === 'success' || type === 'warning') {
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.style.transition = 'opacity 0.5s ease';
                messageElement.style.opacity = '0';
                setTimeout(() => {
                    if (messageElement.parentNode) {
                        messageElement.remove();
                    }
                }, 500);
            }
        }, 8000);
    }
}

/**
 * Najde vhodné místo pro vložení zprávy
 */
function findInsertionPoint() {
    // Zkusíme najít ARES tlačítko a vložit zprávu za něj
    const aresButton = document.getElementById('load-from-ares');
    if (aresButton) {
        // Hledáme kontejner tlačítka
        const buttonContainer = aresButton.closest('.input-group') || 
                              aresButton.closest('.mb-3') || 
                              aresButton.closest('.form-group') ||
                              aresButton.closest('.col-md-6') ||
                              aresButton.parentNode;
        
        if (buttonContainer) {
            return buttonContainer;
        }
    }
    
    // Fallback - hledáme IČO pole
    const icoField = document.getElementById('frm-clientForm-ic') || 
                    document.querySelector('input[name="ic"]');
    
    if (icoField) {
        const icoContainer = icoField.closest('.mb-3') || 
                           icoField.closest('.form-group') ||
                           icoField.closest('.col-md-6') ||
                           icoField.parentNode;
        
        if (icoContainer) {
            return icoContainer;
        }
    }
    
    // Poslední fallback - tělo formuláře
    const form = document.querySelector('form');
    if (form) {
        return form;
    }
    
    // Úplný fallback - document body
    return document.body;
}

/**
 * Pomocná funkce pro debugging - vypisuje strukturu formuláře
 */
function debugFormFields() {
    console.log('=== DEBUG: Analýza formulářových polí ===');
    
    const form = document.querySelector('form');
    if (form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            console.log(`Pole: id="${input.id}", name="${input.name}", type="${input.type}"`);
        });
    }
    
    console.log('=== Konec debug analýzy ===');
}

// Pro debugging můžete v konzoli zavolat debugFormFields()
window.debugAresFormFields = debugFormFields;