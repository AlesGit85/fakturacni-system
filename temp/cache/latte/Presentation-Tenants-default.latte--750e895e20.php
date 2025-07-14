<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Tenants/default.latte */
final class Template_750e895e20 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Tenants/default.latte';

	public const Blocks = [
		['content' => 'blockContent', 'head' => 'blockHead'],
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
			foreach (array_intersect_key(['tenantData' => '75'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo "\n";
		$this->renderBlock('head', get_defined_vars()) /* line 4 */;
		echo '
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-building me-2" style="color: #B1D235;"></i>
            Správa tenantů
        </h1>
        <p class="page-subtitle">
            <i class="bi bi-shield-check me-1" style="color: #95B11F;"></i>
            Super admin rozhraní pro správu všech tenantů
        </p>
    </div>
    <div>
        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 20 */;
		echo '" class="btn btn-primary-custom">
            <i class="bi bi-plus-circle me-2"></i>
            Vytvořit nový tenant
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stats-card">
        <div class="stats-number" style="color: #B1D235;">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_tenants']) /* line 30 */;
		echo '</div>
        <div class="stats-label">Celkem tenantů</div>
    </div>
    <div class="stats-card">
        <div class="stats-number" style="color: #95B11F;">';
		echo LR\Filters::escapeHtmlText($dashboardStats['active_tenants']) /* line 34 */;
		echo '</div>
        <div class="stats-label">Aktivní</div>
    </div>
    <div class="stats-card">
        <div class="stats-number" style="color: #6c757d;">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_users']) /* line 38 */;
		echo '</div>
        <div class="stats-label">Celkem uživatelů</div>
    </div>
    <div class="stats-card">
        <div class="stats-number" style="color: #212529;">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_invoices']) /* line 42 */;
		echo '</div>
        <div class="stats-label">Celkem faktur</div>
    </div>
    <div class="stats-card">
        <div class="stats-number" style="color: #95B11F;">
            ';
		if ($dashboardStats['monthly_growth'] > 0) /* line 47 */ {
			echo '+';
		}
		echo LR\Filters::escapeHtmlText(($this->filters->number)($dashboardStats['monthly_growth'], 1)) /* line 47 */;
		echo '%
        </div>
        <div class="stats-label">Měsíční růst</div>
    </div>
</div>

<div class="main-card">
    <div class="card-header-custom">
        <h5 class="card-title-custom">
            <i class="bi bi-list-ul me-2" style="color: #B1D235;"></i>
            Seznam všech tenantů (';
		echo LR\Filters::escapeHtmlText(count($tenants)) /* line 58 */;
		echo ')
        </h5>
    </div>
    <div class="p-0">
';
		if (count($tenants) > 0) /* line 62 */ {
			echo '            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Administrátor</th>
                            <th>Statistiky</th>
                            <th>Stav</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
';
			foreach ($tenants as $tenantData) /* line 75 */ {
				$tenant = $tenantData['tenant'] /* line 76 */;
				$stats = $tenantData['stats'] /* line 77 */;
				$admin = $tenantData['admin_user'] /* line 78 */;
				$company = $tenantData['company'] /* line 79 */;
				$lastActivity = $tenantData['last_activity'] /* line 80 */;
				echo '                            
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px; background: linear-gradient(135deg, #B1D235 0%, #95B11F 100%);">
                                                <i class="bi bi-building text-white" style="font-size: 14px;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="tenant-name">';
				echo LR\Filters::escapeHtmlText($tenant['name']) /* line 92 */;
				echo '</div>
';
				if ($company) /* line 93 */ {
					echo '                                                <div class="tenant-detail">';
					echo LR\Filters::escapeHtmlText($company['name']) /* line 94 */;
					echo '</div>
';
				}
				if ($tenant['domain']) /* line 96 */ {
					echo '                                                <div class="tenant-detail">
                                                    <i class="bi bi-globe me-1"></i>';
					echo LR\Filters::escapeHtmlText($tenant['domain']) /* line 98 */;
					echo '
                                                </div>
';
				}
				echo '                                        </div>
                                    </div>
                                </td>
                                <td>
';
				if ($admin) /* line 105 */ {
					echo '                                        <div class="tenant-name">';
					echo LR\Filters::escapeHtmlText($admin['first_name']) /* line 106 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText($admin['last_name']) /* line 106 */;
					echo '</div>
                                        <div class="tenant-detail">@';
					echo LR\Filters::escapeHtmlText($admin['username']) /* line 107 */;
					echo '</div>
                                        <div class="tenant-detail">
                                            <i class="bi bi-envelope me-1"></i>';
					echo LR\Filters::escapeHtmlText($admin['email']) /* line 109 */;
					echo '
                                        </div>
';
					if ($lastActivity) /* line 111 */ {
						echo '                                            <div class="tenant-detail" style="color: #B1D235;">
                                                <i class="bi bi-activity me-1"></i>
                                                ';
						echo LR\Filters::escapeHtmlText(($this->filters->date)($lastActivity, 'd.m.Y H:i')) /* line 114 */;
						echo '
                                            </div>
';
					} else /* line 116 */ {
						echo '                                            <div class="tenant-detail">
                                                <i class="bi bi-clock me-1"></i>Nikdy nepřihlášen
                                            </div>
';
					}
				} else /* line 121 */ {
					echo '                                        <span class="tenant-detail">Žádný admin</span>
';
				}
				echo '                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge badge-custom badge-primary-custom" title="Počet uživatelů">
                                            <i class="bi bi-people me-1"></i>';
				echo LR\Filters::escapeHtmlText($stats['users_count']) /* line 128 */;
				echo ' uživatelů
                                        </span>
                                        <span class="badge badge-custom badge-secondary-custom" title="Počet faktur">
                                            <i class="bi bi-receipt me-1"></i>';
				echo LR\Filters::escapeHtmlText($stats['invoices_count']) /* line 131 */;
				echo ' faktur
                                        </span>
                                        <span class="badge badge-custom badge-neutral" title="Počet klientů">
                                            <i class="bi bi-person-badge me-1"></i>';
				echo LR\Filters::escapeHtmlText($stats['clients_count']) /* line 134 */;
				echo ' klientů
                                        </span>
                                        <span class="badge badge-custom badge-dark" title="Počet modulů">
                                            <i class="bi bi-puzzle me-1"></i>';
				echo LR\Filters::escapeHtmlText($stats['modules_count']) /* line 137 */;
				echo ' modulů
                                        </span>
                                    </div>
                                </td>
                                <td>
';
				if ($tenant['status'] === 'active') /* line 142 */ {
					echo '                                        <span class="badge badge-custom badge-active">
                                            <i class="bi bi-check-circle me-1"></i>Aktivní
                                        </span>
';
				} elseif ($tenant['status'] === 'inactive') /* line 146 */ {
					echo '                                        <span class="badge badge-custom badge-neutral">
                                            <i class="bi bi-pause-circle me-1"></i>Neaktivní
                                        </span>
';
				} elseif ($tenant['status'] === 'suspended') /* line 150 */ {
					echo '                                        <span class="badge badge-custom" style="background-color: #ffc107; color: #212529;">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Pozastavený
                                        </span>
';
				}


				echo '                                </td>
                                <td>
                                    <div class="d-flex">
';
				if ($tenant['status'] === 'active') /* line 158 */ {
					echo '                                            <button type="button" class="btn btn-action btn-warning-outline" 
                                                    onclick="deactivateTenant(';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenant['id'])) /* line 160 */;
					echo ', \'';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escapeHtml)($tenant['name']))) /* line 160 */;
					echo '\')"
                                                    title="Deaktivovat">
                                                <i class="bi bi-pause"></i>
                                            </button>
';
				} else /* line 164 */ {
					echo '                                            <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('activate', ['id' => $tenant['id']])) /* line 165 */;
					echo '" class="btn btn-action btn-success-outline" title="Aktivovat">
                                                <i class="bi bi-play"></i>
                                            </a>
';
				}
				echo '                                        
';
				if ($tenant['id'] !== 1) /* line 170 */ {
					echo '                                            <button type="button" class="btn btn-action btn-danger-outline" 
                                                    onclick="deleteTenant(';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenant['id'])) /* line 172 */;
					echo ', \'';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escapeHtml)($tenant['name']))) /* line 172 */;
					echo '\')"
                                                    title="Smazat">
                                                <i class="bi bi-trash"></i>
                                            </button>
';
				}
				echo '                                    </div>
                                </td>
                            </tr>
';

			}

			echo '                    </tbody>
                </table>
            </div>
';
		} else /* line 184 */ {
			echo '            <div class="text-center py-5">
                <div class="display-1 text-muted mb-3">
                    <i class="bi bi-building"></i>
                </div>
                <h4 class="tenant-name">Žádní tenanty</h4>
                <p class="tenant-detail">Začněte vytvořením prvního tenanta pro vaše zákazníky.</p>
                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 191 */;
			echo '" class="btn btn-primary-custom">
                    <i class="bi bi-plus-circle me-2"></i>
                    Vytvořit první tenant
                </a>
            </div>
';
		}
		echo '    </div>
