<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Sign/up.latte */
final class Template_84e802435c extends Latte\Runtime\Template
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

		$this->parentName = '../@layout.latte';
		return get_defined_vars();
	}


	/** {block title} on line 3 */
	public function blockTitle(array $ʟ_args): void
	{
		echo 'Registrace';
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
';
		if ($isFirstUser) /* line 12 */ {
			echo '                        Vytvoření administrátorského účtu
';
		} else /* line 14 */ {
			echo '                        Registrace nového uživatele
';
		}
		echo '                </h1>
                <p class="auth-subtitle">
';
		if ($isFirstUser) /* line 19 */ {
			echo '                        Vytvořte první účet s administrátorskými právy
';
		} else /* line 21 */ {
			echo '                        Vyplňte údaje pro vytvoření nového účtu
';
		}
		echo '                </p>
            </div>

            <div class="auth-body">
                ';
		$form = $this->global->formsStack[] = $this->global->uiControl['signUpForm'] /* line 28 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'auth-form']) /* line 28 */;
		echo '
                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 30 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 31 */;
		echo "\n";
		if ($form['username']->hasErrors()) /* line 32 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['username']->getError()) /* line 33 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 38 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 39 */;
		echo "\n";
		if ($form['email']->hasErrors()) /* line 40 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['email']->getError()) /* line 41 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 46 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 47 */;
		echo "\n";
		if ($form['password']->hasErrors()) /* line 48 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['password']->getError()) /* line 49 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 54 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 55 */;
		echo "\n";
		if ($form['passwordVerify']->hasErrors()) /* line 56 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['passwordVerify']->getError()) /* line 57 */;
			echo '</div>
';
		}
		echo '                    </div>

';
		if (!$isFirstUser) /* line 61 */ {
			echo '                    <div class="form-floating mb-3">
                        ';
			echo Nette\Bridges\FormsLatte\Runtime::item('role', $this->global)->getControl()->addAttributes(['class' => 'form-select']) /* line 63 */;
			echo '
                        ';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('role', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 64 */;
			echo '
                    </div>
';
		}
		echo '
                    <div class="d-grid">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary btn-lg']) /* line 69 */;
		echo '
                    </div>
                ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 71 */;

		echo '
            </div>

            <div class="auth-footer">
                <p class="text-center text-muted">
                    Už máte účet? 
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:in')) /* line 77 */;
		echo '" class="link-primary">Přihlaste se</a>
                </p>
            </div>
        </div>
    </div>
</div>
';
	}
}
