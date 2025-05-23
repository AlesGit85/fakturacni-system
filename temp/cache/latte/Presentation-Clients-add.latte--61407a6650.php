<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Clients/add.latte */
final class Template_61407a6650 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Clients/add.latte';

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

		echo '<div class="client-form-container">
    <div class="page-header">
        <h1 class="main-title">Přidat nového klienta</h1>
        <p class="text-muted">Vyplňte níže uvedené údaje pro přidání nového klienta do systému</p>
    </div>

    <div class="card shadow-sm rounded-lg border-0">
        <div class="card-body p-4">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['clientForm'] /* line 10 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 10 */;
		echo '
                <!-- Sekce se základními údaji -->
                <div class="col-12">
                    <div class="section-header">
                        <i class="bi bi-person-vcard"></i>
                        <h2 class="section-title">Základní údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 21 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 22 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 35 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 36 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('contact_person', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 42 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('contact_person', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 43 */;
		echo '
                    </div>
                </div>
                
                <!-- Sekce s adresou -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-geo-alt"></i>
                        <h2 class="section-title">Adresa</h2>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 57 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 58 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 64 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 65 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 71 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 72 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 78 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 79 */;
		echo '
                    </div>
                </div>
                
                <!-- Fakturační údaje -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-receipt"></i>
                        <h2 class="section-title">Fakturační údaje</h2>
                    </div>
                </div>
                
<div class="col-md-6">
    <div class="mb-3">
        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getLabel()) /* line 93 */;
		echo '
        <div class="input-group">
            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 95 */;
		echo '
            <button type="button" id="load-from-ares" class="btn btn-outline-primary">
                <i class="bi bi-cloud-download"></i> Načíst z ARES
            </button>
        </div>
        <small class="form-text text-muted">Zadejte IČO a klikněte na tlačítko pro načtení údajů z ARESu</small>
    </div>
</div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 106 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 107 */;
		echo '
                    </div>
                </div>
                
                <div class="col-12 mt-4 d-flex justify-content-between">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 112 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Zpět na seznam klientů
                    </a>
                    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 115 */;
		echo ' class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Uložit klienta
                    </button>
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 119 */;

		echo '
        </div>
    </div>
</div>
';
	}
}
