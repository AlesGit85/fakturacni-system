<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Security/test.latte */
final class Template_7a795a13dc extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Security/test.latte';

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

		echo '
<h1>Security Test</h1>
<p>Pokud vidíš tuto stránku, Security presenter funguje!</p>

<p>Test linku: <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('sqlAudit')) /* line 6 */;
		echo '">SQL Audit</a></p>

<!-- První tlačítko pro JSON AJAX test -->
<button onclick="testJsonAjax()" data-url="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('runSqlAudit!')) /* line 9 */;
		echo '">Test JSON AJAX</button>

<!-- Druhé tlačítko pro simple test -->  
<button onclick="testSimpleAjax()" data-url="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('simpleTest!')) /* line 12 */;
		echo '">Test Simple AJAX</button>

<div id="result"></div>

<script>
function testJsonAjax() {
    const button = event.target;
    const url = button.getAttribute(\'data-url\');
    
    fetch(url, {
        method: \'POST\',
        headers: {
            \'X-Requested-With\': \'XMLHttpRequest\',
            \'Content-Type\': \'application/json\'
        }
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById(\'result\').innerHTML = \'<pre>\' + data + \'</pre>\';
    })
    .catch(error => {
        document.getElementById(\'result\').innerHTML = \'Chyba: \' + error.message;
    });
}

function testSimpleAjax() {
    const button = event.target;
    const url = button.getAttribute(\'data-url\');
    window.location.href = url;
}
</script>

';
	}
}
