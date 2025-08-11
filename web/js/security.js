/**
 * Security Dashboard JavaScript
 * Jednoduchá funkcionalita konzistentní s aplikací
 */

document.addEventListener('DOMContentLoaded', function() {
    // Jednoduchá inicializace
    initSecurityDashboard();
});

/**
 * Inicializace Security Dashboard
 */
function initSecurityDashboard() {
    // Inicializace tooltipů (pokud existují)
    initTooltips();
    
    console.log('Security Dashboard inicializován');
}

/**
 * Inicializace tooltipů
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (tooltipTriggerList.length > 0) {
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Zobrazení detailních security logů
 */
function showSecurityLogs() {
    const modalElement = document.getElementById('securityLogsModal');
    if (!modalElement) return;
    
    const modal = new bootstrap.Modal(modalElement);
    const content = document.getElementById('securityLogsContent');
    
    // Zobraz modal
    modal.show();
    
    // Resetuj obsah na loading stav
    if (content) {
        content.innerHTML = `
            <div class="security-loading">
                <div class="spinner-border" role="status"></div>
                <p>Načítám detailní security logy...</p>
            </div>
        `;
        
        // Načti obsah po krátké pauze
        setTimeout(() => {
            loadSecurityLogsContent();
        }, 1000);
    }
}

/**
 * Načtení obsahu security logů
 */
function loadSecurityLogsContent() {
    const content = document.getElementById('securityLogsContent');
    if (!content) return;
    
    // Zobrazíme informaci o připravované funkcionalitě
    content.innerHTML = `
        <div class="alert alert-info border-0">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle me-3" style="font-size: 1.5rem;"></i>
                <div>
                    <h6 class="mb-1">Detailní Security Logs</h6>
                    <p class="mb-0">Tato funkcionalita bude k dispozici v příští verzi. 
                    Zatím můžete použít základní přehled událostí na dashboardu.</p>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0" style="background: #f8f9fa;">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-clock-history text-muted" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-1">Real-time monitoring</h6>
                        <small class="text-muted">V přípravě</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0" style="background: #f8f9fa;">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-funnel text-muted" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-1">Pokročilé filtry</h6>
                        <small class="text-muted">V přípravě</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0" style="background: #f8f9fa;">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-download text-muted" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-1">Export dat</h6>
                        <small class="text-muted">V přípravě</small>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Utility funkce pro formatování času
 */
function formatTime(timestamp) {
    return new Intl.DateTimeFormat('cs-CZ', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }).format(new Date(timestamp));
}