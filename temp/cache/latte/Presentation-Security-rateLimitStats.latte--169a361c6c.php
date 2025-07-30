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
			foreach (array_intersect_key(['block' => '111', 'type' => '179', 'topIP' => '225'], $this->params) as $ʟ_v => $ʟ_l) {
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
<div class="security-page-header">
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
            <button id="clearExpiredBtn" class="btn security-tool-btn btn-warning">
                <i class="bi bi-trash me-2"></i>Vyčistit expirované
            </button>
        </div>
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
		echo LR\Filters::escapeHtmlText($statistics['currently_blocked_ips'] ?? 0) /* line 35 */;
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
		echo LR\Filters::escapeHtmlText($statistics['attempts_last_24h'] ?? 0) /* line 48 */;
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
		echo LR\Filters::escapeHtmlText($statistics['failed_attempts_last_24h'] ?? 0) /* line 61 */;
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
                    <div class="security-stat-number text-success">';
		echo LR\Filters::escapeHtmlText($statistics['success_rate'] ?? 0) /* line 74 */;
		echo '%</div>
                    <div class="security-stat-label">Úspěšnost</div>
                    <div class="security-stat-subtitle">Rate</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="security-tools-section">
    <h2 class="security-tools-title">Aktuálně blokované IP adresy</h2>
    
';
		if (count($blockedIPs) > 0) /* line 87 */ {
			echo '    <div class="row mb-4">
        <div class="col-12">
            <div class="card security-events-card blocked-ips">
                <div class="security-events-header">
                    <h5>
                        <i class="bi bi-ban me-2"></i>Aktuálně blokované IP adresy
                    </h5>
                    <span class="badge bg-danger">';
			echo LR\Filters::escapeHtmlText(count($blockedIPs)) /* line 95 */;
			echo ' aktivních</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table security-events-table mb-0">
                            <thead>
                                <tr>
                                    <th>IP Adresa</th>
                                    <th>Akce</th>
                                    <th>Blokováno do</th>
                                    <th>Počet blokování</th>
                                    <th>Vytvořeno</th>
                                    <th>Akce</th>
                                </tr>
                            </thead>
                            <tbody>
';
			foreach ($blockedIPs as $block) /* line 111 */ {
				echo '                                <tr>
                                    <td>
                                        <code class="security-event-ip danger">';
				echo LR\Filters::escapeHtmlText($block->ip_address) /* line 114 */;
				echo '</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary security-event-badge">';
				echo LR\Filters::escapeHtmlText($block->action) /* line 117 */;
				echo '</span>
                                    </td>
                                    <td>
                                        <span class="security-event-time">';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($block->blocked_until, 'd.m.Y H:i:s')) /* line 120 */;
				echo '</span>
                                        <div class="security-event-status">
';
				if ($block->blocked_until > new DateTime) /* line 122 */ {
					echo '                                                <span class="badge bg-danger">Aktivní</span>
';
				} else /* line 124 */ {
					echo '                                                <span class="badge bg-success">Expirované</span>
';
				}
				echo '                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">';
				echo LR\Filters::escapeHtmlText($block->block_count) /* line 130 */;
				echo 'x</span>
                                    </td>
                                    <td>
                                        <span class="security-event-time">';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($block->created_at, 'd.m.Y H:i')) /* line 133 */;
				echo '</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success clear-block-btn" 
                                                data-ip="';
				echo LR\Filters::escapeHtmlAttr($block->ip_address) /* line 137 */;
				echo '" 
                                                title="Odblokovat tuto IP">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    </td>
                                </tr>
';

			}

			echo '                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
';
		} else /* line 151 */ {
			echo '    <div class="row mb-4">
        <div class="col-12">
            <div class="security-empty-state success">
                <i class="bi bi-check-circle"></i>
                <h5>Žádné aktivní blokování</h5>
                <p>V tuto chvíli nejsou blokovány žádné IP adresy.</p>
            </div>
        </div>
    </div>
';
		}
		echo '</div>

';
		if (count($blockTypes) > 0) /* line 165 */ {
			echo '<div class="security-tools-section">
    <h2 class="security-tools-title">Nejčastější typy blokování</h2>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card security-issues-card block-types">
                <div class="security-issues-header">
                    <h5>
                        <i class="bi bi-bar-chart me-2"></i>Nejčastější typy blokování (7 dní)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
';
			foreach ($blockTypes as $type) /* line 179 */ {
				echo '                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="rate-limit-block-type">
                                <div class="rate-limit-block-type-content">
                                    <div class="rate-limit-block-type-label">';
				echo LR\Filters::escapeHtmlText($type->action) /* line 183 */;
				echo '</div>
                                    <div class="rate-limit-block-type-subtitle">Typ akce</div>
                                </div>
                                <div class="rate-limit-block-type-number">
                                    <div class="rate-limit-block-type-count">';
				echo LR\Filters::escapeHtmlText($type->count) /* line 187 */;
				echo '</div>
                                    <div class="rate-limit-block-type-unit">blokování</div>
                                </div>
                            </div>
                        </div>
';

			}

			echo '                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';
		}
		echo "\n";
		if (isset($statistics['top_attacking_ips']) && count($statistics['top_attacking_ips']) > 0) /* line 202 */ {
			echo '<div class="security-tools-section">
    <h2 class="security-tools-title">TOP útočící IP adresy</h2>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card security-issues-card top-attackers">
                <div class="security-issues-header">
                    <h5>
                        <i class="bi bi-shield-exclamation me-2"></i>TOP IP adresy s nejvíce neúspěšnými pokusy
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table security-events-table">
                            <thead>
                                <tr>
                                    <th>IP Adresa</th>
                                    <th>Počet pokusů</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
';
			foreach ($statistics['top_attacking_ips'] as $topIP) /* line 225 */ {
				echo '                                <tr>
                                    <td><code class="security-event-ip">';
				echo LR\Filters::escapeHtmlText($topIP->ip_address) /* line 227 */;
				echo '</code></td>
                                    <td><span class="badge bg-danger">';
				echo LR\Filters::escapeHtmlText($topIP->attempt_count) /* line 228 */;
				echo '</span></td>
                                    <td>
                                        <span class="badge bg-secondary">Monitorováno</span>
                                    </td>
                                </tr>
';

			}

			echo '                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';
		}
		echo '
<div class="security-tools-section">
    <h2 class="security-tools-title">Konfigurace rate limiting</h2>
    
    <div class="row">
        <div class="col-12">
            <div class="card security-recommendations-card">
                <div class="security-recommendations-header">
                    <h5>
                        <i class="bi bi-info-circle-fill me-2"></i>Informace o Rate Limiting
                    </h5>
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
    </div>
</div>

<div id="loadingIndicator" class="security-loading-section" style="display: none;">
    <div class="security-loading">
        <div class="spinner-border security-spinner" role="status">
            <span class="visually-hidden">Zpracování...</span>
        </div>
        <div class="security-loading-content">
            <h5>Zpracování...</h5>
            <p>Čistím rate limit záznamy...</p>
        </div>
    </div>
</div>

<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 310 */;
		echo '/js/security-rate-limit-stats.js"></script>

';
	}
}
