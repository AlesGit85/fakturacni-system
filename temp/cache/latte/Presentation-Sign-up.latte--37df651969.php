<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Sign/up.latte */
final class Template_37df651969 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Sign/up.latte';

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

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['error' => '25'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		$this->parentName = '../@layout.latte';
		return get_defined_vars();
	}


	/** {block title} on line 3 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Registrace nového firemního účtu';
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
                <h1 class="auth-title">
                    Vytvoření nového firemního účtu
                </h1>
                <p class="auth-subtitle">
                    Vytvořte si vlastní firemní účet s administrátorským přístupem
                </p>
            </div>

            <div class="auth-body">
                ';
		$form = $this->global->formsStack[] = $this->global->uiControl['signUpForm'] /* line 20 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'auth-form']) /* line 20 */;
		echo "\n";
		if ($form->hasErrors()) /* line 22 */ {
			echo '                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
';
			foreach ($form->getErrors() as $error) /* line 25 */ {
				echo '                                ';
				echo LR\Filters::escapeHtmlText($error) /* line 26 */;
				echo "\n";

			}

			echo '                        </div>
';
		}
		echo '
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Vytváříte nový firemní účet:</strong> Budete automaticky administrátor s plnými právy.
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-building me-2 text-primary"></i>
                                Údaje o firemním účtu
                            </h6>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('company_account_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 46 */;
		echo '
                        <label for="frm-signUpForm-company_account_name" class="form-label">Název firemního účtu</label>
';
		if ($form['company_account_name']->hasErrors()) /* line 48 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['company_account_name']->getError()) /* line 49 */;
			echo '</div>
';
		}
		echo '                        <div class="form-text">Název pro váš firemní účet v systému</div>
                    </div>

                    <div class="form-floating mb-4">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('company_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 55 */;
		echo '
                        <label for="frm-signUpForm-company_name" class="form-label">Název společnosti</label>
';
		if ($form['company_name']->hasErrors()) /* line 57 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['company_name']->getError()) /* line 58 */;
			echo '</div>
';
		}
		echo '                        <div class="form-text">Oficiální název vaší společnosti</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <hr class="text-primary">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-person-badge me-2 text-primary"></i>
                                Administrátorský účet
                            </h6>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 74 */;
		echo '
                        <label for="frm-signUpForm-username" class="form-label">Uživatelské jméno</label>
';
		if ($form['username']->hasErrors()) /* line 76 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['username']->getError()) /* line 77 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 82 */;
		echo '
                        <label for="frm-signUpForm-email" class="form-label">E-mail</label>
';
		if ($form['email']->hasErrors()) /* line 84 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['email']->getError()) /* line 85 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 90 */;
		echo '
                        <label for="frm-signUpForm-password" class="form-label">Heslo</label>
';
		if ($form['password']->hasErrors()) /* line 92 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['password']->getError()) /* line 93 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 98 */;
		echo '
                        <label for="frm-signUpForm-passwordVerify" class="form-label">Heslo znovu</label>
';
		if ($form['passwordVerify']->hasErrors()) /* line 100 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['passwordVerify']->getError()) /* line 101 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Výsledek:</strong> Bude vytvořen nový firemní účet s administrátorským přístupem. 
                        Poté se budete moci přihlásit a spravovat faktury, klienty a další uživatele.
                    </div>

                    <div class="d-grid">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary btn-lg']) /* line 112 */;
		echo '
                    </div>
                ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 114 */;

		echo '
            </div>

            <div class="auth-footer">
                <p class="text-center text-muted">
                    Už máte firemní účet? 
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:in')) /* line 120 */;
		echo '" class="auth-link">Přihlaste se</a>
                </p>
                
                <div class="alert alert-light mt-3">
                    <i class="bi bi-shield-check me-2 text-primary"></i>
                    <strong>Požadavky na heslo:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Alespoň 8 znaků</li>
                        <li>Alespoň jedna číslice</li>
                        <li>Alespoň jedno velké písmeno</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
';
	}
}
