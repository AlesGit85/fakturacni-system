/**
 * Finanční přehledy - JavaScript funkcionalita (aktualizovaná verze pro multitenancy systém)
 * Kompatibilní s Nette Framework a současnou architekturou
 */

class FinancialReportsModule {
    constructor() {
        this.version = '2.0.0';
        this.moduleName = 'Finanční přehledy';
        this.isLoading = false;
        this.tenantId = null;
        this.isSuperAdmin = false;
        this.charts = {};

        // Bind methods to preserve context
        this.loadRealData = this.loadRealData.bind(this);
        this.refreshData = this.refreshData.bind(this);
        this.handleError = this.handleError.bind(this);

        this.log('🟢 FinancialReportsModule inicializován', 'info');
    }

    /**
     * Inicializace modulu
     */
    init() {
        this.log('🟡 Spouštím inicializaci modulu...', 'info');

        // Detekce tenant kontextu z DOM
        this.detectTenantContext();

        // Nastavení event listenerů
        this.setupEventListeners();

        // Inicializace UI komponent
        this.initializeComponents();

        // Auto-loading pokud je tlačítko označené
        this.checkAutoLoad();

        this.log('✅ Modul je připraven k použití', 'success');

        return this;
    }

    /**
     * Detekce tenant kontextu z DOM
     */
    detectTenantContext() {
        // Hledáme tenant informace v meta tagu nebo data attributech
        const tenantMeta = document.querySelector('meta[name="tenant-id"]');
        const adminMeta = document.querySelector('meta[name="is-super-admin"]');

        if (tenantMeta) {
            this.tenantId = parseInt(tenantMeta.content);
        }

        if (adminMeta) {
            this.isSuperAdmin = adminMeta.content === 'true' || adminMeta.content === '1';
        }

        // Backup: hledáme v container elementech
        const container = document.querySelector('[data-tenant-id]');
        if (container && !this.tenantId) {
            this.tenantId = parseInt(container.dataset.tenantId);
        }

        const adminContainer = document.querySelector('[data-super-admin]');
        if (adminContainer && this.isSuperAdmin === false) {
            this.isSuperAdmin = adminContainer.dataset.superAdmin === 'true';
        }

        this.log(`🔍 Tenant kontext: ID=${this.tenantId}, SuperAdmin=${this.isSuperAdmin}`, 'info');

        // Zobrazíme tenant indikátor pokud existuje
        this.updateTenantIndicator();
    }

    /**
     * Aktualizuje tenant indikátor v UI
     */
    updateTenantIndicator() {
        const indicator = document.getElementById('tenantIndicator');
        if (indicator && this.tenantId) {
            const statusText = this.isSuperAdmin ? 'Super Admin (všichni tenanti)' : `Tenant ${this.tenantId}`;
            indicator.innerHTML = `<i class="bi bi-building"></i> <span class="tenant-id">${statusText}</span>`;
            indicator.style.display = 'block';
        }
    }

