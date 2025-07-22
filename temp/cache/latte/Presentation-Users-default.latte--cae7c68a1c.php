<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/default.latte */
final class Template_cae7c68a1c extends Latte\Runtime\Template
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
			foreach (array_intersect_key(['userItem' => '158, 313, 416', 'tenantGroup' => '233'], $this->params) as $ʟ_v => $ʟ_l) {
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
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($totalUsers)) /* line 17 */;
			echo ' uživatelů ve ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_tenants'])) /* line 17 */;
			echo ' tenantů</span>
';
		} else /* line 18 */ {
			echo '                    Uživatelé <span class="total-count">Počet uživatelů v systému: ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($totalUsers)) /* line 20 */;
			echo '</span>
';
		}
		echo '            </h1>
            <p class="text-muted">
';
		if ($isSuperAdmin) /* line 24 */ {
			echo '                    Super admin pohled - správa všech uživatelů napříč tenanty
';
		} else /* line 27 */ {
			echo '                    Správa uživatelských účtů v systému
';
		}
		echo '            </p>
        </div>
        <div class="header-actions">
';
		if ($isSuperAdmin && $searchQuery) /* line 34 */ {
			echo '                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 36 */;
			echo '" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Zpět na přehled
                </a>
';
		}
		echo '            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 41 */;
		echo '" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Přidat uživatele
            </a>
        </div>
    </div>

';
		if ($isSuperAdmin) /* line 48 */ {
			echo '    <div class="search-panel mb-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    ';
			$form = $this->global->formsStack[] = $this->global->uiControl['searchForm'] /* line 53 */;
			Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'search-form']) /* line 53 */;
			echo '
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    ';
			echo Nette\Bridges\FormsLatte\Runtime::item('search', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-lg']) /* line 58 */;
			echo '
                                    ';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('search', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 59 */;
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
			echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary']) /* line 70 */;
			echo '
                                    ';
			echo Nette\Bridges\FormsLatte\Runtime::item('clear', $this->global)->getControl()->addAttributes(['class' => 'btn btn-outline-secondary']) /* line 71 */;
			echo '
                                </div>
                            </div>
                        </div>
                    ';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 75 */;

			echo '
                </div>
            </div>
        </div>
    </div>

';
			if (!$searchQuery) /* line 82 */ {
				echo '    <div class="stats-cards mb-4">
        <div class="row g-3">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-primary">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_tenants'])) /* line 88 */;
				echo '</div>
                    <div class="stat-label">Tenanty</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-secondary">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_users'])) /* line 96 */;
				echo '</div>
                    <div class="stat-label">Uživatelé</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-admin">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_admins'])) /* line 104 */;
				echo '</div>
                    <div class="stat-label">Admini</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-accountant">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_accountants'])) /* line 112 */;
				echo '</div>
                    <div class="stat-label">Účetní</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number text-muted">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_readonly'])) /* line 120 */;
				echo '</div>
                    <div class="stat-label">Readonly</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card stat-card-primary">
                    <div class="stat-number text-white">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['active_users_30d'])) /* line 128 */;
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
		if ($isSuperAdmin && $searchQuery && count($searchResults) > 0) /* line 139 */ {
			echo '    <div class="search-results mb-4">
        <h3>Výsledky vyhledávání pro "';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($searchQuery)) /* line 142 */;
			echo '" (';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)(count($searchResults))) /* line 142 */;
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
			foreach ($searchResults as $userItem) /* line 158 */ {
				echo '                    <tr class="data-row">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3">
                                    <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->username)) /* line 167 */;
				echo '</strong>
';
				if ($userItem->first_name || $userItem->last_name) /* line 168 */ {
					echo '                                        <br><small class="text-muted">';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->first_name)) /* line 170 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->last_name)) /* line 170 */;
					echo '</small>
';
				}
				echo '                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->tenant_name)) /* line 178 */;
				echo '</strong>
';
				if ($userItem->company_name && $userItem->company_name !== $userItem->tenant_name) /* line 179 */ {
					echo '                                    <br><small class="text-muted">';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->company_name)) /* line 181 */;
					echo '</small>
