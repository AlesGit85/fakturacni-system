/**
 * Tenants - JavaScript funkcionalita pro správu tenantů
 * NAMESPACE PATTERN - bez globálních konfliktů
 */

// Namespace pro tenants funkcionalitu
window.TenantsModule = window.TenantsModule || (function() {
    'use strict';
    
    // Privátní proměnné
    let currentTenantId = null;

    console.log('🚀 Tenants Module načten (namespace pattern)');

    /**
     * Inicializace při načtení stránky
     */
    function init() {
        // Detekce stránky a inicializace příslušné funkcionality
        if (isAddTenantPage()) {
            initTenantAddForm();
        } else if (isTenantListPage()) {
            initTenantList();
        }
    }

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
        return document.querySelector('.clients-container') !== null && 
               document.getElementById('tenantSearch') !== null;
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
            console.log('✅ DIČ pole je již zobrazeno (checkbox byl checked)');
        } else {
            dicRow.style.display = 'none';
            console.log('❌ DIČ pole je skryto (checkbox nebyl checked)');
        }
    }

    /**
     * Inicializace ARES lookup funkcí
     */
    function initTenantAresLookup() {
        console.log('🏢 Inicializace ARES lookup pro tenant formulář');
        
        const aresButton = document.getElementById('load-from-ares');
        const icInput = document.querySelector('input[name="ic"]');
        
        if (!aresButton || !icInput) {
            console.error('❌ ARES tlačítko nebo IČ pole nebylo nalezeno');
            return;
        }
        
        aresButton.addEventListener('click', function() {
            const ic = icInput.value.trim();
            
            if (!ic) {
                showTenantMessage('Prosím zadejte IČ pro vyhledání v ARESu', 'warning');
                icInput.focus();
                return;
            }
            
            // Kontrola formátu IČ
            if (!/^\d{8}$/.test(ic)) {
                showTenantMessage('IČ musí obsahovat přesně 8 číslic', 'warning');
                icInput.focus();
                return;
            }
            
            console.log('🔍 Vyhledávám v ARESu IČ:', ic);
            performTenantAresLookup(ic);
        });
    }

    /**
     * Provede ARES lookup
     */
    function performTenantAresLookup(ic) {
        const aresButton = document.getElementById('load-from-ares');
        const originalText = aresButton.textContent;
        const originalIcon = aresButton.querySelector('i').className;
        
        // Změň tlačítko na loading stav
        aresButton.disabled = true;
        aresButton.innerHTML = '<i class="bi bi-arrow-repeat spinner-rotate"></i> Načítám z ARESu...';
        
        // URL pro ARES lookup je předaná ze šablony
        const aresUrl = document.querySelector('meta[name="ares-lookup-url"]')?.content || '/tenants/ares-lookup';
        
        console.log('📞 Volám ARES API na URL:', aresUrl);
        
        fetch(aresUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ic: ic })
        })
        .then(response => {
            console.log('📡 ARES odpověď status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📋 ARES data:', data);
            
            if (data.success) {
                fillTenantFormFromAres(data.data);
            } else {
                showTenantMessage(data.message || 'Nepodařilo se načíst data z ARESu', 'danger');
            }
        })
        .catch(error => {
            console.error('❌ ARES chyba:', error);
            showTenantMessage('Chyba při komunikaci s ARESem', 'danger');
        })
        .finally(() => {
            // Obnov původní stav tlačítka
            aresButton.disabled = false;
            aresButton.innerHTML = `<i class="${originalIcon}"></i> ${originalText}`;
        });
    }

    /**
     * Vyplní formulář daty z ARESu
     */
    function fillTenantFormFromAres(data) {
        console.log('📝 Vyplňuji formulář daty z ARESu:', data);
        
        const filledFields = [];
        
        if (fillTenantField('name', data.obchodni_firma || data.nazev)) {
            filledFields.push('název společnosti');
        }
        
        if (fillTenantField('address', data.sidlo_adresa || data.adresa)) {
            filledFields.push('adresa');
        }
        
        if (fillTenantField('city', data.sidlo_mesto || data.mesto)) {
            filledFields.push('město');
        }
        
        if (fillTenantField('zip', data.sidlo_psc || data.psc)) {
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
        
        // Inicializace vyhledávání
        initTenantSearch();
        
        // Inicializace rozbalování detailů
        initTenantDetailsExpansion();
    }

    /**
     * Inicializace vyhledávání tenantů
     */
    function initTenantSearch() {
        console.log('🔍 Inicializace vyhledávání tenantů');
        
        const searchInput = document.getElementById('tenantSearch');
        const tableRows = document.querySelectorAll('.data-row');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(function(row) {
                    // Preskočíme řádky s detaily
                    if (row.classList.contains('tenant-details-row')) {
                        return;
                    }
                    
                    const tenantName = row.querySelector('.company-name strong');
                    const tenantDetails = row.querySelector('.company-location');
                    
                    if (!tenantName) return;
                    
                    const tenantNameText = tenantName.textContent.toLowerCase();
                    const tenantDetailsText = tenantDetails ? tenantDetails.textContent.toLowerCase() : '';
                    
                    if (tenantNameText.includes(searchTerm) || tenantDetailsText.includes(searchTerm)) {
                        row.style.display = '';
                        // Skryjeme případně rozbalené detaily při vyhledávání
                        const tenantId = row.getAttribute('data-tenant-id');
                        const detailsRow = document.getElementById('details-' + tenantId);
                        if (detailsRow && detailsRow.style.display === 'table-row') {
                            detailsRow.style.display = 'none';
                        }
                    } else {
                        row.style.display = 'none';
                        // Skryjeme i řádek s detaily
                        const tenantId = row.getAttribute('data-tenant-id');
                        const detailsRow = document.getElementById('details-' + tenantId);
                        if (detailsRow) {
                            detailsRow.style.display = 'none';
                        }
                    }
                });
            });
            
            console.log('✅ Vyhledávání tenantů inicializováno');
        }
    }

    /**
     * Inicializace rozbalování detailů tenantů
     */
    function initTenantDetailsExpansion() {
        console.log('🔄 Inicializace rozbalování detailů tenantů');
        
        const tenantRows = document.querySelectorAll('.tenant-row');
        
        tenantRows.forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Robustní prevence event bubbling
                e.preventDefault();
                e.stopImmediatePropagation();
                
                // Zabráníme rozbalení při kliknutí na akční tlačítka
                if (e.target.closest('.action-buttons') || 
                    e.target.closest('a') || 
                    e.target.closest('button')) {
                    console.log('🚫 Kliknutí na akční element - ignoruji');
                    return;
                }
                
                const tenantId = this.getAttribute('data-tenant-id');
                const detailsRow = document.getElementById('details-' + tenantId);
                
                if (detailsRow) {
                    if (detailsRow.style.display === 'none' || !detailsRow.style.display) {
                        // Rozbalit detaily
                        console.log('📂 Rozbalování detailů pro tenant ID:', tenantId);
                        detailsRow.style.display = 'table-row';
                        
                        // Animace rozbalení
                        detailsRow.style.opacity = '0';
                        setTimeout(function() {
                            detailsRow.style.transition = 'opacity 0.3s ease';
                            detailsRow.style.opacity = '1';
                        }, 10);
                        
                    } else {
                        // Skrýt detaily
                        console.log('📁 Zabalování detailů pro tenant ID:', tenantId);
                        detailsRow.style.transition = 'opacity 0.3s ease';
                        detailsRow.style.opacity = '0';
                        
                        setTimeout(function() {
                            detailsRow.style.display = 'none';
                        }, 300);
                    }
                }
            });
            
            // Přidáme hover efekt na celý řádek
            row.addEventListener('mouseenter', function() {
                if (!this.classList.contains('tenant-inactive')) {
                    this.style.backgroundColor = 'rgba(177, 210, 53, 0.05)';
                }
            });
            
            row.addEventListener('mouseleave', function() {
                if (!this.classList.contains('tenant-inactive')) {
                    this.style.backgroundColor = '';
                }
            });
        });
        
        console.log('✅ Rozbalování detailů inicializováno pro', tenantRows.length, 'tenantů');
    }

    /**
     * Funkce pro deaktivaci tenanta (volaná z HTML)
     */
    function deactivateTenant(tenantId, tenantName) {
        currentTenantId = tenantId;
        const nameElement = document.getElementById('deactivate-tenant-name');
        if (nameElement) {
            nameElement.textContent = tenantName;
        }
        const modal = document.getElementById('deactivateModal');
        if (modal && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(modal).show();
        }
    }

    /**
     * Potvrzení deaktivace tenanta (volaná z HTML)
     */
    function confirmDeactivate() {
        const reasonElement = document.getElementById('deactivate-reason');
        const reason = reasonElement ? reasonElement.value || 'Deaktivace super adminem' : 'Deaktivace super adminem';
        window.location.href = '/tenants/deactivate/' + currentTenantId + '?reason=' + encodeURIComponent(reason);
    }

    /**
     * Funkce pro smazání tenanta (volaná z HTML)
     */
    function deleteTenant(tenantId, tenantName) {
        const nameElement = document.getElementById('delete-tenant-name');
        if (nameElement) {
            nameElement.textContent = tenantName;
        }
        
        const idElement = document.getElementById('delete-tenant-id');
        if (idElement) {
            idElement.value = tenantId;
        }
        
        // Nastavíme tenant_id do formuláře
        const tenantIdInput = document.querySelector('#frm-deleteTenantForm input[name="tenant_id"]');
        if (tenantIdInput) {
            tenantIdInput.value = tenantId;
        }
        
        const modal = document.getElementById('deleteModal');
        if (modal && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(modal).show();
        }
    }

    // Inicializace při načtení DOMu
    document.addEventListener('DOMContentLoaded', init);

    // Veřejné API - exportované funkce
    return {
        deactivateTenant: deactivateTenant,
        confirmDeactivate: confirmDeactivate,
        deleteTenant: deleteTenant
    };

})();

// Exportujeme funkce do globálního scope pro použití v onclick atributech
window.deactivateTenant = window.TenantsModule.deactivateTenant;
window.confirmDeactivate = window.TenantsModule.confirmDeactivate;
window.deleteTenant = window.TenantsModule.deleteTenant;