    /**
     * Nastavení event listenerů
     */
    setupEventListeners() {
        // Hlavní tlačítko pro načítání dat
        const loadButton = document.getElementById('loadRealData');
        if (loadButton) {
            loadButton.addEventListener('click', this.loadRealData);
            this.log('✅ Event listener pro loadRealData nastaven', 'debug');
        }

        // Refresh tlačítko
        const refreshButton = document.getElementById('refreshData');
        if (refreshButton) {
            refreshButton.addEventListener('click', this.refreshData);
            this.log('✅ Event listener pro refreshData nastaven', 'debug');
        }

        // Filter změny
        const yearFilter = document.getElementById('yearFilter');
        const monthFilter = document.getElementById('monthFilter');

        if (yearFilter) {
            yearFilter.addEventListener('change', () => this.handleFilterChange());
        }

        if (monthFilter) {
            monthFilter.addEventListener('change', () => this.handleFilterChange());
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                this.refreshData();
            }
        });
    }

    /**
     * Inicializace UI komponent
     */
    initializeComponents() {
        // Nastavení tooltipů pro Bootstrap
        const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipElements.forEach(element => {
            new bootstrap.Tooltip(element);
        });

        // Nastavení progress barů
        this.initializeProgressBars();

        // Příprava kontejnerů pro grafy
        this.prepareChartContainers();
    }

    /**
     * Inicializace progress barů
     */
    initializeProgressBars() {
        const progressBars = document.querySelectorAll('.vat-progress .progress-bar');
        progressBars.forEach(bar => {
            bar.style.width = '0%';
            bar.style.transition = 'width 0.6s ease';
        });
    }

    /**
     * Příprava kontejnerů pro grafy
     */
    prepareChartContainers() {
        const chartContainers = document.querySelectorAll('.chart-container');
        chartContainers.forEach(container => {
            if (!container.querySelector('canvas')) {
                const canvas = document.createElement('canvas');
                canvas.style.width = '100%';
                canvas.style.height = '100%';
                container.appendChild(canvas);
            }
        });
    }

    /**
     * Kontrola automatického načítání
     */
    checkAutoLoad() {
        const autoLoad = document.querySelector('[data-auto-load="true"]');
        if (autoLoad) {
            setTimeout(() => this.loadRealData(), 1000);
        }
    }

    /**
     * Načtení skutečných dat přes AJAX
     */
    async loadRealData() {
        if (this.isLoading) {
            this.log('⏳ Načítání již probíhá, ignoruji požadavek', 'warn');
            return;
        }

        this.isLoading = true;
        this.log('🚀 Spouštím načítání finančních dat...', 'info');

        try {
            // UI stav - loading
            this.setLoadingState(true);

            // Vytvoření AJAX URL pro multitenancy systém
            const ajaxUrl = this.buildAjaxUrl('getAllData');
            this.log(`🔗 AJAX URL: ${ajaxUrl}`, 'debug');

            // AJAX request s error handling
            const response = await this.makeAjaxRequest(ajaxUrl);

            // Zpracování odpovědi
            await this.processResponse(response);

            // Úspěšné dokončení
            this.setSuccessState();
            this.log('✅ Data úspěšně načtena a zobrazena', 'success');

        } catch (error) {
            this.handleError(error);
        } finally {
            this.setLoadingState(false);
            this.isLoading = false;
        }
    }

    /**
     * Sestavení AJAX URL pro aktuální systém
     */
    buildAjaxUrl(action, params = {}) {
        const currentUrl = new URL(window.location);
        const baseUrl = `${currentUrl.origin}${currentUrl.pathname}`;

        // Parametry pro ModuleAdmin presenter
        const ajaxParams = new URLSearchParams({
            do: 'moduleData',
            moduleId: 'financial_reports',
            action: action,
            ...params
        });

        // Přidáme tenant kontext pokud není super admin
        if (this.tenantId && !this.isSuperAdmin) {
            ajaxParams.set('tenantId', this.tenantId);
        }

        return `${baseUrl}?${ajaxParams.toString()}`;
    }

    /**
     * AJAX request s retry logikou
     */
    async makeAjaxRequest(url, retries = 2) {
        for (let attempt = 0; attempt <= retries; attempt++) {
            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache',
                        'X-Tenant-Id': this.tenantId || '',
                        'X-Super-Admin': this.isSuperAdmin ? '1' : '0'
                    }
                });

                this.log(`📥 Response status: ${response.status} ${response.statusText}`, 'debug');

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                this.log(`📄 Response preview: ${JSON.stringify(data).substring(0, 100)}...`, 'debug');

                return data;

            } catch (error) {
                this.log(`❌ Attempt ${attempt + 1} failed: ${error.message}`, 'warn');
                
                if (attempt === retries) {
                    throw error;
                }
                
                // Exponential backoff
                await this.sleep(Math.pow(2, attempt) * 1000);
            }
        }
    }

    /**
     * Pomocná metoda pro čekání
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Zpracování AJAX odpovědi
     */
    async processResponse(data) {
        this.log('🔄 Zpracovávám odpověď serveru...', 'info');

        // Kontrola struktury odpovědi
        if (!data || typeof data !== 'object') {
            throw new Error('Neplatná odpověď serveru');
        }

        if (data.error) {
            throw new Error(data.error || data.message || 'Neznámá chyba serveru');
        }

        if (data.data && data.data.stats && data.data.vatLimits) {
            // Aktualizace statistik
            this.updateFinancialStats(data.data.stats);
            
            // Aktualizace DPH statusu
            this.updateVatStatus(data.data.vatLimits);
            
            // Aktualizace grafů (pokud existují)
            await this.updateCharts(data.data);
            
        } else {
            throw new Error('Neočekávaná struktura dat od serveru');
        }
    }

    /**
     * Aktualizace finančních statistik v UI
     */
    updateFinancialStats(stats) {
        this.log('📊 Aktualizuji statistiky:', 'debug', stats);

        const updates = [
            { id: 'totalCount', value: stats.totalCount },
            { id: 'paidCount', value: stats.paidCount },
            { id: 'unpaidCount', value: stats.unpaidCount },
            { id: 'overdueCount', value: stats.overdueCount },
            { id: 'totalTurnover', value: this.formatAmount(stats.totalTurnover) },
            { id: 'paidAmount', value: this.formatAmount(stats.paidAmount) },
            { id: 'unpaidAmount', value: this.formatAmount(stats.unpaidAmount) },
            { id: 'currentYear', value: stats.year }
        ];

        updates.forEach(update => {
            this.updateElement(update.id, update.value);
        });

        // Animované čítače pro čísla
        this.animateCounters(['totalCount', 'paidCount', 'unpaidCount', 'overdueCount']);
    }

    /**
     * Aktualizace DPH statusu s vylepšeným zobrazením
     */
    updateVatStatus(vatLimits) {
        this.log('📊 Aktualizuji DPH status', 'debug', vatLimits);

        const currentTurnover = vatLimits.currentTurnover || 0;
        const nextLimit = vatLimits.nextLimit || 2000000;
        const progressToNextLimit = vatLimits.progressToNextLimit || 0;

        // Určení stavu podle obratu
        let status = 'normal';
        let displayText = '';
        let remainingAmount = nextLimit - currentTurnover;

        if (currentTurnover >= 2536500) {
            status = 'exceeded';
            displayText = 'PŘEKROČEN LIMIT!';
        } else if (currentTurnover >= 2000000) {
            status = 'reached';
            displayText = 'DOSAŽEN LIMIT!';
        } else if (progressToNextLimit >= 90) {
            status = 'warning';
            displayText = `${progressToNextLimit.toFixed(1)}%`;
        } else {
            status = 'normal';
            displayText = `${progressToNextLimit.toFixed(1)}%`;
        }

        // Aktualizace progress baru
        const progressBar = document.getElementById('vatProgress');
        const progressText = document.getElementById('vatProgressText');
        const progressContainer = document.querySelector('.vat-progress');

        if (progressBar && progressText && progressContainer) {
            // Nastavení šířky progress baru (maximálně 100%)
            const displayProgress = Math.min(100, progressToNextLimit);
            progressBar.style.width = displayProgress + '%';

            // Vyčištění předchozích stavových tříd
            progressBar.classList.remove('limit-reached', 'limit-exceeded');
            progressContainer.classList.remove('complete', 'exceeded');

            // Aplikování nových stavů
            switch (status) {
                case 'exceeded':
                    progressBar.classList.add('limit-exceeded');
                    progressContainer.classList.add('exceeded');
                    progressBar.style.width = '100%';
                    break;
                case 'reached':
                    progressBar.classList.add('limit-reached');
                    progressContainer.classList.add('complete');
                    progressBar.style.width = '100%';
                    break;
                case 'warning':
                    // Žlutá pro blížící se limit
                    progressBar.style.background = 'linear-gradient(90deg, #ffc107, #e0a800)';
                    break;
                default:
                    // Normální zelená
                    progressBar.style.background = 'linear-gradient(90deg, #B1D235, #95B11F)';
            }

            progressText.textContent = displayText;
        }

        // Aktualizace číselných hodnot
        this.updateElement('currentTurnover', this.formatAmount(currentTurnover));
        this.updateElement('nextLimit', this.formatAmount(nextLimit));

        // Inteligentní zobrazení zbývající částky
        const remainingElement = document.getElementById('remainingToLimit');
        if (remainingElement) {
            if (currentTurnover >= nextLimit) {
                const exceeded = currentTurnover - nextLimit;
                remainingElement.innerHTML = `<span class="text-danger">překročeno o ${this.formatAmount(exceeded)}</span>`;

                // Změníme i text nad tím
                const remainingLabel = remainingElement.closest('small');
                if (remainingLabel) {
                    remainingLabel.innerHTML = remainingLabel.innerHTML.replace('zbývá:', 'překročeno o:');
                }
            } else {
                remainingElement.innerHTML = this.formatAmount(remainingAmount);
            }
        }

        // Aktualizace alertů pro DPH
        this.updateVatAlerts(vatLimits.alerts || []);
    }

    /**
     * Aktualizace DPH alertů s podporou zavírání
     */
    updateVatAlerts(alerts) {
        const alertContainer = document.getElementById('vatAlerts');
        if (!alertContainer) {
            this.log('⚠️ VAT alerts container nenalezen', 'warn');
            return;
        }

        // Vyčistí existující alerty
        alertContainer.innerHTML = '';

        if (!alerts || alerts.length === 0) {
            this.log('✅ Žádné DPH alerty k zobrazení', 'debug');
            return;
        }

        alerts.forEach(alert => {
            const alertElement = document.createElement('div');
            alertElement.className = `alert-financial alert-${alert.type} d-flex align-items-start`;
            alertElement.setAttribute('data-alert-id', alert.alert_id);
            
            const closeButton = alert.closable ? `
                <button type="button" 
                        class="btn-close-custom ms-auto" 
                        data-alert-id="${alert.alert_id}"
                        title="Zavřít upozornění"
                        aria-label="Zavřít">
                    ×
                </button>
            ` : '';

            alertElement.innerHTML = `
                <div class="me-3">
                    <i class="bi bi-${alert.type === 'warning' ? 'exclamation-triangle' : 'x-circle'}-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>${alert.title}</strong><br>
                    <span>${alert.message}</span>
                    <div class="mt-2 small text-muted">
                        Aktuální obrat: <strong>${this.formatAmount(alert.amount)}</strong> / 
                        Limit: <strong>${this.formatAmount(alert.limit)}</strong>
                    </div>
                </div>
                ${closeButton}
            `;

            alertContainer.appendChild(alertElement);
            
            this.log(`📋 Alert ${alert.alert_id} přidán do UI`, 'debug');
        });

        // Nastavení event listenerů na zavírací tlačítka
        this.setupCloseAlertListeners();
        
        this.log(`✅ Zobrazeno ${alerts.length} DPH alertů`, 'success');
    }

    /**
     * Nastavuje event listenery pro zavírání alertů
     */
    setupCloseAlertListeners() {
        const closeButtons = document.querySelectorAll('.btn-close-custom[data-alert-id]');
        closeButtons.forEach(button => {
            // Odebereme starý listener (pokud existuje) 
            button.removeEventListener('click', this.handleCloseAlert);
            
            // Přidáme nový listener
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const alertId = e.currentTarget.getAttribute('data-alert-id');
                if (alertId) {
                    this.closeAlert(alertId);
                }
            });
        });
        
        this.log(`🔧 Event listenery pro zavírání alertů nastaveny (${closeButtons.length} tlačítek)`, 'debug');
    }

    /**
     * Zavře DPH alert
     */
    async closeAlert(alertId) {
        this.log(`🔒 Zavírám alert: ${alertId}`, 'info');
        
        try {
            const userId = document.querySelector('meta[name="current-user-id"]')?.content;
            if (!userId) {
                throw new Error('ID uživatele není k dispozici');
            }

            // Sestavení URL pro closeAlert akci
            const ajaxUrl = this.buildAjaxUrl('closeAlert', {
                alertId: alertId,
                userId: userId
            });
            
            this.log(`🔗 Close Alert URL: ${ajaxUrl}`, 'debug');

            // AJAX request
            const response = await this.makeAjaxRequest(ajaxUrl);
            
            if (response.success) {
                // Úspěšné zavření - okamžitě skryj alert z UI
                this.hideAlert(alertId);
                this.log(`✅ Alert ${alertId} úspěšně zavřen`, 'success');
                
                // Zobrazíme krátkou zprávu o úspěchu
                this.showTemporaryMessage('Alert byl úspěšně zavřen', 'success');
            } else {
                throw new Error(response.message || 'Neznámá chyba při zavírání alertu');
            }
            
        } catch (error) {
            this.log(`❌ Chyba při zavírání alertu ${alertId}: ${error.message}`, 'error');
            this.showTemporaryMessage(`Nepodařilo se zavřít upozornění: ${error.message}`, 'error');
        }
    }

    /**
     * Skryje alert z UI
     */
    hideAlert(alertId) {
        // Hledáme alert element podle data-alert-id atributu
        const alertElement = document.querySelector(`[data-alert-id="${alertId}"]`);
        if (alertElement) {
            // Animace zmizení
            alertElement.style.transition = 'opacity 0.3s ease';
            alertElement.style.opacity = '0';
            
            setTimeout(() => {
                alertElement.remove();
                this.log(`🗑️ Alert ${alertId} odstraněn z UI`, 'debug');
            }, 300);
        } else {
            this.log(`⚠️ Alert element s ID ${alertId} nenalezen pro odebrání`, 'warn');
        }
    }

    /**
     * Zobrazí dočasnou zprávu
     */
    showTemporaryMessage(message, type = 'info') {
        const container = document.getElementById('vatAlerts');
        if (!container) return;
        
        const messageElement = document.createElement('div');
        messageElement.className = `alert-financial alert-${type === 'success' ? 'success' : 'danger'} d-flex align-items-center`;
        messageElement.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'x-circle'}-fill me-2"></i>
            <span>${message}</span>
        `;
        
        container.prepend(messageElement);
        
        // Auto-remove po 3 sekundách
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.style.transition = 'opacity 0.3s ease';
                messageElement.style.opacity = '0';
                setTimeout(() => messageElement.remove(), 300);
            }
        }, 3000);
    }

    /**
     * Animované čítače
     */
    animateCounters(elementIds) {
        elementIds.forEach(id => {
            const element = document.getElementById(id);
            if (!element) return;

            const targetValue = parseInt(element.textContent) || 0;
            let currentValue = 0;
            const increment = Math.ceil(targetValue / 30);
            
            const animation = setInterval(() => {
                currentValue += increment;
                if (currentValue >= targetValue) {
                    currentValue = targetValue;
                    clearInterval(animation);
                }
                element.textContent = currentValue;
            }, 50);
        });
    }

    /**
     * Aktualizace grafů (připraveno pro Chart.js)
     */
    async updateCharts(data) {
        // Placeholder pro budoucí implementaci grafů
        this.log('📈 Charts update - připraveno pro implementaci', 'debug');
    }

    /**
     * Refresh dat
     */
    async refreshData() {
        this.log('🔄 Refresh voláno', 'info');
        await this.loadRealData();
    }

    /**
     * Handling změny filtrů
     */
    handleFilterChange() {
        const year = document.getElementById('yearFilter')?.value;
        const month = document.getElementById('monthFilter')?.value;

        this.log(`🔍 Filter změna: rok=${year}, měsíc=${month}`, 'debug');

        // Zde můžeme implementovat filtrované načítání dat
        // this.loadFilteredData(year, month);
    }

    /**
     * Nastavení loading stavu
     */
    setLoadingState(loading) {
        const loadButton = document.getElementById('loadRealData');
        const refreshButton = document.getElementById('refreshData');
        const loadingIndicator = document.getElementById('loadingIndicator');

        if (loading) {
            if (loadButton) {
                loadButton.disabled = true;
                loadButton.innerHTML = '<span class="loading-spinner me-2"></span>Načítám...';
            }
            if (refreshButton) {
                refreshButton.disabled = true;
            }
            if (loadingIndicator) {
                loadingIndicator.style.display = 'block';
            }
        } else {
            if (loadButton) {
                loadButton.disabled = false;
            }
            if (refreshButton) {
                refreshButton.disabled = false;
            }
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
        }
    }

    /**
     * Nastavení success stavu
     */
    setSuccessState() {
        const loadButton = document.getElementById('loadRealData');
        const dataStatus = document.getElementById('dataStatus');
        const loadingIndicator = document.getElementById('loadingIndicator');

        if (loadButton) {
            loadButton.innerHTML = '<i class="bi bi-check"></i> Data načtena z databáze';
            loadButton.className = 'btn btn-success';
        }

        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
            loadingIndicator.className = 'd-flex align-items-center text-success';
            loadingIndicator.innerHTML = `
                <i class="bi bi-check-circle-fill me-2"></i>
                <span>Načteno</span>
            `;
        }

        if (dataStatus) {
            dataStatus.className = 'alert-financial alert-success d-flex align-items-center';
            dataStatus.style.display = 'block';
            dataStatus.innerHTML = `
                <i class="bi bi-check-circle-fill me-2"></i>
                Data byla úspěšně načtena z databáze!
            `;
        }
    }

    /**
     * Error handling
     */
    handleError(error) {
        this.log(`❌ Chyba: ${error.message}`, 'error', error);

        const loadButton = document.getElementById('loadRealData');
        const dataStatus = document.getElementById('dataStatus');

        if (loadButton) {
            loadButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Zkusit znovu';
            loadButton.className = 'btn btn-danger';
        }

        if (dataStatus) {
            dataStatus.className = 'alert-financial alert-danger d-flex align-items-start';
            dataStatus.style.display = 'block';
            dataStatus.innerHTML = `
                <i class="bi bi-x-circle-fill me-2 mt-1"></i>
                <div>
                    <strong>Chyba při načítání dat:</strong><br>
                    <span class="small">${error.message}</span><br>
                    <small class="text-muted mt-2 d-block">
                        Pro více informací otevřete Developer Tools (F12)
                    </small>
                </div>
            `;
        }

        // Fallback na mock data pokud je k dispozici
        this.loadMockDataFallback();
    }

    /**
     * Fallback mock data
     */
    loadMockDataFallback() {
        this.log('🎲 Načítám fallback mock data...', 'warn');

        const mockData = {
            stats: {
                totalCount: 15,
                paidCount: 10,
                unpaidCount: 5,
                overdueCount: 1,
                totalTurnover: 950000,
                paidAmount: 720000,
                unpaidAmount: 230000,
                year: new Date().getFullYear()
            },
            vatLimits: {
                currentTurnover: 950000,
                alerts: [],
                nextLimit: 2000000,
                progressToNextLimit: 47.5
            }
        };

        this.updateFinancialStats(mockData.stats);
        this.updateVatStatus(mockData.vatLimits);
    }

    /**
     * Utility metody
     */
    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
            // Přidej animaci pro zvýraznění změny
            element.classList.add('highlight');
            setTimeout(() => element.classList.remove('highlight'), 1000);
        } else {
            this.log(`⚠️ Element '${id}' nenalezen`, 'warn');
        }
    }

    formatAmount(amount) {
        return new Intl.NumberFormat('cs-CZ', {
            style: 'currency',
            currency: 'CZK',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    log(message, level = 'info', data = null) {
        const timestamp = new Date().toLocaleTimeString();
        const prefix = `[${timestamp}] FinancialReports`;

        switch (level) {
            case 'error':
                console.error(`${prefix} ❌`, message, data || '');
                break;
            case 'warn':
                console.warn(`${prefix} ⚠️`, message, data || '');
                break;
            case 'success':
                console.log(`${prefix} ✅`, message, data || '');
                break;
            case 'debug':
                console.debug(`${prefix} 🔍`, message, data || '');
                break;
            default:
                console.log(`${prefix} ℹ️`, message, data || '');
        }
    }

    /**
     * Veřejné API
     */
    getInfo() {
        return {
            name: this.moduleName,
            version: this.version,
            status: 'active',
            author: 'Allimedia.cz',
            tenantId: this.tenantId,
            isSuperAdmin: this.isSuperAdmin
        };
    }

    destroy() {
        // Cleanup při odstranění modulu
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
        this.log('🗑️ Modul byl odstraněn', 'info');
    }
}

// Automatická inicializace při načtení DOM
document.addEventListener('DOMContentLoaded', function () {
    console.log('🟢 DOM načten, inicializuji FinancialReports modul...');

    // Vytvoření a inicializace instance modulu
    const financialReports = new FinancialReportsModule();
    financialReports.init();

    // Globální přístup pro backwards compatibility a debugging
    window.FinancialReports = financialReports;

    console.log('🌟 FinancialReports modul je připraven:', window.FinancialReports.getInfo());
});

// Export pro ES6 modules (pokud je potřeba)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FinancialReportsModule;
}