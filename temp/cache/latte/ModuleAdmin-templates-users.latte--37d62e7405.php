<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\ModuleAdmin/templates/users.latte */
final class Template_37d62e7405 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\ModuleAdmin/templates/users.latte';

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
			foreach (array_intersect_key(['userData' => '92', 'module' => '162'], $this->params) as $ʟ_v => $ʟ_l) {
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
    <!-- Hlavička sekce - SJEDNOCENO podle Users default.latte -->
    <div class="section-header-row mb-4">
        <div>
            <h1 class="main-title">
                <i class="bi bi-people-fill me-2" style="color: #B1D235;"></i>
                Správa uživatelských modulů
            </h1>
            <p class="text-muted">Přehled modulů nainstalovaných administrátory systému</p>
        </div>
        <div class="header-actions">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 15 */;
		echo '" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zpět na správu vlastních modulů
            </a>
            <button class="btn btn-outline-primary" onclick="toggleAllDetails()">
                <i class="bi bi-eye me-2"></i>
                <span id="toggleText">Rozbalit všechny</span>
            </button>
        </div>
    </div>

    <!-- Statistiky - jemné barvy podle vzoru Users default.latte -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card" style="background-color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted" style="font-weight: 500; font-size: 14px;">ADMINISTRÁTOŘI</h6>
                            <h2 class="mb-0" style="color: #B1D235;">';
		echo LR\Filters::escapeHtmlText($totalUsers) /* line 33 */;
		echo '</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-shield-fill-check" style="font-size: 2rem; color: #B1D235;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background-color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted" style="font-weight: 500; font-size: 14px;">CELKEM MODULŮ</h6>
                            <h2 class="mb-0" style="color: #95B11F;">';
		echo LR\Filters::escapeHtmlText($totalModules) /* line 48 */;
		echo '</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-puzzle" style="font-size: 2rem; color: #95B11F;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background-color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted" style="font-weight: 500; font-size: 14px;">S AKTIVNÍMI MODULY</h6>
                            <h2 class="mb-0" style="color: #6c757d;">';
		echo LR\Filters::escapeHtmlText(count(array_filter($usersWithModules, fn($u) => $u['active_modules_count'] > 0))) /* line 63 */;
		echo '</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-check-fill" style="font-size: 2rem; color: #6c757d;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white" style="background-color: #B1D235;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title" style="color: #212529; font-weight: 500; font-size: 14px;">AKTIVNÍ MODULY</h6>
                            <h2 class="mb-0" style="color: #212529;">';
		echo LR\Filters::escapeHtmlText(array_sum(array_column($usersWithModules, 'active_modules_count'))) /* line 78 */;
		echo '</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle-fill" style="font-size: 2rem; color: #212529;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seznam administrátorů a jejich modulů -->
';
		if (!empty($usersWithModules)) /* line 90 */ {
			echo '        <div class="row">
';
			foreach ($usersWithModules as $userData) /* line 92 */ {
				echo '                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #B1D235;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-link text-decoration-none p-0 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 98 */;
				echo '" aria-expanded="false" aria-controls="collapse';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 98 */;
				echo '">
                                        <i class="bi bi-chevron-right accordion-icon text-primary" style="font-size: 1.2rem; transition: transform 0.2s;"></i>
                                    </button>
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="bi bi-person-circle me-2" style="color: #B1D235;"></i>
                                            <strong>';
				echo LR\Filters::escapeHtmlText($userData['user']->username) /* line 104 */;
				echo '</strong>
';
				if ($userData['user']->first_name || $userData['user']->last_name) /* line 105 */ {
					echo '                                                <small class="text-muted">(';
					echo LR\Filters::escapeHtmlText($userData['user']->first_name) /* line 106 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText($userData['user']->last_name) /* line 106 */;
					echo ')</small>
';
				}
				echo '                                        </h5>
                                        <div class="text-muted small">
                                            <i class="bi bi-envelope me-1"></i>';
				echo LR\Filters::escapeHtmlText($userData['user']->email) /* line 110 */;
				echo '
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-shield me-1"></i>
';
				if ($userData['user']->is_super_admin) /* line 113 */ {
					echo '                                                <span class="badge" style="background-color: #B1D235; color: #212529; font-weight: 600;">Super Admin</span>
';
				} else /* line 115 */ {
					echo '                                                <span class="badge" style="background-color: #95B11F; color: white; font-weight: 600;">Admin</span>
';
				}
				if (isset($userData['user']->tenant_id)) /* line 118 */ {
					echo '                                                <span class="mx-2">|</span>
                                                <i class="bi bi-building me-1"></i>Tenant: ';
					echo LR\Filters::escapeHtmlText($userData['user']->tenant_id) /* line 120 */;
					echo "\n";
				}
				echo '                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
';
				if ($userData['modules_count'] > 0) /* line 126 */ {
					echo '                                        <span class="status-badge status-badge-success me-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            ';
					echo LR\Filters::escapeHtmlText($userData['active_modules_count']) /* line 129 */;
					echo ' aktivních
                                        </span>
                                        <span class="status-badge status-badge-pending">
                                            <i class="bi bi-puzzle-fill me-1"></i>
                                            ';
					echo LR\Filters::escapeHtmlText($userData['modules_count']) /* line 133 */;
					echo ' celkem
                                        </span>
';
				} else /* line 135 */ {
					echo '                                        <span class="status-badge status-badge-pending">
                                            <i class="bi bi-puzzle me-1"></i>
                                            Žádné moduly
                                        </span>
';
				}
				echo '                                </div>
                            </div>
                        </div>
                        
                        <div id="collapse';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 145 */;
				echo '" class="collapse" aria-labelledby="heading';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 145 */;
				echo '">
                            <div class="card-body">
';
				if ($userData['modules_count'] > 0) /* line 147 */ {
					echo '                                    <div class="table-container">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>Modul</th>
                                                    <th>Verze</th>
                                                    <th>Tenant</th>
                                                    <th>Stav</th>
                                                    <th>Nainstalován</th>
                                                    <th>Naposledy použit</th>
                                                    <th class="text-end">Akce</th>
                                                </tr>
                                            </thead>
                                            <tbody>
';
					foreach ($userData['modules'] as $module) /* line 162 */ {
						echo '                                                    <tr class="data-row ';
						if (!$module['is_active']) /* line 163 */ {
							echo 'opacity-50';
						}
						echo '">
                                                        <td class="company-column">
                                                            <div class="company-name">
                                                                <strong>';
						echo LR\Filters::escapeHtmlText($module['name']) /* line 166 */;
						echo '</strong>
                                                            </div>
                                                            <div class="company-location text-muted">
                                                                <small>ID: ';
						echo LR\Filters::escapeHtmlText($module['id']) /* line 169 */;
						echo '</small>
                                                            </div>
                                                        </td>
                                                        <td>';
						echo LR\Filters::escapeHtmlText($module['version']) /* line 172 */;
						echo '</td>
                                                        <td>
                                                            <span class="badge" style="background-color: #B1D235; color: #212529;">';
						echo LR\Filters::escapeHtmlText($module['tenant_id']) /* line 174 */;
						echo '</span>
                                                        </td>
                                                        <td>
';
						if ($module['is_active']) /* line 177 */ {
							echo '                                                                <span class="status-badge status-badge-success">
                                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                                    Aktivní
                                                                </span>
';
						} else /* line 182 */ {
							echo '                                                                <span class="status-badge status-badge-pending">
                                                                    <i class="bi bi-pause-circle me-1"></i>
                                                                    Neaktivní
                                                                </span>
';
						}
						echo '                                                        </td>
                                                        <td>
';
						if ($module['installed_at']) /* line 190 */ {
							echo '                                                                <small>';
							echo LR\Filters::escapeHtmlText(($this->filters->date)($module['installed_at'], 'd.m.Y H:i')) /* line 191 */;
							echo '</small>
';
						} else /* line 192 */ {
							echo '                                                                <small class="text-muted">-</small>
';
						}
						echo '                                                        </td>
                                                        <td>
';
						if ($module['last_used']) /* line 197 */ {
							echo '                                                                <small>';
							echo LR\Filters::escapeHtmlText(($this->filters->date)($module['last_used'], 'd.m.Y H:i')) /* line 198 */;
							echo '</small>
';
						} else /* line 199 */ {
							echo '                                                                <small class="text-muted">Nikdy</small>
';
						}
						echo '                                                        </td>
                                                        <td class="actions-column">
                                                            <div class="action-buttons">
';
						if ($module['is_active']) /* line 206 */ {
							echo '                                                                    <a href="';
							echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleUserModule!', ['moduleId' => $module['id'], 'userId' => $userData['user']->id])) /* line 207 */;
							echo '" 
                                                                       class="btn btn-icon btn-warning" 
                                                                       onclick="return confirm(\'Opravdu chcete deaktivovat modul ';
							echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($module['name'])) /* line 209 */;
							echo ' uživateli ';
							echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userData['user']->username)) /* line 209 */;
							echo '?\')"
                                                                       title="Deaktivovat modul">
                                                                        <i class="bi bi-power"></i>
                                                                    </a>
';
						} else /* line 213 */ {
							echo '                                                                    <a href="';
							echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleUserModule!', ['moduleId' => $module['id'], 'userId' => $userData['user']->id])) /* line 214 */;
							echo '" 
                                                                       class="btn btn-icon btn-primary" 
                                                                       title="Aktivovat modul">
                                                                        <i class="bi bi-power"></i>
                                                                    </a>
';
						}
						echo '                                                                
                                                                <div class="dropdown">
                                                                    <button class="btn btn-icon dropdown-toggle" type="button" 
                                                                            id="dropdownModule';
						echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 224 */;
						echo '_';
						echo LR\Filters::escapeHtmlAttr($module['id']) /* line 224 */;
						echo '" 
                                                                            data-bs-toggle="dropdown" aria-expanded="false"
                                                                            title="Další akce">
                                                                        <i class="bi bi-three-dots-vertical"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end" 
                                                                        aria-labelledby="dropdownModule';
						echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 230 */;
						echo '_';
						echo LR\Filters::escapeHtmlAttr($module['id']) /* line 230 */;
						echo '">
';
						if ($module['is_active']) /* line 231 */ {
							echo '                                                                            <li>
                                                                                <a href="';
							echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleUserModule!', ['moduleId' => $module['id'], 'userId' => $userData['user']->id])) /* line 233 */;
							echo '" 
                                                                                   class="dropdown-item" 
                                                                                   onclick="return confirm(\'Opravdu chcete deaktivovat modul ';
							echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($module['name'])) /* line 235 */;
							echo '?\')">
                                                                                    <i class="bi bi-power text-warning me-2"></i> Deaktivovat
                                                                                </a>
                                                                            </li>
';
						} else /* line 239 */ {
							echo '                                                                            <li>
                                                                                <a href="';
							echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('toggleUserModule!', ['moduleId' => $module['id'], 'userId' => $userData['user']->id])) /* line 241 */;
							echo '" 
                                                                                   class="dropdown-item">
                                                                                    <i class="bi bi-power text-success me-2"></i> Aktivovat
                                                                                </a>
                                                                            </li>
';
						}
						echo '                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li>
                                                                            <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deleteUserModule!', ['moduleId' => $module['id'], 'userId' => $userData['user']->id])) /* line 249 */;
						echo '" 
                                                                               class="dropdown-item text-danger" 
                                                                               onclick="return confirm(\'Opravdu chcete TRVALE SMAZAT modul ';
						echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($module['name'])) /* line 251 */;
						echo ' uživateli ';
						echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userData['user']->username)) /* line 251 */;
						echo '?\\n\\nTato akce nelze vrátit!\')">
                                                                                <i class="bi bi-trash me-2"></i> Smazat modul
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
';

					}

					echo '                                            </tbody>
                                        </table>
                                    </div>
