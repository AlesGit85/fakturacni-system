<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Clients/add.latte */
final class Template_ced71a5940 extends Latte\Runtime\Template
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
        <p class="text-muted">Vyplňte IČ a načtěte data z ARESu, nebo vyplňte ručně</p>
    </div>

    <div class="card shadow-sm rounded-lg border-0">
        <div class="card-body p-4">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['clientForm'] /* line 10 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 10 */;
		echo '
                <!-- Sekce pro načítání z ARESu -->
                <div class="col-12">
                    <div class="section-header">
                        <i class="bi bi-cloud-download"></i>
                        <h2 class="section-title">Načítání z ARESu</h2>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Tip:</strong> Zadejte IČ společnosti a klikněte na "Načíst z ARESu" pro automatické vyplnění údajů.
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="mb-3">
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getLabel()) /* line 25 */;
		echo '
                        <div class="input-group">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getControl()->addAttributes(['class' => 'form-control', 'placeholder' => 'Zadejte 7 nebo 8 číslic IČ']) /* line 27 */;
		echo '
                            <button type="button" id="load-from-ares" class="btn btn-primary">
                                <i class="bi bi-cloud-download"></i> Načíst z ARESu
                            </button>
                        </div>
                        <small class="form-text text-muted">Zadejte IČ a klikněte na tlačítko pro načtení údajů z ARESu</small>
                    </div>
                </div>
                
                <!-- Sekce se základními údaji -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-building"></i>
                        <h2 class="section-title">Základní údaje společnosti</h2>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 46 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 47 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 53 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 54 */;
		echo '
                    </div>
                </div>
                
                <!-- Sekce s adresou -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-geo-alt"></i>
                        <h2 class="section-title">Adresa společnosti</h2>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 68 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 69 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 75 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 76 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 82 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 83 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 89 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 90 */;
		echo '
                    </div>
                </div>
                
                <!-- Kontaktní údaje -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-person-lines-fill"></i>
                        <h2 class="section-title">Kontaktní údaje</h2>
                    </div>
                    <div class="alert alert-light">
                        <i class="bi bi-info-circle me-2"></i>
                        Tyto údaje jsou volitelné a slouží pro lepší komunikaci s klientem.
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('contact_person', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 108 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('contact_person', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 109 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 115 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 116 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 122 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 123 */;
		echo '
                    </div>
                </div>
                
                <div class="col-12 mt-4 d-flex justify-content-between">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 128 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Zpět na seznam klientů
                    </a>
                    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 131 */;
		echo ' class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Uložit klienta
                    </button>
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 135 */;

		echo '
        </div>
    </div>
</div>
';
	}
}
