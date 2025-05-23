<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Sign/in.latte */
final class Template_85f5c6718e extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Sign/in.latte';

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
		echo 'Přihlášení';
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
                <h1 class="auth-title">Přihlášení do systému</h1>
                <p class="auth-subtitle">Zadejte své přihlašovací údaje</p>
            </div>

            <div class="auth-body">
                ';
		$form = $this->global->formsStack[] = $this->global->uiControl['signInForm'] /* line 16 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'auth-form']) /* line 16 */;
		echo "\n";
		if ($form->hasErrors()) /* line 18 */ {
			echo '                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
';
			foreach ($form->getErrors() as $error) /* line 21 */ {
				echo '                                ';
				echo LR\Filters::escapeHtmlText($error) /* line 22 */;
				echo "\n";

			}

			echo '                        </div>
';
		}
		echo '                    
                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 28 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 29 */;
		echo "\n";
		if ($form['username']->hasErrors()) /* line 30 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['username']->getError()) /* line 31 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 36 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 37 */;
		echo "\n";
		if ($form['password']->hasErrors()) /* line 38 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['password']->getError()) /* line 39 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-check mb-4">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('remember', $this->global)->getControl()->addAttributes(['class' => 'form-check-input']) /* line 44 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('remember', $this->global)->getLabel())?->addAttributes(['class' => 'form-check-label']) /* line 45 */;
		echo '
                    </div>

                    <div class="d-grid">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary btn-lg']) /* line 49 */;
		echo '
                    </div>
                ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 51 */;

		echo '
            </div>

            <div class="auth-footer">
                <p class="text-center text-muted">
                    Nemáte účet? 
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:up')) /* line 57 */;
		echo '" class="auth-link">Zaregistrujte se</a>
                </p>
            </div>
        </div>
    </div>
</div>
';
	}
}
