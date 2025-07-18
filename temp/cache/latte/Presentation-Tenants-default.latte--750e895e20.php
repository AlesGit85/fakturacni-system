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
			foreach (array_intersect_key(['tenantData' => '67'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="clients-container">
    <!-- Záhlaví s názvem sekce a počtem tenantů -->
    <div class="section-header-row mb-4">
        <div>
            <h1 class="section-title mb-0">Správa tenantů <span class="total-count">Celkem: ';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_tenants']) /* line 6 */;
		echo '</span></h1>
            <p class="text-muted">Super admin rozhraní pro správu všech tenantů v systému</p>
        </div>
        <div class="header-actions">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 10 */;
		echo '" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Vytvořit nový tenant
            </a>
        </div>
    </div>

    <!-- Statistiky tenantů -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-primary">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_tenants']) /* line 20 */;
		echo '</div>
                <div class="stats-label">Celkem tenantů</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number" style="color: var(--secondary-color);">';
		echo LR\Filters::escapeHtmlText($dashboardStats['active_tenants']) /* line 26 */;
		echo '</div>
                <div class="stats-label">Aktivní</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number" style="color: var(--grey-color);">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_users']) /* line 32 */;
		echo '</div>
                <div class="stats-label">Celkem uživatelů</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number" style="color: var(--dark-color);">';
		echo LR\Filters::escapeHtmlText($dashboardStats['total_invoices']) /* line 38 */;
		echo '</div>
                <div class="stats-label">Celkem faktur</div>
            </div>
        </div>
    </div>

    <!-- Panel s vyhledáváním -->
    <div class="search-panel">
        <div class="search-input-wrapper w-100">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="tenantSearch" class="search-input" placeholder="Vyhledat tenant podle názvu, domény nebo společnosti...">
        </div>
    </div>

    <!-- Tabulka tenantů -->
';
		if (count($tenants) > 0) /* line 53 */ {
			echo '    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="sortable-column">Tenant</th>
                    <th>Uživatelé</th>
                    <th>Faktury</th>
                    <th>Vytvořen</th>
                    <th>Status</th>
                    <th class="text-end">Akce</th>
                </tr>
            </thead>
            <tbody>
';
			foreach ($tenants as $tenantData) /* line 67 */ {
				echo '                <tr class="data-row tenant-row';
				if ($tenantData['tenant']['status'] === 'inactive') /* line 68 */ {
					echo ' tenant-inactive';
				}
				echo '" 
                    data-tenant-id="';
				echo LR\Filters::escapeHtmlAttr($tenantData['tenant']['id']) /* line 69 */;
				echo '" 
                    style="cursor: pointer;"
                    title="Klikněte pro zobrazení detailů">
                    <td class="company-column">
                        <div class="company-name">
                            <strong>';
				echo LR\Filters::escapeHtmlText($tenantData['tenant']['name']) /* line 74 */;
				echo '</strong>
                            <i class="bi bi-chevron-right tenant-expand-icon ms-2" style="color: #B1D235; font-size: 0.8rem; transition: transform 0.3s ease;"></i>
                        </div>
';
				if ($tenantData['tenant']['domain']) /* line 77 */ {
					echo '                            <div class="company-location text-muted">
                                <small><i class="bi bi-globe me-1"></i>';
					echo LR\Filters::escapeHtmlText($tenantData['tenant']['domain']) /* line 79 */;
					echo '</small>
                            </div>
';
				}
				if ($tenantData['company'] && $tenantData['company']['name']) /* line 82 */ {
					echo '                            <div class="company-location text-muted">
                                <small><i class="bi bi-building me-1"></i>';
					echo LR\Filters::escapeHtmlText($tenantData['company']['name']) /* line 84 */;
					echo '</small>
                            </div>
';
				}
				echo '                    </td>
                    <td>
                        <span class="badge badge-primary-custom">';
				echo LR\Filters::escapeHtmlText($tenantData['stats']['users_count']) /* line 89 */;
				echo '</span>
';
				if ($tenantData['admin_user']) /* line 90 */ {
					echo '                            <div class="text-muted mt-1">
                                <small><i class="bi bi-person-gear me-1"></i>';
					echo LR\Filters::escapeHtmlText($tenantData['admin_user']['username']) /* line 92 */;
					echo '</small>
                            </div>
';
				}
				echo '                    </td>
                    <td>
                        <span class="badge badge-neutral">';
				echo LR\Filters::escapeHtmlText($tenantData['stats']['invoices_count']) /* line 97 */;
				echo '</span>
';
				if ($tenantData['stats']['total_revenue'] > 0) /* line 98 */ {
					echo '                            <div class="text-muted mt-1">
                                <small><i class="bi bi-currency-exchange me-1"></i>';
					echo LR\Filters::escapeHtmlText(($this->filters->number)($tenantData['stats']['total_revenue'], 0)) /* line 100 */;
					echo ' Kč</small>
                            </div>
';
				}
				echo '                    </td>
                    <td>
                        <div class="text-muted">
                            <small>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($tenantData['tenant']['created_at'], 'd.m.Y')) /* line 106 */;
				echo '</small>
                        </div>
                        <div class="text-muted">
                            <small>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($tenantData['tenant']['created_at'], 'H:i')) /* line 109 */;
				echo '</small>
                        </div>
                    </td>
                    <td>
';
				if ($tenantData['tenant']['status'] === 'active') /* line 113 */ {
					echo '                            <span class="badge" style="background-color: var(--secondary-color); color: white;">
                                <i class="bi bi-check-circle me-1"></i>
                                Aktivní
                            </span>
';
				} else /* line 118 */ {
					echo '                            <span class="badge bg-secondary">
                                <i class="bi bi-pause-circle me-1"></i>
                                Neaktivní
                            </span>
';
				}
				echo '                    </td>
                    <td class="text-end">
                        <div class="action-buttons">
';
				if ($tenantData['tenant']['status'] === 'active') /* line 127 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deactivate!', ['id' => $tenantData['tenant']['id']])) /* line 128 */;
					echo '" 
                                   class="btn btn-sm" style="color: var(--grey-color); border-color: var(--grey-color);" 
                                   onmouseover="this.style.backgroundColor=\'var(--grey-color)\'; this.style.color=\'white\';"
                                   onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'var(--grey-color)\';"
                                   onclick="event.stopPropagation(); return confirm(\'Opravdu chcete deaktivovat tenant ';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenantData['tenant']['name'])) /* line 132 */;
					echo '?\')"
                                   title="Deaktivovat tenant">
                                    <i class="bi bi-pause"></i>
                                </a>
';
				} else /* line 136 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('activate!', ['id' => $tenantData['tenant']['id']])) /* line 137 */;
					echo '" 
                                   class="btn btn-sm" 
                                   style="color: var(--primary-color); border-color: var(--primary-color);"
                                   onmouseover="this.style.backgroundColor=\'var(--primary-color)\'; this.style.color=\'white\';"
                                   onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'var(--primary-color)\';"
                                   onclick="event.stopPropagation(); return confirm(\'Opravdu chcete aktivovat tenant ';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenantData['tenant']['name'])) /* line 142 */;
					echo '?\')"
                                   title="Aktivovat tenant">
                                    <i class="bi bi-play"></i>
                                </a>
