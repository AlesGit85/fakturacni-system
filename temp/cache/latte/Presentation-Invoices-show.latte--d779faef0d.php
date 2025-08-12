<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Invoices/show.latte */
final class Template_d779faef0d extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Invoices/show.latte';

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
			foreach (array_intersect_key(['item' => '279'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="invoice-detail-container">
    <!-- Záhlaví stránky -->
    <div class="page-header mb-4">
        <div class="header-content">
            <h1 class="main-title mb-2">Faktura ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->number)) /* line 7 */;
		echo '</h1>
            <p class="text-muted">
                Detail faktury • Vystaveno: ';
		echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->issue_date, 'd.m.Y')) /* line 10 */;
		echo ' • Splatnost: ';
		echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->due_date, 'd.m.Y')) /* line 10 */;
		echo '
            </p>
        </div>
        <div class="header-actions">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('pdf', [$invoice->id])) /* line 15 */;
		echo '" class="btn btn-primary">
                <i class="bi bi-file-pdf"></i> Stáhnout PDF
            </a>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$invoice->id])) /* line 18 */;
		echo '" class="btn btn-outline-dark">
                <i class="bi bi-pencil-square"></i> Upravit
            </a>
        </div>
    </div>

    <!-- Status faktury -->
    <div class="status-card mb-4">
        <div class="status-content">
            <div class="status-info">
                <span class="status-label">Stav faktury:</span>
';
		if ($invoice->status == 'created') /* line 30 */ {
			echo '                    <span class="status-badge status-badge-pending">
                        <i class="bi bi-file-earmark me-1"></i> Vystavena
                    </span>
';
		} elseif ($invoice->status == 'paid') /* line 35 */ {
			echo '                    <span class="status-badge status-badge-success">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                        Zaplacena
';
			if ($invoice->payment_date) /* line 40 */ {
				echo '                            <span class="payment-date">(';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->payment_date, 'd.m.Y')) /* line 42 */;
				echo ')</span>
';
			}
			echo '                    </span>
';
		} elseif ($invoice->status == 'overdue') /* line 45 */ {
			echo '                    <span class="status-badge status-badge-danger">
                        <i class="bi bi-exclamation-circle-fill me-1 text-danger"></i>
                        <span class="text-danger">Po splatnosti</span>
                    </span>
';
		}


		echo '            </div>
            <div class="invoice-amount">
                <span class="amount-label">Celkem k úhradě:</span>
                <span class="amount-value">';
		echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 2, ',', ' ')) /* line 57 */;
		echo ' Kč</span>
            </div>
        </div>
    </div>

    <!-- Obsah faktury -->
    <div class="row g-4">
        <!-- Základní informace o faktuře -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <h3>Informace o faktuře</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-hash"></i>
                            Číslo faktury
                        </div>
                        <div class="info-value"><strong>';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->number)) /* line 80 */;
		echo '</strong></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-calendar-event"></i>
                            Datum vystavení
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->issue_date, 'd.m.Y')) /* line 90 */;
		echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-calendar-check"></i>
                            Datum splatnosti
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->due_date, 'd.m.Y')) /* line 100 */;
		echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-credit-card"></i>
                            Způsob platby
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->payment_method)) /* line 110 */;
		echo '</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dodavatel -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-building me-2"></i>
                    <h3>Dodavatel</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-building"></i>
                            Název
                        </div>
                        <div class="info-value"><strong>';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->name)) /* line 132 */;
		echo '</strong></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-geo-alt"></i>
                            Adresa
                        </div>
                        <div class="info-value">
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->address)) /* line 143 */;
		echo '<br>
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->zip)) /* line 144 */;
		echo ' ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->city)) /* line 144 */;
		echo '<br>
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->country)) /* line 145 */;
		echo '
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc"></i>
                            IČ
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->ic)) /* line 156 */;
		echo '</div>
                    </div>
                    
';
		if ($company->dic) /* line 159 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc-scan"></i>
                            DIČ
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->dic)) /* line 167 */;
			echo '</div>
                    </div>
