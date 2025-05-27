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
			foreach (array_intersect_key(['id' => '125', 'module' => '125'], $this->params) as $ʟ_v => $ʟ_l) {
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

    <!-- PHP Upload limity s toggle -->
    <div class="card mb-4" style="border-left: 4px solid #B1D235;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #f8f9fa; color: #212529;">
            <div>
                <i class="bi bi-info-circle me-2"></i>
                <h5 class="d-inline">PHP Upload limity</h5>
            </div>
            <div class="debug-toggle-container">
                <span class="debug-toggle-label me-2">Zobrazit detaily</span>
                <div class="debug-toggle-switch">
                    <input type="checkbox" id="debugToggle" class="debug-toggle-input">
                    <label for="debugToggle" class="debug-toggle-slider"></label>
                </div>
            </div>
        </div>
        <div class="card-body debug-content" id="debugContent" style="display: none;">
            <div class="row">
                <div class="col-md-4">
                    <h6>Upload max filesize:</h6>
                    <p class="mb-1"><strong>';
		echo LR\Filters::escapeHtmlText($debugInfo['upload_max_filesize_formatted']) /* line 29 */;
		echo '</strong></p>
                    <small class="text-muted">(';
		echo LR\Filters::escapeHtmlText($debugInfo['upload_max_filesize_raw']) /* line 30 */;
		echo ')</small>
                </div>
                <div class="col-md-4">
                    <h6>Post max size:</h6>
                    <p class="mb-1"><strong>';
		echo LR\Filters::escapeHtmlText($debugInfo['post_max_size_formatted']) /* line 34 */;
		echo '</strong></p>
                    <small class="text-muted">(';
		echo LR\Filters::escapeHtmlText($debugInfo['post_max_size_raw']) /* line 35 */;
		echo ')</small>
                </div>
                <div class="col-md-4">
                    <h6>Finální limit:</h6>
                    <p class="mb-1"><strong style="color: #B1D235;">';
		echo LR\Filters::escapeHtmlText($debugInfo['final_limit_formatted']) /* line 39 */;
		echo '</strong></p>
                    <small class="text-muted">Použije se nejmenší hodnota</small>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3">
                    <h6>Memory limit:</h6>
                    <p class="mb-1">';
		echo LR\Filters::escapeHtmlText($debugInfo['memory_limit_formatted']) /* line 47 */;
		echo '</p>
                    <small class="text-muted">(';
		echo LR\Filters::escapeHtmlText($debugInfo['memory_limit_raw']) /* line 48 */;
		echo ')</small>
                </div>
                <div class="col-md-3">
                    <h6>Max execution time:</h6>
                    <p class="mb-1">';
		echo LR\Filters::escapeHtmlText($debugInfo['max_execution_time']) /* line 52 */;
		echo 's</p>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning mb-0 permanent-alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Tip:</strong> Pro zvýšení limitu změňte hodnoty v <code>php.ini</code> nebo kontaktujte administrátora serveru.
                    </div>
                </div>
            </div>
        </div>
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
		$form = $this->global->formsStack[] = $this->global->uiControl['uploadForm'] /* line 73 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-3']) /* line 73 */;
		echo '
                        <div class="col-md-8">
                            <div class="form-group">
                                ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('moduleZip', $this->global)->getLabel()) /* line 76 */;
		echo '
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('moduleZip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 77 */;
		echo '
                                <small class="form-text text-muted">Vyberte ZIP soubor obsahující modul. Maximální velikost: <strong style="color: #B1D235;">';
		echo LR\Filters::escapeHtmlText($maxUploadSizeFormatted) /* line 78 */;
		echo '</strong></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('upload', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary mt-4']) /* line 82 */;
		echo '
                        </div>
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 84 */;

		echo '
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Info:</strong> Moduly lze nahrát jako ZIP soubory. Po nahrání budou automaticky rozbaleny a připraveny k použití.
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Pozor:</strong> Aktuální limit pro nahrávání je <strong>';
		echo LR\Filters::escapeHtmlText($maxUploadSizeFormatted) /* line 93 */;
		echo '</strong>. Pokud potřebujete nahrát větší soubor, zvyšte PHP limity.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Seznam modulů -->
    <div class="section-header-row mb-4">
        <div>
            <h2 class="section-title mb-0">
                <i class="bi bi-puzzle-fill me-2 text-primary"></i>
                Nainstalované moduly 
                <span class="total-count">';
		echo LR\Filters::escapeHtmlText(count($modules)) /* line 106 */;
		echo ' modulů</span>
            </h2>
            <p class="text-muted">Správa a konfigurace systémových rozšíření</p>
        </div>
    </div>

';
		if (!empty($modules)) /* line 112 */ {
			echo '    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Modul</th>
                    <th>Verze</th>
                    <th>Autor</th>
                    <th>Stav</th>
                    <th class="text-end">Akce</th>
                </tr>
            </thead>
            <tbody>
';
			foreach ($modules as $id => $module) /* line 125 */ {
				echo '                    <tr class="data-row">
                        <td class="company-column">
                            <div class="company-name">
                                <strong>';
				echo LR\Filters::escapeHtmlText($module['name']) /* line 129 */;
				echo '</strong>
                            </div>
';
				if ($module['description']) /* line 131 */ {
					echo '                            <div class="company-location text-muted">
                                <small>';
					echo LR\Filters::escapeHtmlText($module['description']) /* line 133 */;
					echo '</small>
                            </div>
';
				}
				echo '                        </td>
                        <td>';
				echo LR\Filters::escapeHtmlText($module['version']) /* line 137 */;
				echo '</td>
                        <td>';
				echo LR\Filters::escapeHtmlText($module['author']) /* line 138 */;
				echo '</td>
                        <td>
';
				if (isset($module['active']) && $module['active']) /* line 140 */ {
					echo '                                <span class="status-badge status-badge-success">
                                    <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                    Aktivní
                                </span>
';
				} else /* line 145 */ {
					echo '                                <span class="status-badge status-badge-pending">
                                    <i class="bi bi-pause-circle me-1"></i>
                                    Neaktivní
                                </span>
';
				}
				echo '                        </td>
                        <td class="actions-column">
                            <div class="action-buttons">
                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('detail', ['id' => $id])) /* line 154 */;
				echo '" class="btn btn-icon" title="Detail modulu">
                                    <i class="bi bi-eye"></i>
                                </a>
';
				if (isset($module['active']) && $module['active']) /* line 157 */ {
					echo '                                    <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleModule!', ['id' => $id])) /* line 158 */;
					echo '" class="btn btn-icon text-warning" onclick="return confirm(\'Opravdu chcete deaktivovat modul?\')" title="Deaktivovat modul">
                                        <i class="bi bi-power"></i>
                                    </a>
';
				} else /* line 161 */ {
					echo '                                    <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleModule!', ['id' => $id])) /* line 162 */;
					echo '" class="btn btn-icon text-success" title="Aktivovat modul">
                                        <i class="bi bi-power"></i>
                                    </a>