';
				} else /* line 264 */ {
					echo '                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-puzzle"></i>
                                        </div>
                                        <h5>Žádné moduly</h5>
                                        <p class="text-muted">Tento administrátor nemá nainstalované žádné moduly</p>
                                    </div>
';
				}
				echo '                            </div>
                        </div>
                    </div>
                </div>
';

			}

			echo '        </div>
';
		} else /* line 279 */ {
			echo '        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-people"></i>
            </div>
            <h3>Žádní administrátoři</h3>
            <p class="text-muted">V systému nejsou registrováni žádní administrátoři s moduly</p>
        </div>
';
		}
		echo '</div>

<script>
let allExpanded = false;

function toggleAllDetails() {
    const collapses = document.querySelectorAll(\'.collapse\');
    const toggleText = document.getElementById(\'toggleText\');
    const icons = document.querySelectorAll(\'.accordion-icon\');
    
    if (allExpanded) {
        // Sbalit všechny
        collapses.forEach(collapse => {
            const bsCollapse = new bootstrap.Collapse(collapse, { show: false });
            bsCollapse.hide();
        });
        icons.forEach(icon => {
            icon.style.transform = \'rotate(0deg)\';
        });
        toggleText.textContent = \'Rozbalit všechny\';
        allExpanded = false;
    } else {
        // Rozbalit všechny
        collapses.forEach(collapse => {
            const bsCollapse = new bootstrap.Collapse(collapse, { show: true });
            bsCollapse.show();
        });
        icons.forEach(icon => {
            icon.style.transform = \'rotate(90deg)\';
        });
        toggleText.textContent = \'Sbalit všechny\';
        allExpanded = true;
    }
}

// Animace šipek při rozbalování/sbalování
document.addEventListener(\'DOMContentLoaded\', function() {
    const accordionButtons = document.querySelectorAll(\'[data-bs-toggle="collapse"]\');
    
    accordionButtons.forEach(button => {
        button.addEventListener(\'click\', function() {
            const icon = this.querySelector(\'.accordion-icon\');
            if (icon) {
                const target = document.querySelector(this.getAttribute(\'data-bs-target\'));
                if (target) {
                    target.addEventListener(\'shown.bs.collapse\', function() {
                        icon.style.transform = \'rotate(90deg)\';
                    });
                    target.addEventListener(\'hidden.bs.collapse\', function() {
                        icon.style.transform = \'rotate(0deg)\';
                    });
                }
            }
        });
    });
});
</script>
';
	}
}
