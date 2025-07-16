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
				echo '                <tr class="data-row">
                    <td class="company-column">
                        <div class="company-name">
                            <strong>';
				echo LR\Filters::escapeHtmlText($tenantData['tenant']['name']) /* line 71 */;
				echo '</strong>
                        </div>
';
				if ($tenantData['tenant']['domain']) /* line 73 */ {
					echo '                            <div class="company-location text-muted">
                                <small><i class="bi bi-globe me-1"></i>';
					echo LR\Filters::escapeHtmlText($tenantData['tenant']['domain']) /* line 75 */;
					echo '</small>
                            </div>
';
				}
				if ($tenantData['company'] && $tenantData['company']['name']) /* line 78 */ {
					echo '                            <div class="company-location text-muted">
                                <small><i class="bi bi-building me-1"></i>';
					echo LR\Filters::escapeHtmlText($tenantData['company']['name']) /* line 80 */;
					echo '</small>
                            </div>
';
				}
				echo '                    </td>
                    <td>
                        <span class="badge badge-primary-custom">';
				echo LR\Filters::escapeHtmlText($tenantData['stats']['users_count']) /* line 85 */;
				echo '</span>
';
				if ($tenantData['admin_user']) /* line 86 */ {
					echo '                            <div class="text-muted mt-1">
                                <small><i class="bi bi-person-gear me-1"></i>';
					echo LR\Filters::escapeHtmlText($tenantData['admin_user']['username']) /* line 88 */;
					echo '</small>
                            </div>
';
				}
				echo '                    </td>
                    <td>
                        <span class="badge badge-neutral">';
				echo LR\Filters::escapeHtmlText($tenantData['stats']['invoices_count']) /* line 93 */;
				echo '</span>
';
				if ($tenantData['stats']['total_revenue'] > 0) /* line 94 */ {
					echo '                            <div class="text-muted mt-1">
                                <small><i class="bi bi-currency-exchange me-1"></i>';
					echo LR\Filters::escapeHtmlText(($this->filters->number)($tenantData['stats']['total_revenue'], 0)) /* line 96 */;
					echo ' Kč</small>
                            </div>
';
				}
				echo '                    </td>
                    <td>
                        <div class="text-muted">
                            <small>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($tenantData['tenant']['created_at'], 'd.m.Y')) /* line 102 */;
				echo '</small>
                        </div>
                        <div class="text-muted">
                            <small>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($tenantData['tenant']['created_at'], 'H:i')) /* line 105 */;
				echo '</small>
                        </div>
                    </td>
                    <td>
';
				if ($tenantData['tenant']['status'] === 'active') /* line 109 */ {
					echo '                            <span class="badge" style="background-color: var(--secondary-color); color: white;">
                                <i class="bi bi-check-circle me-1"></i>
                                Aktivní
                            </span>
';
				} else /* line 114 */ {
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
				if ($tenantData['tenant']['status'] === 'active') /* line 123 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deactivate!', ['id' => $tenantData['tenant']['id']])) /* line 124 */;
					echo '" 
                                   class="btn btn-sm" style="color: var(--grey-color); border-color: var(--grey-color);" 
                                   onmouseover="this.style.backgroundColor=\'var(--grey-color)\'; this.style.color=\'white\';"
                                   onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'var(--grey-color)\';"
                                   onclick="return confirm(\'Opravdu chcete deaktivovat tenant ';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenantData['tenant']['name'])) /* line 128 */;
					echo '?\')"
                                   title="Deaktivovat tenant">
                                    <i class="bi bi-pause"></i>
                                </a>
';
				} else /* line 132 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('activate!', ['id' => $tenantData['tenant']['id']])) /* line 133 */;
					echo '" 
                                   class="btn btn-sm" 
                                   style="color: var(--primary-color); border-color: var(--primary-color);"
                                   onmouseover="this.style.backgroundColor=\'var(--primary-color)\'; this.style.color=\'white\';"
                                   onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'var(--primary-color)\';"
                                   onclick="return confirm(\'Opravdu chcete aktivovat tenant ';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenantData['tenant']['name'])) /* line 138 */;
					echo '?\')"
                                   title="Aktivovat tenant">
                                    <i class="bi bi-play"></i>
                                </a>
';
				}
				echo '                            
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete!', ['id' => $tenantData['tenant']['id']])) /* line 144 */;
				echo '" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm(\'POZOR! Opravdu chcete smazat tenant ';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($tenantData['tenant']['name'])) /* line 146 */;
				echo '? Tata akce je nevratná!\')"
                               title="Smazat tenant">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
';

			}

			echo '            </tbody>
        </table>
    </div>
';
		} else /* line 157 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-building"></i>
        </div>
        <h3>Žádní tenanti</h3>
        <p>V systému zatím nejsou žádní tenanti.</p>
        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 164 */;
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

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    // Vyhledávání tenantů (zatím pouze frontend filtrování)
    const searchInput = document.getElementById(\'tenantSearch\');
    const tableRows = document.querySelectorAll(\'.data-row\');

    if (searchInput) {
        searchInput.addEventListener(\'input\', function() {
            const searchTerm = this.value.toLowerCase();
            
            tableRows.forEach(function(row) {
                const tenantName = row.querySelector(\'.company-name strong\').textContent.toLowerCase();
                const tenantDetails = row.querySelector(\'.company-location\') ? 
                    row.querySelector(\'.company-location\').textContent.toLowerCase() : \'\';
                
                if (tenantName.includes(searchTerm) || tenantDetails.includes(searchTerm)) {
                    row.style.display = \'\';
                } else {
                    row.style.display = \'none\';
                }
            });
        });
    }
});
</script>

';
	}
}
