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

		echo '<div class="sign-container">
    <div class="sign-card">
        <div class="sign-header">
            <img src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 9 */;
		echo '/images/qr-webp-white.webp" alt="QRdoklad" class="sign-logo">
            <h1 class="sign-title">Přihlášení do systému</h1>
            <p class="sign-subtitle">Zadejte své přihlašovací údaje</p>
        </div>

        <div class="sign-body">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['signInForm'] /* line 15 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'sign-form']) /* line 15 */;
		echo '
                <div class="form-floating mb-3">
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 17 */;
		echo '
                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 18 */;
		echo '
                </div>

                <div class="form-floating mb-3">
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 22 */;
		echo '
                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 23 */;
		echo '
                </div>

                <div class="form-check mb-3">
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('remember', $this->global)->getControl()->addAttributes(['class' => 'form-check-input']) /* line 27 */;
		echo '
                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('remember', $this->global)->getLabel())?->addAttributes(['class' => 'form-check-label']) /* line 28 */;
		echo '
                </div>

                <div class="d-grid">
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary btn-lg']) /* line 32 */;
		echo '
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 34 */;

		echo '
        </div>

        <div class="sign-footer">
            <p class="text-center text-muted">
                Nemáte účet? 
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Sign:up')) /* line 40 */;
		echo '" class="link-primary">Zaregistrujte se</a>
            </p>
        </div>
    </div>
</div>

<style>
.sign-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #ffffff 0%, #B1D235 100%);
}

.sign-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.15);
    padding: 0;
    width: 100%;
    max-width: 400px;
    overflow: hidden;
}

.sign-header {
    background: linear-gradient(135deg, #B1D235 0%, #95B11F 100%);
    color: white;
    padding: 2rem;
    text-align: center;
}

.sign-logo {
    height: 40px;
    margin-bottom: 1rem;
}

.sign-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.sign-subtitle {
    opacity: 0.9;
    margin-bottom: 0;
}

.sign-body {
    padding: 2rem;
}

.sign-footer {
    padding: 1rem 2rem 2rem;
}

.sign-form .btn {
    font-weight: 600;
}
</style>
';
	}
}
