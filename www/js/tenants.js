/**
 * Tenants - JavaScript funkcionalita pro správu tenantů
 */

// Globální proměnné
let currentTenantId = null;

/**
 * Inicializace při načtení stránky
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Tenants JS načten');
    
    // Detekce stránky a inicializace příslušné funkcionality
    if (isAddTenantPage()) {
        initTenantAddForm();
    } else if (isTenantListPage()) {
        initTenantList();
    }
});

/**
 * Detekce, zda jsme na stránce přidání tenanta
 */
function isAddTenantPage() {
    return document.getElementById('frm-createTenantForm') !== null;
}

/**
 * Detekce, zda jsme na stránce seznamu tenantů
 */
function isTenantListPage() {
    return document.querySelector('.stats-grid') !== null;
}

/* ==========================================================================
   Tenant Add Form - DIČ toggle a ARES funkcionalita
   ========================================================================== */

/**
 * Inicializace formuláře pro přidání tenanta
 */
function initTenantAddForm() {
    console.log('📋 Inicializace tenant add formuláře');
    
    // ZABRÁNĚNÍ KONFLIKTU s ares-lookup.js
    window.tenantFormActive = true;
    
    // Počkej na načtení ostatních skriptů
    setTimeout(function() {
        initTenantVatToggle();
        initTenantAresLookup();
    }, 200);
}

/**
 * Inicializace toggle funkčnosti pro DIČ pole
 */
function initTenantVatToggle() {
    console.log('🔄 Inicializace VAT toggle pro tenant formulář');
    
    const checkbox = document.querySelector('input[name="vat_payer"]') || 
                    document.getElementById('frm-createTenantForm-vat_payer');
    const dicRow = document.getElementById('dic-row');
    
    console.log('Checkbox nalezen:', !!checkbox);
    console.log('DIČ řádek nalezen:', !!dicRow);
    
    if (!checkbox || !dicRow) {
        console.error('❌ Nenašel jsem potřebné elementy pro VAT toggle');
        return;
    }
    
    // Event listener pro checkbox
    checkbox.addEventListener('change', function() {
        console.log('🔄 Checkbox změněn, checked:', this.checked);
        
        const dicInput = dicRow.querySelector('input[name="dic"]');
        
        if (this.checked) {
            dicRow.style.display = 'block';
            if (dicInput) {
                dicInput.required = true;
                setTimeout(() => dicInput.focus(), 100);
            }
            console.log('✅ DIČ pole zobrazeno');
        } else {
            dicRow.style.display = 'none';
            if (dicInput) {
                dicInput.required = false;
                dicInput.value = '';
            }
            console.log('❌ DIČ pole skryto');
        }
    });
    
    // Pokud je checkbox už zaškrtnutý při načtení
    if (checkbox.checked) {
        dicRow.style.display = 'block';
        const dicInput = dicRow.querySelector('input[name="dic"]');
        if (dicInput) {
            dicInput.required = true;
        }
    }
}

/**
 * Inicializace ARES lookup funkcionality
 */
function initTenantAresLookup() {
    console.log('🌐 Inicializace ARES lookup pro tenant formulář');
    
    // Zablokuj původní ares-lookup.js
    if (window.initAresLookup) {
        console.log('🚫 Blokuji ares-lookup.js pro tenant formulář');
        window.initAresLookup = function() {
            console.log('🚫 ares-lookup.js zablokován - tenant má vlastní implementaci');
        };
    }
    
    const aresButton = document.getElementById('load-from-ares');
    
    if (!aresButton) {
        console.log('ARES tlačítko nenalezeno');
        return;
    }
    
    // Vyčisti původní event listenery klonováním tlačítka
    const newButton = aresButton.cloneNode(true);
    aresButton.parentNode.replaceChild(newButton, aresButton);
    
    console.log('ARES tlačítko nalezeno a vyčištěno');
    
    // Přidej nový event listener
    newButton.addEventListener('click', handleAresLookup);
}

/**
 * Zpracování ARES lookup požadavku
 */
function handleAresLookup(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('🖱️ ARES tlačítko kliknuto (tenant verze)');
    
    const aresButton = e.target.closest('button');
    const icoInput = document.querySelector('input[name="ic"]') ||
                    document.getElementById('frm-createTenantForm-ic');
    
    if (!icoInput || icoInput.value.trim() === '') {
        showTenantMessage('Prosím zadejte IČO', 'danger');
        if (icoInput) icoInput.focus();
        return;
    }
    
    const icoValue = icoInput.value.trim().replace(/\s/g, '');
    if (!/^\d{7,8}$/.test(icoValue)) {
        showTenantMessage('IČO musí obsahovat 7 nebo 8 číslic', 'danger');
        icoInput.focus();
        return;
    }
    
    // Loading state
    const originalText = aresButton.innerHTML;
    aresButton.disabled = true;
    aresButton.innerHTML = '<i class="bi bi-arrow-repeat spinner-rotate"></i> Načítám z ARESu...';
    
    console.log('📡 Odesílám AJAX požadavek pro IČO:', icoValue);
    
    // AJAX požadavek
    const url = window.location.href.split('?')[0] + '?do=aresLookup&ico=' + encodeURIComponent(icoValue);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('📥 ARES odpověď přijata, status:', response.status);
        return response.json();
    })
    .then(responseData => {
        console.log('📋 ARES data:', responseData);
        
        if (responseData.error) {
            showTenantMessage(responseData.error, 'danger');
            return;
        }
        
        const data = responseData.data;
        if (!data || !data.name) {
            showTenantMessage('Neočekávaný formát odpovědi z ARESu', 'danger');
            return;
        }
        
        console.log('✅ Začínám vyplňování tenant formuláře...');
        fillTenantFormFromAres(data);
    })
    .catch(error => {
        console.error('❌ Chyba při načítání dat z ARES:', error);
        showTenantMessage('Nepodařilo se načíst data z ARESu: ' + error.message, 'danger');
    })
    .finally(() => {
        // Obnovení tlačítka
        console.log('🔄 Obnovuji tlačítko');
        aresButton.disabled = false;
        aresButton.innerHTML = originalText;
    });
}

