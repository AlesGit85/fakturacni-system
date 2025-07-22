<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Security/default.latte */
final class Template_f5ee97fe01 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Security/default.latte';

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

		$this->renderBlock('content', get_defined_vars()) /* line 2 */;
	}


	/** {block content} on line 2 */
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
                <h1 class="h3 mb-0">🔒 Bezpečnostní nástroje</h1>
                <p class="text-muted mb-0">Nástroje pro monitoring a audit bezpečnosti systému</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <div class="text-primary mb-4">
                    <i class="bi bi-search" style="font-size: 4rem;"></i>
                </div>
                <h4 class="card-title">SQL Security Audit</h4>
                <p class="card-text text-muted mb-4">
                    Kompletní analýza všech SQL dotazů v projektu z hlediska bezpečnosti. 
                    Detekuje potenciální SQL injection vulnerabilities a poskytuje doporučení.
                </p>
                <div class="row text-start mb-4">
                    <div class="col-12">
                        <h6 class="text-success">
                            <i class="bi bi-check-circle me-2"></i>Funkce:
                        </h6>
                        <ul class="list-unstyled text-muted">
                            <li>• Skenování raw SQL dotazů</li>
                            <li>• Detekce nebezpečných vzorů</li>
                            <li>• Bezpečnostní skóre</li>
                            <li>• Podrobná doporučení</li>
                        </ul>
                    </div>
                </div>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:sqlAudit')) /* line 41 */;
		echo '" class="btn btn-primary btn-lg">
                    <i class="bi bi-search"></i> Spustit SQL Audit
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <div class="text-info mb-4">
                    <i class="bi bi-bar-chart" style="font-size: 4rem;"></i>
                </div>
                <h4 class="card-title">Security Dashboard</h4>
                <p class="card-text text-muted mb-4">
                    Přehled bezpečnostních statistik, monitoring událostí a rychlý přístup 
                    ke všem bezpečnostním nástrojům systému.
                </p>
                <div class="row text-start mb-4">
                    <div class="col-12">
                        <h6 class="text-info">
                            <i class="bi bi-graph-up me-2"></i>Monitoring:
                        </h6>
                        <ul class="list-unstyled text-muted">
                            <li>• Přihlašovací statistiky</li>
                            <li>• XSS pokusy</li>
                            <li>• Rate limiting</li>
                            <li>• Security logy</li>
                        </ul>
                    </div>
                </div>
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 72 */;
		echo '" class="btn btn-info btn-lg">
                    <i class="bi bi-speedometer2"></i> Otevřít Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

';
		if ($isSuperAdmin) /* line 81 */ {
			echo '<div class="row">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-shield-exclamation me-2"></i>Super Admin nástroje
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-center">
                            <div class="text-warning mb-2">
                                <i class="bi bi-stopwatch" style="font-size: 2.5rem;"></i>
                            </div>
                            <h6>Rate Limit Monitor</h6>
                            <p class="text-muted small">Správa a monitoring rate limitingu napříč systémem</p>
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:rateLimitStats')) /* line 99 */;
			echo '" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-bar-chart"></i> Statistiky
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center">
                            <div class="text-secondary mb-2">
                                <i class="bi bi-gear" style="font-size: 2.5rem;"></i>
                            </div>
                            <h6>Pokročilé nastavení</h6>
                            <p class="text-muted small">Konfigurace bezpečnostních parametrů systému</p>
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="bi bi-wrench"></i> Připravuje se
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center">
                            <div class="text-danger mb-2">
                                <i class="bi bi-bug" style="font-size: 2.5rem;"></i>
                            </div>
                            <h6>Penetration Test</h6>
                            <p class="text-muted small">Automatické testování bezpečnostních slabin</p>
                            <button class="btn btn-outline-danger btn-sm" disabled>
                                <i class="bi bi-shield-check"></i> V přípravě
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';
		}
		echo '
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-lightbulb-fill me-2"></i>Bezpečnostní doporučení
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-check-circle text-success me-2"></i>Dobré praktiky:</h6>
                        <ul class="text-muted">
                            <li>Pravidelně spouštějte SQL security audit</li>
                            <li>Monitorujte bezpečnostní logy</li>
                            <li>Kontrolujte rate limiting statistiky</li>
                            <li>Udržujte silná hesla pro všechny účty</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-exclamation-triangle text-warning me-2"></i>Pozor na:</h6>
                        <ul class="text-muted">
                            <li>Neobvyklé množství neúspěšných přihlášení</li>
                            <li>XSS pokusy z nových IP adres</li>
                            <li>Abnormální databázovou aktivitu</li>
                            <li>Neočekávané změny v oprávněních</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

';
	}
}
