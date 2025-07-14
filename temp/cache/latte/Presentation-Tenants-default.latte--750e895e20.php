<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Tenants/default.latte */
final class Template_750e895e20 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Tenants/default.latte';

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
			foreach (array_intersect_key(['tenant' => '71'], $this->params) as $ʟ_v => $ʟ_l) {
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
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 15 */;
		echo '" class="btn btn-primary-custom">
            <i class="bi bi-plus-circle me-2"></i>
            Vytvořit nový tenant
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stats-card">
        <div class="stats-number" style="color: #B1D235;">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_tenants']) /* line 25 */;
		echo '</div>
        <div class="stats-label">Celkem tenantů</div>
    </div>
    <div class="stats-card">
        <div class="stats-number" style="color: #95B11F;">';
		echo LR\Filters::escapeHtmlText($dashboardStats['active_tenants']) /* line 29 */;
		echo '</div>
        <div class="stats-label">Aktivní</div>
    </div>
    <div class="stats-card">
        <div class="stats-number" style="color: #6c757d;">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_users']) /* line 33 */;
		echo '</div>
        <div class="stats-label">Celkem uživatelů</div>
    </div>
    <div class="stats-card">
        <div class="stats-number" style="color: #212529;">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_invoices']) /* line 37 */;
		echo '</div>
        <div class="stats-label">Celkem faktur</div>
    </div>
    <div class="stats-card">
        <div class="stats-number" style="color: #95B11F;">
            ';
		if ($dashboardStats['monthly_growth'] > 0) /* line 42 */ {
			echo '+';
		}
		echo LR\Filters::escapeHtmlText(($this->filters->number)($dashboardStats['monthly_growth'], 1)) /* line 42 */;
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
		echo LR\Filters::escapeHtmlText(count($tenants)) /* line 53 */;
		echo ')
        </h5>
    </div>
    <div class="p-0">
';
		if (count($tenants) > 0) /* line 57 */ {
			echo '            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Společnost</th>
                            <th>Administrátor</th>
                            <th>Statistiky</th>
                            <th>Stav</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
';
			foreach ($tenants as $tenant) /* line 71 */ {
				echo '                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="tenant-icon me-3">
                                            <i class="bi bi-building" style="color: #B1D235; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">';
				echo LR\Filters::escapeHtmlText($tenant['tenant']['name']) /* line 79 */;
				echo '</h6>
                                            <small class="text-muted">
                                                Tenant-';
				echo LR\Filters::escapeHtmlText($tenant['tenant']['id']) /* line 81 */;
				echo "\n";
				if ($tenant['tenant']['domain']) /* line 82 */ {
					echo '                                                    • ';
					echo LR\Filters::escapeHtmlText($tenant['tenant']['domain']) /* line 83 */;
					echo "\n";
				}
				echo '                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
';
				if ($tenant['company']) /* line 90 */ {
					echo '                                        <div>
                                            <strong>';
					echo LR\Filters::escapeHtmlText($tenant['company']['name']) /* line 92 */;
					echo '</strong>
';
					if ($tenant['company']['ic']) /* line 93 */ {
						echo '                                                <br><small class="text-muted">@';
						echo LR\Filters::escapeHtmlText($tenant['company']['ic']) /* line 94 */;
						echo '</small>
';
					}
					echo '                                        </div>
';
				} else /* line 97 */ {
					echo '                                        <span class="text-muted">Není nastaveno</span>
';
				}
				echo '                                </td>
                                <td>
';
				if ($tenant['admin_user']) /* line 102 */ {
					echo '                                        <div>
                                            <strong>';
					echo LR\Filters::escapeHtmlText($tenant['admin_user']['first_name']) /* line 104 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText($tenant['admin_user']['last_name']) /* line 104 */;
					echo '</strong>
                                            <br><small class="text-muted">@';
					echo LR\Filters::escapeHtmlText($tenant['admin_user']['username']) /* line 105 */;
					echo '</small>
                                            <br><small class="text-muted">✉ ';
					echo LR\Filters::escapeHtmlText($tenant['admin_user']['email']) /* line 106 */;
					echo '</small>
                                            <br><small style="color: #B1D235;">⚡ ';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($tenant['admin_user']['created_at'], 'd.m.Y H:i')) /* line 107 */;
					echo '</small>
                                        </div>
';
				} else /* line 109 */ {
					echo '                                        <span class="text-muted">Bez admina</span>
';
				}
				echo '                                </td>
                                <td>
                                    <div class="small">
                                        <span class="badge" style="background-color: #B1D235; color: #212529;">👥 ';
				echo LR\Filters::escapeHtmlText($tenant['stats']['users_count']) /* line 115 */;
				echo ' uživatelů</span><br>
                                        <span class="badge bg-success">📄 ';
				echo LR\Filters::escapeHtmlText($tenant['stats']['invoices_count']) /* line 116 */;
				echo ' faktur</span><br>
                                        <span class="badge bg-info">🏢 ';
				echo LR\Filters::escapeHtmlText($tenant['stats']['clients_count']) /* line 117 */;
				echo ' klientů</span><br>
                                        <span class="badge" style="background-color: #95B11F; color: white;">🔧 ';
				echo LR\Filters::escapeHtmlText($tenant['stats']['modules_count']) /* line 118 */;
				echo ' modulů</span>
                                    </div>
                                </td>
                                <td>
';
				if ($tenant['tenant']['status'] === 'active') /* line 122 */ {
					echo '                                        <span class="badge bg-success">✅ Aktivní</span>
';
				} else /* line 124 */ {
					echo '                                        <span class="badge bg-secondary">⏸️ Neaktivní</span>
';
				}
				echo '                                </td>
                                <td>
                                    <div class="d-flex gap-1">
';
				if ($tenant['tenant']['status'] === 'active') /* line 130 */ {
					echo '                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="deactivateTenant(';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenant['tenant']['id'])) /* line 132 */;
					echo ', \'';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escapeHtml)($tenant['tenant']['name']))) /* line 132 */;
					echo '\')"
                                                    title="Deaktivovat tenant">
                                                <i class="bi bi-pause"></i>
                                            </button>
