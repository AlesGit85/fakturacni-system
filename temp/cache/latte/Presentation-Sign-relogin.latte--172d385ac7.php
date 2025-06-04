<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Sign/relogin.latte */
final class Template_172d385ac7 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Sign/relogin.latte';

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

		echo "\n";
		$this->renderBlock('title', get_defined_vars()) /* line 3 */;
		echo '

';
		$this->renderBlock('content', get_defined_vars()) /* line 5 */;
	}


	public function prepare(): array
	{
		extract($this->params);

		$this->parentName = '../@layout.latte';
		return get_defined_vars();
	}


	/** {block title} on line 3 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Odhlášení po změně uživatelského jména';
	}


	/** {block content} on line 5 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="auth-container">
    <div class="auth-inner">
        <div class="auth-card">
            <div class="auth-header">
                <img src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 10 */;
		echo '/images/qr-webp-white.webp" alt="QRdoklad" class="auth-logo">
                <h1 class="auth-title">Změna uživatelského jména</h1>
                <p class="auth-subtitle">Vaše uživatelské jméno bylo úspěšně změněno</p>
            </div>

            <div class="auth-body">
                <div class="alert alert-success mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Úspěch!</strong> Vaše uživatelské jméno bylo změněno na <strong>';
		echo LR\Filters::escapeHtmlText($username) /* line 18 */;
		echo '</strong>.
                </div>
                
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Bezpečnostní opatření:</strong> Z bezpečnostních důvodů musíte být odhlášeni a přihlásit se znovu s novým uživatelským jménem.
                </div>

                <div class="mb-4">
                    <h5>Co se stane:</h5>
                    <ol class="ps-3">
                        <li>Budete automaticky odhlášeni za <span id="countdown">5</span> sekund</li>
                        <li>Budete přesměrováni na přihlašovací stránku</li>
                        <li>Přihlaste se s novým uživatelským jménem: <strong>';
		echo LR\Filters::escapeHtmlText($username) /* line 31 */;
		echo '</strong></li>
                    </ol>
                </div>

                <div class="d-grid gap-2">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('forceLogout')) /* line 36 */;
		echo '" class="btn btn-primary btn-lg" id="logoutBtn">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Odhlásit se ihned
                    </a>
                    
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:in')) /* line 41 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Zrušit (zůstat přihlášen)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Automatické přesměrování za 5 sekund
let countdown = 5;
const countdownElement = document.getElementById(\'countdown\');
const logoutBtn = document.getElementById(\'logoutBtn\');

const timer = setInterval(function() {
    countdown--;
    countdownElement.textContent = countdown;
    
    if (countdown <= 0) {
        clearInterval(timer);
        // Přesměrování na odhlášení
        window.location.href = logoutBtn.href;
    }
}, 1000);

// Pokud uživatel klikne na tlačítko, zruš countdown
logoutBtn.addEventListener(\'click\', function() {
    clearInterval(timer);
});
</script>
';
	}
}
