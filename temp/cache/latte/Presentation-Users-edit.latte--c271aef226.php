<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/edit.latte */
final class Template_c271aef226 extends Latte\Runtime\Template
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


	/** {block content} on line 1 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="user-form-container">
    <div class="page-header">
        <h1 class="main-title">Upravit uživatele: ';
		echo LR\Filters::escapeHtmlText($editUser->username) /* line 4 */;
		echo '</h1>
        <p class="text-muted">Úprava údajů uživatelského účtu</p>
    </div>

    <div class="card shadow-sm rounded-lg border-0">
        <div class="card-body p-4">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['userForm'] /* line 10 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 10 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 21 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 22 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 28 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 29 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('role', $this->global)->getControl()->addAttributes(['class' => 'form-select']) /* line 35 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('role', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 36 */;
		echo '
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
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 54 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 55 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 61 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 62 */;
		echo '
                    </div>
                </div>
                
                <div class="col-12 mt-4 d-flex justify-content-between">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 67 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Zpět na seznam uživatelů
                    </a>
                    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 70 */;
		echo ' class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Uložit změny
                    </button>
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 74 */;

		echo '
        </div>
    </div>
</div>
';
	}
}
