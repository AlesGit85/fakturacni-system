<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Clients/show.latte */
final class Template_21099fea76 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Clients/show.latte';

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


	/** {block content} on line 1 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<h1>';
		echo LR\Filters::escapeHtmlText($client->name) /* line 2 */;
		echo '</h1>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm rounded-lg border-0">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-person-lines-fill me-2"></i>
                <span>Kontaktní údaje</span>
            </div>
            <div class="card-body">
                <p><strong><i class="bi bi-geo-alt text-primary me-2"></i>Adresa:</strong> ';
		echo LR\Filters::escapeHtmlText($client->address) /* line 12 */;
		echo '</p>
                <p><strong><i class="bi bi-building text-primary me-2"></i>Město:</strong> ';
		echo LR\Filters::escapeHtmlText($client->city) /* line 13 */;
		echo '</p>
                <p><strong><i class="bi bi-mailbox text-primary me-2"></i>PSČ:</strong> ';
		echo LR\Filters::escapeHtmlText($client->zip) /* line 14 */;
		echo '</p>
                <p><strong><i class="bi bi-globe text-primary me-2"></i>Země:</strong> ';
		echo LR\Filters::escapeHtmlText($client->country) /* line 15 */;
		echo '</p>
';
		if ($client->contact_person) /* line 16 */ {
			echo '                <p><strong><i class="bi bi-person text-primary me-2"></i>Kontaktní osoba:</strong> ';
			echo LR\Filters::escapeHtmlText($client->contact_person) /* line 16 */;
			echo '</p>';
		}
		echo '
                <p><strong><i class="bi bi-envelope text-primary me-2"></i>E-mail:</strong> ';
		echo LR\Filters::escapeHtmlText($client->email) /* line 17 */;
		echo '</p>
                <p><strong><i class="bi bi-telephone text-primary me-2"></i>Telefon:</strong> ';
		echo LR\Filters::escapeHtmlText($client->phone) /* line 18 */;
		echo '</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm rounded-lg border-0">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-receipt me-2"></i>
                <span>Fakturační údaje</span>
            </div>
            <div class="card-body">
                <p><strong><i class="bi bi-upc text-primary me-2"></i>IČ:</strong> ';
		echo LR\Filters::escapeHtmlText($client->ic) /* line 30 */;
		echo '</p>
                <p><strong><i class="bi bi-upc-scan text-primary me-2"></i>DIČ:</strong> ';
		echo LR\Filters::escapeHtmlText($client->dic) /* line 31 */;
		echo '</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$client->id])) /* line 38 */;
		echo '" class="btn btn-warning">
        <i class="bi bi-pencil-square"></i> Upravit
    </a>
    
';
		$invoiceCount = $presenter->getClientInvoiceCount($client->id) /* line 42 */;
		if ($invoiceCount == 0) /* line 43 */ {
			echo '        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$client->id])) /* line 44 */;
			echo '" class="btn btn-danger" onclick="return confirm(\'Opravdu chcete smazat tohoto klienta?\')">
            <i class="bi bi-trash"></i> Smazat
        </a>
';
		} else /* line 47 */ {
			echo '        <button class="btn btn-danger" disabled title="Klient má ';
			echo LR\Filters::escapeHtmlAttr($invoiceCount) /* line 48 */;
			echo ' faktur a nelze ho smazat">
            <i class="bi bi-trash"></i> Smazat
        </button>
        <small class="text-danger d-block mt-2">Pro smazání klienta je nutné nejprve smazat všechny jeho faktury (';
			echo LR\Filters::escapeHtmlText($invoiceCount) /* line 51 */;
			echo ')</small>
';
		}
		echo '    
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 54 */;
		echo '" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Zpět na seznam klientů
    </a>
</div>
';
	}
}