';
				}
				echo '                            </div>
                        </td>
                        <td>
                            <a href="mailto:';
				echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($userItem->email)) /* line 187 */;
				echo '" class="user-email-link">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->email)) /* line 187 */;
				echo '</a>
                        </td>
                        <td>
';
				if ($userItem->is_super_admin) /* line 190 */ {
					echo '                                <span class="badge badge-super-admin">Super Admin</span>
';
				} elseif ($userItem->role === 'admin') /* line 192 */ {
					echo '                                <span class="badge badge-admin">Admin</span>
';
				} elseif ($userItem->role === 'accountant') /* line 194 */ {
					echo '                                <span class="badge badge-accountant">Účetní</span>
';
				} else /* line 196 */ {
					echo '                                <span class="badge badge-readonly">Readonly</span>
';
				}


				echo '                        </td>
                        <td>
';
				if ($userItem->last_login) /* line 201 */ {
					echo '                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($userItem->last_login, 'd.m.Y H:i'))) /* line 203 */;
					echo "\n";
				} else /* line 204 */ {
					echo '                                <span class="text-muted">Nikdy</span>
';
				}
				echo '                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$userItem->id])) /* line 212 */;
				echo '" class="btn btn-icon btn-primary" title="Upravit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-icon btn-warning" onclick="loadUserForMove(';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escape)($userItem->id))) /* line 216 */;
				echo ', \'';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escape)($userItem->username))) /* line 216 */;
				echo '\', \'';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escape)($userItem->tenant_name))) /* line 216 */;
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
		if ($isSuperAdmin && !$searchQuery && count($groupedUsers) > 0) /* line 230 */ {
			echo '    <div class="tenants-accordion">
        <div class="accordion" id="tenantsAccordion">
';
			foreach ($groupedUsers as $tenantGroup) /* line 233 */ {
				echo '            <div class="accordion-item tenant-accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#tenant';
				echo LR\Filters::escapeHtmlAttr($tenantGroup['tenant_id']) /* line 237 */;
				echo '" 
                        aria-expanded="false" 
                        aria-controls="tenant';
				echo LR\Filters::escapeHtmlAttr($tenantGroup['tenant_id']) /* line 239 */;
				echo '">
                        <div class="tenant-summary w-100">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div class="tenant-info">
                                    <div class="tenant-name">
                                        <i class="bi bi-building me-2 text-primary"></i>
                                        <strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($tenantGroup['company_name'])) /* line 246 */;
				echo '</strong>
                                    </div>
                                    <div class="tenant-details mt-1">
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="bi bi-people me-1"></i>
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($tenantGroup['user_count'])) /* line 252 */;
				echo ' uživatelů
                                        </span>
                                        <span class="badge bg-admin me-2">
                                            <i class="bi bi-shield-check me-1"></i>
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($tenantGroup['admin_count'])) /* line 257 */;
				echo ' adminů
                                        </span>
';
				if ($tenantGroup['owner']) /* line 259 */ {
					echo '                                            <span class="badge bg-owner me-3">
                                                <i class="bi bi-crown me-1"></i>
                                                Majitel: ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($tenantGroup['owner']->username)) /* line 263 */;
					echo '
                                            </span>
';
				}
				echo '                                    </div>
                                </div>
                                <div class="tenant-meta">
                                    <span class="badge bg-secondary">ID: ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($tenantGroup['tenant_id'])) /* line 270 */;
				echo '</span>
                                </div>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="tenant';
				echo LR\Filters::escapeHtmlAttr($tenantGroup['tenant_id']) /* line 276 */;
				echo '" 
                    class="accordion-collapse collapse" 
                    data-bs-parent="#tenantsAccordion">
                    <div class="accordion-body">
';
				if ($tenantGroup['company_email'] || $tenantGroup['company_phone']) /* line 280 */ {
					echo '                        <div class="tenant-contact-info mb-3">
';
					if ($tenantGroup['company_email']) /* line 282 */ {
						echo '                                <span class="me-4">
                                    <i class="bi bi-envelope me-2 text-muted"></i>
                                    <a href="mailto:';
						echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($tenantGroup['company_email'])) /* line 286 */;
						echo '" class="tenant-contact-link">';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)($tenantGroup['company_email'])) /* line 286 */;
						echo '</a>
                                </span>
