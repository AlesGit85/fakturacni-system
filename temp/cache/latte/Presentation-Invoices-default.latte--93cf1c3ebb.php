<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Invoices/default.latte */
final class Template_93cf1c3ebb extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Invoices/default.latte';

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
			foreach (array_intersect_key(['invoice' => '38'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<h1>Faktury</h1>

<div class="row mb-4">
    <div class="col-md-6">
        <p><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 6 */;
		echo '" class="btn btn-success">Vytvořit novou fakturu</a></p>
    </div>
    <div class="col-md-6">
        ';
		$form = $this->global->formsStack[] = $this->global->uiControl['searchForm'] /* line 9 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'd-flex']) /* line 9 */;
		echo '
            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('search', $this->global)->getControl()->addAttributes(['class' => 'form-control me-2']) /* line 10 */;
		echo '
            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-outline-primary']) /* line 11 */;
		echo '
        ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 12 */;

		echo '
    </div>
</div>

';
		if ($search) /* line 16 */ {
			echo '<div class="alert alert-info">
    Výsledky vyhledávání pro: <strong>';
			echo LR\Filters::escapeHtmlText($search) /* line 18 */;
			echo '</strong>
    <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 19 */;
			echo '" class="float-end">Zrušit vyhledávání</a>
</div>
';
		}
		echo "\n";
		if ($invoices->count() > 0) /* line 23 */ {
			echo '<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Číslo faktury</th>
                <th>Klient</th>
                <th>Vystaveno</th>
                <th>Splatnost</th>
                <th>Částka</th>
                <th>Stav</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
';
			foreach ($invoices as $invoice) /* line 38 */ {
				echo '            <tr ';
				if ($invoice->status == 'overdue') /* line 39 */ {
					echo 'class="table-danger"';
				} elseif ($invoice->status == 'paid') /* line 39 */ {
					echo 'class="table-success"';
				}

				echo '>
                <td>';
				echo LR\Filters::escapeHtmlText($invoice->number) /* line 40 */;
				echo '</td>
                <td>';
				if ($invoice->manual_client) /* line 41 */ {
					echo LR\Filters::escapeHtmlText($invoice->client_name) /* line 41 */;
				} else /* line 41 */ {
					echo LR\Filters::escapeHtmlText($invoice->ref('client_id')->name) /* line 41 */;
				}
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->issue_date, 'd.m.Y')) /* line 42 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->due_date, 'd.m.Y')) /* line 43 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 2, ',', ' ')) /* line 44 */;
				echo ' Kč</td>
                <td>
';
				if ($invoice->status == 'created') /* line 46 */ {
					echo '                        <span class="badge bg-warning">Vystavena</span>
';
				} elseif ($invoice->status == 'paid') /* line 48 */ {
					echo '                        <span class="badge bg-success">Zaplacena</span>
';
					if ($invoice->payment_date) /* line 50 */ {
						echo '                            <small class="d-block text-muted">(';
						echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->payment_date, 'd.m.Y')) /* line 51 */;
						echo ')</small>
';
					}
				} elseif ($invoice->status == 'overdue') /* line 53 */ {
					echo '                        <span class="badge bg-danger">Po splatnosti</span>
';
				}


				echo '                </td>
                <td class="table-actions">
                    <div class="btn-group">
                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('show', [$invoice->id])) /* line 59 */;
				echo '" class="btn btn-primary btn-sm">Detail</a>
                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$invoice->id])) /* line 60 */;
				echo '" class="btn btn-warning btn-sm">Upravit</a>
                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('pdf', [$invoice->id])) /* line 61 */;
				echo '" class="btn btn-info btn-sm">PDF</a>
                    </div>
                    <div class="btn-group mt-1">
';
				if ($invoice->status != 'paid') /* line 64 */ {
					echo '                            <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('markAsPaid!', [$invoice->id])) /* line 65 */;
					echo '" class="btn btn-success btn-sm" onclick="return confirm(\'Označit fakturu jako zaplacenou?\')">✓ Zaplaceno</a>
';
				} else /* line 66 */ {
					echo '                            <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('markAsCreated!', [$invoice->id])) /* line 67 */;
					echo '" class="btn btn-outline-secondary btn-sm" onclick="return confirm(\'Označit fakturu jako nezaplacenou?\')">Zrušit zaplaceno</a>
';
				}
				echo '                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$invoice->id])) /* line 69 */;
				echo '" class="btn btn-danger btn-sm" onclick="return confirm(\'Opravdu chcete smazat tuto fakturu?\')">Smazat</a>
                    </div>
                </td>
            </tr>
';

			}

			echo '        </tbody>
    </table>
</div>
';
		} else /* line 77 */ {
			echo '<div class="alert alert-info">
';
			if ($search) /* line 79 */ {
				echo '        Pro hledaný výraz "';
				echo LR\Filters::escapeHtmlText($search) /* line 80 */;
				echo '" nebyly nalezeny žádné faktury.
';
			} else /* line 81 */ {
				echo '        Zatím nebyla vytvořena žádná faktura.
';
			}
			echo '</div>
';
		}
	}
}
