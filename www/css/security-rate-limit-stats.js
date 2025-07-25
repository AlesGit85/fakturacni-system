/**
 * Rate Limit Statistics - JavaScript funkcionalita
 * Fakturační systém - Security modul
 */

document.addEventListener('DOMContentLoaded', function() {
    const clearExpiredBtn = document.getElementById('clearExpiredBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');
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
    
    // Event listenery pro odblokování konkrétních IP adres
    clearBlockBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const ip = this.getAttribute('data-ip');
            if (ip) {
                clearSpecificIPBlock(ip);
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
        
        // Získej URL z Nette linkingu
        const url = generateClearRateLimitUrl();
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
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
                setTimeout(() => location.reload(), 1000); // Obnovíme stránku po 1 sekundě
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
     * Odblokuje konkrétní IP adresu
     * @param {string} ip - IP adresa k odblokování
     */
    function clearSpecificIPBlock(ip) {
        const confirmed = confirm(`Opravdu chcete odblokovat IP adresu ${ip}?`);
        
        if (!confirmed) {
            return;
        }
        
        showLoading(`Odblokovávám IP ${ip}...`);
        
        // Získej URL s parametrem IP
        const url = generateClearRateLimitUrl() + '?ip=' + encodeURIComponent(ip);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
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
                setTimeout(() => location.reload(), 1000); // Obnovíme stránku po 1 sekundě
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
     * Generuje URL pro clearRateLimit akci
     * Fallback pokud Nette linking nefunguje
     * @returns {string}
     */
    function generateClearRateLimitUrl() {
        // Zkusíme najít URL z nějakého linku na stránce
        // Fallback: sestavíme URL manuálně
        const currentUrl = window.location.href;
        const baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/'));
        
        // Pokud jsme na /Security/rateLimitStats, přidáme clearRateLimit action
        if (currentUrl.includes('rateLimitStats')) {
            return baseUrl.replace('rateLimitStats', 'clearRateLimit');
        }
        
        // Fallback URL
        return baseUrl + '/clearRateLimit';
    }
    
    /**
     * Zobrazí loading indikátor
     * @param {string} message - Zpráva k zobrazení
     */
    function showLoading(message = 'Zpracování...') {
        if (loadingIndicator) {
            // Aktualizuj zprávu
            const messageElement = loadingIndicator.querySelector('.security-loading-content p');
            if (messageElement) {
                messageElement.textContent = message;
            }
            
            loadingIndicator.style.display = 'block';
        }
        
        // Zakázat všechna tlačítka
        disableAllButtons();
    }
    
    /**
     * Skryje loading indikátor
     */
    function hideLoading() {
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
        
        // Povolit všechna tlačítka
        enableAllButtons();
    }
    
    /**
     * Zakáže všechna tlačítka na stránce
     */
    function disableAllButtons() {
        document.querySelectorAll('button').forEach(btn => {
            btn.disabled = true;
            btn.classList.add('disabled');
        });
        
        document.querySelectorAll('a.btn').forEach(btn => {
            btn.classList.add('disabled');
            btn.style.pointerEvents = 'none';
        });
    }
    
    /**
     * Povolí všechna tlačítka na stránce
     */
    function enableAllButtons() {
        document.querySelectorAll('button').forEach(btn => {
            btn.disabled = false;
            btn.classList.remove('disabled');
        });
        
        document.querySelectorAll('a.btn').forEach(btn => {
            btn.classList.remove('disabled');
            btn.style.pointerEvents = '';
        });
    }
    
    /**
     * Zobrazí úspěšnou zprávu
     * @param {string} message 
     */
    function showSuccessMessage(message) {
        // Pro jednoduchost používáme alert, ale lze rozšířit o toast notifikace
        alert(message);
    }
    
    /**
     * Zobrazí chybovou zprávu
     * @param {string} message 
     */
    function showErrorMessage(message) {
        // Pro jednoduchost používáme alert, ale lze rozšířit o toast notifikace
        alert(message);
    }
    
    /**
     * Refresh stránky s animací
     */
    function refreshPageWithAnimation() {
        // Přidat fade out efekt před refresh
        document.body.style.opacity = '0.7';
        document.body.style.transition = 'opacity 0.3s ease';
        
        setTimeout(() => {
            location.reload();
        }, 300);
    }
    
    // Periodické obnovení statistik (každých 30 sekund)
    // Pouze pokud není aktivní loading
    setInterval(() => {
        if (!loadingIndicator || loadingIndicator.style.display === 'none') {
            // Tichá aktualizace statistik bez full refresh
            updateStatisticsQuietly();
        }
    }, 30000); // 30 sekund
    
    /**
     * Tichá aktualizace statistik bez reload stránky
     */
    function updateStatisticsQuietly() {
        // Tuto funkcionalitu lze implementovat později
        // Pro teď pouze refresh stránky
        console.log('Automatická aktualizace statistik...');
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+R nebo F5 - refresh stránky
        if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
            e.preventDefault();
            refreshPageWithAnimation();
        }
        
        // Escape - zrušit loading (pokud je možné)
        if (e.key === 'Escape' && loadingIndicator && loadingIndicator.style.display !== 'none') {
            // Můžeme přidat abort functionality později
            console.log('Loading přerušen uživatelem');
        }
    });
});