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
			foreach (array_intersect_key(['invoice' => '21'], $this->params) as $ʟ_v => $ʟ_l) {
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

<p><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 4 */;
		echo '" class="btn btn-success">Vytvořit novou fakturu</a></p>

';
		if ($invoices->count() > 0) /* line 6 */ {
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
			foreach ($invoices as $invoice) /* line 21 */ {
				echo '            <tr>
                <td>';
				echo LR\Filters::escapeHtmlText($invoice->number) /* line 23 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText($invoice->ref('client_id')->name) /* line 24 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->issue_date, 'd.m.Y')) /* line 25 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->due_date, 'd.m.Y')) /* line 26 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 2, ',', ' ')) /* line 27 */;
				echo ' Kč</td>
                <td>
';
				if ($invoice->status == 'created') /* line 29 */ {
					echo '                        <span class="badge bg-warning">Vystavena</span>
';
				} elseif ($invoice->status == 'paid') /* line 31 */ {
					echo '                        <span class="badge bg-success">Zaplacena</span>
';
				} elseif ($invoice->status == 'overdue') /* line 33 */ {
					echo '                        <span class="badge bg-danger">Po splatnosti</span>
';
				}


				echo '                </td>
                <td class="table-actions">
                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('show', [$invoice->id])) /* line 38 */;
				echo '" class="btn btn-primary btn-sm">Detail</a>
                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$invoice->id])) /* line 39 */;
				echo '" class="btn btn-warning btn-sm">Upravit</a>
                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('pdf', [$invoice->id])) /* line 40 */;
				echo '" class="btn btn-info btn-sm">PDF</a>
                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$invoice->id])) /* line 41 */;
				echo '" class="btn btn-danger btn-sm" onclick="return confirm(\'Opravdu chcete smazat tuto fakturu?\')">Smazat</a>
                </td>
            </tr>
';

			}

			echo '        </tbody>
    </table>
</div>
';
		} else /* line 48 */ {
			echo '<div class="alert alert-info">
    Zatím nebyla vytvořena žádná faktura.
</div>
';
		}
	}
}
