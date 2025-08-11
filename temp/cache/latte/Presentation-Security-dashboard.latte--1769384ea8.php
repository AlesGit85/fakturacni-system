<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Security/dashboard.latte */
final class Template_1769384ea8 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Security/dashboard.latte';

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
			foreach (array_intersect_key(['event' => '162'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block title} on line 2 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Security Dashboard';
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
            <h1>🛡️ Security Dashboard</h1>
            <p>Bezpečnostní monitoring a nástroje systému</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card security-stat-card">
            <div class="card-body">
                <div class="security-stat-icon">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="security-stat-number">';
		echo LR\Filters::escapeHtmlText($securityStats['login_attempts_today']) /* line 24 */;
		echo '</div>
                <div class="security-stat-label">Přihlášení dnes</div>
                <div class="security-stat-subtitle">Celkem pokusů</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card security-stat-card">
            <div class="card-body">
                <div class="security-stat-icon">
                    <i class="bi bi-person-x"></i>
                </div>
                <div class="security-stat-number">';
		echo LR\Filters::escapeHtmlText($securityStats['failed_logins_today']) /* line 37 */;
		echo '</div>
                <div class="security-stat-label">Neúspěšná přihlášení</div>
                <div class="security-stat-subtitle">Zablokovány</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card security-stat-card">
            <div class="card-body">
                <div class="security-stat-icon">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div class="security-stat-number">';
		echo LR\Filters::escapeHtmlText($securityStats['xss_attempts_today']) /* line 50 */;
		echo '</div>
                <div class="security-stat-label">XSS pokusy</div>
                <div class="security-stat-subtitle">Detekované útoky</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card security-stat-card">
            <div class="card-body">
                <div class="security-stat-icon">
                    <i class="bi bi-stopwatch"></i>
                </div>
                <div class="security-stat-number">';
		echo LR\Filters::escapeHtmlText($securityStats['rate_limit_blocks_today']) /* line 63 */;
		echo '</div>
                <div class="security-stat-label">Rate Limit bloky</div>
                <div class="security-stat-subtitle">Aktivní omezení</div>
            </div>
        </div>
    </div>
</div>

<div class="security-tools-section">
    <h2 class="security-tools-title">Bezpečnostní nástroje</h2>
    
    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card security-tool-card">
                <div class="card-body">
                    <div class="security-tool-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5 class="security-tool-title">SQL Security Audit</h5>
                    <p class="security-tool-description">
                        Komplexní analýza SQL dotazů z hlediska bezpečnosti. Automatické skenování 
                        a detekce SQL injection vulnerabilities.
                    </p>
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:sqlAudit')) /* line 87 */;
		echo '" class="btn security-tool-btn btn-primary">
                        <i class="bi bi-search me-2"></i>Spustit Audit
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card security-tool-card">
                <div class="card-body">
                    <div class="security-tool-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h5 class="security-tool-title">Rate Limit Monitor</h5>
                    <p class="security-tool-description">
                        Monitoring rate limitingu s přehledem blokovaných IP adres 
                        a statistikami pokusů o útoky.
                    </p>
';
		if ($isSuperAdmin || $isUserAdmin) /* line 105 */ {
			echo '                        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:rateLimitStats')) /* line 106 */;
			echo '" class="btn security-tool-btn btn-warning">
                            <i class="bi bi-bar-chart me-2"></i>Zobrazit Statistiky
                        </a>
';
		} else /* line 109 */ {
			echo '                        <button class="btn security-tool-btn disabled" disabled>
                            <i class="bi bi-lock me-2"></i>Pouze Admin
                        </button>
';
		}
		echo '                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card security-tool-card">
                <div class="card-body">
                    <div class="security-tool-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h5 class="security-tool-title">Security Logs</h5>
                    <p class="security-tool-description">
                        Detailní audit trail všech bezpečnostních událostí 
                        s možností filtrování a exportu.
                    </p>
                    <button class="btn security-tool-btn btn-info" onclick="showSecurityLogs()">
                        <i class="bi bi-list me-2"></i>Zobrazit Logy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card security-events-card">
            <div class="security-events-header">
                <h5>
                    <i class="bi bi-clock-history me-2"></i>
                    Poslední bezpečnostní události
                </h5>
            </div>
            <div class="card-body p-0">
