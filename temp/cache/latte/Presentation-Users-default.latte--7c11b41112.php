<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/default.latte */
final class Template_7c11b41112 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Users/default.latte';

	public const Blocks = [
		['head' => 'blockHead', 'content' => 'blockContent'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		$this->renderBlock('head', get_defined_vars()) /* line 2 */;
		echo "\n";
		$this->renderBlock('content', get_defined_vars()) /* line 8 */;
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['userItem' => '135, 273, 364', 'tenantGroup' => '201'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block head} on line 2 */
	public function blockHead(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		if ($isSuperAdmin) /* line 3 */ {
			echo '        <link rel="stylesheet" href="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 4 */;
			echo '/css/users-super-admin.css">
';
		}
	}


	/** {block content} on line 8 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '
<div class="users-container">
    <!-- Záhlaví s názvem sekce a počtem uživatelů -->
    <div class="section-header-row mb-4">
        <div>
            <h1 class="section-title mb-0">
';
		if ($isSuperAdmin) /* line 15 */ {
			echo '                    Správa uživatelů <span class="total-count">Celkem: ';
			echo LR\Filters::escapeHtmlText($totalUsers) /* line 16 */;
			echo ' uživatelů ve ';
			echo LR\Filters::escapeHtmlText($superAdminStats['total_tenants']) /* line 16 */;
			echo ' tenantů</span>
';
		} else /* line 17 */ {
			echo '                    Uživatelé <span class="total-count">Počet uživatelů v systému: ';
			echo LR\Filters::escapeHtmlText($totalUsers) /* line 18 */;
			echo '</span>
';
		}
		echo '            </h1>
            <p class="text-muted">
';
		if ($isSuperAdmin) /* line 22 */ {
			echo '                    Super admin pohled - správa všech uživatelů napříč tenanty
';
		} else /* line 24 */ {
			echo '                    Správa uživatelských účtů v systému
';
		}
		echo '            </p>
        </div>
        <div class="header-actions">
';
		if ($isSuperAdmin && $searchQuery) /* line 30 */ {
			echo '                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 31 */;
			echo '" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Zpět na přehled
                </a>
';
		}
		echo '            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 35 */;
		echo '" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Přidat uživatele
            </a>
        </div>
    </div>

';
		if ($isSuperAdmin) /* line 42 */ {
			echo '    <div class="search-panel mb-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    ';
			$form = $this->global->formsStack[] = $this->global->uiControl['searchForm'] /* line 47 */;
			Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'search-form']) /* line 47 */;
			echo '
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    ';
			echo Nette\Bridges\FormsLatte\Runtime::item('search', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-lg']) /* line 51 */;
			echo '
                                    ';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('search', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 52 */;
			echo '
                                </div>
                                <small class="form-text text-muted mt-1">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Vyhledávejte podle jména uživatele, e-mailu, názvu firmy nebo tenanta
                                </small>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2 justify-content-center">
                                    ';
			echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary']) /* line 61 */;
			echo '
                                    ';
			echo Nette\Bridges\FormsLatte\Runtime::item('clear', $this->global)->getControl()->addAttributes(['class' => 'btn btn-outline-secondary']) /* line 62 */;
			echo '
                                </div>
                            </div>
                        </div>
                    ';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 66 */;

			echo '
                </div>
            </div>
        </div>
    </div>

';
			if (!$searchQuery) /* line 73 */ {
				echo '    <div class="stats-cards mb-4">
        <div class="row g-3">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-primary">';
				echo LR\Filters::escapeHtmlText($superAdminStats['total_tenants']) /* line 78 */;
				echo '</div>
                    <div class="stat-label">Tenanty</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-secondary">';
				echo LR\Filters::escapeHtmlText($superAdminStats['total_users']) /* line 84 */;
				echo '</div>
                    <div class="stat-label">Uživatelé</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-admin">';
				echo LR\Filters::escapeHtmlText($superAdminStats['total_admins']) /* line 90 */;
				echo '</div>
                    <div class="stat-label">Admini</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-accountant">';
				echo LR\Filters::escapeHtmlText($superAdminStats['total_accountants']) /* line 96 */;
				echo '</div>
                    <div class="stat-label">Účetní</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-muted">';
				echo LR\Filters::escapeHtmlText($superAdminStats['total_readonly']) /* line 102 */;
				echo '</div>
                    <div class="stat-label">Readonly</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card stat-card-primary">
                    <div class="stat-number text-white">';
				echo LR\Filters::escapeHtmlText($superAdminStats['active_users_30d']) /* line 108 */;
				echo '</div>
                    <div class="stat-label text-white">Aktivní (30d)</div>
                </div>
            </div>
        </div>
    </div>
';
			}
		}
		echo "\n";
		if ($isSuperAdmin && $searchQuery && count($searchResults) > 0) /* line 118 */ {
			echo '    <div class="search-results mb-4">
        <h3>Výsledky vyhledávání pro "';
			echo LR\Filters::escapeHtmlText($searchQuery) /* line 120 */;
			echo '" (';
			echo LR\Filters::escapeHtmlText(count($searchResults)) /* line 120 */;
			echo ' výsledků)</h3>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Uživatel</th>
                        <th>Tenant / Firma</th>
                        <th>E-mail</th>
                        <th>Role</th>
                        <th>Poslední přihlášení</th>
                        <th class="text-end">Akce</th>
                    </tr>
                </thead>
                <tbody>
';
			foreach ($searchResults as $userItem) /* line 135 */ {
				echo '                    <tr class="data-row">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3">
                                    <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <strong>';
				echo LR\Filters::escapeHtmlText($userItem->username) /* line 143 */;
				echo '</strong>
';
				if ($userItem->first_name || $userItem->last_name) /* line 144 */ {
					echo '                                        <br><small class="text-muted">';
					echo LR\Filters::escapeHtmlText($userItem->first_name) /* line 145 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText($userItem->last_name) /* line 145 */;
					echo '</small>
';
				}
				echo '                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>';
				echo LR\Filters::escapeHtmlText($userItem->tenant_name) /* line 152 */;
				echo '</strong>
';
				if ($userItem->company_name && $userItem->company_name !== $userItem->tenant_name) /* line 153 */ {
					echo '                                    <br><small class="text-muted">';
					echo LR\Filters::escapeHtmlText($userItem->company_name) /* line 154 */;
					echo '</small>
';
				}
				echo '                            </div>
                        </td>
                        <td>
                            <a href="mailto:';
				echo LR\Filters::escapeHtmlAttr($userItem->email) /* line 159 */;
				echo '" class="user-email-link">';
				echo LR\Filters::escapeHtmlText($userItem->email) /* line 159 */;
				echo '</a>
                        </td>
                        <td>
';
				if ($userItem->is_super_admin) /* line 162 */ {
					echo '                                <span class="badge badge-super-admin">Super Admin</span>
';
				} elseif ($userItem->role === 'admin') /* line 164 */ {
					echo '                                <span class="badge badge-admin">Admin</span>
';
				} elseif ($userItem->role === 'accountant') /* line 166 */ {
					echo '                                <span class="badge badge-accountant">Účetní</span>
';
				} else /* line 168 */ {
					echo '                                <span class="badge badge-readonly">Readonly</span>
';
				}


				echo '                        </td>
                        <td>
';
				if ($userItem->last_login) /* line 173 */ {
					echo '                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($userItem->last_login, 'd.m.Y H:i')) /* line 174 */;
					echo "\n";
				} else /* line 175 */ {
					echo '                                <span class="text-muted">Nikdy</span>
';
				}
				echo '                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$userItem->id])) /* line 181 */;
				echo '" class="btn btn-icon btn-primary" title="Upravit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-icon btn-warning" onclick="loadUserForMove(';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->id)) /* line 184 */;
				echo ', \'';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->username)) /* line 184 */;
				echo '\', \'';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->tenant_name)) /* line 184 */;
				echo '\')" title="Přesunout do jiného tenanta">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
