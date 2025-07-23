<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/edit.latte */
final class Template_2cbc6490ba extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Users/edit.latte';

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
			foreach (array_intersect_key(['error' => '18'], $this->params) as $ʟ_v => $ʟ_l) {
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
        <h1 class="main-title">Upravit uživatele: ';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($editUser->username)) /* line 5 */;
		echo '</h1>
        <p class="text-muted">Úprava údajů uživatelského účtu</p>
    </div>

    <div class="card shadow-sm rounded-lg border-0">
        <div class="card-body p-4">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['userForm'] /* line 12 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 12 */;
		echo "\n";
		if ($form->hasErrors()) /* line 14 */ {
			echo '                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
';
			foreach ($form->getErrors() as $error) /* line 18 */ {
				echo '                                ';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($error)) /* line 20 */;
				echo "\n";

			}

			echo '                        </div>
                    </div>
';
		}
		echo '
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
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 38 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 39 */;
		echo "\n";
		if ($form['username']->hasErrors()) /* line 40 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['username']->getError())) /* line 43 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 59 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 60 */;
		echo "\n";
		if ($form['email']->hasErrors()) /* line 61 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['email']->getError())) /* line 64 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('role', $this->global)->getControl()->addAttributes(['class' => 'form-select']) /* line 73 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('role', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 74 */;
		echo "\n";
		if ($form['role']->hasErrors()) /* line 75 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['role']->getError())) /* line 78 */;
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
                        <h2 class="section-title">Změna hesla</h2>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Pokud nechcete měnit heslo, ponechte pole prázdná.
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 109 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 110 */;
		echo "\n";
		if ($form['password']->hasErrors()) /* line 111 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['password']->getError())) /* line 114 */;
			echo '
                            </div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 123 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 124 */;
		echo "\n";
		if ($form['passwordVerify']->hasErrors()) /* line 125 */ {
			echo '                            <div class="text-danger mt-1">
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($form['passwordVerify']->getError())) /* line 128 */;
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
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 152 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Zpět na seznam uživatelů
                    </a>
                    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 156 */;
		echo ' class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Uložit změny
                    </button>
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 160 */;

		echo '
        </div>
    </div>
</div>
';
	}
}
