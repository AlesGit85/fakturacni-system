<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Security/sqlAudit.latte */
final class Template_0fcf3ea2f6 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Security/sqlAudit.latte';

	public const Blocks = [
		['title' => 'blockTitle', 'content' => 'blockContent'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		$this->renderBlock('title', get_defined_vars()) /* line 2 */;
		echo '

';
		$this->renderBlock('content', get_defined_vars()) /* line 4 */;
	}


	/** {block title} on line 2 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'SQL Security Audit';
	}


	/** {block content} on line 4 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '
<div class="security-page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>🔒 SQL Security Audit</h1>
            <p>Komplexní analýza SQL bezpečnosti projektu</p>
        </div>
        <div class="security-header-actions">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 14 */;
		echo '" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Zpět na Dashboard
            </a>
            <button id="runAuditBtn" class="btn security-tool-btn btn-primary" data-url="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('runSqlAudit!')) /* line 17 */;
		echo '">
                <i class="bi bi-search me-2"></i>Spustit Audit
            </button>
        </div>
    </div>
</div>

<div id="auditLoading" class="security-loading-section" style="display: none;">
    <div class="security-loading">
        <div class="spinner-border security-spinner" role="status">
            <span class="visually-hidden">Načítání...</span>
        </div>
        <div class="security-loading-content">
            <h5>Prohledávám projekt...</h5>
            <p>Analyzuji SQL dotazy z hlediska bezpečnosti</p>
            <div class="security-progress-container">
                <div class="progress security-progress">
                    <div id="auditProgress" class="progress-bar" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="auditIntro" class="security-audit-intro">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card security-intro-card">
                <div class="card-body">
                    <div class="security-intro-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="security-intro-title">SQL Security Audit Tool</h3>
                    <p class="security-intro-description">
                        Tento nástroj prohledá celý projekt a analyzuje všechny SQL dotazy z hlediska bezpečnosti.
                        Detekuje potenciální SQL injection vulnerabilities a poskytuje doporučení pro jejich opravu.
                    </p>
                    
                    <div class="row security-intro-features">
                        <div class="col-md-6">
                            <div class="security-feature-section">
                                <h6 class="security-feature-title">
                                    <i class="bi bi-check-circle me-2"></i>Co kontroluje:
                                </h6>
                                <ul class="security-feature-list">
                                    <li>Raw SQL dotazy (database->query)</li>
                                    <li>Parametrizované vs. neescapované dotazy</li>
                                    <li>Nebezpečné vzory konkatenace</li>
                                    <li>Bezpečnostní skóre každého dotazu</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="security-feature-section">
                                <h6 class="security-feature-title">
                                    <i class="bi bi-folder me-2"></i>Prohledávané adresáře:
                                </h6>
                                <ul class="security-feature-list">
                                    <li><code>app/Model/</code></li>
                                    <li><code>app/Presentation/</code></li>
                                    <li><code>Modules/</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="security-intro-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Poznámka:</strong> Audit může trvat několik sekund až minut v závislosti na velikosti projektu.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="auditResults" class="security-audit-results" style="display: none;">
    
    <div class="security-tools-section">
        <h2 class="security-tools-title">Shrnutí auditu</h2>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card security-summary-card">
                    <div class="card-body">
                        <div class="row security-summary-stats">
                            <div class="col-md-2 col-6 mb-3">
                                <div class="security-summary-item">
                                    <div class="security-summary-number" id="filesScanned">-</div>
                                    <div class="security-summary-label">Skenované soubory</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <div class="security-summary-item">
                                    <div class="security-summary-number" id="queriesFound">-</div>
                                    <div class="security-summary-label">SQL dotazy</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <div class="security-summary-item">
                                    <div class="security-summary-number text-success" id="safeQueries">-</div>
                                    <div class="security-summary-label">Bezpečné</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <div class="security-summary-item">
                                    <div class="security-summary-number text-warning" id="potentialIssues">-</div>
                                    <div class="security-summary-label">Problémy</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <div class="security-summary-item">
                                    <div class="security-summary-number text-info" id="safetyPercentage">-</div>
                                    <div class="security-summary-label">Bezpečnost</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <div class="security-summary-item">
                                    <span id="overallStatus" class="badge security-status-badge">-</span>
                                    <div class="security-summary-label">Celkový stav</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="priorityIssuesSection" class="security-tools-section" style="display: none;">
        <h2 class="security-tools-title">Prioritní bezpečnostní problémy</h2>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card security-issues-card priority-issues">
                    <div class="security-issues-header">
                        <h5>
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Prioritní bezpečnostní problémy
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="priorityIssuesList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="allIssuesSection" class="security-tools-section" style="display: none;">
        <h2 class="security-tools-title">Všechny nalezené problémy</h2>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card security-issues-card all-issues">
                    <div class="security-issues-header">
                        <h5>
                            <i class="bi bi-bug me-2"></i>Všechny nalezené problémy
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="allIssuesList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="safeQueriesSection" class="security-tools-section" style="display: none;">
        <h2 class="security-tools-title">Bezpečné SQL dotazy</h2>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card security-issues-card safe-queries">
                    <div class="security-issues-header">
                        <h5>
                            <i class="bi bi-shield-check me-2"></i>Bezpečné SQL dotazy (ukázka)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="safeQueriesList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="recommendationsSection" class="security-tools-section">
        <h2 class="security-tools-title">Doporučení pro zlepšení bezpečnosti</h2>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card security-recommendations-card">
                    <div class="security-recommendations-header">
                        <h5>
                            <i class="bi bi-lightbulb me-2"></i>Doporučení pro zlepšení bezpečnosti
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="recommendationsList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="auditError" class="security-audit-error" style="display: none;">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="alert alert-danger security-error-alert">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle me-2"></i>Chyba při spuštění auditu
                </h5>
                <p id="auditErrorMessage" class="mb-0"></p>
            </div>
        </div>
    </div>
</div>

<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 246 */;
		echo '/js/security-sql-audit.js"></script>

';
	}
}
