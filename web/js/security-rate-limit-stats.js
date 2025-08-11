/**
 * Rate Limit Statistics - JavaScript funkcionalita
 * Fakturační systém - Security modul
 * Barvy: primární #B1D235, sekundární #95B11F, šedá #6c757d, černá #212529
 */

document.addEventListener('DOMContentLoaded', function() {
    const clearExpiredBtn = document.getElementById('clearExpiredBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadingText = document.getElementById('loadingText');
    const clearBlockBtns = document.querySelectorAll('.clear-block-btn');
    
    // Zkontroluj zda elementy existují
    if (!clearExpiredBtn) {
        console.error('Rate Limit Stats: Tlačítko clearExpiredBtn nebylo nalezeno');
        return;
    }
    
    // Event listener pro vyčištění expirovaných záznamů
    clearExpiredBtn.addEventListener('click', function() {
        clearExpiredRateLimits();
    });
    
    // Event listener pro vyčištění všech záznamů
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            clearAllRateLimits();
        });
    }
    
    // Event listenery pro odblokování konkrétních IP adres
    clearBlockBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const ip = this.getAttribute('data-ip');
            const url = this.getAttribute('data-url');
            if (ip && url) {
                clearSpecificIPBlock(ip, url);
            } else {
                console.error('Chybí data-ip nebo data-url atribut na tlačítku');
            }
        });
    });
    
    /**
     * Vyčistí všechny expirované rate limit záznamy
     */
    function clearExpiredRateLimits() {
        const confirmed = confirm('Opravdu chcete vyčistit všechny expirované rate limit záznamy?');
        
        if (!confirmed) {
            return;
        }
        
        showLoading('Čistím expirované záznamy...');
        
        // Získáme URL z data-url atributu tlačítka
        const url = clearExpiredBtn.getAttribute('data-url');
        
        if (!url) {
            hideLoading();
            showErrorMessage('❌ Chyba: URL pro clearing není definované');
            return;
        }
        
        // Vytvoř FormData s CSRF tokenem
        const formData = new FormData();
        if (window.csrfToken) {
            formData.append('_csrf_token', window.csrfToken);
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccessMessage('✅ ' + data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('❌ Chyba: ' + (data.error || 'Neznámá chyba'));
            }
        })
        .catch(error => {
            console.error('Rate Limit Clear Error:', error);
            hideLoading();
            showErrorMessage('❌ Nastala chyba při komunikaci se serverem: ' + error.message);
        });
    }
    
    /**
     * Vyčistí všechny rate limit záznamy
     */
    function clearAllRateLimits() {
        const confirmed = confirm('⚠️ POZOR! Tato akce vymaže VŠECHNY rate limit záznamy!\n\nOpravdu chcete pokračovat?');
        
        if (!confirmed) {
            return;
        }
        
        showLoading('Mažu všechny rate limit záznamy...');
        
        // Získáme URL z data-url atributu tlačítka
        const url = clearAllBtn.getAttribute('data-url');
        
        if (!url) {
            hideLoading();
            showErrorMessage('❌ Chyba: URL pro clearing není definované');
            return;
        }
        
        // Vytvoř FormData s CSRF tokenem
        const formData = new FormData();
        if (window.csrfToken) {
            formData.append('_csrf_token', window.csrfToken);
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccessMessage('✅ ' + data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorMessage('❌ Chyba: ' + (data.error || 'Neznámá chyba'));
            }
        })
        .catch(error => {
            console.error('Clear All Rate Limits Error:', error);
            hideLoading();
            showErrorMessage('❌ Nastala chyba při komunikaci se serverem: ' + error.message);
        });
    }
    
    /**
     * Odblokuje konkrétní IP adresu
     * @param {string} ip - IP adresa k odblokování
     * @param {string} url - URL pro odblokování
     */
    function clearSpecificIPBlock(ip, url) {
        const confirmed = confirm(`Opravdu chcete odblokovat IP adresu ${ip}?`);
        
        if (!confirmed) {
            return;
        }
        
        showLoading(`Odblokovávám IP ${ip}...`);
        
        // Přidáme IP parametr k URL
        const fullUrl = url + (url.includes('?') ? '&' : '?') + 'ip=' + encodeURIComponent(ip);
        
        // Vytvoř FormData s CSRF tokenem
        const formData = new FormData();
        if (window.csrfToken) {
            formData.append('_csrf_token', window.csrfToken);
        }
        
        fetch(fullUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccessMessage('✅ ' + data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('❌ Chyba: ' + (data.error || 'Neznámá chyba'));
            }
        })
        .catch(error => {
            console.error('Rate Limit IP Clear Error:', error);
            hideLoading();
            showErrorMessage('❌ Nastala chyba při komunikaci se serverem: ' + error.message);
        });
    }
    
    /**
     * Zobrazí loading indikátor
     * @param {string} message - Zpráva k zobrazení
     */
    function showLoading(message) {
        if (loadingIndicator && loadingText) {
            loadingText.textContent = message;
            loadingIndicator.classList.remove('d-none');
        }
        
        // Deaktivuj všechna tlačítka
        clearExpiredBtn.disabled = true;
        if (clearAllBtn) clearAllBtn.disabled = true;
        clearBlockBtns.forEach(btn => btn.disabled = true);
    }
    
    /**
     * Skryje loading indikátor
     */
    function hideLoading() {
        if (loadingIndicator) {
            loadingIndicator.classList.add('d-none');
        }
        
        // Aktivuj všechna tlačítka
        clearExpiredBtn.disabled = false;
        if (clearAllBtn) clearAllBtn.disabled = false;
        clearBlockBtns.forEach(btn => btn.disabled = false);
    }
    
    /**
     * Zobrazí úspěšnou zprávu
     * @param {string} message - Zpráva k zobrazení
     */
    function showSuccessMessage(message) {
        // Vytvoříme dočasné upozornění
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Automatické odstranění po 5 sekundách
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    /**
     * Zobrazí chybovou zprávu
     * @param {string} message - Zpráva k zobrazení
     */
    function showErrorMessage(message) {
        // Vytvoříme dočasné upozornění
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Automatické odstranění po 8 sekundách (déle pro chyby)
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 8000);
    }
    
    /**
     * Zobrazí obecnou zprávu
     * @param {string} message - Zpráva k zobrazení
     * @param {string} type - Typ zprávy (success, danger, warning, info)
     */
    function showMessage(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});

/**
 * Globální funkce pro debug a testing
 */
window.rateLimitStatsDebug = {
    testNotification: function() {
        document.dispatchEvent(new Event('DOMContentLoaded'));
    },
    
    showTestMessage: function(message, type = 'info') {
        const event = new CustomEvent('showMessage', {
            detail: { message, type }
        });
        document.dispatchEvent(event);
    }
};