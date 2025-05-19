<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Invoices/show.latte */
final class Template_b4e0184bd6 extends Latte\Runtime\Template
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
			foreach (array_intersect_key(['item' => '115'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="mb-4">
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 3 */;
		echo '" class="btn btn-secondary">Zpět na seznam faktur</a>
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$invoice->id])) /* line 4 */;
		echo '" class="btn btn-warning">Upravit fakturu</a>
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('pdf', [$invoice->id])) /* line 5 */;
		echo '" class="btn btn-primary">Stáhnout PDF</a>
</div>

';
		$isVatPayer = $company && $company->vat_payer /* line 8 */;
		echo '
<div class="card">
    <div class="card-body">
        <div class="invoice-header">
            <div class="row">
                <div class="col-md-6">
                    <div class="invoice-title">
                        <h2>Faktura ';
		echo LR\Filters::escapeHtmlText($invoice->number) /* line 16 */;
		echo '</h2>
                        <div>Vystaveno: ';
		echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->issue_date, 'd.m.Y')) /* line 17 */;
		echo '</div>
                        <div>Splatnost: ';
		echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->due_date, 'd.m.Y')) /* line 18 */;
		echo '</div>
                        <div>Způsob platby: ';
		echo LR\Filters::escapeHtmlText($invoice->payment_method) /* line 19 */;
		echo '</div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <h2>Stav: 
';
		if ($invoice->status == 'created') /* line 24 */ {
			echo '                            <span class="badge bg-warning">Vystavena</span>
';
		} elseif ($invoice->status == 'paid') /* line 26 */ {
			echo '                            <span class="badge bg-success">Zaplacena</span>
';
		} elseif ($invoice->status == 'overdue') /* line 28 */ {
			echo '                            <span class="badge bg-danger">Po splatnosti</span>
';
		}


		echo '                    </h2>
                </div>
            </div>
        </div>
        
        <div class="row invoice-details mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Dodavatel</div>
                    <div class="card-body">
                        <h5>';
		echo LR\Filters::escapeHtmlText($company->name) /* line 41 */;
		echo '</h5>
                        <p>
                            ';
		echo LR\Filters::escapeHtmlText($company->address) /* line 43 */;
		echo '<br>
                            ';
		echo LR\Filters::escapeHtmlText($company->zip) /* line 44 */;
		echo ' ';
		echo LR\Filters::escapeHtmlText($company->city) /* line 44 */;
		echo '<br>
                            ';
		echo LR\Filters::escapeHtmlText($company->country) /* line 45 */;
		echo '
                        </p>
                        <p>
                            IČ: ';
		echo LR\Filters::escapeHtmlText($company->ic) /* line 48 */;
		echo '<br>
                            ';
		if ($company->dic) /* line 49 */ {
			echo 'DIČ: ';
			echo LR\Filters::escapeHtmlText($company->dic) /* line 49 */;
		}
		echo '
                        </p>
                        <p>
                            E-mail: ';
		echo LR\Filters::escapeHtmlText($company->email) /* line 52 */;
		echo '<br>
                            Telefon: ';
		echo LR\Filters::escapeHtmlText($company->phone) /* line 53 */;
		echo '
                        </p>
                        <p>
                            Bankovní účet: ';
		echo LR\Filters::escapeHtmlText($company->bank_account) /* line 56 */;
		echo '<br>
                            Banka: ';
		echo LR\Filters::escapeHtmlText($company->bank_name) /* line 57 */;
		echo '
                        </p>
';
		if (!$isVatPayer) /* line 59 */ {
			echo '                        <p class="text-muted">
                            <em>Dodavatel není plátcem DPH.</em>
                        </p>
';
		}
		echo '                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Odběratel</div>
                    <div class="card-body">
                        <h5>';
		echo LR\Filters::escapeHtmlText($client->name) /* line 72 */;
		echo '</h5>
                        <p>
                            ';
		echo LR\Filters::escapeHtmlText($client->address) /* line 74 */;
		echo '<br>
                            ';
		echo LR\Filters::escapeHtmlText($client->zip) /* line 75 */;
		echo ' ';
		echo LR\Filters::escapeHtmlText($client->city) /* line 75 */;
		echo '<br>
                            ';
		echo LR\Filters::escapeHtmlText($client->country) /* line 76 */;
		echo '
                        </p>