';

			}

			echo '                </tbody>
            </table>
        </div>
    </div>
';
		}
		echo "\n";
		if ($isSuperAdmin && !$searchQuery && count($groupedUsers) > 0) /* line 198 */ {
			echo '    <div class="tenants-accordion">
        <div class="accordion" id="tenantsAccordion">
';
			foreach ($groupedUsers as $tenantGroup) /* line 201 */ {
				echo '            <div class="accordion-item tenant-accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#tenant';
				echo LR\Filters::escapeHtmlAttr($tenantGroup['tenant_id']) /* line 205 */;
				echo '" 
                        aria-expanded="false" 
                        aria-controls="tenant';
				echo LR\Filters::escapeHtmlAttr($tenantGroup['tenant_id']) /* line 207 */;
				echo '">
                        <div class="tenant-summary w-100">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div class="tenant-info">
                                    <div class="tenant-name">
                                        <i class="bi bi-building me-2 text-primary"></i>
                                        <strong>';
				echo LR\Filters::escapeHtmlText($tenantGroup['company_name']) /* line 213 */;
				echo '</strong>
                                    </div>
                                    <div class="tenant-details mt-1">
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="bi bi-people me-1"></i>
                                            ';
				echo LR\Filters::escapeHtmlText($tenantGroup['user_count']) /* line 218 */;
				echo ' uživatelů
                                        </span>
                                        <span class="badge bg-admin me-2">
                                            <i class="bi bi-shield-check me-1"></i>
                                            ';
				echo LR\Filters::escapeHtmlText($tenantGroup['admin_count']) /* line 222 */;
				echo ' adminů
                                        </span>
';
				if ($tenantGroup['owner']) /* line 224 */ {
					echo '                                            <span class="badge bg-owner me-3">
                                                <i class="bi bi-crown me-1"></i>
                                                Majitel: ';
					echo LR\Filters::escapeHtmlText($tenantGroup['owner']->username) /* line 227 */;
					echo '
                                            </span>
';
				}
				echo '                                    </div>
                                </div>
                                <div class="tenant-meta">
                                    <span class="badge bg-secondary">ID: ';
				echo LR\Filters::escapeHtmlText($tenantGroup['tenant_id']) /* line 233 */;
				echo '</span>
                                </div>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="tenant';
				echo LR\Filters::escapeHtmlAttr($tenantGroup['tenant_id']) /* line 239 */;
				echo '" 
                    class="accordion-collapse collapse" 
                    data-bs-parent="#tenantsAccordion">
                    <div class="accordion-body">
';
				if ($tenantGroup['company_email'] || $tenantGroup['company_phone']) /* line 243 */ {
					echo '                        <div class="tenant-contact-info mb-3">
';
					if ($tenantGroup['company_email']) /* line 245 */ {
						echo '                                <span class="me-4">
                                    <i class="bi bi-envelope me-2 text-muted"></i>
                                    <a href="mailto:';
						echo LR\Filters::escapeHtmlAttr($tenantGroup['company_email']) /* line 248 */;
						echo '" class="tenant-contact-link">';
						echo LR\Filters::escapeHtmlText($tenantGroup['company_email']) /* line 248 */;
						echo '</a>
                                </span>
';
					}
					if ($tenantGroup['company_phone']) /* line 251 */ {
						echo '                                <span>
                                    <i class="bi bi-telephone me-2 text-muted"></i>
                                    <a href="tel:';
						echo LR\Filters::escapeHtmlAttr($tenantGroup['company_phone']) /* line 254 */;
						echo '" class="tenant-contact-link">';
						echo LR\Filters::escapeHtmlText($tenantGroup['company_phone']) /* line 254 */;
						echo '</a>
                                </span>
';
					}
					echo '                        </div>
';
				}
				echo '
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Uživatel</th>
                                        <th>E-mail</th>
                                        <th>Role</th>
                                        <th>Vytvořen</th>
                                        <th>Poslední přihlášení</th>
                                        <th class="text-end">Akce</th>
                                    </tr>
                                </thead>
                                <tbody>
';
				foreach ($tenantGroup['users'] as $userItem) /* line 273 */ {
					echo '                                    <tr class="data-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                                                </div>
                                                <div>
                                                    <strong>';
					echo LR\Filters::escapeHtmlText($userItem->username) /* line 281 */;
					echo '</strong>
';
					if ($userItem->first_name || $userItem->last_name) /* line 282 */ {
						echo '                                                        <br><small class="text-muted">';
						echo LR\Filters::escapeHtmlText($userItem->first_name) /* line 283 */;
						echo ' ';
						echo LR\Filters::escapeHtmlText($userItem->last_name) /* line 283 */;
						echo '</small>
';
					}
					if ($userItem->id === $currentUser->id) /* line 285 */ {
						echo '                                                        <span class="badge bg-info ms-2">To jste vy</span>
';
					}
					echo '                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:';
					echo LR\Filters::escapeHtmlAttr($userItem->email) /* line 292 */;
					echo '" class="user-email-link">';
					echo LR\Filters::escapeHtmlText($userItem->email) /* line 292 */;
					echo '</a>
                                        </td>
                                        <td>
';
					if ($userItem->is_super_admin) /* line 295 */ {
						echo '                                                <span class="badge badge-super-admin">Super Admin</span>
';
					} elseif ($userItem->role === 'admin') /* line 297 */ {
						echo '                                                <span class="badge badge-admin">Admin</span>
';
					} elseif ($userItem->role === 'accountant') /* line 299 */ {
						echo '                                                <span class="badge badge-accountant">Účetní</span>
';
					} else /* line 301 */ {
						echo '                                                <span class="badge badge-readonly">Readonly</span>
';
					}


					echo '                                        </td>
                                        <td>
';
					if ($userItem->created_at) /* line 306 */ {
						echo '                                                ';
						echo LR\Filters::escapeHtmlText(($this->filters->date)($userItem->created_at, 'd.m.Y')) /* line 307 */;
						echo "\n";
					} else /* line 308 */ {
						echo '                                                <span class="text-muted">—</span>
';
					}
					echo '                                        </td>
                                        <td>
';
					if ($userItem->last_login) /* line 313 */ {
						echo '                                                ';
						echo LR\Filters::escapeHtmlText(($this->filters->date)($userItem->last_login, 'd.m.Y H:i')) /* line 314 */;
						echo "\n";
					} else /* line 315 */ {
						echo '                                                <span class="text-muted">Nikdy</span>
';
					}
					echo '                                        </td>
                                        <td class="text-end">
                                            <div class="action-buttons">
                                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$userItem->id])) /* line 321 */;
					echo '" class="btn btn-icon btn-primary" title="Upravit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-warning" onclick="loadUserForMove(';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->id)) /* line 324 */;
					echo ', \'';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->username)) /* line 324 */;
					echo '\', \'Tenant ID: ';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->tenant_id)) /* line 324 */;
					echo '\')" title="Přesunout do jiného tenanta">
                                                    <i class="bi bi-arrow-left-right"></i>
                                                </button>
