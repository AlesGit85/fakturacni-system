/**
 * Fakturační systém - Správa formulářů faktur
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace přepínání typů klientů
    initClientTypeToggle();
    
    // Inicializace položek faktury podle typu formuláře
    if (document.getElementById('add-item')) {
        initInvoiceItems();
    } else if (document.getElementById('simple-total')) {
        initSimpleInvoiceForm();
    }
});

/**
 * Inicializace přepínání mezi existujícím a ručně zadaným klientem
 */
function initClientTypeToggle() {
    const clientTypeRadios = document.querySelectorAll('input[name="client_type"]');
    const existingClientSection = document.getElementById('existing-client-section');
    const manualClientSection = document.getElementById('manual-client-section');
    
    if (!clientTypeRadios.length || !existingClientSection || !manualClientSection) {
        return; // Nic neděláme, pokud nejsme na správné stránce
    }
    
    function toggleClientSections() {
        const selectedType = document.querySelector('input[name="client_type"]:checked').value;
        
        if (selectedType === 'existing') {
            existingClientSection.style.display = 'block';
            manualClientSection.style.display = 'none';
        } else {
            existingClientSection.style.display = 'none';
            manualClientSection.style.display = 'block';
        }
    }
    
    // Inicializace při načtení stránky
    toggleClientSections();
    
    // Přidání posluchačů událostí pro přepínání
    clientTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleClientSections);
    });
}

/**
 * Inicializace položek faktury pro plátce DPH
 */
function initInvoiceItems() {
    const addItemButton = document.getElementById('add-item');
    if (!addItemButton) return;
    
    addItemButton.addEventListener('click', function() {
        const itemsContainer = document.getElementById('invoice-items');
        const itemCount = itemsContainer.querySelectorAll('.invoice-item').length;
        
        const newItem = createInvoiceItemElement(itemCount + 1);
        itemsContainer.appendChild(newItem);
        
        // Přidat posluchače událostí pro výpočet celkové ceny
        addCalculationListeners(newItem);
        
        // Přidat posluchač pro odebrání položky
        newItem.querySelector('.remove-item').addEventListener('click', function() {
            itemsContainer.removeChild(newItem);
            updateItemNumbers();
        });
    });
    
    // Přidat posluchače pro existující položky
    document.querySelectorAll('.invoice-item').forEach(item => {
        addCalculationListeners(item);
        
        // Přidat posluchač pro odebrání položky
        const removeButton = item.querySelector('.remove-item');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                item.parentNode.removeChild(item);
                updateItemNumbers();
            });
        }
    });
}

/**
 * Vytvoří element pro položku faktury
 */
function createInvoiceItemElement(itemNumber) {
    const itemIndex = itemNumber - 1;
    const newItem = document.createElement('div');
    newItem.className = 'invoice-item card mb-3';
    newItem.innerHTML = `
        <div class="card-header">
            Položka #${itemNumber}
            <button type="button" class="btn btn-sm btn-outline-danger float-end remove-item">Odebrat</button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Název položky</label>
                    <input type="text" name="items[${itemIndex}][name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Popis</label>
                    <input type="text" name="items[${itemIndex}][description]" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label">Množství</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" value="1" min="0.01" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jednotka</label>
                    <input type="text" name="items[${itemIndex}][unit]" class="form-control" value="ks" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cena/ks bez DPH</label>
                    <input type="number" name="items[${itemIndex}][price]" class="form-control item-price" min="0.01" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">DPH %</label>
                    <input type="number" name="items[${itemIndex}][vat]" class="form-control item-vat" value="21" min="0" max="100" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Celkem s DPH</label>
                    <input type="number" name="items[${itemIndex}][total]" class="form-control item-total" readonly>
                </div>
            </div>
        </div>
    `;
    return newItem;
}

/**
 * Aktualizace čísel položek
 */
function updateItemNumbers() {
    const items = document.querySelectorAll('.invoice-item');
    items.forEach((item, index) => {
        const header = item.querySelector('.card-header');
        if (header) {
            header.innerHTML = `Položka #${index + 1}<button type="button" class="btn btn-sm btn-outline-danger float-end remove-item">Odebrat</button>`;
        }
    });
}

/**
 * Přidání posluchačů událostí pro výpočet ceny
 */
function addCalculationListeners(item) {
    const quantityInput = item.querySelector('.item-quantity');
    const priceInput = item.querySelector('.item-price');
    const vatInput = item.querySelector('.item-vat');
    const totalInput = item.querySelector('.item-total');
    
    if (!quantityInput || !priceInput || !vatInput || !totalInput) return;
    
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

/**
 * Inicializace jednoduchého formuláře faktury pro neplátce DPH
 */
function initSimpleInvoiceForm() {
    const simpleTotal = document.getElementById('simple-total');
    const simplePrice = document.getElementById('simple-price');
    
    if (!simpleTotal || !simplePrice) return;
    
    // Nastavení ceny při načtení stránky
    if (simpleTotal.value) {
        simplePrice.value = simpleTotal.value;
    }
    
    // Když se změní celková částka, nastavíme ji také jako cenu/ks
    simpleTotal.addEventListener('input', function() {
        simplePrice.value = simpleTotal.value || 0;
    });
}