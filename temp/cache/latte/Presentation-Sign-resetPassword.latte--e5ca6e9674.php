<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Sign/resetPassword.latte */
final class Template_e5ca6e9674 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Sign/resetPassword.latte';

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
		echo 'Nastavení nového hesla';
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
                <h1 class="auth-title">Nastavení nového hesla</h1>
                <p class="auth-subtitle">Zadejte své nové heslo</p>
            </div>

            <div class="auth-body">
                ';
		$form = $this->global->formsStack[] = $this->global->uiControl['resetPasswordForm'] /* line 16 */;
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
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('token', $this->global)->getControl()->addAttributes(['value' => $token]) /* line 27 */;
		echo '
                    
                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 30 */;
		echo '
                        <label for="frm-resetPasswordForm-password" class="form-label">Nové heslo</label>
';
		if ($form['password']->hasErrors()) /* line 32 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['password']->getError()) /* line 33 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="form-floating mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 38 */;
		echo '
                        <label for="frm-resetPasswordForm-passwordVerify" class="form-label">Heslo znovu</label>
';
		if ($form['passwordVerify']->hasErrors()) /* line 40 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['passwordVerify']->getError()) /* line 41 */;
			echo '</div>
';
		}
		echo '                    </div>

                    <div class="d-grid mb-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary btn-lg']) /* line 46 */;
		echo '
                    </div>
                ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 48 */;

		echo '
            </div>

            <div class="auth-footer">
                <div class="alert alert-warning">
                    <i class="bi bi-shield-exclamation me-2"></i>
                    <strong>Bezpečnostní požadavky:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Heslo musí mít alespoň 8 znaků</li>
                        <li>Musí obsahovat alespoň jednu číslici</li>
                        <li>Musí obsahovat alespoň jedno velké písmeno</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
';
	}
}
