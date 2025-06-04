/**
 * Testovací modul - JavaScript funkcionalita
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Testovací modul - JavaScript načten');
    
    // Inicializace funkcí testovacího modulu
    initTestModule();
});

/**
 * Inicializace testovacího modulu
 */
function initTestModule() {
    const testButton = document.getElementById('testButton');
    const resetButton = document.getElementById('resetButton');
    const testResult = document.getElementById('testResult');
    
    if (testButton) {
        testButton.addEventListener('click', function() {
            runModuleTest();
        });
    }
    
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            resetModuleTest();
        });
    }
    
    // Automatický test při načtení
    setTimeout(() => {
        console.log('Testovací modul je připraven k použití');
    }, 1000);
}

/**
 * Spustí test modulu
 */
function runModuleTest() {
    const testButton = document.getElementById('testButton');
    const testResult = document.getElementById('testResult');
    
    // Změna tlačítka na loading stav
    testButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Testování...';
    testButton.disabled = true;
    
    // Simulace testu
    setTimeout(() => {
        // Zobrazení výsledku
        if (testResult) {
            testResult.style.display = 'block';
            testResult.innerHTML = `
                <div class="alert test-result-success mb-0">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <small><strong>Test úspěšný!</strong> Všechny funkce testovacího modulu fungují správně.</small>
                </div>
            `;
        }
        
        // Obnovení tlačítka
        testButton.innerHTML = '<i class="bi bi-check-circle"></i> Test dokončen';
        testButton.className = 'btn btn-success btn-sm mb-2 w-100';
        
        console.log('Test modulu úspěšně dokončen');
    }, 2000);
}

/**
 * Resetuje test modulu
 */
function resetModuleTest() {
    const testButton = document.getElementById('testButton');
    const testResult = document.getElementById('testResult');
    
    // Skrytí výsledku
    if (testResult) {
        testResult.style.display = 'none';
    }
    
    // Obnovení tlačítka
    testButton.innerHTML = '<i class="bi bi-play-circle"></i> Spustit test';
    testButton.className = 'btn btn-primary btn-sm mb-2 w-100';
    testButton.disabled = false;
    
    console.log('Test modulu byl resetován');
}

/**
 * Veřejná API funkce pro použití v jiných částech systému
 */
window.TestModule = {
    version: '1.0.0',
    
    getInfo: function() {
        return {
            name: 'Testovací modul',
            version: this.version,
            status: 'active'
        };
    },
    
    runTest: runModuleTest,
    reset: resetModuleTest
};