</div>

<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff3cd;">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Potvrzení deaktivace
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Opravdu chcete deaktivovat tenant <strong id="deactivate-tenant-name"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    Uživatelé tenanta se nebudou moci přihlásit, dokud tenant znovu neaktivujete.
                </div>
                <div class="mb-3">
                    <label for="deactivate-reason" class="form-label">Důvod deaktivace:</label>
                    <textarea id="deactivate-reason" class="form-control" rows="3" 
                              placeholder="Uveďte důvod deaktivace..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                <button type="button" class="btn btn-warning" onclick="confirmDeactivate()">
                    <i class="bi bi-pause me-2"></i>Deaktivovat
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    NEBEZPEČNÁ OPERACE
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="delete-form" method="post">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>VAROVÁNÍ!</h6>
                        <p class="mb-0">Tato akce <strong>NENÁVRATNĚ SMAŽE</strong> tenant <strong id="delete-tenant-name"></strong> a všechna jeho data:</p>
                        <ul class="mt-2 mb-0">
                            <li>Všechny uživatele tenanta</li>
                            <li>Všechny faktury a jejich položky</li>
                            <li>Všechny klienty</li>
                            <li>Firemní údaje</li>
                            <li>Všechny moduly a jejich data</li>
                        </ul>
                    </div>
                    
                    ';
		$form = $this->global->formsStack[] = $this->global->uiControl['deleteTenantForm'] /* line 258 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 258 */;
		echo '
                        <input type="hidden" name="tenant_id" id="delete-tenant-id">
                        
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('reason', $this->global)->getLabel())?->addAttributes(['class' => 'form-label fw-bold']) /* line 262 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('reason', $this->global)->getControl() /* line 263 */;
		echo '
                        </div>
                        
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('confirmation', $this->global)->getLabel())?->addAttributes(['class' => 'form-label fw-bold']) /* line 267 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('confirmation', $this->global)->getControl() /* line 268 */;
		echo '
                        </div>
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 270 */;

		echo '
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                    <button type="submit" form="frm-deleteTenantForm" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>SMAZAT TENANT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentTenantId = null;

function deactivateTenant(tenantId, tenantName) {
    currentTenantId = tenantId;
    document.getElementById(\'deactivate-tenant-name\').textContent = tenantName;
    new bootstrap.Modal(document.getElementById(\'deactivateModal\')).show();
}

function confirmDeactivate() {
    const reason = document.getElementById(\'deactivate-reason\').value || \'Deaktivace super adminem\';
    // Redirect na deactivate akci
    window.location.href = \'/tenants/deactivate/\' + currentTenantId + \'?reason=\' + encodeURIComponent(reason);
}

function deleteTenant(tenantId, tenantName) {
    document.getElementById(\'delete-tenant-name\').textContent = tenantName;
    document.getElementById(\'delete-tenant-id\').value = tenantId;
    // Nastavíme tenant_id do formuláře
    document.querySelector(\'#frm-deleteTenantForm input[name="tenant_id"]\').value = tenantId;
    new bootstrap.Modal(document.getElementById(\'deleteModal\')).show();
}
</script>

';
	}


	/** {block head} on line 4 */
	public function blockHead(array $ʟ_args): void
	{
		echo "\n";
	}
}
