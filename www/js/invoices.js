// Jednoduchý JavaScript pro dropdown v tabulkách
document.addEventListener('DOMContentLoaded', function() {
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
});