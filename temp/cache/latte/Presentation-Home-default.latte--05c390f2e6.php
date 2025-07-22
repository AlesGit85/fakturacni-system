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
			foreach (array_intersect_key(['step' => '64', 'invoice' => '215, 388'], $this->params) as $ʟ_v => $ʟ_l) {
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
<div class="home-container">
    <!-- Uvítací sekce -->
    <div class="welcome-section mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title mb-2">
';
		if ($userDisplayName) /* line 9 */ {
			echo '                        Vítejte zpět, ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($userDisplayName)) /* line 11 */;
			echo '!
';
		} else /* line 12 */ {
			echo '                        Vítejte v QRdokladu!
';
		}
		echo '                </h1>
                <p class="welcome-subtitle text-muted">
';
		if ($isSetupComplete) /* line 18 */ {
			echo '                        Váš fakturační systém je připraven k použití
';
		} else /* line 21 */ {
			echo '                        Dokončete nastavení pro plné využití systému
';
		}
		echo '                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="quick-actions">
';
		if ($isUserAccountant) /* line 30 */ {
			echo '                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:add')) /* line 32 */;
			echo '" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nová faktura
                        </a>
';
		} else /* line 36 */ {
			echo '                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Users:profile')) /* line 39 */;
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
		if (!$isSetupComplete) /* line 50 */ {
			echo '    <div class="setup-section mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list-check text-primary me-2"></i>
                    <h3 class="mb-0">Začínáme</h3>
                    <span class="badge bg-primary ms-2">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)(count($setupSteps))) /* line 59 */;
			echo ' kroků zbývá</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
';
			foreach ($setupSteps as $step) /* line 64 */ {
				echo '                    <div class="col-md-4">
                        <div class="setup-step">
                            <div class="step-icon">
                                <i class="';
				echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($step['icon'])) /* line 69 */;
				echo ' text-primary"></i>
                            </div>
                            <div class="step-content">
                                <h5 class="step-title">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($step['title'])) /* line 73 */;
				echo '</h5>
                                <p class="step-description text-muted">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($step['description'])) /* line 75 */;
				echo '</p>
                                <a href="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl(($this->filters->escape)($step['link']))) /* line 77 */;
				echo '" class="btn btn-outline-primary btn-sm">
                                    ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($step['linkText'])) /* line 79 */;
				echo '
                                </a>
                            </div>
                        </div>
                    </div>
';

			}

			echo '                </div>
            </div>
        </div>
    </div>
';
		}
		echo '
<!-- Dashboard statistiky -->
    <div class="dashboard-stats mb-4">
        <div class="row g-4">
            <!-- Celkové statistiky -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['clients'])) /* line 102 */;
		echo '</div>
                        <div class="stat-label">Klientů</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:default')) /* line 108 */;
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
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['total'])) /* line 122 */;
		echo '</div>
                        <div class="stat-label">Celkem faktur</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 128 */;
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
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['paid'])) /* line 142 */;
		echo '</div>
                        <div class="stat-label">Zaplacených</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'paid'])) /* line 148 */;
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
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['overdue'])) /* line 162 */;
		echo '</div>
                        <div class="stat-label">Po splatnosti</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'overdue'])) /* line 168 */;
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
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'created'])) /* line 193 */;
		echo '" class="btn btn-sm btn-outline-primary">
                            Zobrazit všechny
                        </a>
                    </div>
                </div>
                <div class="card-body">
';
		$upcomingCount = is_array($upcomingInvoices) ? count($upcomingInvoices) : ($upcomingInvoices ? $upcomingInvoices->count() : 0) /* line 200 */;
		if ($upcomingCount > 0) /* line 201 */ {
			echo '                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Faktura</th>
                                        <th>Klient</th>
                                        <th>Splatnost</th>
                                        <th class="text-end">Částka</th>
                                        <th class="text-center">Akce</th>
                                    </tr>
                                </thead>
                                <tbody>
';
			foreach ($upcomingInvoices as $invoice) /* line 215 */ {
				echo '                                    <tr>
                                        <td>
                                            <strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->number)) /* line 219 */;
				echo '</strong>
                                        </td>
                                        <td>