';
		if ($client->ic || $client->dic) /* line 78 */ {
			echo '                        <p>
                            ';
			if ($client->ic) /* line 80 */ {
				echo 'IČ: ';
				echo LR\Filters::escapeHtmlText($client->ic) /* line 80 */;
				echo '<br>';
			}
			echo '
                            ';
			if ($client->dic) /* line 81 */ {
				echo 'DIČ: ';
				echo LR\Filters::escapeHtmlText($client->dic) /* line 81 */;
			}
			echo '
                        </p>
';
		}
		if ($client->email || $client->phone) /* line 84 */ {
			echo '                        <p>
                            ';
			if ($client->email) /* line 86 */ {
				echo 'E-mail: ';
				echo LR\Filters::escapeHtmlText($client->email) /* line 86 */;
				echo '<br>';
			}
			echo '
                            ';
			if ($client->phone) /* line 87 */ {
				echo 'Telefon: ';
				echo LR\Filters::escapeHtmlText($client->phone) /* line 87 */;
			}
			echo '
                        </p>
';
		}
		echo '                    </div>
                </div>
            </div>
        </div>
        
        <div class="invoice-items">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
';
		if ($isVatPayer) /* line 100 */ {
			echo '                                <th>Položka</th>
                                <th>Popis</th>
                                <th class="text-center">Množství</th>
                                <th class="text-center">Jednotka</th>
                                <th class="text-end">Cena/jedn. bez DPH</th>
                                <th class="text-center">DPH %</th>
                                <th class="text-end">Celkem s DPH</th>
';
		} else /* line 108 */ {
			echo '                                <th>Předmět fakturace</th>
                                <th class="text-end">Částka</th>
';
		}
		echo '                        </tr>
                    </thead>
                    <tbody>
';
		foreach ($invoiceItems as $item) /* line 115 */ {
			echo '                        <tr>
';
			if ($isVatPayer) /* line 117 */ {
				echo '                                <td>';
				echo LR\Filters::escapeHtmlText($item->name) /* line 118 */;
				echo '</td>
                                <td>';
				echo LR\Filters::escapeHtmlText($item->description) /* line 119 */;
				echo '</td>
                                <td class="text-center">';
				echo LR\Filters::escapeHtmlText($item->quantity) /* line 120 */;
				echo '</td>
                                <td class="text-center">';
				echo LR\Filters::escapeHtmlText($item->unit) /* line 121 */;
				echo '</td>
                                <td class="text-end">';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($item->price, 2, ',', ' ')) /* line 122 */;
				echo ' Kč</td>
                                <td class="text-center">';
				echo LR\Filters::escapeHtmlText($item->vat) /* line 123 */;
				echo ' %</td>
                                <td class="text-end">';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($item->total, 2, ',', ' ')) /* line 124 */;
				echo ' Kč</td>
';
			} else /* line 125 */ {
				echo '                                <td>';
				echo LR\Filters::escapeHtmlText($item->name) /* line 126 */;
				echo '</td>
                                <td class="text-end">';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($item->total, 2, ',', ' ')) /* line 127 */;
				echo ' Kč</td>
';
			}
			echo '                        </tr>
';

		}

		echo '                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="invoice-total mb-4">
            <div class="row">
                <div class="col-md-8">
';
		if ($invoice->note) /* line 139 */ {
			echo '                    <div class="invoice-note">
                        <strong>Poznámka:</strong> ';
			echo LR\Filters::escapeHtmlText($invoice->note) /* line 141 */;
			echo '
                    </div>
';
		}
		echo '                </div>
                <div class="col-md-4">
                    <table class="table table-bordered">
                        <tr>
                            <th>Celkem k úhradě:</th>
                            <td class="text-end"><strong>';
		echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 2, ',', ' ')) /* line 149 */;
		echo ' Kč</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
';
		if ($invoice->qr_payment && $company->bank_account) /* line 156 */ {
			echo '        <div class="row mb-4">
            <div class="col-md-12 text-center">
                <h5>Platební údaje</h5>
                <p>
                    Bankovní účet: <strong>';
			echo LR\Filters::escapeHtmlText($company->bank_account) /* line 161 */;
			echo '</strong><br>
                    Variabilní symbol: <strong>';
			echo LR\Filters::escapeHtmlText(str_replace('/', '', $invoice->number)) /* line 162 */;
			echo '</strong><br>
                    Částka: <strong>';
			echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 2, ',', ' ')) /* line 163 */;
			echo ' Kč</strong>
                </p>
            </div>
        </div>
';
		}
		echo '    </div>
</div>
';
	}
}
