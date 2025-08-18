<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Home/default.latte */
final class Template_05c390f2e6 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Home/default.latte';

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
			foreach (array_intersect_key(['step' => '368', 'invoice' => '532, 662'], $this->params) as $ʟ_v => $ʟ_l) {
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
		if (isset($isSuperAdmin) && $isSuperAdmin) /* line 4 */ {
			echo '
<div class="home-container">
    <!-- Super Admin uvítací sekce -->
    <div class="welcome-section mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title mb-2">
';
			if ($userDisplayName) /* line 12 */ {
				echo '                        Vítejte zpět, ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($userDisplayName)) /* line 14 */;
				echo '!
';
			} else /* line 15 */ {
				echo '                        Vítejte v QRdokladu!
';
			}
			echo '                </h1>
                <p class="welcome-subtitle text-muted">
                    <i class="bi bi-shield-check me-2" style="color: #B1D235;"></i>
                    Super Admin Dashboard - Správa celého fakturačního systému
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="quick-actions">
                    <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tenants:default')) /* line 27 */;
			echo '" class="btn btn-primary btn-lg">
                        <i class="bi bi-building me-2"></i>
                        Správa tenantů
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Super Admin statistiky -->
    <div class="statistics-section mb-4">
        <div class="row g-4">
            <!-- Počet tenantů -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_tenants'])) /* line 46 */;
			echo '</div>
                        <div class="stat-label">Tenantů</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tenants:default')) /* line 50 */;
			echo '" class="btn btn-icon-dashboard" title="Správa tenantů">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Počet uživatelů -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_users'])) /* line 64 */;
			echo '</div>
                        <div class="stat-label">Uživatelů</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Users:default')) /* line 68 */;
			echo '" class="btn btn-icon-dashboard" title="Správa uživatelů">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Počet klientů -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_clients'])) /* line 82 */;
			echo '</div>
                        <div class="stat-label">Klientů</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:default')) /* line 86 */;
			echo '" class="btn btn-icon-dashboard" title="Všichni klienti">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Počet faktur -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_invoices'])) /* line 100 */;
			echo '</div>
                        <div class="stat-label">Faktur</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 104 */;
			echo '" class="btn btn-icon-dashboard" title="Všechny faktury">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Druhý řádek statistik -->
    <div class="statistics-section mb-4">
        <div class="row g-4">
            <!-- Aktivní moduly -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card" style="border-color: #B1D235;">
                    <div class="stat-icon" style="color: #B1D235;">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: #B1D235;">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['total_active_modules'])) /* line 123 */;
			echo '</div>
                        <div class="stat-label">Aktivních modulů</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:users')) /* line 127 */;
			echo '" class="btn btn-icon-dashboard" title="Správa uživatelských modulů">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Blokované IP adresy -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card" style="border-color: #dc3545;">
                    <div class="stat-icon" style="color: #dc3545;">
                        <i class="bi bi-ban"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: #dc3545;">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['blocked_ips_count'])) /* line 141 */;
			echo '</div>
                        <div class="stat-label">Blokovaných IP</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:rateLimitStats')) /* line 145 */;
			echo '" class="btn btn-icon-dashboard" title="Rate limit statistiky">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Neúspěšné pokusy za 24h -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card" style="border-color: #ffc107;">
                    <div class="stat-icon" style="color: #ffc107;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: #ffc107;">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($superAdminStats['failed_attempts_24h'])) /* line 159 */;
			echo '</div>
                        <div class="stat-label">Neúspěšné pokusy 24h</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 163 */;
			echo '" class="btn btn-icon-dashboard" title="Security dashboard">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Poslední tenant -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card" style="border-color: #6c757d;">
                    <div class="stat-icon" style="color: #6c757d;">
                        <i class="bi bi-calendar-plus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: #6c757d; font-size: 1.2rem;">
