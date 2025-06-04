<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Home/default.latte */
final class Template_1d533538c6 extends Latte\Runtime\Template
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
			foreach (array_intersect_key(['step' => '46', 'invoice' => '177, 320'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="home-container">
    <!-- Uvítací sekce -->
    <div class="welcome-section mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title mb-2">
';
		if ($company && $company->name) /* line 8 */ {
			echo '                        Vítejte zpět, ';
			echo LR\Filters::escapeHtmlText($company->name) /* line 9 */;
			echo '!
';
		} else /* line 10 */ {
			echo '                        Vítejte v QRdokladu!
';
		}
		echo '                </h1>
                <p class="welcome-subtitle text-muted">
';
		if ($isSetupComplete) /* line 15 */ {
			echo '                        Váš fakturační systém je připraven k použití
';
		} else /* line 17 */ {
			echo '                        Dokončete nastavení pro plné využití systému
';
		}
		echo '                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="quick-actions">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:add')) /* line 24 */;
		echo '" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nová faktura
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Začínáme sekce (zobrazí se jen pokud není setup kompletní) -->
';
		if (!$isSetupComplete) /* line 34 */ {
			echo '    <div class="setup-section mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list-check text-primary me-2"></i>
                    <h3 class="mb-0">Začínáme</h3>
                    <span class="badge bg-primary ms-2">';
			echo LR\Filters::escapeHtmlText(count($setupSteps)) /* line 41 */;
			echo ' kroků zbývá</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
';
			foreach ($setupSteps as $step) /* line 46 */ {
				echo '                    <div class="col-md-4">
                        <div class="setup-step">
                            <div class="step-icon">
                                <i class="';
				echo LR\Filters::escapeHtmlAttr($step['icon']) /* line 50 */;
				echo ' text-primary"></i>
                            </div>
                            <div class="step-content">
                                <h5 class="step-title">';
				echo LR\Filters::escapeHtmlText($step['title']) /* line 53 */;
				echo '</h5>
                                <p class="step-description text-muted">';
				echo LR\Filters::escapeHtmlText($step['description']) /* line 54 */;
				echo '</p>
                                <a href="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($step['link'])) /* line 55 */;
				echo '" class="btn btn-outline-primary btn-sm">
                                    ';
				echo LR\Filters::escapeHtmlText($step['linkText']) /* line 56 */;
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
		echo LR\Filters::escapeHtmlText($dashboardStats['clients']) /* line 78 */;
		echo '</div>
                        <div class="stat-label">Klientů</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:default')) /* line 82 */;
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
		echo LR\Filters::escapeHtmlText($dashboardStats['invoices']['total']) /* line 95 */;
		echo '</div>
                        <div class="stat-label">Celkem faktur</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 99 */;
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
		echo LR\Filters::escapeHtmlText($dashboardStats['invoices']['paid']) /* line 112 */;
		echo '</div>
                        <div class="stat-label">Zaplacených</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'paid'])) /* line 116 */;
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
		echo LR\Filters::escapeHtmlText($dashboardStats['invoices']['overdue']) /* line 129 */;
		echo '</div>
                        <div class="stat-label">Po splatnosti</div>
                    </div>
                    <div class="stat-action">
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'overdue'])) /* line 133 */;
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
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['filter' => 'created'])) /* line 156 */;
		echo '" class="btn btn-sm btn-outline-primary">
                            Zobrazit všechny
                        </a>
                    </div>
                </div>
                <div class="card-body">
