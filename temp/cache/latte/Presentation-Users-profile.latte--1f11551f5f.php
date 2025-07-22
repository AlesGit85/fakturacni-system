<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/profile.latte */
final class Template_1f11551f5f extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Users/profile.latte';

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
			foreach (array_intersect_key(['error' => '114'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="user-form-container">
    <div class="page-header">
        <h1 class="main-title">Můj profil</h1>
        <p class="text-muted">Správa vašeho uživatelského účtu</p>
    </div>

    <!-- Aktuální informace o profilu -->
    <div class="info-card mb-4">
        <div class="info-card-header">
            <i class="bi bi-person-circle me-2"></i>
            <h3>Aktuální údaje</h3>
        </div>
        <div class="info-card-body">
            <div class="row">
                <div class="col-md-6">
';
		if ($profileUser->first_name || $profileUser->last_name) /* line 19 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-person"></i>
                            Jméno
                        </div>
                        <div class="info-value">
                            ';
			if ($profileUser->first_name) /* line 28 */ {
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($profileUser->first_name)) /* line 28 */;
			}
			echo '
                            ';
			if ($profileUser->last_name) /* line 29 */ {
				echo ' ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($profileUser->last_name)) /* line 29 */;
			}
			echo '
                        </div>
                    </div>
';
		}
		echo '                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-at"></i>
                            Uživatelské jméno
                        </div>
                        <div class="info-value">
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($profileUser->username)) /* line 42 */;
		echo '
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-envelope"></i>
                            E-mail
                        </div>
                        <div class="info-value">
                            ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($profileUser->email)) /* line 54 */;
		echo '
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-shield"></i>
                            Role
                        </div>
                        <div class="info-value">
';
		if ($profileUser->is_super_admin) /* line 66 */ {
			echo '                                <span class="badge" style="background-color: #B1D235; color: #212529; font-weight: 600;">Super Administrátor</span>
';
		} elseif ($profileUser->role === 'admin') /* line 69 */ {
			echo '                                <span class="badge bg-danger">Administrátor</span>
';
		} elseif ($profileUser->role === 'accountant') /* line 71 */ {
			echo '                                <span class="badge bg-warning text-dark">Účetní</span>
';
		} else /* line 73 */ {
			echo '                                <span class="badge bg-secondary">Pouze čtení</span>
';
		}


		echo '                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-calendar"></i>
                            Registrován
                        </div>
                        <div class="info-value">
