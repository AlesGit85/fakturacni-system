<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\ModuleAdmin/templates/default.latte */
final class Template_56fce24031 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\ModuleAdmin/templates/default.latte';

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

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['id' => '63', 'module' => '63'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
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
        <h1 class="main-title">Správa modulů</h1>
        <p class="text-muted">Instalace a správa rozšiřujících modulů systému</p>
    </div>

    <!-- Nahrání nového modulu -->
    <div class="card mb-4">
        <div class="card-header" style="background-color: #B1D235; color: #212529;">
            <i class="bi bi-cloud-upload me-2"></i>
            <h3 class="d-inline">Nahrát nový modul</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    ';
		$form = $this->global->formsStack[] = $this->global->uiControl['uploadForm'] /* line 19 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-3']) /* line 19 */;
		echo '
                        <div class="col-md-8">
                            <div class="form-group">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('moduleZip', $this->global)->getLabel()) /* line 22 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('moduleZip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 23 */;
		echo '
                                <small class="form-text text-muted">Vyberte ZIP soubor obsahující modul.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('upload', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary mt-4']) /* line 28 */;
		echo '
                        </div>
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 30 */;

		echo '
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Info:</strong> Moduly lze nahrát jako ZIP soubory. Po nahrání budou automaticky rozbaleny a připraveny k použití.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Seznam modulů -->
    <div class="card">
        <div class="card-header" style="background-color: #B1D235; color: #212529;">
            <i class="bi bi-puzzle-fill me-2"></i>
            <h3 class="d-inline">Nainstalované moduly</h3>
        </div>
        <div class="card-body">
';
		if (!empty($modules)) /* line 49 */ {
			echo '                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Název</th>
                                <th>Verze</th>
                                <th>Popis</th>
                                <th>Autor</th>
                                <th>Stav</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
';
			foreach ($modules as $id => $module) /* line 63 */ {
				echo '                                <tr>
                                    <td><strong>';
				echo LR\Filters::escapeHtmlText($module['name']) /* line 65 */;
				echo '</strong></td>
                                    <td>';
				echo LR\Filters::escapeHtmlText($module['version']) /* line 66 */;
				echo '</td>
                                    <td>';
				echo LR\Filters::escapeHtmlText($module['description']) /* line 67 */;
				echo '</td>
                                    <td>';
				echo LR\Filters::escapeHtmlText($module['author']) /* line 68 */;
				echo '</td>
                                    <td>
';
				if (isset($module['active']) && $module['active']) /* line 70 */ {
					echo '                                            <span class="badge bg-success">Aktivní</span>
';
				} else /* line 72 */ {
					echo '                                            <span class="badge bg-secondary">Neaktivní</span>
';
				}
				echo '                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('detail', ['id' => $id])) /* line 78 */;
				echo '" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
';
				if (isset($module['active']) && $module['active']) /* line 81 */ {
					echo '                                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleModule!', ['id' => $id])) /* line 82 */;
					echo '" class="btn btn-sm btn-outline-warning" onclick="return confirm(\'Opravdu chcete deaktivovat modul?\')">
                                                    <i class="bi bi-power"></i> Deaktivovat
                                                </a>
';
				} else /* line 85 */ {
					echo '                                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleModule!', ['id' => $id])) /* line 86 */;
					echo '" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-power"></i> Aktivovat
                                                </a>
';
				}
				echo '                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('uninstallModule!', ['id' => $id])) /* line 90 */;
				echo '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Opravdu chcete odinstalovat modul? Tato akce nelze vrátit.\')">
                                                <i class="bi bi-trash"></i> Odinstalovat
                                            </a>
                                        </div>
                                    </td>
                                </tr>
';

			}

			echo '                        </tbody>
                    </table>
                </div>
';
		} else /* line 100 */ {
			echo '                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Zatím nejsou nainstalovány žádné moduly.
                </div>
';
		}
		echo '        </div>
    </div>
</div>
';
	}
}
