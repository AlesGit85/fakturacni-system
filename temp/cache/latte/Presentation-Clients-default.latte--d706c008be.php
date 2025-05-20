<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Clients/default.latte */
final class Template_d706c008be extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Clients/default.latte';

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
			foreach (array_intersect_key(['client' => '20'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<h1>Klienti</h1>

<p><a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 4 */;
		echo '" class="btn btn-success">Přidat nového klienta</a></p>

';
		if ($clients->count() > 0) /* line 6 */ {
			echo '<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Název</th>
                <th>IČ</th>
                <th>DIČ</th>
                <th>E-mail</th>
                <th>Telefon</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
';
			foreach ($clients as $client) /* line 20 */ {
				echo '            <tr>
                <td>';
				echo LR\Filters::escapeHtmlText($client->name) /* line 22 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText($client->ic) /* line 23 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText($client->dic) /* line 24 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText($client->email) /* line 25 */;
				echo '</td>
                <td>';
				echo LR\Filters::escapeHtmlText($client->phone) /* line 26 */;
				echo '</td>
                <td>
                 <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('show', [$client->id])) /* line 28 */;
				echo '" class="btn btn-primary btn-sm">Detail</a>
                  <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$client->id])) /* line 29 */;
				echo '" class="btn btn-warning btn-sm">Upravit</a>
    
';
				$invoiceCount = $presenter->getClientInvoiceCount($client->id) /* line 31 */;
				if ($invoiceCount == 0) /* line 32 */ {
					echo '                     <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$client->id])) /* line 33 */;
					echo '" class="btn btn-danger btn-sm" onclick="return confirm(\'Opravdu chcete smazat tohoto klienta?\')">Smazat</a>
';
				} else /* line 34 */ {
					echo '                     <button class="btn btn-danger btn-sm" disabled title="Klient má ';
					echo LR\Filters::escapeHtmlAttr($invoiceCount) /* line 35 */;
					echo ' faktur a nelze ho smazat">Smazat</button>
';
				}
				echo '                </td>
            </tr>
';

			}

			echo '        </tbody>
    </table>
</div>
';
		} else /* line 43 */ {
			echo '<div class="alert alert-info">
    Zatím nebyl přidán žádný klient.
</div>
';
		}
	}
}
