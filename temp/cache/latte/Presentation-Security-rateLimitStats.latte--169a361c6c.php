<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Security/rateLimitStats.latte */
final class Template_169a361c6c extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Security/rateLimitStats.latte';

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


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['block' => '130', 'type' => '196'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 2 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Rate Limiting Statistiky';
	}


	/** {block content} on line 4 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '
<div class="security-page-header" data-csrf="';
		echo LR\Filters::escapeHtmlAttr($presenter->getCsrfToken()) /* line 7 */;
		echo '">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>📊 Rate Limiting Statistiky</h1>
            <p>Monitoring a správa rate limiting systému</p>
        </div>
        <div class="security-header-actions">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 14 */;
		echo '" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Zpět na Dashboard
            </a>
            <button id="clearExpiredBtn" 
                    data-url="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('clearRateLimit!')) /* line 18 */;
		echo '" 
                    class="btn btn-warning">
                <i class="bi bi-trash me-2"></i>Vyčistit expirované
            </button>
        </div>
    </div>
</div>

<div id="loadingIndicator" class="alert alert-info d-none">
    <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span id="loadingText">Zpracovávám požadavek...</span>
    </div>
</div>

<div class="security-tools-section">
    <h2 class="security-tools-title">Přehled statistik</h2>
    
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card security-stat-card rate-limit-stat">
                <div class="card-body">
                    <div class="security-stat-icon danger">
                        <i class="bi bi-ban"></i>
                    </div>
                    <div class="security-stat-number text-danger">';
		echo LR\Filters::escapeHtmlText($statistics['currently_blocked_ips'] ?? 0) /* line 47 */;
		echo '</div>
                    <div class="security-stat-label">Aktuálně blokované</div>
                    <div class="security-stat-subtitle">IP adres</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card security-stat-card rate-limit-stat">
                <div class="card-body">
                    <div class="security-stat-icon info">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="security-stat-number text-info">';
		echo LR\Filters::escapeHtmlText($statistics['attempts_last_24h'] ?? 0) /* line 60 */;
		echo '</div>
                    <div class="security-stat-label">Pokusy za 24h</div>
                    <div class="security-stat-subtitle">Celkem</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card security-stat-card rate-limit-stat">
                <div class="card-body">
                    <div class="security-stat-icon warning">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="security-stat-number text-warning">';
		echo LR\Filters::escapeHtmlText($statistics['failed_attempts_last_24h'] ?? 0) /* line 73 */;
		echo '</div>
                    <div class="security-stat-label">Neúspěšné pokusy</div>
                    <div class="security-stat-subtitle">Za 24h</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card security-stat-card rate-limit-stat">
                <div class="card-body">
                    <div class="security-stat-icon success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="security-stat-number" style="color: #B1D235;">
';
		if (($statistics['attempts_last_24h'] ?? 0) > 0) /* line 87 */ {
			echo '                            ';
			echo LR\Filters::escapeHtmlText(round($statistics['success_rate'] ?? 0, 1)) /* line 88 */;
			echo '%
';
		} else /* line 89 */ {
			echo '                            100%
';
		}
		echo '                    </div>
                    <div class="security-stat-label">Úspěšnost</div>
                    <div class="security-stat-subtitle">Poměr úspěch/pokus</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="security-tools-section">
    <div class="security-issues-card blocked-ips">
        <div class="security-issues-header">
            <h5>
                <i class="bi bi-ban me-2"></i>
                Aktuálně blokované IP adresy (';
		echo LR\Filters::escapeHtmlText(($this->filters->length)($blockedIPs) ?? 0) /* line 107 */;
		echo ')
            </h5>
            <button id="clearAllBtn" 
                    data-url="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('clearAllRateLimits!')) /* line 110 */;
		echo '" 
                    class="btn btn-danger btn-sm">
                <i class="bi bi-trash-fill me-1"></i>
                Vymazat všechny
            </button>
        </div>
        <div class="security-issues-body">
';
		if ($blockedIPs && count($blockedIPs) > 0) /* line 117 */ {
			echo '                <div class="table-responsive">
                    <table class="table table-hover security-issues-table">
                        <thead>
                            <tr>
                                <th>IP Adresa</th>
                                <th>Typ blokování</th>
                                <th>Počet pokusů</th>
                                <th>Zablokováno do</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
';
			foreach ($blockedIPs as $block) /* line 130 */ {
				echo '                                <tr>
                                    <td>
                                        <code class="security-code">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($block->ip_address)) /* line 133 */;
				echo '</code>
';
				if (isset($currentIP) && $block->ip_address === $currentIP) /* line 134 */ {
					echo '                                            <span class="badge bg-warning text-dark ms-1">
                                                <i class="bi bi-person-fill me-1"></i>Vaše IP
                                            </span>
';
				}
				echo '                                    </td>
                                    <td>
                                        <span class="badge security-badge rate-limit">
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($block->action ?? 'general')) /* line 142 */;
				echo '
                                        </span>
                                    </td>
                                    <td>
                                        <span class="security-metric-number text-warning">
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($block->block_count ?? 1)) /* line 147 */;
				echo '
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($block->blocked_until, 'd.m.Y H:i:s'))) /* line 152 */;
				echo '
                                        </span>
                                    </td>
                                    <td>
                                        <div class="security-actions">
                                            <button class="clear-block-btn btn btn-outline-danger btn-sm" 
                                                    data-url="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('clearRateLimit!')) /* line 158 */;
				echo '"
                                                    data-ip="';
				echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($block->ip_address)) /* line 159 */;
				echo '"
                                                    title="Odblokovat IP ';
				echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($block->ip_address)) /* line 160 */;
				echo '">
                                                <i class="bi bi-trash me-1"></i>
                                                Odblokovat
                                            </button>
                                        </div>
                                    </td>
                                </tr>
