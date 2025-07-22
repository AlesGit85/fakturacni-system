<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/add.latte */
final class Template_b68e85a372 extends Latte\Runtime\Template
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
			foreach (array_intersect_key(['error' => '21'], $this->params) as $ʟ_v => $ʟ_l) {
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
		$form = $this->global->formsStack[] = $this->global->uiControl['userForm'] /* line 15 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 15 */;
		echo "\n";
		if ($form->hasErrors()) /* line 17 */ {
			echo '                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
';
			foreach ($form->getErrors() as $error) /* line 21 */ {
				echo '                                ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($error)) /* line 23 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 49 */;
		echo '
                        <label for="frm-userForm-username" class="form-label">Uživatelské jméno</label>
';
		if ($form['username']->hasErrors()) /* line 51 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['username']->getError())) /* line 54 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                    <div class="form-text mt-2">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Povolené znaky:</strong> písmena, číslice, podtržítka a pomlčky (bez diakritiky)
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 70 */;
		echo '
                        <label for="frm-userForm-email" class="form-label">E-mail</label>
';
		if ($form['email']->hasErrors()) /* line 72 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['email']->getError())) /* line 75 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('role', $this->global)->getControl()->addAttributes(['class' => 'form-select']) /* line 84 */;
		echo '
                        <label for="frm-userForm-role" class="form-label">Role</label>
';
		if ($form['role']->hasErrors()) /* line 86 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['role']->getError())) /* line 89 */;
			echo '
                            </div>
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
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 115 */;
		echo '
                        <label for="frm-userForm-password" class="form-label">Heslo</label>
';
		if ($form['password']->hasErrors()) /* line 117 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['password']->getError())) /* line 120 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 129 */;
		echo '
                        <label for="frm-userForm-passwordVerify" class="form-label">Heslo znovu</label>
';
		if ($form['passwordVerify']->hasErrors()) /* line 131 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['passwordVerify']->getError())) /* line 134 */;
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
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 156 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Zpět na seznam uživatelů
                    </a>
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary']) /* line 160 */;
		echo '
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 162 */;

		echo '
        </div>
    </div>
</div>
';
	}
}
