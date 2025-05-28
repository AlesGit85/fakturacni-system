<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\ModuleAdmin/templates/detail.latte */
final class Template_17ed9f1a8d extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\ModuleAdmin/templates/detail.latte';

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

		echo "\n";
		$this->renderBlock('content', get_defined_vars()) /* line 3 */;
	}


	public function prepare(): array
	{
		extract($this->params);

		$this->parentName = '../../@layout.latte';
		return get_defined_vars();
	}


	/** {block content} on line 3 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="container-fluid">
    <div class="page-header">
        <h1 class="main-title">';
		echo LR\Filters::escapeHtmlText($moduleInfo['name']) /* line 6 */;
		echo '</h1>
        <p class="text-muted">Verze ';
		echo LR\Filters::escapeHtmlText($moduleInfo['version']) /* line 7 */;
		echo ' | ';
		echo LR\Filters::escapeHtmlText($moduleInfo['author']) /* line 7 */;
		echo '</p>
    </div>
    
';
		if (isset($moduleCss)) /* line 10 */ {
			echo '        <link rel="stylesheet" href="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 11 */;
			echo LR\Filters::escapeHtmlAttr($moduleCss) /* line 11 */;
			echo '">
';
		}
		echo '    
    <div class="card">
        <div class="card-header" style="background-color: #B1D235; color: #212529;">
            <i class="';
		echo LR\Filters::escapeHtmlQuotes($moduleInfo['icon']) /* line 16 */;
		echo ' me-2"></i>
            ';
		echo LR\Filters::escapeHtmlText($moduleInfo['name']) /* line 17 */;
		echo '
        </div>
        <div class="card-body">
';
		if (isset($moduleTemplatePath)) /* line 20 */ {
			$this->createTemplate($moduleTemplatePath, $this->params, 'include')->renderToContentType('html') /* line 21 */;
		} else /* line 22 */ {
			echo '                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Modul nemá žádnou šablonu k zobrazení.
                </div>
                <p>';
			echo LR\Filters::escapeHtmlText($moduleInfo['description']) /* line 27 */;
			echo '</p>
';
		}
		echo '        </div>
    </div>
    
    <div class="action-buttons-container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 34 */;
		echo '" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zpět na správa modulů
            </a>
        </div>
    </div>
</div>

';
		if (isset($moduleJs)) /* line 41 */ {
			echo '    <script src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 42 */;
			echo LR\Filters::escapeHtmlAttr($moduleJs) /* line 42 */;
			echo '"></script>
';
		}
		echo "\n";
		if (isset($ajaxUrl)) /* line 45 */ {
			echo '    <script>
        // Předání AJAX URL do JavaScriptu
        window.FINANCIAL_REPORTS_AJAX_URL = ';
			echo LR\Filters::escapeJs(($this->filters->datastream)($ajaxUrl)) /* line 48 */;
			echo ';
    </script>
';
		}
	}
}