';
			if ($superAdminStats['latest_tenant_registration']) /* line 178 */ {
				echo '                                ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($superAdminStats['latest_tenant_registration'], 'd.m.Y'))) /* line 179 */;
				echo "\n";
			} else /* line 180 */ {
				echo '                                N/A
';
			}
			echo '                        </div>
                        <div class="stat-label">Poslední tenant</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tenants:default')) /* line 187 */;
			echo '" class="btn btn-icon-dashboard" title="Správa tenantů">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Super Admin rychlé akce -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-lightning-fill text-primary me-2"></i>
                        Rychlé akce
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tenants:add')) /* line 209 */;
			echo '" class="quick-action-card text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="quick-action-icon me-3">
                                        <i class="bi bi-building-add" style="color: #B1D235; font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Vytvořit nový tenant</h6>
                                        <small class="text-muted">Založení nové firmy v systému</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Users:default')) /* line 222 */;
			echo '" class="quick-action-card text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="quick-action-icon me-3">
                                        <i class="bi bi-people-fill" style="color: #95B11F; font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Správa uživatelů</h6>
                                        <small class="text-muted">Všichni uživatelé napříč tenanty</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 235 */;
			echo '" class="quick-action-card text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="quick-action-icon me-3">
                                        <i class="bi bi-shield-check" style="color: #dc3545; font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Security Dashboard</h6>
                                        <small class="text-muted">Bezpečnostní monitoring systému</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:users')) /* line 248 */;
			echo '" class="quick-action-card text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="quick-action-icon me-3">
                                        <i class="bi bi-gear-fill" style="color: #6c757d; font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Správa modulů</h6>
                                        <small class="text-muted">Moduly napříč všemi tenanty</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-info-circle text-info me-2"></i>
                        Systémové informace
                    </h4>
                </div>
                <div class="card-body">
                    <div class="system-info">
                        <div class="system-info-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Verze systému:</span>
                                <span class="fw-bold">QRdoklad 1.9.4</span>
                            </div>
                        </div>
                        <div class="system-info-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Přihlášen jako:</span>
                                <span class="fw-bold text-success">Super Admin</span>
                            </div>
                        </div>
                        <div class="system-info-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Poslední přihlášení:</span>
                                <span class="fw-bold">';
			echo LR\Filters::escapeHtmlText(date('d.m.Y H:i')) /* line 290 */;
			echo '</span>
                            </div>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Settings:default')) /* line 295 */;
			echo '" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-gear me-2"></i>Systémové nastavení
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

';
		} else /* line 307 */ {
			echo '
<div class="home-container">
    <!-- Uvítací sekce -->
    <div class="welcome-section mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title mb-2">
';
			if ($userDisplayName) /* line 315 */ {
				echo '                        Vítejte zpět, ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($userDisplayName)) /* line 317 */;
				echo '!
';
			} else /* line 318 */ {
				echo '                        Vítejte v QRdokladu!
';
			}
			echo '                </h1>
                <p class="welcome-subtitle text-muted">
';
			if ($isSetupComplete) /* line 324 */ {
				echo '                        Váš fakturační systém je připraven k použití
';
			} else /* line 327 */ {
				echo '                        Dokončete nastavení pro plné využití systému
';
			}
			echo '                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="quick-actions">
';
			if ($isUserAccountant) /* line 336 */ {
				echo '                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:add')) /* line 338 */;
				echo '" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nová faktura
                        </a>
';
			} else /* line 342 */ {
				echo '                        <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Users:profile')) /* line 345 */;
				echo '" class="btn btn-primary btn-lg">
                            <i class="bi bi-person me-2"></i>
                            Můj profil
                        </a>
';
			}
			echo '                </div>
            </div>
        </div>
    </div>

    <!-- Začínáme sekce (zobrazí se jen pokud není setup kompletní) -->
