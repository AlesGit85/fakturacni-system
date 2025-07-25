<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Security/default.latte */
final class Template_f5ee97fe01 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Security/default.latte';

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
		echo 'Bezpečnostní nástroje';
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
            <h1>🔒 Bezpečnostní nástroje</h1>
            <p>Nástroje pro monitoring a audit bezpečnosti systému</p>
        </div>
    </div>
</div>

<div class="security-tools-section">
    <h2 class="security-tools-title">Hlavní nástroje</h2>
    
    <div class="row">
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card security-tool-card security-main-tool">
                <div class="card-body">
                    <div class="security-tool-icon security-main-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h4 class="security-tool-title">SQL Security Audit</h4>
                    <p class="security-tool-description">
                        Kompletní analýza všech SQL dotazů v projektu z hlediska bezpečnosti. 
                        Detekuje potenciální SQL injection vulnerabilities a poskytuje doporučení.
                    </p>
                    
                    <div class="security-features-list">
                        <h6 class="security-features-title">
                            <i class="bi bi-check-circle me-2"></i>Funkce:
                        </h6>
                        <ul class="security-features">
                            <li>• Skenování raw SQL dotazů</li>
                            <li>• Detekce nebezpečných vzorů</li>
                            <li>• Bezpečnostní skóre</li>
                            <li>• Podrobná doporučení</li>
                        </ul>
                    </div>
                    
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:sqlAudit')) /* line 45 */;
		echo '" class="btn security-tool-btn btn-primary">
                        <i class="bi bi-search me-2"></i>Spustit SQL Audit
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card security-tool-card security-main-tool">
                <div class="card-body">
                    <div class="security-tool-icon security-main-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h4 class="security-tool-title">Security Dashboard</h4>
                    <p class="security-tool-description">
                        Přehled bezpečnostních statistik, monitoring událostí a rychlý přístup 
                        ke všem bezpečnostním nástrojům systému.
                    </p>
                    
                    <div class="security-features-list">
                        <h6 class="security-features-title">
                            <i class="bi bi-graph-up me-2"></i>Monitoring:
                        </h6>
                        <ul class="security-features">
                            <li>• Přihlašovací statistiky</li>
                            <li>• XSS pokusy</li>
                            <li>• Rate limiting</li>
                            <li>• Security logy</li>
                        </ul>
                    </div>
                    
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 76 */;
		echo '" class="btn security-tool-btn btn-info">
                        <i class="bi bi-speedometer2 me-2"></i>Otevřít Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

';
		if ($isSuperAdmin) /* line 86 */ {
			echo '<div class="security-tools-section">
    <h2 class="security-tools-title">Super Admin nástroje</h2>
    
    <div class="card security-admin-tools-card">
        <div class="security-admin-tools-header">
            <h5>
                <i class="bi bi-shield-exclamation me-2"></i>Super Admin nástroje
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card security-tool-card security-admin-tool">
                        <div class="card-body">
                            <div class="security-tool-icon">
                                <i class="bi bi-stopwatch"></i>
                            </div>
                            <h6 class="security-tool-title">Rate Limit Monitor</h6>
                            <p class="security-tool-description">
                                Správa a monitoring rate limitingu napříč systémem
                            </p>
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:rateLimitStats')) /* line 108 */;
			echo '" class="btn security-tool-btn btn-warning">
                                <i class="bi bi-bar-chart me-2"></i>Statistiky
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card security-tool-card security-admin-tool">
                        <div class="card-body">
                            <div class="security-tool-icon">
                                <i class="bi bi-gear"></i>
                            </div>
                            <h6 class="security-tool-title">Pokročilé nastavení</h6>
                            <p class="security-tool-description">
                                Konfigurace bezpečnostních parametrů systému
                            </p>
                            <button class="btn security-tool-btn disabled" disabled>
                                <i class="bi bi-wrench me-2"></i>Připravuje se
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card security-tool-card security-admin-tool">
                        <div class="card-body">
                            <div class="security-tool-icon">
                                <i class="bi bi-bug"></i>
                            </div>
                            <h6 class="security-tool-title">Penetration Test</h6>
                            <p class="security-tool-description">
                                Automatické testování bezpečnostních slabin
                            </p>
                            <button class="btn security-tool-btn btn-outline-danger disabled" disabled>
                                <i class="bi bi-shield-check me-2"></i>V přípravě
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
<div class="security-tools-section">
    <h2 class="security-tools-title">Bezpečnostní doporučení</h2>
    
    <div class="card security-recommendations-card">
        <div class="security-recommendations-header">
            <h5>
                <i class="bi bi-lightbulb-fill me-2"></i>Bezpečnostní doporučení
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="security-recommendation-section">
                        <h6 class="security-recommendation-title">
                            <i class="bi bi-check-circle me-2"></i>Dobré praktiky:
                        </h6>
                        <ul class="security-recommendation-list">
                            <li>Pravidelně spouštějte SQL security audit</li>
                            <li>Monitorujte bezpečnostní logy</li>
                            <li>Kontrolujte rate limiting statistiky</li>
                            <li>Udržujte silná hesla pro všechny účty</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="security-recommendation-section">
                        <h6 class="security-recommendation-title">
                            <i class="bi bi-exclamation-triangle me-2"></i>Pozor na:
                        </h6>
                        <ul class="security-recommendation-list">
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