';
					if ($userItem->id !== $currentUser->id) /* line 327 */ {
						echo '                                                    <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$userItem->id])) /* line 328 */;
						echo '" class="btn btn-icon btn-danger" 
                                                       onclick="return confirm(\'Opravdu chcete smazat uživatele ';
						echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->username)) /* line 329 */;
						echo '?\')" 
                                                       title="Smazat uživatele">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
';
					}
					echo '                                            </div>
                                        </td>
                                    </tr>
';

				}

				echo '                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
';

			}

			echo '        </div>
    </div>
';
		}
		echo "\n";
		if (!$isSuperAdmin && $totalUsers > 0) /* line 350 */ {
			echo '    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Uživatel</th>
                    <th>E-mail</th>
                    <th>Role</th>
                    <th>Vytvořen</th>
                    <th>Poslední přihlášení</th>
                    <th class="text-end">Akce</th>
                </tr>
            </thead>
            <tbody>
';
			foreach ($users as $userItem) /* line 364 */ {
				echo '                <tr class="data-row">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                                <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <strong>';
				echo LR\Filters::escapeHtmlText($userItem->username) /* line 372 */;
				echo '</strong>
';
				if ($userItem->first_name || $userItem->last_name) /* line 373 */ {
					echo '                                    <br><small class="text-muted">';
					echo LR\Filters::escapeHtmlText($userItem->first_name) /* line 374 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText($userItem->last_name) /* line 374 */;
					echo '</small>
';
				}
				if ($userItem->id === $currentUser->id) /* line 376 */ {
					echo '                                    <span class="badge bg-info ms-2">To jste vy</span>
';
				}
				echo '                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="mailto:';
				echo LR\Filters::escapeHtmlAttr($userItem->email) /* line 383 */;
				echo '" class="user-email-link">';
				echo LR\Filters::escapeHtmlText($userItem->email) /* line 383 */;
				echo '</a>
                    </td>
                    <td>
';
				if ($userItem->is_super_admin) /* line 386 */ {
					echo '                            <span class="badge badge-super-admin">Super Admin</span>
';
				} elseif ($userItem->role === 'admin') /* line 388 */ {
					echo '                            <span class="badge badge-admin">Admin</span>
';
				} elseif ($userItem->role === 'accountant') /* line 390 */ {
					echo '                            <span class="badge badge-accountant">Účetní</span>
';
				} else /* line 392 */ {
					echo '                            <span class="badge badge-readonly">Readonly</span>
';
				}


				echo '                    </td>
                    <td>
';
				if ($userItem->created_at) /* line 397 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($userItem->created_at, 'd.m.Y')) /* line 398 */;
					echo "\n";
				} else /* line 399 */ {
					echo '                            <span class="text-muted">—</span>
';
				}
				echo '                    </td>
                    <td>
';
				if ($userItem->last_login) /* line 404 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($userItem->last_login, 'd.m.Y H:i')) /* line 405 */;
					echo "\n";
				} else /* line 406 */ {
					echo '                            <span class="text-muted">Nikdy</span>
';
				}
				echo '                    </td>
                    <td class="text-end">
                        <div class="action-buttons">
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$userItem->id])) /* line 412 */;
				echo '" class="btn btn-icon btn-primary" title="Upravit">
                                <i class="bi bi-pencil"></i>
                            </a>