';
			if (!$isSetupComplete) /* line 356 */ {
				echo '    <div class="setup-section mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list-check text-primary me-2"></i>
                    <h4 class="mb-0">Začínáme</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
';
				foreach ($setupSteps as $step) /* line 368 */ {
					echo '                    <div class="col-md-4 mb-3">
                        <div class="setup-step">
                            <div class="setup-step-icon">
                                <i class="bi ';
					echo LR\Filters::escapeHtmlAttr($step['icon']) /* line 373 */;
					echo '"></i>
                            </div>
                            <div class="setup-step-content">
                                <h6>';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($step['title'])) /* line 377 */;
					echo '</h6>
                                <p class="text-muted small">';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($step['description'])) /* line 378 */;
					echo '</p>
                                <a href="';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($step['link'])) /* line 380 */;
					echo '" class="btn btn-sm btn-outline-primary">
                                    Dokončit
                                </a>
                            </div>
                        </div>
                    </div>
';

				}

				echo '                </div>
';
				if (!$isUserAccountant) /* line 388 */ {
					echo '                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Upozornění:</strong>
                        <small>Máte pouze oprávnění ke čtení. 
                        Pro vytváření faktur a klientů kontaktujte administrátora.</small>
                    </div>
';
				}
				echo '            </div>
        </div>
    </div>
';
			}
			echo '
    <!-- Statistiky -->
    <div class="statistics-section mb-4">
        <div class="row g-4">
            <!-- Celkové statistiky -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['clients'])) /* line 413 */;
			echo '</div>
                        <div class="stat-label">Klientů</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:default')) /* line 419 */;
			echo '" class="btn btn-icon-dashboard" title="Zobrazit klienty">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['total'])) /* line 433 */;
			echo '</div>
                        <div class="stat-label">Celkem faktur</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 439 */;
			echo '" class="btn btn-icon-dashboard" title="Zobrazit faktury">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['paid'])) /* line 453 */;
			echo '</div>
                        <div class="stat-label">Zaplacených</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'paid'])) /* line 459 */;
			echo '" class="btn btn-icon-dashboard" title="Zobrazit zaplacené faktury">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['overdue'])) /* line 473 */;
			echo '</div>
                        <div class="stat-label">Po splatnosti</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'overdue'])) /* line 479 */;
			echo '" class="btn btn-icon-dashboard" title="Zobrazit faktury po splatnosti">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Obsah dashboardu -->
    <div class="row g-4">
        <!-- Blížící se splatnosti -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="bi bi-calendar-event text-warning me-2"></i>
                                Blížící se splatnosti
                            </h4>
                            <small class="text-muted">Faktury splatné do 7 dnů</small>
                        </div>
                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'created'])) /* line 504 */;
			echo '" class="btn btn-sm btn-outline-primary">
                            Zobrazit všechny
                        </a>
                    </div>
                </div>
                <div class="card-body">
';
			if (is_array($upcomingInvoices)) /* line 510 */ {
				$upcomingCount = count($upcomingInvoices) /* line 511 */;
			} elseif ($upcomingInvoices) /* line 512 */ {
				$upcomingCount = $upcomingInvoices->count() /* line 513 */;
			} else /* line 514 */ {
				$upcomingCount = 0 /* line 515 */;
			}

			echo '                    
';
			if ($upcomingCount > 0) /* line 518 */ {
				echo '                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Číslo</th>
                                        <th>Klient</th>
                                        <th>Splatnost</th>
                                        <th class="text-end">Částka</th>
                                        <th class="text-center">Akce</th>
                                    </tr>
                                </thead>
                                <tbody>
';
				foreach ($upcomingInvoices as $invoice) /* line 532 */ {
					echo '                                    <tr>
                                        <td>
                                            <strong>';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->number)) /* line 536 */;
					echo '</strong>
                                        </td>
                                        <td>
';
					if ($invoice->manual_client) /* line 540 */ {
						echo '                                                ';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->client_name)) /* line 541 */;
						echo "\n";
					} else /* line 542 */ {
						echo '                                                ';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->ref('client_id')->name)) /* line 543 */;
						echo "\n";
					}
					echo '                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($invoice->due_date, 'd.m.Y'))) /* line 549 */;
					echo '
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <strong>';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->number)($invoice->total, 0, ',', ' '))) /* line 554 */;
					echo ' Kč</strong>
                                        </td>
                                        <td class="text-center">
                                            <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:show', [$invoice->id])) /* line 558 */;
					echo '" class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
