<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app/Modules/financial_reports/templates/dashboard.latte */
final class Template_632ee60b93 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app/Modules/financial_reports/templates/dashboard.latte';


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		echo '
<div class="financial-reports-dashboard">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Finanční přehledy pro rok ';
		echo LR\Filters::escapeHtmlText($currentYear) /* line 12 */;
		echo '</strong> - Data budou načítána ze skutečných faktur v systému.
            </div>
        </div>
    </div>

    <!-- Prozatím použijeme testovací data, dokud nezajistíme správné načítání -->
';
		$testStats = ['totalCount' => 0, 'paidCount' => 0, 'unpaidCount' => 0, 'overdueCount' => 0, 'totalTurnover' => 0, 'paidAmount' => 0, 'unpaidAmount' => 0, 'year' => $currentYear] /* line 18 */;
		echo "\n";
		$testVatLimits = ['currentTurnover' => 0, 'alerts' => [], 'nextLimit' => 2000000, 'progressToNextLimit' => 0] /* line 29 */;
		echo '
    <!-- DPH upozornění -->
';
		if (!empty($testVatLimits['alerts'])) /* line 37 */ {
			echo '    <div class="row mb-4">
        <div class="col-12">
';
			foreach ($testVatLimits['alerts'] as $alert) /* line 40 */ {
				echo '                <div class="alert alert-';
				echo LR\Filters::escapeHtmlAttr($alert['type']) /* line 41 */;
				echo ' ';
				if ($alert['type'] === 'warning') /* line 41 */ {
					echo 'vat-warning';
				} else /* line 41 */ {
					echo 'vat-danger';
				}
				echo '">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-';
				if ($alert['type'] === 'warning') /* line 44 */ {
					echo 'exclamation-triangle';
				} else /* line 44 */ {
					echo 'exclamation-octagon';
				}
				echo '-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-1">';
				echo LR\Filters::escapeHtmlText($alert['title']) /* line 47 */;
				echo '</h5>
                            <p class="mb-1">';
				echo LR\Filters::escapeHtmlText($alert['message']) /* line 48 */;
				echo '</p>
                            <small>Aktuální obrat: <strong>';
				echo LR\Filters::escapeHtmlText(number_format($alert['amount'], 0, ',', ' ')) /* line 49 */;
				echo ' Kč</strong> (limit: ';
				echo LR\Filters::escapeHtmlText(number_format($alert['limit'], 0, ',', ' ')) /* line 49 */;
				echo ' Kč)</small>
                        </div>
                    </div>
                </div>
';

			}

			echo '        </div>
    </div>
';
		}
		echo '
    <!-- Hlavní statistiky -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="financial-stat-card text-center">
                <div class="stat-icon">
                    <i class="bi bi-file-earmark-text" style="color: #B1D235;"></i>
                </div>
                <div class="stat-number" id="totalCount">';
		echo LR\Filters::escapeHtmlText($testStats['totalCount']) /* line 65 */;
		echo '</div>
                <div class="stat-label">Celkem faktur</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="financial-stat-card text-center">
                <div class="stat-icon">
                    <i class="bi bi-check-circle" style="color: #28a745;"></i>
                </div>
                <div class="stat-number" id="paidCount">';
		echo LR\Filters::escapeHtmlText($testStats['paidCount']) /* line 75 */;
		echo '</div>
                <div class="stat-label">Zaplacených</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="financial-stat-card text-center">
                <div class="stat-icon">
                    <i class="bi bi-clock" style="color: #ffc107;"></i>
                </div>
                <div class="stat-number" id="unpaidCount">';
		echo LR\Filters::escapeHtmlText($testStats['unpaidCount']) /* line 85 */;
		echo '</div>
                <div class="stat-label">Nezaplacených</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="financial-stat-card text-center">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-circle" style="color: #dc3545;"></i>
                </div>
                <div class="stat-number" id="overdueCount">';
		echo LR\Filters::escapeHtmlText($testStats['overdueCount']) /* line 95 */;
		echo '</div>
                <div class="stat-label">Po splatnosti</div>
            </div>
        </div>
    </div>

    <!-- Finanční přehled -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #B1D235; color: #212529;">
                    <i class="bi bi-currency-exchange me-2"></i>
                    <h5 class="mb-0">Finanční přehled za rok ';
		echo LR\Filters::escapeHtmlText($testStats['year']) /* line 107 */;
		echo '</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <h6 class="text-muted">Celkový obrat</h6>
                            <h4 style="color: #B1D235; font-weight: 700;" id="totalTurnover">
                                ';
		echo LR\Filters::escapeHtmlText(number_format($testStats['totalTurnover'], 0, ',', ' ')) /* line 114 */;
		echo ' Kč
                            </h4>
                            <small class="text-muted">Všechny vystavené faktury</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <h6 class="text-muted">Zaplaceno</h6>
                            <h4 style="color: #28a745; font-weight: 700;" id="paidAmount">
                                ';
		echo LR\Filters::escapeHtmlText(number_format($testStats['paidAmount'], 0, ',', ' ')) /* line 121 */;
		echo ' Kč
                            </h4>
                            <small class="text-muted">Skutečné příjmy</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <h6 class="text-muted">Nezaplaceno</h6>
                            <h4 style="color: #dc3545; font-weight: 700;" id="unpaidAmount">
                                ';
		echo LR\Filters::escapeHtmlText(number_format($testStats['unpaidAmount'], 0, ',', ' ')) /* line 128 */;
		echo ' Kč
                            </h4>
                            <small class="text-muted">Čeká na úhradu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #95B11F; color: white;">
                    <i class="bi bi-percent me-2"></i>
                    <h5 class="mb-0">DPH Status</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h6>Obrat k DPH limitu</h6>
                        <div class="progress mb-3" style="height: 20px;">
                            <div class="progress-bar" 
                                 style="background-color: #B1D235; width: ';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeCss(min($testVatLimits['progressToNextLimit'], 100))) /* line 148 */;
		echo '%;"
                                 role="progressbar"
                                 id="vatProgress">
                                <span id="vatProgressText">';
		echo LR\Filters::escapeHtmlText(number_format($testVatLimits['progressToNextLimit'], 1, ',', ' ')) /* line 151 */;
		echo '%</span>
                            </div>
                        </div>
                        <p class="mb-1">
                            <strong id="currentTurnover">';
		echo LR\Filters::escapeHtmlText(number_format($testVatLimits['currentTurnover'], 0, ',', ' ')) /* line 155 */;
		echo ' Kč</strong>
                        </p>
                        <small class="text-muted">
                            Do limitu <span id="nextLimit">';
		echo LR\Filters::escapeHtmlText(number_format($testVatLimits['nextLimit'], 0, ',', ' ')) /* line 158 */;
		echo '</span> Kč<br>
                            zbývá: <span id="remainingToLimit">';
		echo LR\Filters::escapeHtmlText(number_format($testVatLimits['nextLimit'] - $testVatLimits['currentTurnover'], 0, ',', ' ')) /* line 159 */;
		echo '</span> Kč
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Načítání skutečných dat -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #6c757d; color: white;">
                    <i class="bi bi-database me-2"></i>
                    <h5 class="mb-0">Načítání dat</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary" id="loadRealData">
                        <i class="bi bi-arrow-repeat"></i> Načíst skutečná data z databáze
                    </button>
                    <div id="loadingIndicator" class="mt-3" style="display: none;">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>Načítám data...</span>
                        </div>
                    </div>
                    <div id="dataStatus" class="alert mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Návod pro další kroky -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>V dalších krocích přidáme:</strong>
                <ul class="mb-0 mt-2">
                    <li>Načítání skutečných dat pomocí AJAX</li>
                    <li>Graf příjmů po měsících</li>
                    <li>Detailní tabulku faktur s filtrováním</li>
                    <li>Měsíční přehledy</li>
                    <li>Export dat do CSV/PDF</li>
                </ul>
            </div>
        </div>
    </div>
</div>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['alert' => '40'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		$currentYear = date('Y') /* line 5 */;
		return get_defined_vars();
	}
}
