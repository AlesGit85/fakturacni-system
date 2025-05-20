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
			foreach (array_intersect_key(['key' => '13', 'label' => '13', 'i' => '135', 'item' => '135'], $this->params) as $ʟ_v => $ʟ_l) {
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
                            </div>
                        </div>
                    </div>
                    
                    <div id="manual-client-section" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_name', $this->global)->getLabel()) /* line 34 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 35 */;
		echo '
                            </div>
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_ic', $this->global)->getLabel()) /* line 38 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_ic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 39 */;
		echo '
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_address', $this->global)->getLabel()) /* line 44 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_address', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 45 */;
		echo '
                            </div>
                            <div class="col-md-6">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_dic', $this->global)->getLabel()) /* line 48 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_dic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 49 */;
		echo '
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_city', $this->global)->getLabel()) /* line 54 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_city', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 55 */;
		echo '
                            </div>
                            <div class="col-md-4">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_zip', $this->global)->getLabel()) /* line 58 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_zip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 59 */;
		echo '
                            </div>
                            <div class="col-md-4">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_country', $this->global)->getLabel()) /* line 62 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_country', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 63 */;
		echo '
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('number', $this->global)->getLabel()) /* line 73 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('number', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 74 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('issue_date', $this->global)->getLabel()) /* line 79 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('issue_date', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 80 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('due_date', $this->global)->getLabel()) /* line 85 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('due_date', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 86 */;
		echo '
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('payment_method', $this->global)->getLabel()) /* line 94 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('payment_method', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 95 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('qr_payment', $this->global)->getControl() /* line 108 */;
		echo ' ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('qr_payment', $this->global)->getLabel()) /* line 108 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 form-check">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('show_logo', $this->global)->getControl() /* line 113 */;
		echo ' ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('show_logo', $this->global)->getLabel()) /* line 113 */;
		echo '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 form-check">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('show_signature', $this->global)->getControl() /* line 118 */;
		echo ' ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('show_signature', $this->global)->getLabel()) /* line 118 */;
		echo '
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
';
		if ($isVatPayer) /* line 125 */ {
			echo '            <!-- Formulář pro plátce DPH - s položkami a DPH -->
            <div class="card mb-4">
                <div class="card-header">
                    Položky faktury
                    <button type="button" id="add-item" class="btn btn-sm btn-primary float-end">Přidat položku</button>
                </div>
                <div class="card-body">
                    <div id="invoice-items">
';
			if ($invoiceItems->count() > 0) /* line 134 */ {
				foreach ($invoiceItems as $i => $item) /* line 135 */ {
					echo '                                <div class="invoice-item card mb-3">
                                    <div class="card-header">
                                        Položka #';
					echo LR\Filters::escapeHtmlText($i + 1) /* line 138 */;
					echo '
                                        <button type="button" class="btn btn-sm btn-outline-danger float-end remove-item">Odebrat</button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Název položky</label>
                                                <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 145 */;
					echo '][name]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr($item->name) /* line 145 */;
					echo '" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Popis</label>
                                                <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 149 */;
					echo '][description]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr($item->description) /* line 149 */;
					echo '">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <label class="form-label">Množství</label>
                                                <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 155 */;
					echo '][quantity]" class="form-control item-quantity" value="';
					echo LR\Filters::escapeHtmlAttr($item->quantity) /* line 155 */;
					echo '" min="0.01" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Jednotka</label>
                                                <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 159 */;
					echo '][unit]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr($item->unit) /* line 159 */;
					echo '" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cena/ks bez DPH</label>
                                                <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 163 */;
					echo '][price]" class="form-control item-price" value="';
					echo LR\Filters::escapeHtmlAttr($item->price) /* line 163 */;
					echo '" min="0.01" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">DPH %</label>
                                                <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 167 */;
					echo '][vat]" class="form-control item-vat" value="';
					echo LR\Filters::escapeHtmlAttr($item->vat) /* line 167 */;
					echo '" min="0" max="100" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Celkem s DPH</label>
                                                <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 171 */;
					echo '][total]" class="form-control item-total" value="';
					echo LR\Filters::escapeHtmlAttr($item->total) /* line 171 */;
					echo '" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
';

				}

			} else /* line 177 */ {
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
		} else /* line 222 */ {
			echo '            <!-- Formulář pro neplátce DPH - zjednodušený bez DPH -->
            <div class="card mb-4">
                <div class="card-header">
                    Předmět fakturace
                </div>
                <div class="card-body">
';
			$firstItem = $invoiceItems->count() > 0 ? $invoiceItems->fetch() : null /* line 229 */;
			echo '                    <div class="mb-3">
                        <label class="form-label">Předmět fakturace</label>
                        <textarea name="items[0][name]" class="form-control" rows="3" required>';
			if ($firstItem) /* line 232 */ {
				echo LR\Filters::escapeHtmlText($firstItem->name) /* line 232 */;
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
			if ($firstItem) /* line 242 */ {
				echo LR\Filters::escapeHtmlAttr($firstItem->total) /* line 242 */;
			} else /* line 242 */ {
				echo '0';
			}
			echo '">
                            <span class="input-group-text">Kč</span>
                        </div>
                        <input type="hidden" name="items[0][price]" id="simple-price" 
                            value="';
			if ($firstItem) /* line 246 */ {
				echo LR\Filters::escapeHtmlAttr($firstItem->price) /* line 246 */;
			} else /* line 246 */ {
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
		echo Nette\Bridges\FormsLatte\Runtime::item('note', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 256 */;
		echo '
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary']) /* line 262 */;
		echo '
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 263 */;
		echo '" class="btn btn-secondary">Zpět na seznam faktur</a>
            </div>
        ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 265 */;

		echo '
    </div>
</div>
';
	}
}
