<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Invoices/edit.latte */
final class Template_6b34de3fa4 extends Latte\Runtime\Template
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
			foreach (array_intersect_key(['i' => '231', 'item' => '231'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="invoice-form-container">
    <div class="page-header">
        <h1 class="main-title">Upravit fakturu</h1>
        <p class="text-muted">Upravte údaje faktury podle potřeby</p>
    </div>

    <div class="row">
        <div class="col-md-12">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['invoiceForm'] /* line 12 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 12 */;
		echo '
                <!-- Výběr klienta -->
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header">
                        <i class="bi bi-person-lines-fill me-2"></i>
                        <h3>Klient</h3>
                    </div>
                    <div class="card-body">
                        <div class="client-type-selector mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="radio" name="client_type" id="client_type_existing" value="existing" ';
		if ($form['client_type']->value === 'existing') /* line 25 */ {
			echo 'checked';
		}
		echo ' class="client-type-radio">
                                    <label for="client_type_existing" class="client-type-option">
                                        <div class="option-content">
                                            <div class="option-title">Existující klient</div>
                                            <div class="option-description">Vybrat z databáze klientů</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" name="client_type" id="client_type_manual" value="manual" ';
		if ($form['client_type']->value === 'manual') /* line 36 */ {
			echo 'checked';
		}
		echo ' class="client-type-radio">
                                    <label for="client_type_manual" class="client-type-option">
                                        <div class="option-content">
                                            <div class="option-title">Zadat ručně</div>
                                            <div class="option-description">Vyplnit údaje klienta</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="existing-client-section">
                            <div class="form-floating">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_id', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 51 */;
		echo '
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_id', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 52 */;
		echo '
                            </div>
                        </div>
                        
                        <div id="manual-client-section" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 61 */;
		echo '
                                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 62 */;
		echo '
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_ic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 68 */;
		echo '
                                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_ic', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 69 */;
		echo '
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_address', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 75 */;
		echo '
                                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_address', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 76 */;
		echo '
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_dic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 82 */;
		echo '
                                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_dic', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 83 */;
		echo '
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_city', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 89 */;
		echo '
                                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_city', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 90 */;
		echo '
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_zip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 96 */;
		echo '
                                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_zip', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 97 */;
		echo '
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('client_country', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 103 */;
		echo '
                                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('client_country', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 104 */;
		echo '
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Základní údaje faktury -->
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        <h3>Základní údaje faktury</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('number', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 124 */;
		echo '
                                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('number', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 125 */;
		echo '
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('issue_date', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 131 */;
		echo '
                                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('issue_date', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 132 */;
		echo '
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('due_date', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 138 */;
		echo '
                                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('due_date', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 139 */;
		echo '
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('payment_method', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 145 */;
		echo '
                                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('payment_method', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 146 */;
		echo '
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Možnosti zobrazení -->
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header">
                        <i class="bi bi-gear me-2"></i>
                        <h3>Možnosti zobrazení</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="modern-checkbox-wrapper">
                                    <input type="checkbox" id="qr_payment_checkbox" name="qr_payment" value="1" ';
		if ($form['qr_payment']->value) /* line 165 */ {
			echo 'checked';
		}
		echo ' class="modern-checkbox">
                                    <label for="qr_payment_checkbox" class="modern-checkbox-label">
                                        <div class="checkbox-content">
                                            <i class="bi bi-qr-code checkbox-icon"></i>
                                            <div class="checkbox-text">
                                                <div class="checkbox-title">QR kód pro platbu</div>
                                                <div class="checkbox-description">Generovat QR kód</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="modern-checkbox-wrapper">
                                    <input type="checkbox" id="show_logo_checkbox" name="show_logo" value="1" ';
		if ($form['show_logo']->value) /* line 181 */ {
			echo 'checked';
		}
		echo ' class="modern-checkbox">
                                    <label for="show_logo_checkbox" class="modern-checkbox-label">
                                        <div class="checkbox-content">
                                            <i class="bi bi-image checkbox-icon"></i>
                                            <div class="checkbox-text">
                                                <div class="checkbox-title">Zobrazit logo</div>
                                                <div class="checkbox-description">Logo na faktuře</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="modern-checkbox-wrapper">
                                    <input type="checkbox" id="show_signature_checkbox" name="show_signature" value="1" ';
		if ($form['show_signature']->value) /* line 197 */ {
			echo 'checked';
		}
		echo ' class="modern-checkbox">
                                    <label for="show_signature_checkbox" class="modern-checkbox-label">
                                        <div class="checkbox-content">
                                            <i class="bi bi-pen checkbox-icon"></i>
                                            <div class="checkbox-text">
                                                <div class="checkbox-title">Zobrazit podpis</div>
                                                <div class="checkbox-description">Podpis na faktuře</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
';
		if ($isVatPayer) /* line 214 */ {
			echo '                <!-- Formulář pro plátce DPH - s položkami a DPH -->
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-list-ul me-2"></i>
                            <h3 class="d-inline">Položky faktury</h3>
                        </div>
                        <button type="button" id="add-item" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Přidat položku
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="invoice-items">
';
			if ($invoiceItems->count() > 0) /* line 230 */ {
				foreach ($invoiceItems as $i => $item) /* line 231 */ {
					echo '                                    <div class="invoice-item card mb-3">
                                        <div class="card-header">
                                            Položka #';
					echo LR\Filters::escapeHtmlText($i + 1) /* line 235 */;
					echo '
                                            <button type="button" class="btn btn-sm btn-outline-danger float-end remove-item">Odebrat</button>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Název položky</label>
                                                    <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 244 */;
					echo '][name]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($item->name)) /* line 244 */;
					echo '" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Popis</label>
                                                    <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 250 */;
					echo '][description]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($item->description)) /* line 250 */;
					echo '">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <label class="form-label">Množství</label>
                                                    <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 258 */;
					echo '][quantity]" class="form-control item-quantity" value="';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($item->quantity)) /* line 258 */;
					echo '" min="0.01" step="0.01" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Jednotka</label>
                                                    <input type="text" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 264 */;
					echo '][unit]" class="form-control" value="';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($item->unit)) /* line 264 */;
					echo '" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Cena/ks bez DPH</label>
                                                    <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 270 */;
					echo '][price]" class="form-control item-price" value="';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($item->price)) /* line 270 */;
					echo '" min="0.01" step="0.01" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">DPH %</label>
                                                    <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 276 */;
					echo '][vat]" class="form-control item-vat" value="';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($item->vat)) /* line 276 */;
					echo '" min="0" max="100" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Celkem s DPH</label>
                                                    <input type="number" name="items[';
					echo LR\Filters::escapeHtmlAttr($i) /* line 282 */;
					echo '][total]" class="form-control item-total" value="';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($item->total)) /* line 282 */;
					echo '" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
';

				}

			} else /* line 288 */ {
				echo '                                <div class="invoice-item card mb-3">
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
			echo '                        </div>
                    </div>
                </div>
';
		} else /* line 348 */ {
			echo '                <!-- Formulář pro neplátce DPH - zjednodušený bez DPH -->
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header">
                        <i class="bi bi-list-ul me-2"></i>
                        <h3>Předmět fakturace</h3>
                    </div>
                    <div class="card-body">
';
			$firstItem = $invoiceItems->count() > 0 ? $invoiceItems->fetch() : null /* line 357 */;
			echo '                        <div class="mb-3">
                            <label class="form-label">Předmět fakturace</label>
                            <textarea name="items[0][name]" class="form-control" rows="3" required>';
			if ($firstItem) /* line 362 */ {
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($firstItem->name)) /* line 362 */;
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
			if ($firstItem) /* line 374 */ {
				echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($firstItem->total)) /* line 374 */;
			} else /* line 374 */ {
				echo '0';
			}
			echo '">
                                <span class="input-group-text">Kč</span>
                            </div>
                            <input type="hidden" name="items[0][price]" id="simple-price" 
                                value="';
			if ($firstItem) /* line 379 */ {
				echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($firstItem->price)) /* line 379 */;
			} else /* line 379 */ {
				echo '0';
			}
			echo '">
                        </div>
                    </div>
                </div>
';
		}
		echo '                
                <!-- Poznámka -->
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header">
                        <i class="bi bi-chat-left-text me-2"></i>
                        <h3>Poznámka</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-floating">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('note', $this->global)->getControl()->addAttributes(['class' => 'form-control', 'style' => 'height: 100px']) /* line 395 */;
		echo '
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('note', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 396 */;
		echo '
                        </div>
                    </div>
                </div>
                
                <!-- Akční tlačítka -->
                <div class="action-buttons-container">
                    <div class="d-flex justify-content-between">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 405 */;
		echo '" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Zpět na seznam faktur
                        </a>
                        <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 409 */;
		echo ' class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Uložit změny
                        </button>
                    </div>
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 414 */;

		echo '
        </div>
    </div>
</div>
';
	}
}
