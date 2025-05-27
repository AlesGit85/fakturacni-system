/**
 * Fakturační systém - Správa modulů
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace toggle přepínačů
    initModuleToggles();
    
    // Inicializace dropdown menu
    initModuleDropdowns();
    
    // Inicializace permanent alertů
    initPermanentAlerts();
});

/**
 * Inicializace toggle přepínačů pro moduly
 */
function initModuleToggles() {
    // PHP Debug toggle (s localStorage)
    const debugToggle = document.getElementById('debugToggle');
    const debugContent = document.getElementById('debugContent');
    
    if (debugToggle && debugContent) {
        // Načtení uloženého stavu z localStorage
        const savedState = localStorage.getItem('moduleAdminDebugVisible');
        const isVisible = savedState === 'true';
        
        // Nastavení výchozího stavu
        debugToggle.checked = isVisible;
        if (isVisible) {
            debugContent.style.display = 'block';
            debugContent.classList.add('show');
        }
        
        // Event listener pro debug toggle
        debugToggle.addEventListener('change', function() {
            const isChecked = this.checked;
            
            // Uložení stavu do localStorage
            localStorage.setItem('moduleAdminDebugVisible', isChecked.toString());
            
            if (isChecked) {
                // Zobrazení s animací
                debugContent.style.display = 'block';
                debugContent.classList.remove('hide');
                debugContent.classList.add('show');
            } else {
                // Skrytí s animací
                debugContent.classList.remove('show');
                debugContent.classList.add('hide');
                
                // Skrytí po animaci
                setTimeout(() => {
                    if (!debugToggle.checked) {
                        debugContent.style.display = 'none';
                    }
                }, 300);
            }
        });
    }

    // Upload toggle (bez localStorage - vždy začíná skrytý)
    const uploadToggle = document.getElementById('uploadToggle');
    const uploadContent = document.getElementById('uploadContent');
    
    if (uploadToggle && uploadContent) {
        // Výchozí stav je vždy skrytý
        uploadToggle.checked = false;
        uploadContent.style.display = 'none';
        
        // Event listener pro upload toggle
        uploadToggle.addEventListener('change', function() {
            const isChecked = this.checked;
            
            if (isChecked) {
                // Zobrazení s animací
                uploadContent.style.display = 'block';
                uploadContent.classList.remove('hide');
                uploadContent.classList.add('show');
            } else {
                // Skrytí s animací
                uploadContent.classList.remove('show');
                uploadContent.classList.add('hide');
                
                // Skrytí po animaci
                setTimeout(() => {
                    if (!uploadToggle.checked) {
                        uploadContent.style.display = 'none';
                    }
                }, 300);
            }
        });
    }
}

/**
 * Inicializace dropdown menu v tabulce modulů
 */
function initModuleDropdowns() {
    const dropdownToggles = document.querySelectorAll('.data-table .dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const row = this.closest('tr');
            const tableBody = this.closest('tbody');
            const allRows = Array.from(tableBody.querySelectorAll('tr'));
            const rowIndex = allRows.indexOf(row);
            const totalRows = allRows.length;
            
            // Pokud je to jeden z posledních dvou řádků, přidáme CSS třídu
            if (rowIndex >= totalRows - 2) {
                row.classList.add('show-dropdown-up');
            } else {
                row.classList.remove('show-dropdown-up');
            }
        });
        
        // Při zavření dropdown odebereme třídu
        toggle.addEventListener('hidden.bs.dropdown', function() {
            const row = this.closest('tr');
            row.classList.remove('show-dropdown-up');
        });
    });
    
    // Odebereme třídu při kliknutí mimo
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('tr.show-dropdown-up').forEach(row => {
                row.classList.remove('show-dropdown-up');
            });
        }
    });
}

/**
 * Inicializace permanent alertů
 */
function initPermanentAlerts() {
    // Zabránění automatickému skrývání permanent alertů
    const permanentAlerts = document.querySelectorAll('.permanent-alert');
    permanentAlerts.forEach(alert => {
        // Přidáme data atribut aby main.js věděl, že se nemá skrývat
        alert.setAttribute('data-permanent', 'true');
    });
}