';
				if ($userItem->id !== $currentUser->id) /* line 415 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$userItem->id])) /* line 416 */;
					echo '" class="btn btn-icon btn-danger" 
                                   onclick="return confirm(\'Opravdu chcete smazat uživatele ';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->username)) /* line 417 */;
					echo '?\')" 
                                   title="Smazat uživatele">
                                    <i class="bi bi-trash"></i>
                                </a>
';
				}
				echo '                        </div>
                    </td>
                </tr>
';

			}

			echo '            </tbody>
        </table>
    </div>
';
		}
		echo "\n";
		if ($totalUsers == 0) /* line 432 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-people"></i>
        </div>
        <h3>Zatím zde nejsou žádní uživatelé</h3>
        <p>Začněte přidáním nového uživatele do systému</p>
        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 439 */;
			echo '" class="btn btn-primary mt-3">
            <i class="bi bi-person-plus"></i> Přidat prvního uživatele
        </a>
    </div>
';
		}
		echo '</div>

';
		if ($isSuperAdmin) /* line 447 */ {
			echo '<div class="modal fade" id="moveTenantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #B1D235; color: #212529;">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-left-right me-2"></i>
                    Přesunout uživatele mezi tenanty
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavřít"></button>
            </div>
            ';
			$form = $this->global->formsStack[] = $this->global->uiControl['moveTenantForm'] /* line 458 */;
			Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 458 */;
			echo '
            <div class="modal-body">
                ';
			echo Nette\Bridges\FormsLatte\Runtime::item('user_id', $this->global)->getControl() /* line 460 */;
			echo '
                
                <div id="currentUserInfo"></div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Pozor!</strong> Přesunutí uživatele do jiného tenanta je nevratná operace. 
                    Uživatel ztratí přístup ke všem datům současného tenanta.
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        ';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('new_tenant_id', $this->global)->getLabel())?->addAttributes(['class' => 'form-label fw-bold']) /* line 473 */;
			echo '
                        ';
			echo Nette\Bridges\FormsLatte\Runtime::item('new_tenant_id', $this->global)->getControl()->addAttributes(['class' => 'form-select form-select-lg']) /* line 474 */;
			echo '
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Formát: Název firmy - Tenant (ID: číslo)
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    ';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('reason', $this->global)->getLabel())?->addAttributes(['class' => 'form-label fw-bold']) /* line 483 */;
			echo '
                    ';
			echo Nette\Bridges\FormsLatte\Runtime::item('reason', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 484 */;
			echo '
                    <div class="form-text">
                        <i class="bi bi-pencil me-1"></i>
                        Důvod bude zalogován do bezpečnostního protokolu
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Zrušit
                </button>
                ';
			echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-warning btn-lg', 'value' => 'Přesunout uživatele']) /* line 495 */;
			echo '
            </div>
            ';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 497 */;

			echo '
        </div>
    </div>
</div>
';
		}
		echo "\n";
		if ($isSuperAdmin) /* line 504 */ {
			echo '    <script src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 505 */;
			echo '/js/users-super-admin.js" defer></script>
';
		}
		echo "\n";
	}
}