';
		$upcomingCount = is_array($upcomingInvoices) ? count($upcomingInvoices) : ($upcomingInvoices ? $upcomingInvoices->count() : 0) /* line 163 */;
		if ($upcomingCount > 0) /* line 164 */ {
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
			foreach ($upcomingInvoices as $invoice) /* line 177 */ {
				echo '                                    <tr>
                                        <td><strong>';
				echo LR\Filters::escapeHtmlText($invoice->number) /* line 179 */;
				echo '</strong></td>
                                        <td>
';
				if ($invoice->manual_client) /* line 181 */ {
					echo '                                                ';
					echo LR\Filters::escapeHtmlText($invoice->client_name) /* line 182 */;
					echo "\n";
				} else /* line 183 */ {
					echo '                                                ';
					echo LR\Filters::escapeHtmlText($invoice->ref('client_id')->name) /* line 184 */;
					echo "\n";
				}
				echo '                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                ';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->due_date, 'd.m.Y')) /* line 189 */;
				echo '
                                            </span>
                                        </td>
                                        <td class="text-end">';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 0, ',', ' ')) /* line 192 */;
				echo ' Kč</td>
                                        <td class="text-center">
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:show', [$invoice->id])) /* line 194 */;
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
		} else /* line 203 */ {
			echo '                        <div class="empty-state-small">
                            <i class="bi bi-check-circle text-success"></i>
';
			if ($isUserAccountant) /* line 206 */ {
				echo '                                <p class="mb-0">Žádné faktury se neblíží splatnosti</p>
';
			} else /* line 208 */ {
				echo '                                <p class="mb-0">Pro zobrazení faktur potřebujete vyšší oprávnění</p>
                                <small class="text-muted">Kontaktujte administrátora pro přidělení role Účetní</small>
';
			}
			echo '                        </div>
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
		echo LR\Filters::escapeHtmlText(($this->filters->number)($dashboardStats['invoices']['unpaidAmount'], 0, ',', ' ')) /* line 233 */;
		echo ' Kč
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mini-stat">
                                    <div class="mini-stat-number text-success">';
		echo LR\Filters::escapeHtmlText($dashboardStats['invoices']['paid']) /* line 240 */;
		echo '</div>
                                    <div class="mini-stat-label">Zaplaceno</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-stat">
                                    <div class="mini-stat-number text-warning">';
		echo LR\Filters::escapeHtmlText($dashboardStats['invoices']['overdue']) /* line 246 */;
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
		if ($isUserAccountant) /* line 265 */ {
			echo '                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:add')) /* line 266 */;
			echo '" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Nová faktura
                            </a>
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:add')) /* line 270 */;
			echo '" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>
                                Nový klient
                            </a>
';
		} else /* line 274 */ {
			echo '                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>Pro vytváření faktur a klientů potřebujete roli Účetní nebo vyšší</small>
                            </div>
';
		}
		echo '                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Settings:default')) /* line 280 */;
		echo '" class="btn btn-outline-secondary">
                            <i class="bi bi-gear me-2"></i>
                            Nastavení
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nedávné faktury -->
';
		$recentCount = is_array($recentInvoices) ? count($recentInvoices) : ($recentInvoices ? $recentInvoices->count() : 0) /* line 291 */;
		if ($recentCount > 0) /* line 292 */ {
			echo '    <div class="recent-invoices mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-clock-history text-info me-2"></i>
                        Nedávné faktury
                    </h4>
                    <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default')) /* line 301 */;
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
			foreach ($recentInvoices as $invoice) /* line 320 */ {
				echo '                            <tr>
                                <td><strong>';
				echo LR\Filters::escapeHtmlText($invoice->number) /* line 322 */;
				echo '</strong></td>
                                <td>
';
				if ($invoice->manual_client) /* line 324 */ {
					echo '                                        ';
					echo LR\Filters::escapeHtmlText($invoice->client_name) /* line 325 */;
					echo "\n";
				} else /* line 326 */ {
					echo '                                        ';
					echo LR\Filters::escapeHtmlText($invoice->ref('client_id')->name) /* line 327 */;
					echo "\n";
				}
				echo '                                </td>
                                <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->issue_date, 'd.m.Y')) /* line 330 */;
				echo '</td>
                                <td>
';
				if ($invoice->status == 'created') /* line 332 */ {
					echo '                                        <span class="badge bg-secondary">Vystavena</span>
';
				} elseif ($invoice->status == 'paid') /* line 334 */ {
					echo '                                        <span class="badge bg-success">Zaplacena</span>
';
				} elseif ($invoice->status == 'overdue') /* line 336 */ {
					echo '                                        <span class="badge bg-danger">Po splatnosti</span>
';
				}


				echo '                                </td>
                                <td class="text-end">';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 0, ',', ' ')) /* line 340 */;
				echo ' Kč</td>
                                <td class="text-center">
                                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:show', [$invoice->id])) /* line 342 */;
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
