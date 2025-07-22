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
			foreach (array_intersect_key(['event' => '159'], $this->params) as $ʟ_v => $ʟ_l) {
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
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">🛡️ Security Dashboard</h1>
                <p class="text-muted mb-0">Bezpečnostní nástroje a monitoring</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="bi bi-person-check display-4"></i>
                </div>
                <h5 class="card-title">Přihlášení dnes</h5>
                <div class="h3 text-success">';
		echo LR\Filters::escapeHtmlText($securityStats['login_attempts_today']) /* line 26 */;
		echo '</div>
                <small class="text-muted">Celkem pokusů</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="bi bi-person-x display-4"></i>
                </div>
                <h5 class="card-title">Neúspěšná přihlášení</h5>
                <div class="h3 text-warning">';
		echo LR\Filters::escapeHtmlText($securityStats['failed_logins_today']) /* line 39 */;
		echo '</div>
                <small class="text-muted">Dnes</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-danger mb-2">
                    <i class="bi bi-shield-exclamation display-4"></i>
                </div>
                <h5 class="card-title">XSS pokusy</h5>
                <div class="h3 text-danger">';
		echo LR\Filters::escapeHtmlText($securityStats['xss_attempts_today']) /* line 52 */;
		echo '</div>
                <small class="text-muted">Dnes</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2">
                    <i class="bi bi-stopwatch display-4"></i>
                </div>
                <h5 class="card-title">Rate Limit bloky</h5>
                <div class="h3 text-info">';
		echo LR\Filters::escapeHtmlText($securityStats['rate_limit_blocks_today']) /* line 65 */;
		echo '</div>
                <small class="text-muted">Dnes</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">🔧 Bezpečnostní nástroje</h4>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100 security-tool-card">
            <div class="card-body text-center">
                <div class="text-primary mb-3">
                    <i class="bi bi-search display-1"></i>
                </div>
                <h5 class="card-title">SQL Security Audit</h5>
                <p class="card-text text-muted">
                    Komplexní analýza SQL dotazů z hlediska bezpečnosti. Detekuje SQL injection vulnerabilities.
                </p>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:sqlAudit')) /* line 88 */;
		echo '" class="btn btn-primary">
                    <i class="bi bi-search"></i> Spustit Audit
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100 security-tool-card">
            <div class="card-body text-center">
                <div class="text-warning mb-3">
                    <i class="bi bi-speedometer2 display-1"></i>
                </div>
                <h5 class="card-title">Rate Limit Monitor</h5>
                <p class="card-text text-muted">
                    Monitoring a správa rate limitingu. Přehled blokovaných IP adres a statistiky.
                </p>
';
		if ($isSuperAdmin) /* line 105 */ {
			echo '                    <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:rateLimitStats')) /* line 106 */;
			echo '" class="btn btn-warning">
                        <i class="bi bi-bar-chart"></i> Zobrazit Statistiky
                    </a>
';
		} else /* line 109 */ {
			echo '                    <span class="btn btn-outline-secondary disabled">
                        <i class="bi bi-lock"></i> Pouze Super Admin
                    </span>
';
		}
		echo '            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100 security-tool-card">
            <div class="card-body text-center">
                <div class="text-info mb-3">
                    <i class="bi bi-file-earmark-text display-1"></i>
                </div>
                <h5 class="card-title">Security Logs</h5>
                <p class="card-text text-muted">
                    Přehled bezpečnostních událostí, logů a audit trail pro sledování aktivit.
                </p>
                <button class="btn btn-info" onclick="showSecurityLogs()">
                    <i class="bi bi-list"></i> Zobrazit Logy
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>Poslední bezpečnostní události
                </h5>
            </div>
            <div class="card-body">
';
		if (count($recentEvents) > 0) /* line 146 */ {
			echo '                    <div class="table-responsive">
                        <table class="table table-sm">
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
			foreach ($recentEvents as $event) /* line 159 */ {
				echo '                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($event->created_at, 'd.m.Y H:i:s')) /* line 163 */;
				echo '
                                        </small>
                                    </td>
                                    <td>
';
				if ($event->event_type == 'login_success') /* line 167 */ {
					echo '                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Přihlášení
                                            </span>
';
				} elseif ($event->event_type == 'login_failure') /* line 171 */ {
					echo '                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Neúspěšné přihlášení
                                            </span>
';
				} elseif ($event->event_type == 'xss_attempt') /* line 175 */ {
					echo '                                            <span class="badge bg-warning">
                                                <i class="bi bi-shield-exclamation"></i> XSS pokus
                                            </span>
';
				} elseif ($event->event_type == 'rate_limit_exceeded') /* line 179 */ {
					echo '                                            <span class="badge bg-info">
                                                <i class="bi bi-stopwatch"></i> Rate Limit
                                            </span>
';
				} else /* line 183 */ {
					echo '                                            <span class="badge bg-secondary">
                                                ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($event->event_type)) /* line 185 */;
					echo '
                                            </span>
';
				}



				echo '                                    </td>
                                    <td>
                                        <span class="text-break">
                                            ';
				echo LR\Filters::escapeHtmlText(($this->filters->truncate)(($this->filters->escape)($event->description), 80)) /* line 191 */;
				echo '
                                        </span>
                                    </td>
                                    <td>
                                        <code class="small">';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($event->ip_address)) /* line 195 */;
				echo '</code>
                                    </td>
                                    <td>
';
				if ($event->user_id) /* line 198 */ {
					echo '                                            <span class="text-primary">
                                                ';
					echo LR\Filters::escapeHtmlText($event->user_id) /* line 200 */;
					echo '
                                            </span>
';
				} else /* line 202 */ {
					echo '                                            <span class="text-muted">-</span>
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
		} else /* line 211 */ {
			echo '                    <div class="text-center py-4">
                        <i class="bi bi-info-circle display-4 text-info"></i>
                        <p class="text-muted mt-3">
';
			if ($securityStats['login_attempts_today'] == 0 && $securityStats['failed_logins_today'] == 0 && $securityStats['xss_attempts_today'] == 0 && $securityStats['rate_limit_blocks_today'] == 0) /* line 215 */ {
				echo '                                Security logging není aktivní nebo tabulky ještě neexistují.<br>
                                <small>Bezpečnostní události se začnou zobrazovat po aktivaci SecurityLogger služby.</small>
';
			} else /* line 218 */ {
				echo '                                Žádné bezpečnostní události dnes
';
			}
			echo '                        </p>
                    </div>
';
		}
		echo '            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="securityLogsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text me-2"></i>Security Logs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="securityLogsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Načítání...</span>
                        </div>
                        <p class="mt-2">Načítám security logy...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showSecurityLogs() {
    const modal = new bootstrap.Modal(document.getElementById(\'securityLogsModal\'));
    modal.show();
    
    // Zde by bylo možné načíst další logy přes AJAX
    setTimeout(() => {
        document.getElementById(\'securityLogsContent\').innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Funkce v přípravě:</strong> Detailní security logy budou k dispozici v další verzi.
                Zatím můžete použít základní přehled výše.
            </div>
        `;
    }, 1000);
}
</script>

<style>
.security-tool-card {
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.security-tool-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    border-color: #B1D235;
}

.security-tool-card .btn {
    transition: all 0.2s ease;
}

.security-tool-card:hover .btn {
    transform: scale(1.05);
}

.display-1 {
    font-size: 3rem;
}

.display-4 {
    font-size: 2rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>

';
	}
}