';
		if ($profileUser->created_at) /* line 86 */ {
			echo '                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)(($this->filters->date)($profileUser->created_at, 'd.m.Y'))) /* line 88 */;
			echo "\n";
		} else /* line 89 */ {
			echo '                                <span class="text-muted">—</span>
';
		}
		echo '                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulář pro úpravu profilu -->
    <div class="card shadow-sm rounded-lg border-0">
        <div class="card-header">
            <i class="bi bi-pencil-square me-2"></i>
            <h3>Upravit profil</h3>
        </div>
        <div class="card-body p-4">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['profileForm'] /* line 108 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 108 */;
		echo "\n";
		if ($form->hasErrors()) /* line 110 */ {
			echo '                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
';
			foreach ($form->getErrors() as $error) /* line 114 */ {
				echo '                                ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($error)) /* line 116 */;
				echo "\n";

			}

			echo '                        </div>
                    </div>
';
		}
		echo '
                <!-- Osobní údaje -->
                <div class="col-12">
                    <div class="section-header">
                        <i class="bi bi-person-vcard"></i>
                        <h2 class="section-title">Osobní údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('first_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 134 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('first_name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 135 */;
		echo "\n";
		if ($form['first_name']->hasErrors()) /* line 136 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['first_name']->getError())) /* line 139 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('last_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 148 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('last_name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 149 */;
		echo "\n";
		if ($form['last_name']->hasErrors()) /* line 150 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['last_name']->getError())) /* line 153 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <!-- Přihlašovací údaje -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-at"></i>
                        <h2 class="section-title">Přihlašovací údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 171 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 172 */;
		echo "\n";
		if ($form['username']->hasErrors()) /* line 173 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['username']->getError())) /* line 176 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                    <div class="form-text mt-2">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Povolené znaky:</strong> písmena bez diakritiky (a-z, A-Z), číslice, podtržítka a pomlčky (bez diakritiky)
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 192 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 193 */;
		echo "\n";
		if ($form['email']->hasErrors()) /* line 194 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['email']->getError())) /* line 197 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <!-- Změna hesla -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-key"></i>
                        <h2 class="section-title">Změna hesla</h2>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Pro změnu hesla vyplňte všechna tři pole. Heslo musí splňovat požadavky na bezpečnost. Pokud nechcete měnit heslo, ponechte pole prázdná.
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('currentPassword', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 220 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('currentPassword', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 221 */;
		echo "\n";
		if ($form['currentPassword']->hasErrors()) /* line 222 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['currentPassword']->getError())) /* line 225 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 234 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 235 */;
		echo "\n";
		if ($form['password']->hasErrors()) /* line 236 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['password']->getError())) /* line 239 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 248 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 249 */;
		echo "\n";
		if ($form['passwordVerify']->hasErrors()) /* line 250 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['passwordVerify']->getError())) /* line 253 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>

                <!-- Požadavky na heslo -->
                <div class="col-12">
                    <div class="alert alert-light">
                        <i class="bi bi-shield-check me-2 text-primary"></i>
                        <strong>Požadavky na heslo (pouze při změně):</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Alespoň 8 znaků</li>
                            <li>Alespoň jedna číslice</li>
                            <li>Alespoň jedno velké písmeno</li>
                            <li>Alespoň jedno malé písmeno</li>
                            <li>Alespoň jeden speciální znak (!@#$%^&* atd.)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-12 mt-4 d-flex justify-content-between">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 277 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Zpět na uživatele
                    </a>
                    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 281 */;
		echo ' class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Uložit změny
                    </button>
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 285 */;

		echo '
        </div>
    </div>
</div>

';
		if (isset($showLogoutCountdown) && $showLogoutCountdown) /* line 290 */ {
			echo '<!-- Modal overlay pro countdown -->
<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7);" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #B1D235; color: #212529;">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Uživatelské jméno změněno
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="alert alert-success mb-4">
                    <strong>Úspěch!</strong> Vaše uživatelské jméno bylo změněno z 
                    <strong>';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($originalUsername)) /* line 306 */;
			echo '</strong> na <strong>';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($newUsername)) /* line 306 */;
			echo '</strong>.
                </div>
                
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Bezpečnostní opatření:</strong> Z bezpečnostních důvodů musíte být odhlášeni a přihlásit se znovu s novým uživatelským jménem.
                </div>
                
                <p class="mb-4">
                    Budete automaticky odhlášeni za <span id="countdown" style="color: #B1D235; font-weight: bold; font-size: 1.2em;">10</span> sekund.
                </p>
                
                <div class="d-grid gap-2">
                    <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Sign:out')) /* line 322 */;
			echo '" class="btn btn-primary btn-lg" id="logoutBtn">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Odhlásit se ihned
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let countdown = 10;
let timer;
const countdownElement = document.getElementById(\'countdown\');
const logoutBtn = document.getElementById(\'logoutBtn\');

function startCountdown() {
    timer = setInterval(function() {
        countdown--;
        countdownElement.textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(timer);
            // ✅ BEZPEČNÉ: Používáme href z DOM elementu, který byl vytvořen Nette linkem
            window.location.href = logoutBtn.href;
        }
    }, 1000);
}

// Spustíme countdown při načtení
startCountdown();

// Pokud uživatel klikne na tlačítko odhlásit, zruš countdown
logoutBtn.addEventListener(\'click\', function() {
    clearInterval(timer);
});
</script>
';
		}
		echo "\n";
	}
}