';
				}
				echo '                                <div class="dropdown">
                                    <button class="btn btn-icon dropdown-toggle" type="button" id="dropdownMenuButton';
				echo LR\Filters::escapeHtmlAttr($id) /* line 167 */;
				echo '" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton';
				echo LR\Filters::escapeHtmlAttr($id) /* line 170 */;
				echo '">
                                        <li>
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('detail', ['id' => $id])) /* line 172 */;
				echo '" class="dropdown-item">
                                                <i class="bi bi-eye me-2"></i> Detail modulu
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
';
				if (isset($module['active']) && $module['active']) /* line 177 */ {
					echo '                                            <li>
                                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleModule!', ['id' => $id])) /* line 179 */;
					echo '" class="dropdown-item" onclick="return confirm(\'Opravdu chcete deaktivovat modul?\')">
                                                    <i class="bi bi-power text-warning me-2"></i> Deaktivovat
                                                </a>
                                            </li>
';
				} else /* line 183 */ {
					echo '                                            <li>
                                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleModule!', ['id' => $id])) /* line 185 */;
					echo '" class="dropdown-item">
                                                    <i class="bi bi-power text-success me-2"></i> Aktivovat
                                                </a>
                                            </li>
';
				}
				echo '                                        <li>
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('uninstallModule!', ['id' => $id])) /* line 191 */;
				echo '" class="dropdown-item text-danger" onclick="return confirm(\'Opravdu chcete odinstalovat modul? Tato akce nelze vrátit.\')">
                                                <i class="bi bi-trash me-2"></i> Odinstalovat
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
';

			}

			echo '            </tbody>
        </table>
    </div>
