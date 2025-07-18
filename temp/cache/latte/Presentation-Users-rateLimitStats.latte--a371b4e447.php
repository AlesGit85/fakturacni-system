<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/rateLimitStats.latte */
final class Template_a371b4e447 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Users/rateLimitStats.latte';

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
			foreach (array_intersect_key(['block' => '101', 'action' => '157', 'status' => '157', 'index' => '213', 'ip' => '213'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="section-title mb-0"> <i class="bi bi-shield-check"></i> Rate Limiting Dashboard</h1>
            <p class="text-muted mb-0">Monitoring a správa bezpečnostních limitů</p>
        </div>
        <div class="d-flex gap-2">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Users:default')) /* line 10 */;
		echo '" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zpět
            </a>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('clearAllRateLimits!')) /* line 13 */;
		echo '" class="btn btn-danger" 
               onclick="return confirm(\'Opravdu chcete vymazat všechny rate limity?\')">
                <i class="bi bi-trash"></i> Vymazat vše
            </a>
        </div>
    </div>

    <!-- Statistiky v kartách -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-danger mb-2">
                        ';
		echo LR\Filters::escapeHtmlText($statistics['currently_blocked_ips']) /* line 26 */;
		echo '
                    </div>
                    <div class="text-muted fs-6">Zablokované IP</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-primary mb-2">
                        ';
		echo LR\Filters::escapeHtmlText($statistics['attempts_last_24h']) /* line 36 */;
		echo '
                    </div>
                    <div class="text-muted fs-6">Pokusy za 24h</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-warning mb-2">
                        ';
		echo LR\Filters::escapeHtmlText($statistics['failed_attempts_last_24h']) /* line 46 */;
		echo '
                    </div>
                    <div class="text-muted fs-6">Neúspěšné za 24h</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-success mb-2">
                        ';
		echo LR\Filters::escapeHtmlText($statistics['success_rate']) /* line 56 */;
		echo '%
                    </div>
                    <div class="text-muted fs-6">Úspěšnost</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vyhledávací pole -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0" 
                       placeholder="Vyhledat IP adresu..." id="searchInput">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <span class="text-muted">Vaše IP: </span>
            <span class="badge bg-light text-dark">';
		echo LR\Filters::escapeHtmlText($currentIP) /* line 77 */;
		echo '</span>
        </div>
    </div>

    <!-- Tabulka zablokovaných IP -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0">Zablokované IP adresy</h6>
        </div>
        <div class="card-body p-0">
';
		if (count($blockedIPs) > 0) /* line 87 */ {
			echo '                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>IP Adresa</th>
                                <th>Akce</th>
                                <th>Počet pokusů</th>
                                <th>Blokováno do</th>
                                <th>Status</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
';
			foreach ($blockedIPs as $block) /* line 101 */ {
				echo '                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">';
				echo LR\Filters::escapeHtmlText($block->ip_address) /* line 104 */;
				echo '</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">';
				echo LR\Filters::escapeHtmlText($block->action) /* line 107 */;
				echo '</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">';
				echo LR\Filters::escapeHtmlText($block->block_count) /* line 110 */;
				echo '</span>
                                    </td>
                                    <td>
                                        <span class="text-muted fs-6">';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($block->blocked_until, 'd.m.Y H:i:s')) /* line 113 */;
				echo '</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-x"></i> Aktivní
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-secondary" title="Pozastavit">
                                                <i class="bi bi-pause"></i>
                                            </button>
                                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('clearRateLimit!', [$block->ip_address])) /* line 125 */;
				echo '" 
                                               class="btn btn-sm btn-outline-danger" 
                                               title="Odblokovat"
                                               onclick="return confirm(\'Odblokovat IP ';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($block->ip_address)) /* line 128 */;
				echo '?\')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
';

			}

			echo '                        </tbody>
                    </table>
                </div>
';
		} else /* line 138 */ {
			echo '                <div class="text-center py-5">
                    <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                    <h6 class="text-success mt-3">Žádné zablokované IP adresy</h6>
                    <p class="text-muted fs-6">Systém je v pořádku, žádné bezpečnostní hrozby</p>
                </div>
';
		}
		echo '        </div>
    </div>

    <!-- Rate Limit Status pro různé akce -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0">Status vašich limitů</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
';
		foreach ($rateLimitStatuses as $action => $status) /* line 157 */ {
			echo '                            <div class="col-md-6 col-lg-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 text-uppercase fs-6">';
			echo LR\Filters::escapeHtmlText($action) /* line 161 */;
			echo '</h6>
';
			if ($status['is_blocked']) /* line 162 */ {
				echo '                                            <span class="badge bg-danger">Blokováno</span>
';
			} else /* line 164 */ {
				echo '                                            <span class="badge bg-success">Aktivní</span>
';
			}
			echo '                                    </div>
                                    
';
			if ($status['is_blocked']) /* line 169 */ {
				echo '                                        <span class="text-danger fs-6">
                                            <i class="bi bi-clock"></i>
                                            Do: ';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($status['blocked_until'], 'H:i:s')) /* line 172 */;
				echo '
                                        </span>
';
			} else /* line 174 */ {
				echo '                                        <div class="progress mb-2" style="height: 6px;">
';
				$percentage = $status['attempts_used'] / $status['attempts_max'] * 100 /* line 176 */;
				echo '                                            <div class="progress-bar bg-success" 
                                                 style="width: ';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeCss($percentage)) /* line 178 */;
				echo '%"></div>
                                        </div>
                                        <span class="text-muted fs-6">
                                            ';
				echo LR\Filters::escapeHtmlText($status['attempts_used']) /* line 181 */;
				echo '/';
				echo LR\Filters::escapeHtmlText($status['attempts_max']) /* line 181 */;
				echo ' pokusů
                                        </span>
';
			}
			echo '                                </div>
                            </div>
';

		}

		echo '                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top IP adresy -->
';
		if (!empty($statistics['top_ips'])) /* line 194 */ {
			echo '        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0">Top podezřelé IP adresy (24h)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pozice</th>
                                        <th>IP Adresa</th>
                                        <th>Neúspěšné pokusy</th>
                                        <th>Aktivita</th>
                                    </tr>
                                </thead>
                                <tbody>
';
			foreach ($statistics['top_ips'] as $index => $ip) /* line 213 */ {
				echo '                                        <tr>
                                            <td>
                                                <span class="badge bg-warning">#';
				echo LR\Filters::escapeHtmlText($index + 1) /* line 216 */;
				echo '</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">';
				echo LR\Filters::escapeHtmlText($ip->ip_address) /* line 219 */;
				echo '</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">';
				echo LR\Filters::escapeHtmlText($ip->attempt_count) /* line 222 */;
				echo '</span>
                                            </td>
                                            <td>
';
				$maxAttempts = $statistics['top_ips'][0]->attempt_count /* line 225 */;
				$percentage = $ip->attempt_count / $maxAttempts * 100 /* line 226 */;
				echo '                                                <div class="progress" style="height: 6px; width: 200px;">
                                                    <div class="progress-bar bg-danger" 
                                                         style="width: ';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeCss($percentage)) /* line 229 */;
				echo '%"></div>
                                                </div>
                                            </td>
                                        </tr>
';

			}

			echo '                                </tbody>
                            </table>
                        </div>
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