';
				if ($invoice->manual_client) /* line 222 */ {
					echo '                                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->client_name)) /* line 224 */;
					echo "\n";
				} else /* line 225 */ {
					echo '                                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->ref('client_id')->name)) /* line 227 */;
					echo "\n";
				}
				echo '                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($invoice->due_date, 'd.m.Y'))) /* line 233 */;
				echo '
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->number)($invoice->total, 0, ',', ' '))) /* line 238 */;
				echo ' Kč
                                        </td>
                                        <td class="text-center">
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:show', [$invoice->id])) /* line 242 */;
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
		} else /* line 251 */ {
			echo '                        <div class="empty-state-small">
                            <i class="bi bi-check-circle text-success"></i>
                            <p class="mb-0">Žádné faktury se neblíží splatnosti</p>
                        </div>
';
		}
		echo '                </div>
            </div>
        </div>

        <!-- Rychlé akce a přehled -->
        <div class="col-lg-4">
            <!-- Finanční přehled -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-currency-exchange text-success me-2"></i>
                        Finanční přehled
                    </h4>
                </div>
                <div class="card-body">
                    <div class="financial-overview">
                        <div class="financial-item">
                            <div class="financial-label">Nezaplaceno celkem</div>
                            <div class="financial-amount text-danger">
                                ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->number)($dashboardStats['invoices']['unpaidAmount'], 0, ',', ' '))) /* line 280 */;
		echo ' Kč
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mini-stat">
                                    <div class="mini-stat-number text-success">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['paid'])) /* line 288 */;
		echo '</div>
                                    <div class="mini-stat-label">Zaplaceno</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-stat">
                                    <div class="mini-stat-number text-warning">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($dashboardStats['invoices']['overdue'])) /* line 296 */;
		echo '</div>
                                    <div class="mini-stat-label">Po splatnosti</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rychlé akce -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-lightning text-primary me-2"></i>
                        Rychlé akce
                    </h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
';
		if ($isUserAccountant) /* line 317 */ {
			echo '                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:add')) /* line 320 */;
			echo '" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Nová faktura
                            </a>
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:add')) /* line 325 */;
			echo '" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>
                                Nový klient
                            </a>
';
			if ($isUserAdmin) /* line 329 */ {
				echo '                                <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Settings:default')) /* line 331 */;
				echo '" class="btn btn-outline-secondary">
                                    <i class="bi bi-gear me-2"></i>
                                    Nastavení
                                </a>
';
			}
		} else /* line 336 */ {
			echo '                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Users:profile')) /* line 339 */;
			echo '" class="btn btn-primary">
                                <i class="bi bi-person me-2"></i>
                                Upravit můj profil
                            </a>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>Máte oprávnění pouze pro čtení. Pro vytváření faktur a klientů kontaktujte administrátora.</small>
                            </div>
';
		}
		echo '                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nedávné faktury -->
';
		$recentCount = is_array($recentInvoices) ? count($recentInvoices) : ($recentInvoices ? $recentInvoices->count() : 0) /* line 356 */;
		if ($recentCount > 0) /* line 357 */ {
			echo '    <div class="recent-invoices mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-clock-history text-info me-2"></i>
                        Nedávné faktury
                    </h4>
                    <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 368 */;
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
			foreach ($recentInvoices as $invoice) /* line 388 */ {
				echo '                            <tr>
                                <td>
                                    <strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->number)) /* line 392 */;
				echo '</strong>
                                </td>
                                <td>
';
				if ($invoice->manual_client) /* line 395 */ {
					echo '                                        ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->client_name)) /* line 397 */;
					echo "\n";
				} else /* line 398 */ {
					echo '                                        ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->ref('client_id')->name)) /* line 400 */;
					echo "\n";
				}
				echo '                                </td>
                                <td>
                                    ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($invoice->issue_date, 'd.m.Y'))) /* line 405 */;
				echo '
                                </td>
                                <td>
';
				if ($invoice->status == 'created') /* line 408 */ {
					echo '                                        <span class="badge bg-secondary">Vystavena</span>
';
				} elseif ($invoice->status == 'paid') /* line 410 */ {
					echo '                                        <span class="badge bg-success">Zaplacena</span>
';
				} elseif ($invoice->status == 'overdue') /* line 412 */ {
					echo '                                        <span class="badge bg-danger">Po splatnosti</span>
';
				}


				echo '                                </td>
                                <td class="text-end">
                                    ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->number)($invoice->total, 0, ',', ' '))) /* line 418 */;
				echo ' Kč
                                </td>
                                <td class="text-center">
                                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:show', [$invoice->id])) /* line 422 */;
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
