<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\ModuleAdmin/default.latte */
final class Template_4d7c53adcb extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\ModuleAdmin/default.latte';

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
			foreach (array_intersect_key(['id' => '37', 'module' => '37'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="container-fluid">
    <div class="page-header">
        <h1 class="main-title">Správa modulů</h1>
        <p class="text-muted">Testovací stránka pro ověření funkčnosti</p>
    </div>
    
    <div class="card mb-4">
        <div class="card-header" style="background-color: #B1D235; color: #212529;">
            <i class="bi bi-info-circle me-2"></i>
            Testovací stránka
        </div>
        <div class="card-body">
            <p>Toto je jednoduchá testovací stránka pro správu modulů.</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header" style="background-color: #B1D235; color: #212529;">
            <i class="bi bi-puzzle-fill me-2"></i>
            Testovací moduly
        </div>
        <div class="card-body">
';
		if (!empty($modules)) /* line 24 */ {
			echo '                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Název</th>
                                <th>Verze</th>
                                <th>Popis</th>
                                <th>Autor</th>
                                <th>Stav</th>
                            </tr>
                        </thead>
                        <tbody>
';
			foreach ($modules as $id => $module) /* line 37 */ {
				echo '                                <tr>
                                    <td><strong>';
				echo LR\Filters::escapeHtmlText($module['name']) /* line 39 */;
				echo '</strong></td>
                                    <td>';
				echo LR\Filters::escapeHtmlText($module['version']) /* line 40 */;
				echo '</td>
                                    <td>';
				echo LR\Filters::escapeHtmlText($module['description']) /* line 41 */;
				echo '</td>
                                    <td>';
				echo LR\Filters::escapeHtmlText($module['author']) /* line 42 */;
				echo '</td>
                                    <td>
';
				if (isset($module['active']) && $module['active']) /* line 44 */ {
					echo '                                            <span class="badge bg-success">Aktivní</span>
';
				} else /* line 46 */ {
					echo '                                            <span class="badge bg-secondary">Neaktivní</span>
';
				}
				echo '                                    </td>
                                </tr>
';

			}

			echo '                        </tbody>
                    </table>
                </div>
';
		} else /* line 55 */ {
			echo '                <p>Žádné moduly nejsou k dispozici.</p>
';
		}
		echo '        </div>
    </div>
</div>
';
	}
}
