<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Home/default.latte */
final class Template_e805e100e6 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Home/default.latte';

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

		echo '<div class="container py-5">
    <div class="row">
        <div class="col-md-12 text-center mb-4">
            <h1>QRdoklad</h1>
            <p class="lead">Jednoduchý systém pro správu faktur a klientů – protože fakturovat nemusí být otrava.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h3 class="card-title">Klienti</h3>
                    <p class="card-text">Správa vašich klientů a jejich kontaktních údajů.</p>
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:default')) /* line 16 */;
		echo '" class="btn btn-primary">Zobrazit klienty</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h3 class="card-title">Faktury</h3>
                    <p class="card-text">Vytváření a správa faktur pro vaše klienty.</p>
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 26 */;
		echo '" class="btn btn-primary">Zobrazit faktury</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h3 class="card-title">Nastavení</h3>
                    <p class="card-text">Úprava firemních údajů a nastavení systému.</p>
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Settings:default')) /* line 36 */;
		echo '" class="btn btn-primary">Upravit nastavení</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4>Začínáme</h4>
                    <p>Pro začátek práce s fakturačním systémem doporučujeme:</p>
                    <ol>
                        <li>Nejprve nastavte vaše firemní údaje v sekci <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Settings:default')) /* line 49 */;
		echo '">Nastavení</a>.</li>
                        <li>Přidejte vaše klienty v sekci <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:default')) /* line 50 */;
		echo '">Klienti</a>.</li>
                        <li>Poté můžete začít vytvářet faktury v sekci <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 51 */;
		echo '">Faktury</a>.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
';
	}
}
