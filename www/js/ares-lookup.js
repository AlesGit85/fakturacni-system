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
        
        const icoInput = document.getElementById('frm-clientForm-ic');
        if (!icoInput || icoInput.value.trim() === '') {
            showErrorMessage('Prosím zadejte IČO');
            if (icoInput) icoInput.focus();
            return;
        }
        
        // Zobrazíme načítací indikátor
        const originalText = aresButton.innerHTML;
        aresButton.disabled = true;
        aresButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Načítám...';
        
        const icoValue = icoInput.value.trim();
        console.log('Odesílám AJAX požadavek pro IČO:', icoValue);
        
        // Správná URL pro Nette signál
        const url = window.location.href.split('?')[0] + '?do=aresLookup&ico=' + encodeURIComponent(icoValue);
        console.log('URL požadavku:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => {
                console.log('ARES odpověď:', response.status, response.statusText);
                
                // Kontrola content type
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                if (!response.ok) {
                    throw new Error('Chyba při komunikaci se serverem: ' + response.status);
                }
                
                // Kontrola, zda je odpověď JSON
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Neočekávaná odpověď (není JSON):', text.substring(0, 200));
                        throw new Error('Server nevrátil JSON odpověď');
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log('ARES data:', data);
                if (data.error) {
                    showErrorMessage(data.error);
                    return;
                }
                
                console.log('Začínám vyplňování formuláře...');
                
                // Předvyplnění formuláře s kontrolou existence polí
                fillFormField('frm-clientForm-name', data.name);
                fillFormField('frm-clientForm-address', data.address);
                fillFormField('frm-clientForm-city', data.city);
                fillFormField('frm-clientForm-zip', data.zip);
                fillFormField('frm-clientForm-country', data.country);
                fillFormField('frm-clientForm-dic', data.dic);
                
                console.log('Formulář vyplněn');
                
                // Zobrazíme informaci o úspěšném načtení
                showSuccessMessage('Data úspěšně načtena z ARESu');
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
 */
function fillFormField(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (field && value) {
        field.value = value;
        console.log(`Vyplněno pole ${fieldId}:`, value);
    } else if (!field) {
        console.warn(`Pole ${fieldId} nebylo nalezeno`);
    } else {
        console.log(`Pole ${fieldId} má prázdnou hodnotu`);
    }
}

/**
 * Zobrazí zprávu o úspěšném načtení
 */
function showSuccessMessage(message) {
    showMessage(message, 'success');
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
    // Zkontrolujeme, zda již existuje element s touto zprávou
    const existingMessage = document.getElementById('ares-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const messageElement = document.createElement('div');
    messageElement.id = 'ares-message';
    messageElement.className = `alert alert-${type} mt-3`;
    
    const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';
    messageElement.innerHTML = `<i class="bi bi-${icon} me-2"></i>${message}`;
    
    // Vložíme zprávu za formulář IČO
    const icoField = document.getElementById('frm-clientForm-ic');
    if (icoField) {
        const icoParent = icoField.closest('.form-group') || icoField.closest('.input-group').parentNode || icoField.closest('.mb-3');
        if (icoParent) {
            icoParent.appendChild(messageElement);
        } else {
            // Fallback - vložíme za IČO pole
            icoField.parentNode.insertBefore(messageElement, icoField.nextSibling);
        }
    }
    
    // Nastavíme automatické zmizení po 5 sekundách
    setTimeout(() => {
        messageElement.style.transition = 'opacity 0.5s ease';
        messageElement.style.opacity = '0';
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.remove();
            }
        }, 500);
    }, 5000);
}