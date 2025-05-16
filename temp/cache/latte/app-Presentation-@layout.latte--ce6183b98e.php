<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation/@layout.latte */
final class Template_ce6183b98e extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation/@layout.latte';

	public const Blocks = [
		['scripts' => 'blockScripts'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Fakturační systém</title>
    <link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 7 */;
		echo '/css/style.css">
    <link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 8 */;
		echo '/css/forms.css">
    <link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 9 */;
		echo '/css/responsive.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Fakturační systém</h1>
            <nav>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Faktura:')) /* line 16 */;
		echo '" class="';
		echo LR\Filters::escapeHtmlAttr($presenter->isLinkCurrent('Faktura:*') ? 'active' : '') /* line 16 */;
		echo '">Faktury</a>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Dodavatel:')) /* line 17 */;
		echo '" class="';
		echo LR\Filters::escapeHtmlAttr($presenter->isLinkCurrent('Dodavatel:*') ? 'active' : '') /* line 17 */;
		echo '">Dodavatel</a>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Odberatel:')) /* line 18 */;
		echo '" class="';
		echo LR\Filters::escapeHtmlAttr($presenter->isLinkCurrent('Odberatel:*') ? 'active' : '') /* line 18 */;
		echo '">Odběratelé</a>
            </nav>
        </header>
        <main>
';
		foreach ($flashes as $flash) /* line 22 */ {
			echo '            <div class="';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 22 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 22 */;
			echo '</div>
';

		}

		$this->renderBlock('content', [], 'html') /* line 23 */;
		echo '        </main>
    </div>
    <script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 26 */;
		echo '/js/scripts.js"></script>
';
		$this->renderBlock('scripts', get_defined_vars()) /* line 27 */;
		echo '</body>
</html>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['flash' => '22'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block scripts} on line 27 */
	public function blockScripts(array $ʟ_args): void
	{
	}
}