';
		if (count($recentEvents) > 0) /* line 149 */ {
			echo '                    <div class="table-responsive">
                        <table class="table security-events-table mb-0">
                            <thead>
                                <tr>
                                    <th>Čas</th>
                                    <th>Typ události</th>
                                    <th>Popis</th>
                                    <th>IP adresa</th>
                                    <th>Uživatel</th>
                                </tr>
                            </thead>
                            <tbody>
';
			foreach ($recentEvents as $event) /* line 162 */ {
				echo '                                <tr>
                                    <td>
                                        <span class="security-event-time">
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($event->created_at, 'd.m.Y H:i:s')) /* line 166 */;
				echo '
                                        </span>
                                    </td>
                                    <td>
';
				if ($event->event_type == 'login_success') /* line 170 */ {
					echo '                                            <span class="badge bg-success security-event-badge">
                                                <i class="bi bi-check-circle me-1"></i>Přihlášení
                                            </span>
';
				} elseif ($event->event_type == 'login_failure') /* line 174 */ {
					echo '                                            <span class="badge bg-danger security-event-badge">
                                                <i class="bi bi-x-circle me-1"></i>Selhání
                                            </span>
';
				} elseif ($event->event_type == 'xss_attempt') /* line 178 */ {
					echo '                                            <span class="badge bg-warning security-event-badge">
                                                <i class="bi bi-shield-exclamation me-1"></i>XSS
                                            </span>
';
				} elseif ($event->event_type == 'rate_limit_exceeded') /* line 182 */ {
					echo '                                            <span class="badge bg-info security-event-badge">
                                                <i class="bi bi-stopwatch me-1"></i>Rate Limit
                                            </span>
';
				} else /* line 186 */ {
					echo '                                            <span class="badge bg-secondary security-event-badge">
                                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($event->event_type)) /* line 188 */;
					echo '
                                            </span>
';
				}



				echo '                                    </td>
                                    <td>
                                        <span class="text-break">
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->truncate)(($this->filters->escape)($event->description), 80)) /* line 194 */;
				echo '
                                        </span>
                                    </td>
                                    <td>
                                        <code class="security-event-ip">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($event->ip_address)) /* line 198 */;
				echo '</code>
                                    </td>
                                    <td>
';
				if ($event->user_id) /* line 201 */ {
					echo '                                            <span class="badge bg-light text-dark border">
                                                ID: ';
					echo LR\Filters::escapeHtmlText($event->user_id) /* line 203 */;
					echo '
                                            </span>
';
				} else /* line 205 */ {
					echo '                                            <span class="text-muted">—</span>
';
				}
				echo '                                    </td>
                                </tr>
';

			}

			echo '                            </tbody>
                        </table>
                    </div>
';
		} else /* line 214 */ {
			echo '                    <div class="security-empty-state">
                        <i class="bi bi-info-circle"></i>
';
			if ($securityStats['login_attempts_today'] == 0 && $securityStats['failed_logins_today'] == 0 && $securityStats['xss_attempts_today'] == 0 && $securityStats['rate_limit_blocks_today'] == 0) /* line 217 */ {
				echo '                            <h5>Security logging není aktivní</h5>
                            <p>
                                Bezpečnostní události se začnou zobrazovat po aktivaci SecurityLogger služby.
                                <br><small class="text-muted">Zkontrolujte konfiguraci v <code>config/services.neon</code></small>
                            </p>
';
			} else /* line 223 */ {
				echo '                            <h5>Žádné události dnes</h5>
                            <p>Váš systém je v pořádku. Žádné podezřelé aktivity nebyly zaznamenány.</p>
';
			}
			echo '                    </div>
';
		}
		echo '            </div>
        </div>
    </div>
</div>

<div class="modal fade security-modal" id="securityLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Detailní Security Logs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavřít"></button>
            </div>
            <div class="modal-body">
                <div id="securityLogsContent">
                    <div class="security-loading">
                        <div class="spinner-border" role="status"></div>
                        <p>Načítám detailní security logy...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

';
	}
}
