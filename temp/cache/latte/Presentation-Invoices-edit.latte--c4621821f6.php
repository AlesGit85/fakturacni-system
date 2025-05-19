<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Invoices/edit.latte */
final class Template_c4621821f6 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Invoices/edit.latte';

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
			foreach (array_intersect_key(['i' => '66', 'item' => '66'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<h1>Upravit fakturu</h1>

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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_id', $this->global)->getLabel()) /* line 13 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_id', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 14 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('number', $this->global)->getLabel()) /* line 19 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('number', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 20 */;
		echo '
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('issue_date', $this->global)->getLabel()) /* line 28 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('issue_date', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 29 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('due_date', $this->global)->getLabel()) /* line 34 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('due_date', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 35 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('payment_method', $this->global)->getLabel()) /* line 40 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('payment_method', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 41 */;
		echo '
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3 form-check">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('qr_payment', $this->global)->getControl() /* line 49 */;
		echo ' ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('qr_payment', $this->global)->getLabel()) /* line 49 */;
		echo '
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
';
		if ($isVatPayer) /* line 56 */ {
			echo '            <!-- Formulář pro plátce DPH - s položkami a DPH -->
            <div class="card mb-4">
                <div class="card-header">
                    Položky faktury
                    <button type="button" id="add-item" class="btn btn-sm btn-primary float-end">Přidat položku</button>
                </div>
                <div class="card-body">
                    <div id="invoice-items">
';
			if ($invoiceItems->count() > 0) /* line 65 */ {
				foreach ($invoiceItems as $i => $item) /* line 66 */ {
					echo '                                <div class="invoice-item card mb-3">
                                    <div class="card-header">
                                        Položka #';
					echo LR\Filters::escapeHtmlText($i + 1) /* line 69 */;
					echo '
                                        <button type="button" class="btn btn-sm btn-outline-danger float-end remove-item">Odebrat</button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Název položky</label>
                                                <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 76 */;
					echo '][name]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr($item->name) /* line 76 */;
					echo '" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Popis</label>
                                                <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 80 */;
					echo '][description]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr($item->description) /* line 80 */;
					echo '">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <label class="form-label">Množství</label>
                                                <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 86 */;
					echo '][quantity]" class="form-control item-quantity" value="';
					echo LR\Filters::escapeHtmlAttr($item->quantity) /* line 86 */;
					echo '" min="0.01" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Jednotka</label>
                                                <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 90 */;
					echo '][unit]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr($item->unit) /* line 90 */;
					echo '" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cena/ks bez DPH</label>
                                                <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 94 */;
					echo '][price]" class="form-control item-price" value="';
					echo LR\Filters::escapeHtmlAttr($item->price) /* line 94 */;
					echo '" min="0.01" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">DPH %</label>
                                                <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 98 */;
					echo '][vat]" class="form-control item-vat" value="';
					echo LR\Filters::escapeHtmlAttr($item->vat) /* line 98 */;
					echo '" min="0" max="100" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Celkem s DPH</label>
                                                <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 102 */;
					echo '][total]" class="form-control item-total" value="';
					echo LR\Filters::escapeHtmlAttr($item->total) /* line 102 */;
					echo '" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
';

				}

			} else /* line 108 */ {
				echo '                            <div class="invoice-item card mb-3">
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
';
			}
			echo '                    </div>
                </div>
            </div>
';
		} else /* line 153 */ {
			echo '            <!-- Formulář pro neplátce DPH - zjednodušený bez DPH -->
            <div class="card mb-4">
                <div class="card-header">
                    Předmět fakturace
                </div>
                <div class="card-body">
';
			$firstItem = $invoiceItems->count() > 0 ? $invoiceItems->fetch() : null /* line 160 */;
			echo '                    <div class="mb-3">
                        <label class="form-label">Předmět fakturace</label>
                        <textarea name="items[0][name]" class="form-control" rows="3" required>';
			if ($firstItem) /* line 163 */ {
				echo LR\Filters::escapeHtmlText($firstItem->name) /* line 163 */;
			}
			echo '</textarea>
                        <input type="hidden" name="items[0][description]" value="">
                        <input type="hidden" name="items[0][quantity]" value="1">
                        <input type="hidden" name="items[0][unit]" value="ks">
                        <input type="hidden" name="items[0][vat]" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Částka (konečná)</label>
                        <div class="input-group">
                            <input type="number" name="items[0][total]" id="simple-total" class="form-control" min="0.01" step="0.01" required 
                                value="';
			if ($firstItem) /* line 173 */ {
				echo LR\Filters::escapeHtmlAttr($firstItem->total) /* line 173 */;
			} else /* line 173 */ {
				echo '0';
			}
			echo '">
                            <span class="input-group-text">Kč</span>
                        </div>
                        <input type="hidden" name="items[0][price]" id="simple-price" 
                            value="';
			if ($firstItem) /* line 177 */ {
				echo LR\Filters::escapeHtmlAttr($firstItem->price) /* line 177 */;
			} else /* line 177 */ {
				echo '0';
			}
			echo '">
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
		echo Nette\Bridges\FormsLatte\Runtime::item('note', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 187 */;
		echo '
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary']) /* line 193 */;
		echo '
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 194 */;
		echo '" class="btn btn-secondary">Zpět na seznam faktur</a>
            </div>
        ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 196 */;

		echo '
    </div>
</div>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
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
    } else {
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
