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
            icoInput.focus();
            return;
        }
        
        // Zobrazíme načítací indikátor
        const originalText = aresButton.innerHTML;
        aresButton.disabled = true;
        aresButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Načítám...';
        
        const icoValue = icoInput.value.trim();
        console.log('Odesílám AJAX požadavek pro IČO:', icoValue);
        
        // AJAX požadavek přes Nette presenter
        const url = '?do=aresLookup&ico=' + encodeURIComponent(icoValue);
        console.log('URL požadavku:', url);
        
        fetch(url)
            .then(response => {
                console.log('ARES odpověď:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error('Chyba při komunikaci se serverem: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('ARES data:', data);
                if (data.error) {
                    showErrorMessage(data.error);
                    return;
                }
                
                // Předvyplnění formuláře
                if (data.name) document.getElementById('frm-clientForm-name').value = data.name;
                if (data.address) document.getElementById('frm-clientForm-address').value = data.address;
                if (data.city) document.getElementById('frm-clientForm-city').value = data.city;
                if (data.zip) document.getElementById('frm-clientForm-zip').value = data.zip;
                if (data.country) document.getElementById('frm-clientForm-country').value = data.country;
                if (data.dic) document.getElementById('frm-clientForm-dic').value = data.dic;
                
                // Zobrazíme informaci o úspěšném načtení
                showSuccessMessage('Data úspěšně načtena z ARESu');
            })
            .catch(error => {
                console.error('Chyba při načítání dat z ARES:', error);
                showErrorMessage('Nepodařilo se načíst data z ARESu. Zkuste to prosím později.');
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
    const icoParent = icoField.closest('.form-group') || icoField.closest('.input-group').parentNode;
    icoParent.appendChild(messageElement);
    
    // Nastavíme automatické zmizení po 5 sekundách
    setTimeout(() => {
        messageElement.style.transition = 'opacity 0.5s ease';
        messageElement.style.opacity = '0';
        setTimeout(() => {
            messageElement.remove();
        }, 500);
    }, 5000);
}