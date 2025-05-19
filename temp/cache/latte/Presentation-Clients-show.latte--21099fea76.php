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
        <div class="card">
            <div class="card-header">
                Kontaktní údaje
            </div>
            <div class="card-body">
                <p><strong>Adresa:</strong> ';
		echo LR\Filters::escapeHtmlText($client->address) /* line 11 */;
		echo '</p>
                <p><strong>Město:</strong> ';
		echo LR\Filters::escapeHtmlText($client->city) /* line 12 */;
		echo '</p>
                <p><strong>PSČ:</strong> ';
		echo LR\Filters::escapeHtmlText($client->zip) /* line 13 */;
		echo '</p>
                <p><strong>Země:</strong> ';
		echo LR\Filters::escapeHtmlText($client->country) /* line 14 */;
		echo '</p>
                <p><strong>E-mail:</strong> ';
		echo LR\Filters::escapeHtmlText($client->email) /* line 15 */;
		echo '</p>
                <p><strong>Telefon:</strong> ';
		echo LR\Filters::escapeHtmlText($client->phone) /* line 16 */;
		echo '</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                Fakturační údaje
            </div>
            <div class="card-body">
                <p><strong>IČ:</strong> ';
		echo LR\Filters::escapeHtmlText($client->ic) /* line 27 */;
		echo '</p>
                <p><strong>DIČ:</strong> ';
		echo LR\Filters::escapeHtmlText($client->dic) /* line 28 */;
		echo '</p>
                <p><strong>Bankovní účet:</strong> ';
		echo LR\Filters::escapeHtmlText($client->bank_account) /* line 29 */;
		echo '</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$client->id])) /* line 36 */;
		echo '" class="btn btn-warning">Upravit</a>
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 37 */;
		echo '" class="btn btn-secondary">Zpět na seznam klientů</a>
</div>
';
	}
}
