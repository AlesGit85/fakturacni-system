/**
 * Finanční přehledy - JavaScript funkcionalita
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 Finanční přehledy - JavaScript načten a spuštěn');
    
    // Inicializace modulu
    initFinancialReports();
});

/**
 * Inicializace finančních přehledů
 */
function initFinancialReports() {
    console.log('🟡 Inicializace finančních přehledů...');
    
    // Nastavení event listeneru pro načítání dat
    const loadButton = document.getElementById('loadRealData');
    console.log('🔍 Hledám tlačítko loadRealData:', loadButton);
    
    if (loadButton) {
        console.log('✅ Tlačítko nalezeno, přidávám event listener');
        loadButton.addEventListener('click', function() {
            console.log('🖱️ Tlačítko bylo kliknuto!');
            loadRealFinancialData();
        });
    } else {
        console.error('❌ Tlačítko loadRealData nebylo nalezeno!');
    }
    
    console.log('✅ Finanční přehledy jsou připraveny k použití');
}

/**
 * Načte skutečná finanční data pomocí AJAX
 */
function loadRealFinancialData() {
    console.log('🚀 Spouštím načítání finančních dat...');
    
    const loadButton = document.getElementById('loadRealData');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const dataStatus = document.getElementById('dataStatus');
    
    console.log('🔍 Kontrola elementů:', {
        loadButton: !!loadButton,
        loadingIndicator: !!loadingIndicator,
        dataStatus: !!dataStatus
    });
    
    // Zobrazení loading stavu
    if (loadButton) {
        loadButton.disabled = true;
        loadButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Načítám...';
    }
    
    if (loadingIndicator) {
        loadingIndicator.style.display = 'block';
    }
    
    if (dataStatus) {
        dataStatus.style.display = 'none';
    }
    
    console.log('⏳ Loading stav nastaven, spouštím AJAX volání...');
    
    // Vytvoríme správnou URL ručně
    const currentUrl = window.location.href;
    const baseUrl = currentUrl.split('?')[0]; // odstraníme query parametry
    const ajaxUrl = baseUrl + '?moduleId=financial_reports&action=getAllData&do=moduleData';
    
    console.log('🔗 Původní URL z window:', window.FINANCIAL_REPORTS_AJAX_URL);
    console.log('🔧 Vytvořená AJAX URL:', ajaxUrl);
    console.log('🔍 Typ URL:', typeof ajaxUrl);
    
    if (!ajaxUrl) {
        console.error('❌ AJAX URL se nepodařilo sestavit!');
        // Fallback na mock data
        const mockData = generateMockFinancialData();
        updateFinancialStats(mockData.stats);
        updateVatStatus(mockData.vatLimits);
        
        if (dataStatus) {
            dataStatus.className = 'alert alert-warning mt-3';
            dataStatus.style.display = 'block';
            dataStatus.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>AJAX URL se nepodařilo sestavit - použita mock data';
        }
        
        // Obnovení tlačítka
        if (loadButton) {
            loadButton.disabled = false;
            loadButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Načíst skutečná data z databáze';
        }
        
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
        return;
    }
    
    console.log('📡 AJAX URL pro volání:', ajaxUrl);
    
    // Skutečné AJAX volání
    fetch(ajaxUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => {
        console.log('📥 AJAX odpověď received:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
            url: response.url
        });
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('❌ Server error response:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
        
        return response.text().then(text => {
            console.log('📄 Raw response text:', text.substring(0, 500) + (text.length > 500 ? '...' : ''));
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('❌ JSON parse error:', e);
                throw new Error('Server nevrátil validní JSON: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('📊 AJAX data parsed:', data);
        
        if (data.success) {
            console.log('✅ Data úspěšně načtena z databáze');
            
            // Aktualizace UI s reálnými daty
            updateFinancialStats(data.data.stats);
            updateVatStatus(data.data.vatLimits);
            
            // Zobrazení úspěchu
            if (dataStatus) {
                dataStatus.className = 'alert alert-success mt-3';
                dataStatus.style.display = 'block';
                dataStatus.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Skutečná data byla úspěšně načtena z databáze!';
            }
            
            if (loadButton) {
                loadButton.innerHTML = '<i class="bi bi-check"></i> Data načtena z databáze';
                loadButton.className = 'btn btn-success';
            }
            
        } else {
            throw new Error(data.error || 'Neznámá chyba serveru');
        }
    })
    .catch(error => {
        console.error('❌ AJAX chyba:', error);
        
        // Zobrazení chyby
        if (dataStatus) {
            dataStatus.className = 'alert alert-danger mt-3';
            dataStatus.style.display = 'block';
            dataStatus.innerHTML = `<i class="bi bi-x-circle-fill me-2"></i>Chyba při načítání dat: ${error.message}`;
        }
        
        if (loadButton) {
            loadButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Zkusit znovu';
            loadButton.className = 'btn btn-danger';
        }
        
        // Fallback na mock data
        console.log('🔄 Fallback na mock data...');
        const mockData = generateMockFinancialData();
        updateFinancialStats(mockData.stats);
        updateVatStatus(mockData.vatLimits);
    })
    .finally(() => {
        // Skrytí loading stavu
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
        
        if (loadButton) {
            loadButton.disabled = false;
        }
        
        console.log('✅ AJAX operace dokončena');
    });
}

/**
 * Generuje mock data pro testování (fallback)
 */
function generateMockFinancialData() {
    console.log('🎲 Generuji mock data jako fallback...');
    
    // Simulovaná data pro případ, že AJAX selže
    const data = {
        stats: {
            totalCount: 25,
            paidCount: 18,
            unpaidCount: 7,
            overdueCount: 2,
            totalTurnover: 1850000,
            paidAmount: 1420000,
            unpaidAmount: 430000,
            year: new Date().getFullYear()
        },
        vatLimits: {
            currentTurnover: 1850000,
            alerts: [
                {
                    type: 'warning',
                    title: 'Blížíte se k DPH limitu',
                    message: 'Při překročení 2 mil. Kč se stanete plátcem DPH',
                    amount: 1850000,
                    limit: 2000000
                }
            ],
            nextLimit: 2000000,
            progressToNextLimit: 92.5
        }
    };
    
    console.log('📋 Mock data připravena:', data);
    return data;
}

/**
 * Aktualizuje statistiky v UI
 */
function updateFinancialStats(stats) {
    console.log('📊 Aktualizuji statistiky:', stats);
    
    // Aktualizace základních statistik
    updateElementText('totalCount', stats.totalCount);
    updateElementText('paidCount', stats.paidCount);
    updateElementText('unpaidCount', stats.unpaidCount);
    updateElementText('overdueCount', stats.overdueCount);
    
    // Aktualizace finančních částek
    updateElementText('totalTurnover', formatAmount(stats.totalTurnover));
    updateElementText('paidAmount', formatAmount(stats.paidAmount));
    updateElementText('unpaidAmount', formatAmount(stats.unpaidAmount));
    
    console.log('✅ Statistiky aktualizovány');
}

/**
 * Aktualizuje DPH status
 */
function updateVatStatus(vatLimits) {
    console.log('💰 Aktualizuji DPH status:', vatLimits);
    
    updateElementText('currentTurnover', formatAmount(vatLimits.currentTurnover));
    updateElementText('nextLimit', formatAmount(vatLimits.nextLimit));
    updateElementText('remainingToLimit', formatAmount(vatLimits.nextLimit - vatLimits.currentTurnover));
    
    // Aktualizace progress baru
    const progressBar = document.getElementById('vatProgress');
    const progressText = document.getElementById('vatProgressText');
    
    console.log('📊 Progress bar elementy:', { progressBar: !!progressBar, progressText: !!progressText });
    
    if (progressBar && progressText) {
        const percentage = Math.min(vatLimits.progressToNextLimit, 100);
        progressBar.style.width = percentage + '%';
        progressText.textContent = percentage.toFixed(1) + '%';
        console.log('📈 Progress bar nastaven na:', percentage + '%');
    }
    
    console.log('✅ DPH status aktualizován');
}

/**
 * Pomocná funkce pro aktualizaci textu elementu
 */
function updateElementText(id, value) {
    const element = document.getElementById(id);
    console.log(`🔄 Aktualizuji element ${id}:`, { element: !!element, value: value });
    
    if (element) {
        element.textContent = value;
        console.log(`✅ Element ${id} aktualizován na: ${value}`);
    } else {
        console.error(`❌ Element s ID '${id}' nenalezen!`);
    }
}

/**
 * Formátování částky do českého formátu
 */
function formatAmount(amount) {
    const formatted = new Intl.NumberFormat('cs-CZ').format(amount) + ' Kč';
    console.log(`💰 Formátuji částku ${amount} na: ${formatted}`);
    return formatted;
}

/**
 * Veřejné API modulu
 */
window.FinancialReports = {
    version: '1.0.0',
    
    getInfo: function() {
        return {
            name: 'Finanční přehledy',
            version: this.version,
            status: 'active',
            author: 'Allimedia.cz'
        };
    },
    
    refresh: function() {
        console.log('🔄 Refresh voláno z API');
        loadRealFinancialData();
    },
    
    loadData: function() {
        console.log('📥 LoadData voláno z API');
        loadRealFinancialData();
    }
};

console.log('🌟 FinancialReports API je dostupné:', window.FinancialReports);