/**
 * Vyplní formulář daty z ARESu
 */
function fillTenantFormFromAres(data) {
    const filledFields = [];
    
    // Vyplníme jednotlivá pole
    if (fillTenantField('company_name', data.name)) {
        filledFields.push('název společnosti');
    }
    
    if (fillTenantField('address', data.address)) {
        filledFields.push('adresa');
    }
    
    if (fillTenantField('city', data.city)) {
        filledFields.push('město');
    }
    
    if (fillTenantField('zip', data.zip)) {
        filledFields.push('PSČ');
    }
    
    if (fillTenantField('country', data.country)) {
        filledFields.push('země');
    }
    
    if (fillTenantField('dic', data.dic)) {
        filledFields.push('DIČ');
        // Automaticky zaškrtni "Plátce DPH" pokud má DIČ
        const vatCheckbox = document.querySelector('input[name="vat_payer"]');
        if (vatCheckbox && data.dic) {
            vatCheckbox.checked = true;
            vatCheckbox.dispatchEvent(new Event('change'));
        }
    }
    
    if (filledFields.length > 0) {
        showTenantMessage('Data úspěšně načtena z ARESu: ' + filledFields.join(', '), 'success');
        console.log('✅ Formulář vyplněn, pole:', filledFields);
    } else {
        showTenantMessage('Data z ARESu byla načtena, ale nepodařilo se vyplnit žádné pole formuláře', 'warning');
    }
}

/**
 * Vyplní konkrétní pole formuláře
 */
function fillTenantField(fieldName, value) {
    if (!value) return false;
    
    const field = document.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"], select[name="${fieldName}"]`);
    
    if (field && value.toString().trim() !== '') {
        field.value = value.toString().trim();
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        console.log('📝 Vyplněno pole', fieldName + ':', value);
        return true;
    }
    
    return false;
}

/**
 * Zobrazí zprávu v tenant formuláři
 */
function showTenantMessage(message, type) {
    // Odstraň všechny staré zprávy
    const existingMessages = document.querySelectorAll('[id^="tenant-ares-message"], [id^="ares-message"], .alert');
    existingMessages.forEach(msg => {
        if (msg.textContent && (msg.textContent.includes('ARESu') || msg.textContent.includes('načten'))) {
            msg.remove();
        }
    });
    
    const messageElement = document.createElement('div');
    messageElement.id = 'tenant-ares-message-' + Date.now();
    messageElement.className = `alert alert-${type} mt-3`;
    
    const icons = {
        'success': 'check-circle-fill',
        'warning': 'exclamation-triangle-fill',
        'danger': 'x-circle-fill',
        'info': 'info-circle-fill'
    };
    
    const icon = icons[type] || icons['info'];
    messageElement.innerHTML = `<i class="bi bi-${icon} me-2"></i>${message}`;
    
    // Vlož zprávu za ARES tlačítko
    const aresButton = document.getElementById('load-from-ares');
    if (aresButton) {
        const container = aresButton.closest('.input-group') || 
                         aresButton.closest('.mb-3') || 
                         aresButton.parentNode;
        if (container) {
            container.appendChild(messageElement);
        }
    }
    
    console.log('💬 Zobrazena zpráva:', message);
    
    // Automatické zmizení po 8 sekundách
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

/* ==========================================================================
   Tenant List - správa seznamu tenantů
   ========================================================================== */

/**
 * Inicializace seznamu tenantů
 */
function initTenantList() {
    console.log('📊 Inicializace tenant list');
    
    // Event listenery pro modaly jsou už v HTML jako onclick funkce
    // Zde by mohly být další inicializace pokud potřeba
}

/**
 * Funkce pro deaktivaci tenanta (volaná z HTML)
 */
function deactivateTenant(tenantId, tenantName) {
    currentTenantId = tenantId;
    document.getElementById('deactivate-tenant-name').textContent = tenantName;
    new bootstrap.Modal(document.getElementById('deactivateModal')).show();
}

/**
 * Potvrzení deaktivace tenanta (volaná z HTML)
 */
function confirmDeactivate() {
    const reason = document.getElementById('deactivate-reason').value || 'Deaktivace super adminem';
    window.location.href = '/tenants/deactivate/' + currentTenantId + '?reason=' + encodeURIComponent(reason);
}

/**
 * Funkce pro smazání tenanta (volaná z HTML)
 */
function deleteTenant(tenantId, tenantName) {
    document.getElementById('delete-tenant-name').textContent = tenantName;
    document.getElementById('delete-tenant-id').value = tenantId;
    
    // Nastavíme tenant_id do formuláře
    const tenantIdInput = document.querySelector('#frm-deleteTenantForm input[name="tenant_id"]');
    if (tenantIdInput) {
        tenantIdInput.value = tenantId;
    }
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

/* ==========================================================================
   Veřejné API pro použití v HTML
   ========================================================================== */

// Exportujeme funkce do globálního scope pro použití v onclick atributech
window.deactivateTenant = deactivateTenant;
window.confirmDeactivate = confirmDeactivate;
window.deleteTenant = deleteTenant;