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
			foreach (array_intersect_key(['block' => '105', 'type' => '173', 'topIP' => '215'], $this->params) as $ʟ_v => $ʟ_l) {
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
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">📊 Rate Limiting Statistiky</h1>
                <p class="text-muted mb-0">Monitoring a správa rate limiting systému</p>
            </div>
            <div>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 14 */;
		echo '" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Zpět na Dashboard
                </a>
                <button id="clearExpiredBtn" class="btn btn-warning">
                    <i class="bi bi-trash"></i> Vyčistit expirované
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-danger mb-2">
                    <i class="bi bi-ban display-4"></i>
                </div>
                <h5 class="card-title">Aktuálně blokované</h5>
                <div class="h3 text-danger">';
		echo LR\Filters::escapeHtmlText($statistics['currently_blocked_ips'] ?? 0) /* line 34 */;
		echo '</div>
                <small class="text-muted">IP adres</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2">
                    <i class="bi bi-activity display-4"></i>
                </div>
                <h5 class="card-title">Pokusy za 24h</h5>
                <div class="h3 text-info">';
		echo LR\Filters::escapeHtmlText($statistics['attempts_last_24h'] ?? 0) /* line 47 */;
		echo '</div>
                <small class="text-muted">Celkem</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="bi bi-exclamation-triangle display-4"></i>
                </div>
                <h5 class="card-title">Neúspěšné pokusy</h5>
                <div class="h3 text-warning">';
		echo LR\Filters::escapeHtmlText($statistics['failed_attempts_last_24h'] ?? 0) /* line 60 */;
		echo '</div>
                <small class="text-muted">Za 24h</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="bi bi-check-circle display-4"></i>
                </div>
                <h5 class="card-title">Úspěšnost</h5>
                <div class="h3 text-success">';
		echo LR\Filters::escapeHtmlText($statistics['success_rate'] ?? 0) /* line 73 */;
		echo '%</div>
                <small class="text-muted">Rate</small>
            </div>
        </div>
    </div>
</div>

';
		if (count($blockedIPs) > 0) /* line 81 */ {
			echo '<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-ban me-2 text-danger"></i>Aktuálně blokované IP adresy
                </h5>
                <span class="badge bg-danger">';
			echo LR\Filters::escapeHtmlText(count($blockedIPs)) /* line 89 */;
			echo ' aktivních</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
			foreach ($blockedIPs as $block) /* line 105 */ {
				echo '                            <tr>
                                <td>
                                    <code class="text-danger fw-bold">';
				echo LR\Filters::escapeHtmlText($block->ip_address) /* line 108 */;
				echo '</code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">';
				echo LR\Filters::escapeHtmlText($block->action) /* line 111 */;
				echo '</span>
                                </td>
                                <td>
                                    ';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($block->blocked_until, 'd.m.Y H:i:s')) /* line 114 */;
				echo '
                                    <br><small class="text-muted">
';
				if ($block->blocked_until > new DateTime) /* line 116 */ {
					echo '                                            <span class="text-danger">Aktivní</span>
';
				} else /* line 118 */ {
					echo '                                            <span class="text-success">Expirované</span>
';
				}
				echo '                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">';
				echo LR\Filters::escapeHtmlText($block->block_count) /* line 124 */;
				echo 'x</span>
                                </td>
                                <td>
                                    <small class="text-muted">';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($block->created_at, 'd.m.Y H:i')) /* line 127 */;
				echo '</small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success clear-block-btn" 
                                            data-ip="';
				echo LR\Filters::escapeHtmlAttr($block->ip_address) /* line 131 */;
				echo '" 
                                            title="Odblokovat tuto IP">
                                        <i class="bi bi-unlock"></i>
                                    </button>
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
</div>
';
		} else /* line 145 */ {
			echo '<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body text-center py-5">
                <div class="text-success mb-3">
                    <i class="bi bi-check-circle display-1"></i>
                </div>
                <h4 class="text-success">Žádné aktivní blokování</h4>
                <p class="text-muted">V tuto chvíli nejsou blokovány žádné IP adresy.</p>
            </div>
        </div>
    </div>
</div>
';
		}
		echo "\n";
		if (count($blockTypes) > 0) /* line 162 */ {
			echo '<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart me-2 text-info"></i>Nejčastější typy blokování (7 dní)
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
';
			foreach ($blockTypes as $type) /* line 173 */ {
				echo '                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-grow-1">
                                <div class="fw-bold">';
				echo LR\Filters::escapeHtmlText($type->action) /* line 177 */;
				echo '</div>
                                <small class="text-muted">Typ akce</small>
                            </div>
                            <div class="text-end">
                                <div class="h5 mb-0 text-primary">';
				echo LR\Filters::escapeHtmlText($type->count) /* line 181 */;
				echo '</div>
                                <small class="text-muted">blokování</small>
                            </div>
                        </div>
                    </div>
';

			}

			echo '                </div>
            </div>
        </div>
    </div>
</div>
';
		}
		echo "\n";
		if (isset($statistics['top_attacking_ips']) && count($statistics['top_attacking_ips']) > 0) /* line 195 */ {
			echo '<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-shield-exclamation me-2 text-warning"></i>TOP IP adresy s nejvíce neúspěšnými pokusy
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>IP Adresa</th>
                                <th>Počet pokusů</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
';
			foreach ($statistics['top_attacking_ips'] as $topIP) /* line 215 */ {
				echo '                            <tr>
                                <td><code>';
				echo LR\Filters::escapeHtmlText($topIP->ip_address) /* line 217 */;
				echo '</code></td>
                                <td><span class="badge bg-danger">';
				echo LR\Filters::escapeHtmlText($topIP->attempt_count) /* line 218 */;
				echo '</span></td>
                                <td>
                                    <span class="badge bg-secondary">Neznámý</span>
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
</div>
';
		}
		echo '
<div class="row">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle-fill me-2"></i>Informace o Rate Limiting
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-gear text-primary me-2"></i>Nastavení:</h6>
                        <ul class="text-muted">
                            <li><strong>Login:</strong> max 5 pokusů za 15 minut</li>
                            <li><strong>Password reset:</strong> max 3 pokusy za 30 minut</li>
                            <li><strong>ARES lookup:</strong> max 10 pokusů za 5 minut</li>
                            <li><strong>API calls:</strong> max 100 pokusů za hodinu</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-clock text-warning me-2"></i>Délka blokování:</h6>
                        <ul class="text-muted">
                            <li><strong>Login:</strong> 30 minut</li>
                            <li><strong>Password reset:</strong> 60 minut</li>
                            <li><strong>ARES lookup:</strong> 10 minut</li>
                            <li><strong>API calls:</strong> 120 minut</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Pozor:</strong> Vyčištění rate limitů by mělo být používáno pouze v případě potřeby. 
                    Automatické čištění expirovaných záznamů probíhá pravidelně.
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadingIndicator" class="text-center py-3" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Načítání...</span>
    </div>
    <p class="mt-2 text-muted">Zpracování...</p>
</div>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    // Vyčištění expirovaných záznamů
    const clearExpiredBtn = document.getElementById(\'clearExpiredBtn\');
    const loadingIndicator = document.getElementById(\'loadingIndicator\');
    
    if (clearExpiredBtn) {
        clearExpiredBtn.addEventListener(\'click\', function() {
            if (confirm(\'Opravdu chcete vyčistit všechny expirované rate limit záznamy?\')) {
                showLoading();
                
                fetch(\'';
		echo LR\Filters::escapeJs($this->global->uiControl->link('clearRateLimit!')) /* line 292 */;
		echo '\', {
                    method: \'POST\',
                    headers: {
                        \'X-Requested-With\': \'XMLHttpRequest\',
                        \'Content-Type\': \'application/json\'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        alert(\'✅ \' + data.message);
                        location.reload(); // Obnovíme stránku pro aktualizaci dat
                    } else {
                        alert(\'❌ Chyba: \' + data.error);
                    }
                })
                .catch(error => {
                    hideLoading();
                    alert(\'❌ Nastala chyba při komunikaci se serverem: \' + error.message);
                });
            }
        });
    }
    
    // Odblokování konkrétní IP adresy
    const clearBlockBtns = document.querySelectorAll(\'.clear-block-btn\');
    clearBlockBtns.forEach(btn => {
        btn.addEventListener(\'click\', function() {
            const ip = this.getAttribute(\'data-ip\');
            
            if (confirm(\'Opravdu chcete odblokovat IP adresu \' + ip + \'?\')) {
                showLoading();
                
                fetch(\'';
		echo LR\Filters::escapeJs($this->global->uiControl->link('clearRateLimit!')) /* line 327 */;
		echo '?ip=\' + encodeURIComponent(ip), {
                    method: \'POST\',
                    headers: {
                        \'X-Requested-With\': \'XMLHttpRequest\',
                        \'Content-Type\': \'application/json\'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        alert(\'✅ \' + data.message);
                        location.reload(); // Obnovíme stránku pro aktualizaci dat
                    } else {
                        alert(\'❌ Chyba: \' + data.error);
                    }
                })
                .catch(error => {
                    hideLoading();
                    alert(\'❌ Nastala chyba při komunikaci se serverem: \' + error.message);
                });
            }
        });
    });
    
    function showLoading() {
        loadingIndicator.style.display = \'block\';
        // Zakázat všechna tlačítka
        document.querySelectorAll(\'button\').forEach(btn => btn.disabled = true);
    }
    
    function hideLoading() {
        loadingIndicator.style.display = \'none\';
        // Povolit všechna tlačítka
        document.querySelectorAll(\'button\').forEach(btn => btn.disabled = false);
    }
});
</script>

<style>
.card {
    transition: all 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.border-start {
    border-left-width: 4px !important;
}

.clear-block-btn:hover {
    transform: scale(1.1);
}

code {
    font-family: \'Courier New\', monospace;
    font-size: 0.9em;
}
</style>

';
	}
}