';
		} else /* line 204 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-puzzle-fill"></i>
        </div>
        <h3>Zatím nejsou nainstalovány žádné moduly</h3>
        <p>Nahrajte první modul pomocí formuláře výše</p>
    </div>
';
		}
		echo '</div>

<style>
/* Toggle switch styly */
.debug-toggle-container {
    display: flex;
    align-items: center;
}

.debug-toggle-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

.debug-toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.debug-toggle-input {
    opacity: 0;
    width: 0;
    height: 0;
}

.debug-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #dee2e6;
    transition: 0.3s ease;
    border-radius: 24px;
}

.debug-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s ease;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.debug-toggle-input:checked + .debug-toggle-slider {
    background-color: #B1D235;
}

.debug-toggle-input:checked + .debug-toggle-slider:before {
    transform: translateX(26px);
}

.debug-toggle-slider:hover {
    box-shadow: 0 0 8px rgba(177, 210, 53, 0.3);
}

/* Animace pro debug content */
.debug-content {
    transition: all 0.3s ease;
    overflow: hidden;
}

.debug-content.show {
    display: block !important;
    animation: slideDown 0.3s ease;
}

.debug-content.hide {
    animation: slideUp 0.3s ease;
}

/* Permanent alerty se nebudou automaticky skrývat */
.permanent-alert {
    opacity: 1 !important;
    display: block !important;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        padding: 0 1.5rem;
    }
    to {
        opacity: 1;
        max-height: 500px;
        padding: 1.5rem;
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        max-height: 500px;
        padding: 1.5rem;
    }
    to {
        opacity: 0;
        max-height: 0;
        padding: 0 1.5rem;
    }
}
</style>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    const debugToggle = document.getElementById(\'debugToggle\');
    const debugContent = document.getElementById(\'debugContent\');
    
    // Načtení uloženého stavu z localStorage
    const savedState = localStorage.getItem(\'moduleAdminDebugVisible\');
    const isVisible = savedState === \'true\';
    
    // Nastavení výchozího stavu
    debugToggle.checked = isVisible;
    if (isVisible) {
        debugContent.style.display = \'block\';
        debugContent.classList.add(\'show\');
    }
    
    // Event listener pro toggle
    debugToggle.addEventListener(\'change\', function() {
        const isChecked = this.checked;
        
        // Uložení stavu do localStorage
        localStorage.setItem(\'moduleAdminDebugVisible\', isChecked.toString());
        
        if (isChecked) {
            // Zobrazení s animací
            debugContent.style.display = \'block\';
            debugContent.classList.remove(\'hide\');
            debugContent.classList.add(\'show\');
        } else {
            // Skrytí s animací
            debugContent.classList.remove(\'show\');
            debugContent.classList.add(\'hide\');
            
            // Skrytí po animaci
            setTimeout(() => {
                if (!debugToggle.checked) {
                    debugContent.style.display = \'none\';
                }
            }, 300);
        }
    });

    // Zabránění automatickému skrývání permanent alertů
    const permanentAlerts = document.querySelectorAll(\'.permanent-alert\');
    permanentAlerts.forEach(alert => {
        // Přidáme data atribut aby main.js věděl, že se nemá skrývat
        alert.setAttribute(\'data-permanent\', \'true\');
    });

    // Inicializace dropdown menu v tabulce modulů
    const dropdownToggles = document.querySelectorAll(\'.data-table .dropdown-toggle\');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener(\'click\', function(e) {
            const row = this.closest(\'tr\');
            const tableBody = this.closest(\'tbody\');
            const allRows = Array.from(tableBody.querySelectorAll(\'tr\'));
            const rowIndex = allRows.indexOf(row);
            const totalRows = allRows.length;
            
            // Pokud je to jeden z posledních dvou řádků, přidáme CSS třídu
            if (rowIndex >= totalRows - 2) {
                row.classList.add(\'show-dropdown-up\');
            } else {
                row.classList.remove(\'show-dropdown-up\');
            }
        });
        
        // Při zavření dropdown odebereme třídu
        toggle.addEventListener(\'hidden.bs.dropdown\', function() {
            const row = this.closest(\'tr\');
            row.classList.remove(\'show-dropdown-up\');
        });
    });
    
    // Odebereme třídu při kliknutí mimo
    document.addEventListener(\'click\', function(e) {
        if (!e.target.closest(\'.dropdown\')) {
            document.querySelectorAll(\'tr.show-dropdown-up\').forEach(row => {
                row.classList.remove(\'show-dropdown-up\');
            });
        }
    });
});
</script>
';
	}
}
