<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Invoices/add.latte */
final class Template_aebea78be5 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Invoices/add.latte';

	public const Blocks = [
		['content' => 'blockContent'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		$this->renderBlock('content', get_defined_vars()) /* line 1 */;
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['key' => '13', 'label' => '13'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block content} on line 1 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<h1>Vytvořit novou fakturu</h1>

<div class="row">
    <div class="col-md-12">
        ';
		$form = $this->global->formsStack[] = $this->global->uiControl['invoiceForm'] /* line 6 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 6 */;
		echo '
            <div class="card mb-4">
                <div class="card-header">Základní údaje</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Klient:</label>
                        <div class="mb-3">
';
		foreach ($form['client_type']->items as $key => $label) /* line 13 */ {
			echo '                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="client_type" id="client_type_';
			echo LR\Filters::escapeHtmlAttr($key) /* line 15 */;
			echo '" value="';
			echo LR\Filters::escapeHtmlAttr($key) /* line 15 */;
			echo '" ';
			if ($form['client_type']->value === $key) /* line 15 */ {
				echo 'checked';
			}
			echo '>
                                    <label class="form-check-label" for="client_type_';
			echo LR\Filters::escapeHtmlAttr($key) /* line 16 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($label) /* line 16 */;
			echo '</label>
                                </div>
';

		}

		echo '                        </div>
                    </div>
                    
                    <div id="existing-client-section">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_id', $this->global)->getLabel()) /* line 25 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_id', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 26 */;
		echo '
                                <div class="form-text"><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:add')) /* line 27 */;
		echo '" target="_blank">Přidat nového klienta</a></div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="manual-client-section" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_name', $this->global)->getLabel()) /* line 35 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 36 */;
		echo '
                            </div>
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_ic', $this->global)->getLabel()) /* line 39 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_ic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 40 */;
		echo '
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_address', $this->global)->getLabel()) /* line 45 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_address', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 46 */;
		echo '
                            </div>
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_dic', $this->global)->getLabel()) /* line 49 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_dic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 50 */;
		echo '
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_city', $this->global)->getLabel()) /* line 55 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_city', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 56 */;
		echo '
                            </div>
                            <div class="col-md-4">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_zip', $this->global)->getLabel()) /* line 59 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_zip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 60 */;
		echo '
                            </div>
                            <div class="col-md-4">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_country', $this->global)->getLabel()) /* line 63 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_country', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 64 */;
		echo '
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('number', $this->global)->getLabel()) /* line 74 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('number', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 75 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('issue_date', $this->global)->getLabel()) /* line 80 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('issue_date', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 81 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('due_date', $this->global)->getLabel()) /* line 86 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('due_date', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 87 */;
		echo '
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('payment_method', $this->global)->getLabel()) /* line 95 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('payment_method', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 96 */;
		echo '
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">Možnosti zobrazení</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3 form-check">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('qr_payment', $this->global)->getControl() /* line 109 */;
		echo ' ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('qr_payment', $this->global)->getLabel()) /* line 109 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 form-check">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('show_logo', $this->global)->getControl() /* line 114 */;
		echo ' ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('show_logo', $this->global)->getLabel()) /* line 114 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 form-check">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('show_signature', $this->global)->getControl() /* line 119 */;
		echo ' ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('show_signature', $this->global)->getLabel()) /* line 119 */;
		echo '
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
';
		if ($isVatPayer) /* line 126 */ {
			echo '            <!-- Formulář pro plátce DPH - s položkami a DPH -->
            <div class="card mb-4">
                <div class="card-header">
                    Položky faktury
                    <button type="button" id="add-item" class="btn btn-sm btn-primary float-end">Přidat položku</button>
                </div>
                <div class="card-body">
                    <div id="invoice-items">
                        <div class="invoice-item card mb-3">
                            <div class="card-header">
                                Položka #1
                                <button type="button" class="btn btn-sm btn-outline-danger float-end remove-item" style="display: none;">Odebrat</button>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Název položky</label>
                                        <input type="text" name="items[0][name]" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Popis</label>
                                        <input type="text" name="items[0][description]" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="form-label">Množství</label>
                                        <input type="number" name="items[0][quantity]" class="form-control item-quantity" value="1" min="0.01" step="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Jednotka</label>
                                        <input type="text" name="items[0][unit]" class="form-control" value="ks" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Cena/ks bez DPH</label>
                                        <input type="number" name="items[0][price]" class="form-control item-price" min="0.01" step="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">DPH %</label>
                                        <input type="number" name="items[0][vat]" class="form-control item-vat" value="21" min="0" max="100" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Celkem s DPH</label>
                                        <input type="number" name="items[0][total]" class="form-control item-total" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
';
		} else /* line 178 */ {
			echo '            <!-- Formulář pro neplátce DPH - zjednodušený bez DPH -->
            <div class="card mb-4">
                <div class="card-header">
                    Předmět fakturace
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Předmět fakturace</label>
                        <textarea name="items[0][name]" class="form-control" rows="3" required></textarea>
                        <input type="hidden" name="items[0][description]" value="">
                        <input type="hidden" name="items[0][quantity]" value="1">
                        <input type="hidden" name="items[0][unit]" value="ks">
                        <input type="hidden" name="items[0][vat]" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Částka (konečná)</label>
                        <div class="input-group">
                            <input type="number" name="items[0][total]" id="simple-total" class="form-control" min="0.01" step="0.01" value="0" required>
                            <span class="input-group-text">Kč</span>
                        </div>
                        <input type="hidden" name="items[0][price]" id="simple-price" value="0">
                    </div>
                </div>
            </div>
';
		}
		echo '            
            <div class="card mb-4">
                <div class="card-header">Poznámka</div>
                <div class="card-body">
                    <div class="mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('note', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 209 */;
		echo '
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary']) /* line 215 */;
		echo '
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 216 */;
		echo '" class="btn btn-secondary">Zpět na seznam faktur</a>
            </div>
        ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 218 */;

		echo '
    </div>