';

			}

			echo '                        </tbody>
                    </table>
                </div>
';
		} else /* line 171 */ {
			echo '                <div class="security-empty-state text-center py-5">
                    <i class="bi bi-shield-check security-empty-icon text-success"></i>
                    <h6 class="security-empty-title text-success mt-3">Žádné blokované IP adresy</h6>
                    <p class="security-empty-subtitle text-muted">
                        Systém je v pořádku, žádné bezpečnostní hrozby nejsou detekovány.
                    </p>
                </div>
';
		}
		echo '        </div>
    </div>
</div>

<div class="security-tools-section">
    <div class="security-issues-card block-types">
        <div class="security-issues-header">
            <h5>
                <i class="bi bi-graph-up me-2"></i>
                Typy blokování za posledních 24h
            </h5>
        </div>
        <div class="security-issues-body">
';
		if ($blockTypes && count($blockTypes) > 0) /* line 194 */ {
			echo '                <div class="row">
';
			foreach ($blockTypes as $type) /* line 196 */ {
				echo '                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="rate-limit-block-type">
                                <div class="rate-limit-block-type-content">
                                    <div class="rate-limit-block-type-label">
                                        ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($type->action)) /* line 201 */;
				echo '
                                    </div>
                                    <div class="rate-limit-block-type-subtitle">
                                        Rate limit typ
                                    </div>
                                </div>
                                <div class="rate-limit-block-type-number">
                                    <div class="rate-limit-block-type-count">
                                        ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($type->total_blocks)) /* line 209 */;
				echo '
                                    </div>
                                    <div class="rate-limit-block-type-unit">
                                        bloků
                                    </div>
                                </div>
                            </div>
                        </div>
';

			}

			echo '                </div>
';
		} else /* line 219 */ {
			echo '                <div class="security-empty-state text-center py-4">
                    <i class="bi bi-graph-up security-empty-icon text-muted"></i>
                    <h6 class="security-empty-title text-muted mt-3">Žádné blokování za posledních 24h</h6>
                    <p class="security-empty-subtitle text-muted">
                        Žádné rate limit blokování nebylo zaznamenáno.
                    </p>
                </div>
';
		}
		echo '        </div>
    </div>
</div>

<div class="security-tools-section">
    <div class="card">
        <div class="card-header" style="background-color: rgba(177, 210, 53, 0.1); border-bottom: 1px solid #B1D235;">
            <h6 class="mb-0">
                <i class="bi bi-info-circle me-2" style="color: #B1D235;"></i>
                Informace o Rate Limiting
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="security-recommendation-section">
                        <h6 class="security-recommendation-title">
                            <i class="bi bi-gear me-2"></i>Nastavení:
                        </h6>
                        <ul class="security-recommendation-list">
                            <li><strong>Login:</strong> max 5 pokusů za 15 minut</li>
                            <li><strong>Password reset:</strong> max 3 pokusy za 30 minut</li>
                            <li><strong>ARES lookup:</strong> max 10 pokusů za 5 minut</li>
                            <li><strong>API calls:</strong> max 100 pokusů za hodinu</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="security-recommendation-section">
                        <h6 class="security-recommendation-title">
                            <i class="bi bi-clock me-2"></i>Délka blokování:
                        </h6>
                        <ul class="security-recommendation-list">
                            <li><strong>Login:</strong> 30 minut</li>
                            <li><strong>Password reset:</strong> 60 minut</li>
                            <li><strong>ARES lookup:</strong> 10 minut</li>
                            <li><strong>API calls:</strong> 120 minut</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="security-intro-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Pozor:</strong> Vyčištění rate limitů by mělo být používáno pouze v případě potřeby. 
                Automatické čištění expirovaných záznamů probíhá pravidelně.
            </div>
        </div>
    </div>
</div>

<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 280 */;
		echo '/js/security-rate-limit-stats.js?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 280 */;
		echo '"></script>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    // Načti CSRF token z data atributu
    const headerElement = document.querySelector(\'.security-page-header\');
    window.csrfToken = headerElement ? headerElement.getAttribute(\'data-csrf\') : null;
    
    console.log(\'CSRF token loaded:\', window.csrfToken ? \'YES\' : \'NO\');
});
</script>

';
	}
}