';
				} else /* line 136 */ {
					echo '                                            <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('activate', ['id' => $tenant['tenant']['id']])) /* line 137 */;
					echo '" 
                                               class="btn btn-sm btn-outline-success"
                                               title="Aktivovat tenant"
                                               onclick="return confirm(\'Opravdu chcete aktivovat tento tenant?\')">
                                                <i class="bi bi-play"></i>
                                            </a>
';
				}
				echo '                                        
';
				if ($tenant['tenant']['id'] !== 1) /* line 145 */ {
					echo '                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteTenant(';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenant['tenant']['id'])) /* line 147 */;
					echo ', \'';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs(($this->filters->escapeHtml)($tenant['tenant']['name']))) /* line 147 */;
					echo '\')"
                                                    title="Smazat tenant">
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
		} else /* line 159 */ {
			echo '            <div class="text-center py-5">
                <div class="display-1 text-muted mb-3">
                    <i class="bi bi-building"></i>
                </div>
                <h4>Žádní tenanty</h4>
                <p class="text-muted">Začněte vytvořením prvního tenanta pro vaše zákazníky.</p>
                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 166 */;
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
            
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['deleteTenantForm'] /* line 220 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 220 */;
		echo '
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
		echo Nette\Bridges\FormsLatte\Runtime::item('tenant_id', $this->global)->getControl() /* line 234 */;
		echo '
                    
                    <div class="mb-3">
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('reason', $this->global)->getLabel())?->addAttributes(['class' => 'form-label fw-bold']) /* line 237 */;
		echo '
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('reason', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 238 */;
		echo '
                    </div>
                    
                    <div class="mb-3">
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('confirmation', $this->global)->getLabel())?->addAttributes(['class' => 'form-label fw-bold']) /* line 242 */;
		echo '
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('confirmation', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 243 */;
		echo '
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-danger btn-lg', 'value' => 'SMAZAT TENANT']) /* line 248 */;
		echo '
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 250 */;

		echo '
            
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
    window.location.href = \'/tenants/deactivate/\' + currentTenantId + \'?reason=\' + encodeURIComponent(reason);
}

function deleteTenant(tenantId, tenantName) {
    document.getElementById(\'delete-tenant-name\').textContent = tenantName;
    
    const tenantIdInput = document.querySelector(\'input[name="tenant_id"]\');
    if (tenantIdInput) {
        tenantIdInput.value = tenantId;
    }
    
    const modal = new bootstrap.Modal(document.getElementById(\'deleteModal\'));
    modal.show();
}
</script>

';
	}
}