';
				}
				echo '                            
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete!', ['id' => $tenantData['tenant']['id']])) /* line 148 */;
				echo '" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="event.stopPropagation(); return confirm(\'POZOR! Opravdu chcete smazat tenant ';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenantData['tenant']['name'])) /* line 150 */;
				echo '? Tata akce je nevratná!\')"
                               title="Smazat tenant">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                
                <tr class="tenant-details-row" id="details-';
				echo LR\Filters::escapeHtmlAttr($tenantData['tenant']['id']) /* line 159 */;
				echo '" style="display: none;">
                    <td colspan="6" class="tenant-details-cell">
                        <div class="tenant-details-content">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="detail-section">
                                        <h6 class="detail-section-title">
                                            <i class="bi bi-building me-2" style="color: #B1D235;"></i>
                                            Základní údaje
                                        </h6>
                                        <div class="detail-item">
                                            <strong>Název tenanta:</strong><br>
                                            <span>';
				echo LR\Filters::escapeHtmlText($tenantData['tenant']['name']) /* line 172 */;
				echo '</span>
                                        </div>
';
				if ($tenantData['tenant']['domain']) /* line 174 */ {
					echo '                                            <div class="detail-item">
                                                <strong>Doména:</strong><br>
                                                <span>';
					echo LR\Filters::escapeHtmlText($tenantData['tenant']['domain']) /* line 177 */;
					echo '</span>
                                            </div>
';
				}
				echo '                                        <div class="detail-item">
                                            <strong>Vytvořen:</strong><br>
                                            <span>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($tenantData['tenant']['created_at'], 'd.m.Y H:i')) /* line 182 */;
				echo '</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="detail-section">
                                        <h6 class="detail-section-title">
                                            <i class="bi bi-briefcase me-2" style="color: #95B11F;"></i>
                                            Údaje společnosti
                                        </h6>
';
				if ($tenantData['company']) /* line 194 */ {
					echo '                                            <div class="detail-item">
                                                <strong>Název společnosti:</strong><br>
                                                <span>';
					echo LR\Filters::escapeHtmlText($tenantData['company']['name']) /* line 197 */;
					echo '</span>
                                            </div>
';
					if ($tenantData['company']['ic']) /* line 199 */ {
						echo '                                                <div class="detail-item">
                                                    <strong>IČ:</strong><br>
                                                    <span>';
						echo LR\Filters::escapeHtmlText($tenantData['company']['ic']) /* line 202 */;
						echo '</span>
                                                </div>
';
					}
					if ($tenantData['company']['dic']) /* line 205 */ {
						echo '                                                <div class="detail-item">
                                                    <strong>DIČ:</strong><br>
                                                    <span>';
						echo LR\Filters::escapeHtmlText($tenantData['company']['dic']) /* line 208 */;
						echo '</span>
                                                </div>
';
					}
					if ($tenantData['company']['address']) /* line 211 */ {
						echo '                                                <div class="detail-item">
                                                    <strong>Adresa:</strong><br>
                                                    <span>
                                                        ';
						echo LR\Filters::escapeHtmlText($tenantData['company']['address']) /* line 215 */;
						echo '<br>
                                                        ';
						if ($tenantData['company']['city']) /* line 216 */ {
							echo LR\Filters::escapeHtmlText($tenantData['company']['city']) /* line 216 */;
						}
						echo '
                                                        ';
						if ($tenantData['company']['zip']) /* line 217 */ {
							echo ' ';
							echo LR\Filters::escapeHtmlText($tenantData['company']['zip']) /* line 217 */;
						}
						echo '
                                                    </span>
                                                </div>
';
					}
				} else /* line 221 */ {
					echo '                                            <div class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Údaje společnosti nejsou nastaveny
                                            </div>
';
				}
				echo '                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="detail-section">
                                        <h6 class="detail-section-title">
                                            <i class="bi bi-person-gear me-2" style="color: #6c757d;"></i>
                                            Administrátor tenanta
                                        </h6>