</div>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    // Přepínání mezi existujícím a ručně zadaným klientem
    const clientTypeRadios = document.querySelectorAll(\'input[name="client_type"]\');
    const existingClientSection = document.getElementById(\'existing-client-section\');
    const manualClientSection = document.getElementById(\'manual-client-section\');
    
    function toggleClientSections() {
        const selectedType = document.querySelector(\'input[name="client_type"]:checked\').value;
        
        if (selectedType === \'existing\') {
            existingClientSection.style.display = \'block\';
            manualClientSection.style.display = \'none\';
        } else {
            existingClientSection.style.display = \'none\';
            manualClientSection.style.display = \'block\';
        }
    }
    
    // Inicializace při načtení stránky
    toggleClientSections();
    
    // Přidání posluchačů událostí pro přepínání
    clientTypeRadios.forEach(radio => {
        radio.addEventListener(\'change\', toggleClientSections);
    });
    
    // Kód pro výpočet cen a manipulaci s položkami
    if (document.getElementById(\'add-item\')) {
        // Plátce DPH - dynamické přidávání položek faktury
        const addItemButton = document.getElementById(\'add-item\');
        addItemButton.addEventListener(\'click\', function() {
            const itemsContainer = document.getElementById(\'invoice-items\');
            const itemCount = itemsContainer.querySelectorAll(\'.invoice-item\').length;
            
            const newItem = document.createElement(\'div\');
            newItem.className = \'invoice-item card mb-3\';
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
            newItem.querySelector(\'.remove-item\').addEventListener(\'click\', function() {
                itemsContainer.removeChild(newItem);
                updateItemNumbers();
            });
        });
        
        // Aktualizace čísel položek
        function updateItemNumbers() {
            const items = document.querySelectorAll(\'.invoice-item\');
            items.forEach((item, index) => {
                const header = item.querySelector(\'.card-header\');
                if (header) {
                    header.innerHTML = `Položka #${index + 1}<button type="button" class="btn btn-sm btn-outline-danger float-end remove-item">Odebrat</button>`;
                }
            });
        }
        
        // Přidání posluchačů událostí pro výpočet ceny
        function addCalculationListeners(item) {
            const quantityInput = item.querySelector(\'.item-quantity\');
            const priceInput = item.querySelector(\'.item-price\');
            const vatInput = item.querySelector(\'.item-vat\');
            const totalInput = item.querySelector(\'.item-total\');
            
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
            
            quantityInput.addEventListener(\'input\', calculateTotal);
            priceInput.addEventListener(\'input\', calculateTotal);
            vatInput.addEventListener(\'input\', calculateTotal);
        }
        
        // Přidat posluchače pro existující položky
        document.querySelectorAll(\'.invoice-item\').forEach(item => {
            addCalculationListeners(item);
            
            // Přidat posluchač pro odebrání položky
            const removeButton = item.querySelector(\'.remove-item\');
            if (removeButton) {
                removeButton.addEventListener(\'click\', function() {
                    item.parentNode.removeChild(item);
                    updateItemNumbers();
                });
            }
        });
    } else if (document.getElementById(\'simple-total\')) {
        // Neplátce DPH - zjednodušený formulář
        const simpleTotal = document.getElementById(\'simple-total\');
        const simplePrice = document.getElementById(\'simple-price\');
        
        // Nastavení ceny při načtení stránky
        if (simpleTotal.value) {
            simplePrice.value = simpleTotal.value;
        }
        
        // Když se změní celková částka, nastavíme ji také jako cenu/ks
        simpleTotal.addEventListener(\'input\', function() {
            simplePrice.value = simpleTotal.value || 0;
        });
    }
});
</script>
';
	}
}
