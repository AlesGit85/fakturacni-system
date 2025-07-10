<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Extensions/templates/default.latte */
final class Template_f8787db053 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Extensions/templates/default.latte';

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
			foreach (array_intersect_key(['moduleId' => '44', 'module' => '44'], $this->params) as $ʟ_v => $ʟ_l) {
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
        <h1 class="main-title">
            <i class="bi bi-puzzle me-2" style="color: #B1D235;"></i>
            Moje rozšíření
        </h1>
        <p class="text-muted">Správa a spuštění vašich nainstalovaných modulů</p>
    </div>

';
		if (count($modules) === 0) /* line 13 */ {
			echo '        <!-- Prázdný stav -->
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-puzzle" style="font-size: 4rem; color: #6c757d;"></i>
                        </div>
                        <h4 class="text-muted">Žádná rozšíření</h4>
                        <p class="text-muted mb-4">
                            Nemáte nainstalovaná žádná rozšíření. 
';
			if ($isUserAdmin) /* line 25 */ {
				echo '                                Kontaktujte správce systému pro instalaci nových modulů.
';
			} else /* line 27 */ {
				echo '                                Kontaktujte administrátora pro instalaci nových rozšíření.
';
			}
			echo '                        </p>
';
			if ($isUserAdmin) /* line 31 */ {
				echo '                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 32 */;
				echo '" class="btn btn-primary" style="background-color: #B1D235; border-color: #95B11F; color: #212529;">
                                <i class="bi bi-gear me-2"></i>
                                Správa modulů
                            </a>
';
			}
			echo '                    </div>
                </div>
            </div>
        </div>
';
		} else /* line 41 */ {
			echo '        <!-- Seznam rozšíření -->
        <div class="row">
';
			foreach ($modules as $moduleId => $module) /* line 44 */ {
				echo '                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 shadow-sm module-card">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #f8f9fa;">
                            <div class="d-flex align-items-center">
                                <i class="';
				echo LR\Filters::escapeHtmlQuotes($module['icon']) /* line 49 */;
				echo ' me-2" style="color: #B1D235; font-size: 1.2rem;"></i>
                                <span class="fw-bold">';
				echo LR\Filters::escapeHtmlText($module['name']) /* line 50 */;
				echo '</span>
                            </div>
                            
                            <!-- Přepínač aktivace -->
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       ';
				if (isset($module['active']) && $module['active']) /* line 56 */ {
					echo 'checked';
				}
				echo '
                                       onchange="toggleModule(\'';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($moduleId)) /* line 57 */;
				echo '\', this.checked)"
                                       title="';
				if (isset($module['active']) && $module['active']) /* line 58 */ {
					echo 'Deaktivovat';
				} else /* line 58 */ {
					echo 'Aktivovat';
				}
				echo ' modul">
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <p class="card-text text-muted small mb-3">';
				echo LR\Filters::escapeHtmlText($module['description']) /* line 63 */;
				echo '</p>
                            
                            <div class="mt-auto">
                                <!-- Informace o modulu -->
                                <div class="module-info mb-3">
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i>
                                        Verze ';
				echo LR\Filters::escapeHtmlText($module['version']) /* line 70 */;
				echo '
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-person me-1"></i>
                                        ';
				echo LR\Filters::escapeHtmlText($module['author']) /* line 75 */;
				echo '
                                    </small>
';
				if (isset($module['installed_at'])) /* line 77 */ {
					echo '                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            Nainstalováno ';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($module['installed_at'], 'd.m.Y')) /* line 81 */;
					echo '
                                        </small>
';
				}
				if (isset($module['last_used']) && $module['last_used']) /* line 84 */ {
					echo '                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            Naposledy použito ';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($module['last_used'], 'd.m.Y H:i')) /* line 88 */;
					echo '
                                        </small>
';
				}
				echo '                                </div>

                                <!-- Akční tlačítka -->
                                <div class="d-grid gap-2">
';
				if (isset($module['active']) && $module['active']) /* line 95 */ {
					echo '                                        <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('detail', ['id' => $moduleId])) /* line 96 */;
					echo '" class="btn btn-primary" style="background-color: #B1D235; border-color: #95B11F; color: #212529;">
                                            <i class="bi bi-play-fill me-2"></i>
                                            Spustit modul
                                        </a>
';
				} else /* line 100 */ {
					echo '                                        <button class="btn btn-outline-secondary" disabled>
                                            <i class="bi bi-pause-fill me-2"></i>
                                            Modul deaktivován
                                        </button>
';
				}
				echo '                                </div>
                            </div>
                        </div>
                    </div>
                </div>
';

			}

			echo '        </div>

        <!-- Statistiky -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background-color: #f8f9fa;">
                        <h6 class="mb-0">
                            <i class="bi bi-bar-chart me-2"></i>
                            Přehled rozšíření
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 style="color: #B1D235;">';
			echo LR\Filters::escapeHtmlText(count($modules)) /* line 128 */;
			echo '</h4>
                                    <small class="text-muted">Celkem modulů</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 style="color: #95B11F;">
                                        ';
			echo LR\Filters::escapeHtmlText(count(array_filter($modules, fn($m) => isset($m['active']) && $m['active']))) /* line 135 */;
			echo '
                                    </h4>
                                    <small class="text-muted">Aktivních</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 style="color: #6c757d;">
                                        ';
			echo LR\Filters::escapeHtmlText(count(array_filter($modules, fn($m) => !isset($m['active']) || !$m['active']))) /* line 143 */;
			echo '
                                    </h4>
                                    <small class="text-muted">Deaktivovaných</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <h4 style="color: #212529;">
                                    ';
			echo LR\Filters::escapeHtmlText(count(array_unique(array_column($modules, 'author')))) /* line 150 */;
			echo '
                                </h4>
                                <small class="text-muted">Autorů</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
';
		}
		echo '
    <!-- Akční tlačítka pro administrátory -->
';
		if ($isUserAdmin) /* line 162 */ {
			echo '        <div class="action-buttons-container mt-4">
            <div class="d-flex justify-content-end">
                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 165 */;
			echo '" class="btn btn-outline-primary">
                    <i class="bi bi-gear me-2"></i>
                    Správa modulů
                </a>
            </div>
        </div>
';
		}
		echo '</div>

<style>
.module-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.module-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.module-info {
    min-height: 80px;
}
</style>

<script>
function toggleModule(moduleId, isActive) {
    if (isActive) {
        // Aktivace
        window.location.href = ';
		echo LR\Filters::escapeJs($this->global->uiControl->link('toggle!', ['id' => 'REPLACE_ID'])) /* line 193 */;
		echo '.replace(\'REPLACE_ID\', moduleId);
    } else {
        // Deaktivace - zeptáme se uživatele
        if (confirm(\'Opravdu chcete deaktivovat modul "\' + moduleId + \'"?\')) {
            window.location.href = ';
		echo LR\Filters::escapeJs($this->global->uiControl->link('toggle!', ['id' => 'REPLACE_ID'])) /* line 197 */;
		echo '.replace(\'REPLACE_ID\', moduleId);
        } else {
            // Vrátíme přepínač zpět
            event.target.checked = true;
        }
    }
}
</script>
';
	}
}
