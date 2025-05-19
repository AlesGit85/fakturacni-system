document.addEventListener('DOMContentLoaded', function() {
    // Dynamické přidávání položek faktury
    const addItemButton = document.getElementById('add-item');
    if (addItemButton) {
        addItemButton.addEventListener('click', function() {
            const itemsContainer = document.getElementById('invoice-items');
            const itemCount = itemsContainer.querySelectorAll('.invoice-item').length;
            
            const newItem = document.createElement('div');
            newItem.className = 'invoice-item card mb-3';
            newItem.innerHTML = `
                <div class="card-header">
                    Položka #${itemCount + 1}
                    <button type="button" class="btn btn-sm btn-outline-danger float-end remove-item">Odebrat</button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Název položky</label>
                            <input type="text" name="items[${itemCount}][name]" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Popis</label>
                            <input type="text" name="items[${itemCount}][description]" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label class="form-label">Množství</label>
                            <input type="number" name="items[${itemCount}][quantity]" class="form-control item-quantity" value="1" min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jednotka</label>
                            <input type="text" name="items[${itemCount}][unit]" class="form-control" value="ks" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cena/ks bez DPH</label>
                            <input type="number" name="items[${itemCount}][price]" class="form-control item-price" min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">DPH %</label>
                            <input type="number" name="items[${itemCount}][vat]" class="form-control item-vat" value="21" min="0" max="100" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Celkem s DPH</label>
                            <input type="number" name="items[${itemCount}][total]" class="form-control item-total" readonly>
                        </div>
                    </div>
                </div>
            `;
            
            itemsContainer.appendChild(newItem);
            
            // Přidat posluchače událostí pro výpočet celkové ceny
            addCalculationListeners(newItem);
            
            // Přidat posluchač pro odebrání položky
            newItem.querySelector('.remove-item').addEventListener('click', function() {
                itemsContainer.removeChild(newItem);
                updateItemNumbers();
            });
        });
    }
    
    // Aktualizace čísel položek
    function updateItemNumbers() {
        const items = document.querySelectorAll('.invoice-item');
        items.forEach((item, index) => {
            item.querySelector('.card-header').innerText = `Položka #${index + 1}`;
            // Aktualizace názvů vstupních polí zde není potřeba, protože se 
            // odesílají jako pole a PHP si s tím poradí
        });
    }
    
    // Přidání posluchačů událostí pro výpočet ceny
    function addCalculationListeners(item) {
        const quantityInput = item.querySelector('.item-quantity');
        const priceInput = item.querySelector('.item-price');
        const vatInput = item.querySelector('.item-vat');
        const totalInput = item.querySelector('.item-total');
        
        const calculateTotal = function() {
            if (quantityInput.value && priceInput.value && vatInput.value) {
                const quantity = parseFloat(quantityInput.value);
                const price = parseFloat(priceInput.value);
                const vat = parseFloat(vatInput.value);
                
                const totalWithoutVat = quantity * price;
                const totalWithVat = totalWithoutVat * (1 + vat / 100);
                
                totalInput.value = totalWithVat.toFixed(2);
            }
        };
        
        quantityInput.addEventListener('input', calculateTotal);
        priceInput.addEventListener('input', calculateTotal);
        vatInput.addEventListener('input', calculateTotal);
    }
    
    // Přidat posluchače pro existující položky
    document.querySelectorAll('.invoice-item').forEach(item => {
        addCalculationListeners(item);
        
        // Přidat posluchač pro odebrání položky
        item.querySelector('.remove-item')?.addEventListener('click', function() {
            item.parentNode.removeChild(item);
            updateItemNumbers();
        });
    });
});