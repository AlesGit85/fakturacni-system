<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/add.latte */
final class Template_40a785af9c extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Users/add.latte';

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
			foreach (array_intersect_key(['error' => '20'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		$this->parentName = '../@layout.latte';
		return get_defined_vars();
	}


	/** {block title} on line 3 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Přidat uživatele';
	}


	/** {block content} on line 5 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="user-form-container">
    <div class="page-header">
        <h1 class="main-title">Přidání nového uživatele</h1>
        <p class="text-muted">Vyplňte údaje pro vytvoření nového uživatele v rámci vašeho fireního účtu</p>
    </div>

    <div class="card shadow-sm rounded-lg border-0">
        <div class="card-body p-4">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['userForm'] /* line 14 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 14 */;
		echo "\n";
		if ($form->hasErrors()) /* line 16 */ {
			echo '                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
';
			foreach ($form->getErrors() as $error) /* line 20 */ {
				echo '                                ';
				echo LR\Filters::escapeHtmlText($error) /* line 21 */;
				echo "\n";

			}

			echo '                        </div>
                    </div>
';
		}
		echo '
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Nový uživatel bude přidán do vašeho firemního účtu.</strong> 
                    </div>
                </div>

                <!-- Základní údaje -->
                <div class="col-12">
                    <div class="section-header">
                        <i class="bi bi-person-vcard"></i>
                        <h2 class="section-title">Základní údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 44 */;
		echo '
                        <label for="frm-userForm-username" class="form-label">Uživatelské jméno</label>
';
		if ($form['username']->hasErrors()) /* line 46 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['username']->getError()) /* line 47 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 54 */;
		echo '
                        <label for="frm-userForm-email" class="form-label">E-mail</label>
';
		if ($form['email']->hasErrors()) /* line 56 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['email']->getError()) /* line 57 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('role', $this->global)->getControl()->addAttributes(['class' => 'form-select']) /* line 64 */;
		echo '
                        <label for="frm-userForm-role" class="form-label">Role</label>
';
		if ($form['role']->hasErrors()) /* line 66 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['role']->getError()) /* line 67 */;
			echo '</div>
';
		}
		echo '                    </div>
                    <div class="form-text mt-2">
                        <small class="text-muted">
                            <strong>Readonly:</strong> Může pouze prohlížet data |
                            <strong>Účetní:</strong> Může vytvářet faktury a klienty |
                            <strong>Admin:</strong> Má plný přístup včetně správy uživatelů
                        </small>
                    </div>
                </div>
                
                <!-- Heslo -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-key"></i>
                        <h2 class="section-title">Přihlašovací údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 89 */;
		echo '
                        <label for="frm-userForm-password" class="form-label">Heslo</label>
';
		if ($form['password']->hasErrors()) /* line 91 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['password']->getError()) /* line 92 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 99 */;
		echo '
                        <label for="frm-userForm-passwordVerify" class="form-label">Heslo znovu</label>
';
		if ($form['passwordVerify']->hasErrors()) /* line 101 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['passwordVerify']->getError()) /* line 102 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>

                <!-- Požadavky na heslo -->
                <div class="col-12">
                    <div class="alert alert-light">
                        <i class="bi bi-shield-check me-2 text-primary"></i>
                        <strong>Požadavky na heslo:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Alespoň 8 znaků</li>
                            <li>Alespoň jedna číslice</li>
                            <li>Alespoň jedno velké písmeno</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-12 mt-4 d-flex justify-content-between">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 121 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Zpět na seznam uživatelů
                    </a>
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary']) /* line 124 */;
		echo '
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 126 */;

		echo '
        </div>
    </div>
</div>
';
	}
}