';

				}

				echo '                                </tbody>
                            </table>
                        </div>
';
			} else /* line 567 */ {
				echo '                        <div class="text-center text-muted py-4">
                            <i class="bi bi-calendar-check display-4 text-success"></i>
                            <p class="mt-2">Žádné faktury se neblíží splatnosti</p>
                        </div>
';
			}
			echo '                </div>
            </div>
        </div>

        <!-- Finanční přehled -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-graph-up text-success me-2"></i>
                        Finanční přehled
                    </h4>
                </div>
                <div class="card-body">
                    <div class="financial-overview">
                        <div class="financial-item">
                            <div class="financial-label">Nezaplaceno celkem</div>
                            <div class="financial-amount text-danger">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->number)($dashboardStats['invoices']['unpaidAmount'], 0, ',', ' '))) /* line 595 */;
			echo ' Kč
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mini-stat">
                                    <div class="mini-stat-number text-success">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['paid'])) /* line 603 */;
			echo '</div>
                                    <div class="mini-stat-label">Zaplaceno</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-stat">
                                    <div class="mini-stat-number text-warning">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['overdue'])) /* line 611 */;
			echo '</div>
                                    <div class="mini-stat-label">Po splatnosti</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nedávné faktury -->
';
			if (is_array($recentInvoices)) /* line 624 */ {
				$recentCount = count($recentInvoices) /* line 625 */;
			} elseif ($recentInvoices) /* line 626 */ {
				$recentCount = $recentInvoices->count() /* line 627 */;
			} else /* line 628 */ {
				$recentCount = 0 /* line 629 */;
			}

			if ($recentCount > 0) /* line 631 */ {
				echo '    <div class="recent-invoices mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-clock-history text-info me-2"></i>
                        Nedávné faktury
                    </h4>
                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 642 */;
				echo '" class="btn btn-sm btn-outline-primary">
                        Zobrazit všechny
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Číslo</th>
                                <th>Klient</th>
                                <th>Vystaveno</th>
                                <th>Stav</th>
                                <th class="text-end">Částka</th>
                                <th class="text-center">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
';
				foreach ($recentInvoices as $invoice) /* line 662 */ {
					echo '                            <tr>
                                <td>
                                    <strong>';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->number)) /* line 666 */;
					echo '</strong>
                                </td>
                                <td>
';
					if ($invoice->manual_client) /* line 670 */ {
						echo '                                        ';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->client_name)) /* line 671 */;
						echo "\n";
					} else /* line 672 */ {
						echo '                                        ';
						echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->ref('client_id')->name)) /* line 673 */;
						echo "\n";
					}
					echo '                                </td>
                                <td>
                                    ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($invoice->created_at, 'd.m.Y'))) /* line 678 */;
					echo '
                                </td>
                                <td>
';
					if ($invoice->status === 'paid') /* line 682 */ {
						echo '                                        <span class="badge bg-success">Zaplaceno</span>
';
					} elseif ($invoice->status === 'overdue') /* line 684 */ {
						echo '                                        <span class="badge bg-danger">Po splatnosti</span>
';
					} else /* line 686 */ {
						echo '                                        <span class="badge bg-warning text-dark">Vytvořeno</span>
';
					}

					echo '                                </td>
                                <td class="text-end">
                                    <strong>';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->number)($invoice->total, 0, ',', ' '))) /* line 692 */;
					echo ' Kč</strong>
                                </td>
                                <td class="text-center">
                                    <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:show', [$invoice->id])) /* line 696 */;
					echo '" class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
';

				}

				echo '                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
';
			}
			echo '</div>

';
		}
	}
}