';
					}
					if ($tenantGroup['company_phone']) /* line 289 */ {
						echo '                                <span>
                                    <i class="bi bi-telephone me-2 text-muted"></i>
                                    <a href="tel:';
						echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($tenantGroup['company_phone'])) /* line 293 */;
						echo '" class="tenant-contact-link">';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)($tenantGroup['company_phone'])) /* line 293 */;
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
				foreach ($tenantGroup['users'] as $userItem) /* line 313 */ {
					echo '                                    <tr class="data-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                                                </div>
                                                <div>
                                                    <strong>';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->username)) /* line 322 */;
					echo '</strong>
';
					if ($userItem->first_name || $userItem->last_name) /* line 323 */ {
						echo '                                                        <br><small class="text-muted">';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->first_name)) /* line 325 */;
						echo ' ';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->last_name)) /* line 325 */;
						echo '</small>
';
					}
					if ($userItem->id === $currentUser->id) /* line 327 */ {
						echo '                                                        <span class="badge bg-info ms-2">To jste vy</span>
';
					}
					echo '                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($userItem->email)) /* line 336 */;
					echo '" class="user-email-link">';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->email)) /* line 336 */;
					echo '</a>
                                        </td>
                                        <td>
';
					if ($userItem->is_super_admin) /* line 339 */ {
						echo '                                                <span class="badge badge-super-admin">Super Admin</span>
';
					} elseif ($userItem->role === 'admin') /* line 341 */ {
						echo '                                                <span class="badge badge-admin">Admin</span>
';
					} elseif ($userItem->role === 'accountant') /* line 343 */ {
						echo '                                                <span class="badge badge-accountant">Účetní</span>
';
					} else /* line 345 */ {
						echo '                                                <span class="badge badge-readonly">Readonly</span>
';
					}


					echo '                                        </td>
                                        <td>
';
					if ($userItem->created_at) /* line 350 */ {
						echo '                                                ';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($userItem->created_at, 'd.m.Y'))) /* line 352 */;
						echo "\n";
					} else /* line 353 */ {
						echo '                                                <span class="text-muted">—</span>
';
					}
					echo '                                        </td>
                                        <td>
';
					if ($userItem->last_login) /* line 359 */ {
						echo '                                                ';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($userItem->last_login, 'd.m.Y H:i'))) /* line 361 */;
						echo "\n";
					} else /* line 362 */ {
						echo '                                                <span class="text-muted">Nikdy</span>
';
					}
					echo '                                        </td>
                                        <td class="text-end">
                                            <div class="action-buttons">
                                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$userItem->id])) /* line 370 */;
					echo '" class="btn btn-icon btn-primary" title="Upravit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-warning" onclick="loadUserForMove(';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escape)($userItem->id))) /* line 374 */;
					echo ', \'';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escape)($userItem->username))) /* line 374 */;
					echo '\', \'';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escape)($tenantGroup['company_name']))) /* line 374 */;
					echo '\')" title="Přesunout do jiného tenanta">
                                                    <i class="bi bi-arrow-left-right"></i>
                                                </button>
';
					if ($userItem->id !== $currentUser->id) /* line 377 */ {
						echo '                                                    <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$userItem->id])) /* line 379 */;
						echo '" class="btn btn-icon btn-danger" 
                                                       onclick="return confirm(\'Opravdu chcete smazat uživatele ';
						echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escape)($userItem->username))) /* line 380 */;
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
		if (!$isSuperAdmin && $totalUsers > 0) /* line 401 */ {
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
			foreach ($users as $userItem) /* line 416 */ {
				echo '                <tr class="data-row">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                                <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->username)) /* line 425 */;
				echo '</strong>
';
				if ($userItem->first_name || $userItem->last_name) /* line 426 */ {
					echo '                                    <br><small class="text-muted">';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->first_name)) /* line 428 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->last_name)) /* line 428 */;
					echo '</small>
';
				}
				if ($userItem->id === $currentUser->id) /* line 430 */ {
					echo '                                    <span class="badge bg-info ms-2">To jste vy</span>
';
				}
				echo '                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="mailto:';
				echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($userItem->email)) /* line 439 */;
				echo '" class="user-email-link">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($userItem->email)) /* line 439 */;
				echo '</a>
                    </td>
                    <td>
