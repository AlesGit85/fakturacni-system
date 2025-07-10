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
			foreach (array_intersect_key(['userData' => '96', 'module' => '160'], $this->params) as $ʟ_v => $ʟ_l) {
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
            <i class="bi bi-people me-2" style="color: #B1D235;"></i>
            Správa uživatelských modulů
        </h1>
        <p class="text-muted">Přehled modulů nainstalovaných jednotlivými uživateli</p>
    </div>

    <!-- Statistiky -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white" style="background-color: #B1D235;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title" style="color: #212529;">Celkem uživatelů</h6>
                            <h2 class="mb-0" style="color: #212529;">';
		echo LR\Filters::escapeHtmlText($totalUsers) /* line 21 */;
		echo '</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people" style="font-size: 2rem; color: #212529;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white" style="background-color: #95B11F;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title" style="color: white;">Celkem modulů</h6>
                            <h2 class="mb-0" style="color: white;">';
		echo LR\Filters::escapeHtmlText($totalModules) /* line 36 */;
		echo '</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-puzzle" style="font-size: 2rem; color: white;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white" style="background-color: #6c757d;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Uživatelé s moduly</h6>
                            <h2 class="mb-0">';
		echo LR\Filters::escapeHtmlText(count(array_filter($usersWithModules, fn($u) => $u['modules_count'] > 0))) /* line 51 */;
		echo '</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-check" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white" style="background-color: #212529;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Aktivní moduly</h6>
                            <h2 class="mb-0">';
		echo LR\Filters::escapeHtmlText(array_sum(array_column($usersWithModules, 'active_modules_count'))) /* line 66 */;
		echo '</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigace -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 80 */;
		echo '" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Zpět na správu vlastních modulů
            </a>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="toggleAllDetails()">
                <i class="bi bi-eye me-2"></i>
                <span id="toggleText">Rozbalit všechny</span>
            </button>
        </div>
    </div>

    <!-- Seznam uživatelů a jejich modulů -->
';
		if (!empty($usersWithModules)) /* line 94 */ {
			echo '        <div class="accordion" id="usersAccordion">
';
			foreach ($usersWithModules as $userData) /* line 96 */ {
				echo '                <div class="card mb-3">
                    <div class="card-header" id="heading';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 98 */;
				echo '">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 101 */;
				echo '" aria-expanded="false" aria-controls="collapse';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 101 */;
				echo '">
                                    <i class="bi bi-chevron-right accordion-icon me-2"></i>
                                </button>
                                <div>
                                    <h6 class="mb-0">
                                        <i class="bi bi-person-circle me-2" style="color: #B1D235;"></i>
                                        <strong>';
				echo LR\Filters::escapeHtmlText($userData['user']->username) /* line 107 */;
				echo '</strong>
';
				if ($userData['user']->first_name || $userData['user']->last_name) /* line 108 */ {
					echo '                                            <small class="text-muted">(';
					echo LR\Filters::escapeHtmlText($userData['user']->first_name) /* line 109 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText($userData['user']->last_name) /* line 109 */;
					echo ')</small>
';
				}
				echo '                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-envelope me-1"></i>';
				echo LR\Filters::escapeHtmlText($userData['user']->email) /* line 113 */;
				echo '
                                        <span class="mx-2">|</span>
                                        <i class="bi bi-shield me-1"></i>
';
				if ($userData['user']->is_super_admin) /* line 116 */ {
					echo '                                            <span class="badge" style="background-color: #B1D235; color: #212529;">Super Admin</span>
';
				} elseif ($userData['user']->role === 'admin') /* line 118 */ {
					echo '                                            <span class="badge bg-danger">Admin</span>
';
				} elseif ($userData['user']->role === 'accountant') /* line 120 */ {
					echo '                                            <span class="badge bg-warning">Účetní</span>
';
				} else /* line 122 */ {
					echo '                                            <span class="badge bg-secondary">Pouze čtení</span>
';
				}


				if (isset($userData['user']->tenant_id)) /* line 125 */ {
					echo '                                            <span class="mx-2">|</span>
                                            <i class="bi bi-building me-1"></i>Tenant: ';
					echo LR\Filters::escapeHtmlText($userData['user']->tenant_id) /* line 127 */;
					echo "\n";
				}
				echo '                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
';
				if ($userData['modules_count'] > 0) /* line 133 */ {
					echo '                                    <span class="badge bg-primary me-2">';
					echo LR\Filters::escapeHtmlText($userData['active_modules_count']) /* line 134 */;
					echo ' aktivních</span>
                                    <span class="badge bg-secondary">';
					echo LR\Filters::escapeHtmlText($userData['modules_count']) /* line 135 */;
					echo ' celkem</span>
';
				} else /* line 136 */ {
					echo '                                    <span class="badge bg-light text-dark">Žádné moduly</span>
';
				}
				echo '                            </div>
                        </div>
                    </div>
                    
                    <div id="collapse';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 143 */;
				echo '" class="collapse" aria-labelledby="heading';
				echo LR\Filters::escapeHtmlAttr($userData['user']->id) /* line 143 */;
				echo '" data-bs-parent="#usersAccordion">
                        <div class="card-body">
';
				if ($userData['modules_count'] > 0) /* line 145 */ {
					echo '                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Modul</th>
                                                <th>Verze</th>
                                                <th>Tenant</th>
                                                <th>Cesta</th>
                                                <th>Stav</th>
                                                <th>Nainstalován</th>
                                                <th>Naposledy použit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
';
					foreach ($userData['modules'] as $module) /* line 160 */ {
						echo '                                                <tr class="';
						if (!$module['is_active']) /* line 161 */ {
							echo 'table-secondary';
						}
						echo '">
                                                    <td>
                                                        <strong>';
						echo LR\Filters::escapeHtmlText($module['name']) /* line 163 */;
						echo '</strong>
                                                        <br>
                                                        <small class="text-muted">ID: ';
						echo LR\Filters::escapeHtmlText($module['id']) /* line 165 */;
						echo '</small>
                                                    </td>
                                                    <td>';
						echo LR\Filters::escapeHtmlText($module['version']) /* line 167 */;
						echo '</td>
                                                    <td>
                                                        <span class="badge bg-info">';
						echo LR\Filters::escapeHtmlText($module['tenant_id']) /* line 169 */;
						echo '</span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted font-monospace">';
						echo LR\Filters::escapeHtmlText($module['path']) /* line 172 */;
						echo '</small>
                                                    </td>
                                                    <td>
';
						if ($module['is_active']) /* line 175 */ {
							echo '                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>Aktivní
                                                            </span>
';
						} else /* line 179 */ {
							echo '                                                            <span class="badge bg-secondary">
                                                                <i class="bi bi-pause-circle me-1"></i>Neaktivní
                                                            </span>
';
						}
						echo '                                                    </td>
                                                    <td>
';
						if ($module['installed_at']) /* line 186 */ {
							echo '                                                            <small>';
							echo LR\Filters::escapeHtmlText(($this->filters->date)($module['installed_at'], 'd.m.Y H:i')) /* line 187 */;
							echo '</small>
';
						} else /* line 188 */ {
							echo '                                                            <small class="text-muted">-</small>
';
						}
						echo '                                                    </td>
                                                    <td>
';
						if ($module['last_used']) /* line 193 */ {
							echo '                                                            <small>';
							echo LR\Filters::escapeHtmlText(($this->filters->date)($module['last_used'], 'd.m.Y H:i')) /* line 194 */;
							echo '</small>
';
						} else /* line 195 */ {
							echo '                                                            <small class="text-muted">Nikdy</small>
';
						}
						echo '                                                    </td>
                                                </tr>
';

					}

					echo '                                        </tbody>
                                    </table>
                                </div>
';
				} else /* line 204 */ {
					echo '                                <div class="text-center py-4">
                                    <i class="bi bi-puzzle" style="font-size: 2rem; color: #6c757d;"></i>
                                    <p class="text-muted mt-2 mb-0">Tento uživatel nemá nainstalované žádné moduly</p>
                                </div>
';
				}
				echo '                        </div>
                    </div>
                </div>
';

			}

			echo '        </div>
';
		} else /* line 215 */ {
			echo '        <div class="text-center py-5">
            <i class="bi bi-people" style="font-size: 4rem; color: #6c757d;"></i>
            <h4 class="text-muted mt-3">Žádní uživatelé</h4>
            <p class="text-muted">V systému nejsou registrováni žádní uživatelé</p>
        </div>
';
		}
		echo '</div>

<style>
.accordion-icon {
    transition: transform 0.2s;
}

.collapse.show + .card-body .accordion-icon,
.accordion-icon[aria-expanded="true"] {
    transform: rotate(90deg);
}

.btn-link {
    color: #212529 !important;
    text-decoration: none !important;
}

.btn-link:hover {
    color: #B1D235 !important;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.card {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.badge {
    font-size: 0.75em;
}
</style>

<script>
let allExpanded = false;

function toggleAllDetails() {
    const collapses = document.querySelectorAll(\'.collapse\');
    const toggleText = document.getElementById(\'toggleText\');
    
    if (allExpanded) {
        // Sbalit všechny
        collapses.forEach(collapse => {
            const bsCollapse = new bootstrap.Collapse(collapse, { show: false });
            bsCollapse.hide();
        });
        toggleText.textContent = \'Rozbalit všechny\';
        allExpanded = false;
    } else {
        // Rozbalit všechny
        collapses.forEach(collapse => {
            const bsCollapse = new bootstrap.Collapse(collapse, { show: true });
            bsCollapse.show();
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
                setTimeout(() => {
                    if (this.getAttribute(\'aria-expanded\') === \'true\') {
                        icon.style.transform = \'rotate(90deg)\';
                    } else {
                        icon.style.transform = \'rotate(0deg)\';
                    }
                }, 100);
            }
        });
    });
});
</script>
';
	}
}