';
		}
		echo '                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-bank"></i>
                            Bankovní účet
                        </div>
                        <div class="info-value">
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->bank_account)) /* line 179 */;
		echo '<br>
                            <small class="text-muted">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($company->bank_name)) /* line 180 */;
		echo '</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Odběratel -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    <h3>Odběratel</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-building"></i>
                            Název
                        </div>
                        <div class="info-value"><strong>';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->name)) /* line 203 */;
		echo '</strong></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-geo-alt"></i>
                            Adresa
                        </div>
                        <div class="info-value">
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->address)) /* line 214 */;
		echo '<br>
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->zip)) /* line 215 */;
		echo ' ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->city)) /* line 215 */;
		echo '<br>
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->country)) /* line 216 */;
		echo '
                        </div>
                    </div>
                    
';
		if ($client->ic) /* line 220 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc"></i>
                            IČ
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->ic)) /* line 228 */;
			echo '</div>
                    </div>
';
		}
		echo '                    
';
		if ($client->dic) /* line 232 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc-scan"></i>
                            DIČ
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->dic)) /* line 240 */;
			echo '</div>
                    </div>
';
		}
		echo '                </div>
            </div>
        </div>
        
';
		$isVatPayer = $company && $company->vat_payer /* line 247 */;
		echo '        
        <!-- Položky faktury -->
        <div class="col-12">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-list-ul me-2"></i>
                    <h3>Položky faktury</h3>
                </div>
                <div class="info-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
';
		if ($isVatPayer) /* line 262 */ {
			echo '                                        <th>Položka</th>
                                        <th>Popis</th>
                                        <th class="text-center">Množství</th>
                                        <th class="text-center">Jednotka</th>
                                        <th class="text-end">Cena/jedn. bez DPH</th>
                                        <th class="text-center">DPH %</th>
                                        <th class="text-end">Celkem s DPH</th>
';
		} else /* line 271 */ {
			echo '                                        <th>Předmět fakturace</th>
                                        <th class="text-end">Částka</th>
';
		}
		echo '                                </tr>
                            </thead>
                            <tbody>
';
		foreach ($invoiceItems as $item) /* line 279 */ {
			echo '                                <tr>
';
			if ($isVatPayer) /* line 281 */ {
				echo '                                        <td><strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($item->name)) /* line 283 */;
				echo '</strong></td>
                                        <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($item->description)) /* line 285 */;
				echo '</td>
                                        <td class="text-center">';
				echo LR\Filters::escapeHtmlText($item->quantity) /* line 287 */;
				echo '</td>
                                        <td class="text-center">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($item->unit)) /* line 289 */;
				echo '</td>
                                        <td class="text-end">';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($item->price, 2, ',', ' ')) /* line 291 */;
				echo ' Kč</td>
                                        <td class="text-center">';
				echo LR\Filters::escapeHtmlText($item->vat) /* line 292 */;
				echo ' %</td>
                                        <td class="text-end"><strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($item->total, 2, ',', ' ')) /* line 293 */;
				echo ' Kč</strong></td>
';
			} else /* line 294 */ {
				echo '                                        <td><strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($item->name)) /* line 296 */;
				echo '</strong></td>
                                        <td class="text-end"><strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($item->total, 2, ',', ' ')) /* line 298 */;
				echo ' Kč</strong></td>
';
			}
			echo '                                </tr>
';

		}

		echo '                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Poznámka (pokud existuje) -->
';
		if ($invoice->note) /* line 310 */ {
			echo '        <div class="col-12">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-chat-left-text me-2"></i>
                    <h3>Poznámka</h3>
                </div>
                <div class="info-card-body">
                    <p class="mb-0">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->note)) /* line 320 */;
			echo '</p>
                </div>
            </div>
        </div>
';
		}
		echo '    </div>

    <!-- Akční tlačítka -->
    <div class="action-buttons-container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 331 */;
		echo '" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zpět na seznam faktur
            </a>
            
            <div class="d-flex gap-2">
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('pdf', [$invoice->id])) /* line 337 */;
		echo '" class="btn btn-primary">
                    <i class="bi bi-file-pdf"></i> Stáhnout PDF
                </a>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$invoice->id])) /* line 340 */;
		echo '" class="btn btn-outline-dark">
                    <i class="bi bi-pencil-square"></i> Upravit fakturu
                </a>
            </div>
        </div>
    </div>
</div>
';
	}
}