';
				if ($userItem->is_super_admin) /* line 442 */ {
					echo '                            <span class="badge badge-super-admin">Super Admin</span>
';
				} elseif ($userItem->role === 'admin') /* line 444 */ {
					echo '                            <span class="badge badge-admin">Admin</span>
';
				} elseif ($userItem->role === 'accountant') /* line 446 */ {
					echo '                            <span class="badge badge-accountant">Účetní</span>
';
				} else /* line 448 */ {
					echo '                            <span class="badge badge-readonly">Readonly</span>
';
				}


				echo '                    </td>
                    <td>
';
				if ($userItem->created_at) /* line 453 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($userItem->created_at, 'd.m.Y'))) /* line 455 */;
					echo "\n";
				} else /* line 456 */ {
					echo '                            <span class="text-muted">—</span>
';
				}
				echo '                    </td>
                    <td>
';
				if ($userItem->last_login) /* line 462 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($userItem->last_login, 'd.m.Y H:i'))) /* line 464 */;
					echo "\n";
				} else /* line 465 */ {
					echo '                            <span class="text-muted">Nikdy</span>
';
				}
				echo '                    </td>
                    <td class="text-end">
                        <div class="action-buttons">
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$userItem->id])) /* line 473 */;
				echo '" class="btn btn-icon btn-primary" title="Upravit">
                                <i class="bi bi-pencil"></i>
                            </a>
';
				if ($userItem->id !== $currentUser->id) /* line 476 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$userItem->id])) /* line 478 */;
					echo '" class="btn btn-icon btn-danger" 
                                   onclick="return confirm(\'Opravdu chcete smazat uživatele ';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escape)($userItem->username))) /* line 479 */;
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
		if ($totalUsers == 0) /* line 494 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-people"></i>
        </div>
        <h3>Zatím zde nejsou žádní uživatelé</h3>
        <p>Začněte přidáním nového uživatele do systému</p>
        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 503 */;
			echo '" class="btn btn-primary mt-3">
            <i class="bi bi-person-plus"></i> Přidat prvního uživatele
        </a>
    </div>
';
		}
		echo '</div>

';
		if ($isSuperAdmin) /* line 511 */ {
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
			$form = $this->global->formsStack[] = $this->global->uiControl['moveTenantForm'] /* line 523 */;
			Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 523 */;
			echo '
            <div class="modal-body">
                ';
			echo Nette\Bridges\FormsLatte\Runtime::item('user_id', $this->global)->getControl() /* line 526 */;
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
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('new_tenant_id', $this->global)->getLabel())?->addAttributes(['class' => 'form-label fw-bold']) /* line 541 */;
			echo '
                        ';
			echo Nette\Bridges\FormsLatte\Runtime::item('new_tenant_id', $this->global)->getControl()->addAttributes(['class' => 'form-select form-select-lg']) /* line 542 */;
			echo '
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Formát: Název firmy - Tenant (ID: číslo)
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    ';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('reason', $this->global)->getLabel())?->addAttributes(['class' => 'form-label fw-bold']) /* line 553 */;
			echo '
                    ';
			echo Nette\Bridges\FormsLatte\Runtime::item('reason', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 554 */;
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
			echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-warning btn-lg', 'value' => 'Přesunout uživatele']) /* line 567 */;
			echo '
            </div>
            ';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 569 */;

			echo '
        </div>
    </div>
</div>
';
		}
		echo "\n";
		if ($isSuperAdmin) /* line 576 */ {
			echo '    <script src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 577 */;
			echo '/js/users-super-admin.js" defer></script>
';
		}
		echo "\n";
	}
}
