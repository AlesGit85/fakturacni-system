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

		$this->renderBlock('content', get_defined_vars()) /* line 2 */;
	}


	/** {block content} on line 2 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '
<div class="container">
    <h1>SQL Security Audit - TEST</h1>
    
    <div class="mb-3">
        <button onclick="testSqlAudit()" data-url="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('runSqlAudit!')) /* line 8 */;
		echo '" class="btn btn-primary">
            Test SQL Audit AJAX
        </button>
    </div>
    
    <div id="result"></div>
</div>

<script>
function testSqlAudit() {
    const button = event.target;
    const url = button.getAttribute(\'data-url\');
    
    console.log(\'URL:\', url); // Debug
    
    fetch(url, {
        method: \'POST\',
        headers: {
            \'X-Requested-With\': \'XMLHttpRequest\',
            \'Content-Type\': \'application/json\'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById(\'result\').innerHTML = \'<pre>\' + JSON.stringify(data, null, 2) + \'</pre>\';
    })
    .catch(error => {
        document.getElementById(\'result\').innerHTML = \'Chyba: \' + error.message;
    });
}
</script>

';
	}
}