';
				if ($tenantData['admin_user']) /* line 237 */ {
					echo '                                            <div class="detail-item">
                                                <strong>Jméno:</strong><br>
                                                <span>';
					echo LR\Filters::escapeHtmlText($tenantData['admin_user']['first_name']) /* line 240 */;
					echo ' ';
					echo LR\Filters::escapeHtmlText($tenantData['admin_user']['last_name']) /* line 240 */;
					echo '</span>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Uživatelské jméno:</strong><br>
                                                <span>@';
					echo LR\Filters::escapeHtmlText($tenantData['admin_user']['username']) /* line 244 */;
					echo '</span>
                                            </div>
                                            <div class="detail-item">
                                                <strong>E-mail:</strong><br>
                                                <span>';
					echo LR\Filters::escapeHtmlText($tenantData['admin_user']['email']) /* line 248 */;
					echo '</span>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Registrován:</strong><br>
                                                <span>';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($tenantData['admin_user']['created_at'], 'd.m.Y H:i')) /* line 252 */;
					echo '</span>
                                            </div>
';
				} else /* line 254 */ {
					echo '                                            <div class="text-muted">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Administrátor není přiřazen
                                            </div>
';
				}
				echo '                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="detail-section">
                                        <h6 class="detail-section-title">
                                            <i class="bi bi-graph-up me-2" style="color: #212529;"></i>
                                            Statistiky
                                        </h6>
                                        <div class="detail-stats">
                                            <div class="stat-item">
                                                <span class="stat-number" style="color: #B1D235;">';
				echo LR\Filters::escapeHtmlText($tenantData['stats']['users_count']) /* line 272 */;
				echo '</span>
                                                <span class="stat-label">uživatelů</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-number" style="color: #95B11F;">';
				echo LR\Filters::escapeHtmlText($tenantData['stats']['invoices_count']) /* line 276 */;
				echo '</span>
                                                <span class="stat-label">faktur</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-number" style="color: #6c757d;">';
				echo LR\Filters::escapeHtmlText($tenantData['stats']['clients_count']) /* line 280 */;
				echo '</span>
                                                <span class="stat-label">klientů</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-number" style="color: #212529;">';
				echo LR\Filters::escapeHtmlText($tenantData['stats']['modules_count']) /* line 284 */;
				echo '</span>
                                                <span class="stat-label">modulů</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
		} else /* line 298 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-building"></i>
        </div>
        <h3>Žádní tenanti</h3>
        <p>V systému zatím nejsou žádní tenanti.</p>
        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 305 */;
			echo '" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>
            Vytvořit prvního tenanta
        </a>
    </div>
';
		}
		echo '
    <!-- Nápověda pro správu tenantů -->
    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-question-circle me-2 text-primary"></i>
                        Nápověda pro správu tenantů
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="fw-bold text-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Vytvoření tenanta
                            </h6>
                            <p class="small">
                                Nový tenant představuje samostatnou instanci systému s vlastním administrátorem 
                                a nastavením. Při vytváření se automaticky založí administrátorský účet.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-bold" style="color: var(--secondary-color);">
                                <i class="bi bi-pause-circle me-2"></i>
                                Deaktivace tenanta
                            </h6>
                            <p class="small">
                                Deaktivace tenanta zablokuje přístup všech jeho uživatelů do systému. 
                                Data zůstávají zachována a tenant lze znovu aktivovat.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-bold" style="color: var(--grey-color);">
                                <i class="bi bi-shield-check me-2"></i>
                                Bezpečnost
                            </h6>
                            <p class="small">
                                Pouze super admin má přístup k této sekci. Každý tenant má vlastní 
                                izolované prostředí a nemůže přistupovat k datům jiných tenantů.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-bold text-danger">
                                <i class="bi bi-trash me-2"></i>
                                Smazání tenanta
                            </h6>
                            <p class="small">
                                <strong>POZOR:</strong> Smazání tenanta je nevratná operace! 
                                Všechna data včetně faktur a uživatelů budou trvale ztracena.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 371 */;
		echo '/js/tenants.js"></script>

';
	}
}
