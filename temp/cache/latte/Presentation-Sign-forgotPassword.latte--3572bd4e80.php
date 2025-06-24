<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Sign/forgotPassword.latte */
final class Template_3572bd4e80 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Sign/forgotPassword.latte';

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
		echo 'Zapomenuté heslo';
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
                <h1 class="auth-title">Zapomenuté heslo</h1>
                <p class="auth-subtitle">Zadejte svou e-mailovou adresu a my vám pošleme odkaz pro obnovení hesla</p>
            </div>

            <div class="auth-body">
                ';
		$form = $this->global->formsStack[] = $this->global->uiControl['forgotPasswordForm'] /* line 16 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 28 */;
		echo '
                        <label for="frm-forgotPasswordForm-email" class="form-label">E-mailová adresa</label>
';
		if ($form['email']->hasErrors()) /* line 30 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['email']->getError()) /* line 31 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="d-grid mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary btn-lg']) /* line 36 */;
		echo '
                    </div>
                ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 38 */;

		echo '
            </div>

            <div class="auth-footer">
                <div class="text-center">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:in')) /* line 43 */;
		echo '" class="auth-link">
                        <i class="bi bi-arrow-left"></i> Zpět na přihlášení
                    </a>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Nápověda:</strong> Zadejte e-mailovou adresu, kterou jste použili při registraci. 
                    Pokud účet existuje, obdržíte email s odkazem pro obnovení hesla.
                </div>
            </div>
        </div>
    </div>
</div>
';
	}
}
