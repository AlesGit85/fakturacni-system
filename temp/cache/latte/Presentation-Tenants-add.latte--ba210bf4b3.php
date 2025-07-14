<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Tenants/add.latte */
final class Template_ba210bf4b3 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Tenants/add.latte';

	public const Blocks = [
		['content' => 'blockContent', 'head' => 'blockHead'],
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

		echo "\n";
		$this->renderBlock('head', get_defined_vars()) /* line 4 */;
		echo '
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-plus-circle me-2" style="color: #B1D235;"></i>
            Vytvořit nový tenant
        </h1>
        <p class="page-subtitle">
            <i class="bi bi-info-circle me-1" style="color: #95B11F;"></i>
            Vytvoření nového tenanta s administrátorem a základním nastavením
        </p>
    </div>
    <div>
        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 269 */;
		echo '" class="btn btn-secondary-custom">
            <i class="bi bi-arrow-left me-2"></i>
            Zpět na seznam
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="main-card">
            <div class="card-header-custom">
                <h5 class="card-title-custom">
                    <i class="bi bi-building me-2" style="color: #B1D235;"></i>
                    Údaje nového tenanta
                </h5>
            </div>
            <div class="p-4">
                ';
		$form = $this->global->formsStack[] = $this->global->uiControl['createTenantForm'] /* line 286 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 286 */;
		echo '
                    <div class="form-section">
                        <h6 class="form-section-title section-tenant">
                            <i class="bi bi-building me-2" style="color: #B1D235;"></i>
                            Základní údaje tenanta
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label-custom">Název tenanta</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 297 */;
		echo '
                                <div class="form-text-custom">
                                    <i class="bi bi-info-circle me-1" style="color: #95B11F;"></i>
                                    Název bude použit pro identifikaci tenanta v systému
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-custom">Doména (volitelné)</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('domain', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 305 */;
		echo '
                                <div class="form-text-custom">
                                    <i class="bi bi-globe me-1" style="color: #95B11F;"></i>
                                    např. firma-abc.cz
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h6 class="form-section-title section-company">
                            <i class="bi bi-briefcase me-2" style="color: #95B11F;"></i>
                            Údaje společnosti
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label-custom">Název společnosti</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('company_name', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 324 */;
		echo '
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-custom">IČO</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 328 */;
		echo '
                                <div class="form-text-custom">8 číslic</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label-custom">Telefon</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 336 */;
		echo '
                                <div class="form-text-custom">např. +420 123 456 789</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check-custom">
                                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('vat_payer', $this->global)->getControl()->addAttributes(['class' => 'form-check-input form-check-input-custom', 'onchange' => 'toggleDic(this)']) /* line 341 */;
		echo '
                                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('vat_payer', $this->global)->getLabel())?->addAttributes(['class' => 'form-check-label-custom']) /* line 342 */;
		echo '
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" id="dic-row" style="display: none;">
                            <div class="col-md-4 mb-3">
                                <label class="form-label-custom">DIČ</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 350 */;
		echo '
                                <div class="form-text-custom">např. CZ12345678</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Adresa</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom', 'rows' => 2]) /* line 358 */;
		echo '
                                <div class="form-text-custom">Ulice a číslo popisné</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label-custom">Město</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 363 */;
		echo '
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label-custom">PSČ</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 367 */;
		echo '
                                <div class="form-text-custom">123 45</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Země</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 375 */;
		echo '
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h6 class="form-section-title section-admin">
                            <i class="bi bi-person-gear me-2" style="color: #6c757d;"></i>
                            Administrátor tenanta
                        </h6>
                        
                        <div class="alert-custom mb-3">
                            <div class="alert-custom-text">
                                <i class="bi bi-info-circle me-2" style="color: #95B11F;"></i>
                                <strong>Důležité:</strong> Tento uživatel bude mít plná administrátorská práva v rámci vytvořeného tenanta.
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Uživatelské jméno</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 397 */;
		echo '
                                <div class="form-text-custom">
                                    <i class="bi bi-person me-1" style="color: #95B11F;"></i>
                                    Minimálně 3 znaky, bude použito pro přihlášení
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Email</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 405 */;
		echo '
                                <div class="form-text-custom">
                                    <i class="bi bi-envelope me-1" style="color: #95B11F;"></i>
                                    Hlavní kontaktní email
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Heslo</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 416 */;
		echo '
                                <div class="form-text-custom">
                                    <i class="bi bi-shield-lock me-1" style="color: #95B11F;"></i>
                                    Minimálně 6 znaků
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Potvrzení hesla</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password_confirm', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 424 */;
		echo '
                                <div class="form-text-custom">
                                    <i class="bi bi-check2-circle me-1" style="color: #95B11F;"></i>
                                    Hesla se musí shodovat
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Jméno</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('first_name', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 435 */;
		echo '
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Příjmení</label>
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('last_name', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-custom']) /* line 439 */;
		echo '
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-4" style="border-top: 1px solid #e0e0e0;">
                        <div class="form-text-custom">
                            <i class="bi bi-info-circle me-1"></i>
                            Po vytvoření bude tenant ihned aktivní a administrátor se bude moci přihlásit.
                        </div>
                        <div class="d-flex gap-2 btn-group-mobile">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('cancel', $this->global)->getControl()->addAttributes(['class' => 'btn btn-secondary-custom']) /* line 451 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary-custom']) /* line 452 */;
		echo '
                        </div>
                    </div>
                ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 455 */;

		echo '
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-lg-2">
        <div class="sticky-top" style="top: 2rem;">
            <div class="help-card">
                <div class="p-3">
                    <h6 class="help-title">
                        <i class="bi bi-lightbulb me-2" style="color: #B1D235;"></i>
                        Nápověda
                    </h6>
                    
                    <div class="mb-3">
                        <h6 class="help-subtitle" style="color: #95B11F;">Co se vytvoří?</h6>
                        <ul class="list-unstyled">
                            <li class="help-text">• Nový tenant v systému</li>
                            <li class="help-text">• Admin uživatel s plnými právy</li>
                            <li class="help-text">• Základní firemní údaje</li>
                            <li class="help-text">• Adresářová struktura pro moduly</li>
                            <li class="help-text">• Zkopírování základních modulů</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="help-subtitle" style="color: #95B11F;">Jak vyplnit formulář?</h6>
                        <ul class="list-unstyled">
                            <li class="help-text">• Název tenanta musí být unikátní</li>
                            <li class="help-text">• IČO je povinné pro firemní údaje</li>
                            <li class="help-text">• DIČ se zobrazí po zaškrtnutí "Plátce DPH"</li>
                            <li class="help-text">• Username pro admin musí být unikátní</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="help-subtitle" style="color: #95B11F;">Bezpečnost</h6>
                        <ul class="list-unstyled">
                            <li class="help-text">• Každý tenant má izolovaná data</li>
                            <li class="help-text">• Admin vidí pouze svůj tenant</li>
                            <li class="help-text">• Automatické logování změn</li>
                        </ul>
                    </div>
                    
                    <div class="help-alert">
                        <div class="help-alert-text">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Tip:</strong> Zkontrolujte všechny údaje před vytvořením. Některé údaje se později obtížně mění.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDic(checkbox) {
    const dicRow = document.getElementById(\'dic-row\');
    
    if (dicRow) {
        if (checkbox.checked) {
            // Zobraz DIČ pole
            dicRow.style.display = \'block\';
            dicRow.style.opacity = \'0\';
            
            // Animace fade in
            setTimeout(() => {
                dicRow.style.transition = \'opacity 0.3s ease\';
                dicRow.style.opacity = \'1\';
            }, 10);
            
            // Nastav required
            const dicInput = dicRow.querySelector(\'input\');
            if (dicInput) {
                dicInput.required = true;
                // Focus po animaci
                setTimeout(() => {
                    dicInput.focus();
                }, 300);
            }
        } else {
            // Skryj DIČ pole
            dicRow.style.transition = \'opacity 0.3s ease\';
            dicRow.style.opacity = \'0\';
            
            setTimeout(() => {
                dicRow.style.display = \'none\';
            }, 300);
            
            // Odstraň required a vymaž hodnotu
            const dicInput = dicRow.querySelector(\'input\');
            if (dicInput) {
                dicInput.required = false;
                dicInput.value = \'\';
            }
        }
    } else {
        console.error(\'DIČ řádek nenalezen!\');
    }
}

// Inicializace při načtení
document.addEventListener(\'DOMContentLoaded\', function() {
    // Najdi checkbox podle správného Nette Forms ID
    const vatCheckbox = document.getElementById(\'frm-createTenantForm-vat_payer\');
    
    if (vatCheckbox && vatCheckbox.checked) {
        // Pokud je checkbox už zaškrtnutý, zobraz DIČ pole
        toggleDic(vatCheckbox);
    }
    
    // Debug info
    console.log(\'VAT checkbox:\', vatCheckbox);
    console.log(\'DIC row:\', document.getElementById(\'dic-row\'));
});
</script>

';
	}


	/** {block head} on line 4 */
	public function blockHead(array $ʟ_args): void
	{
		echo '<style>
    /* Konzistentní fonty podle celého webu */
    .page-title {
        font-family: \'Inter\', sans-serif;
        font-weight: 700;
        font-size: 28px;
        color: #212529;
        margin-bottom: 8px;
    }
    
    .page-subtitle {
        font-family: \'Inter\', sans-serif;
        font-weight: 400;
        font-size: 16px;
        color: #6c757d;
        margin-bottom: 0;
    }
    
    .main-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .card-header-custom {
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        padding: 1rem 1.5rem;
        border-radius: 8px 8px 0 0;
    }
    
    .card-title-custom {
        font-family: \'Inter\', sans-serif;
        font-weight: 600;
        font-size: 18px;
        color: #212529;
        margin-bottom: 0;
    }
    
    .form-section {
        margin-bottom: 2rem;
        position: relative;
    }
    
    .form-section-title {
        font-family: \'Inter\', sans-serif;
        font-weight: 600;
        font-size: 16px;
        color: #212529;
        margin-bottom: 1rem;
        padding-bottom: 8px;
        border-bottom: 2px solid;
        display: flex;
        align-items: center;
    }
    
    .form-section-title.section-tenant {
        border-bottom-color: #B1D235;
    }
    
    .form-section-title.section-company {
        border-bottom-color: #95B11F;
    }
    
    .form-section-title.section-admin {
        border-bottom-color: #6c757d;
    }
    
    .form-label-custom {
        font-family: \'Inter\', sans-serif;
        font-weight: 500;
        font-size: 14px;
        color: #212529;
        margin-bottom: 4px;
    }
    
    .form-control-custom {
        font-family: \'Inter\', sans-serif;
        font-weight: 400;
        font-size: 14px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 8px 12px;
        transition: all 0.2s ease;
    }
    
    .form-control-custom:focus {
        border-color: #B1D235;
        box-shadow: 0 0 0 2px rgba(177, 210, 53, 0.25);
        outline: none;
    }
    
    .form-text-custom {
        font-family: \'Inter\', sans-serif;
        font-weight: 400;
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }
    
    .form-check-custom {
        display: flex;
        align-items: center;
        margin-top: 1rem;
    }
    
    .form-check-input-custom {
        margin-right: 8px;
    }
    
    .form-check-input-custom:checked {
        background-color: #B1D235;
        border-color: #B1D235;
    }
    
    .form-check-input-custom:focus {
        border-color: #95B11F;
        box-shadow: 0 0 0 2px rgba(149, 177, 31, 0.25);
    }
    
    .form-check-label-custom {
        font-family: \'Inter\', sans-serif;
        font-weight: 500;
        font-size: 14px;
        color: #212529;
    }
    
    .btn-primary-custom {
        background: linear-gradient(135deg, #B1D235 0%, #95B11F 100%);
        color: #212529;
        border: none;
        font-family: \'Inter\', sans-serif;
        font-weight: 600;
        font-size: 16px;
        padding: 10px 20px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #95B11F 0%, #7a9b1a 100%);
        color: #212529;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(149, 177, 31, 0.3);
    }
    
    .btn-secondary-custom {
        background: transparent;
        color: #6c757d;
        border: 1px solid #e0e0e0;
        font-family: \'Inter\', sans-serif;
        font-weight: 500;
        font-size: 16px;
        padding: 10px 20px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .btn-secondary-custom:hover {
        background: #f8f9fa;
        color: #495057;
        border-color: #6c757d;
    }
    
    .alert-custom {
        background-color: rgba(177, 210, 53, 0.1);
        border: 1px solid rgba(177, 210, 53, 0.3);
        border-left: 4px solid #B1D235;
        border-radius: 6px;
        padding: 12px 16px;
    }
    
    .alert-custom-text {
        font-family: \'Inter\', sans-serif;
        font-weight: 400;
        font-size: 14px;
        color: #212529;
        margin-bottom: 0;
    }
    
    .help-card {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .help-title {
        font-family: \'Inter\', sans-serif;
        font-weight: 600;
        font-size: 16px;
        color: #212529;
    }
    
    .help-subtitle {
        font-family: \'Inter\', sans-serif;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .help-text {
        font-family: \'Inter\', sans-serif;
        font-weight: 400;
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 4px;
    }
    
    .help-alert {
        background-color: rgba(255, 193, 7, 0.1);
        border: 1px solid rgba(255, 193, 7, 0.3);
        border-radius: 4px;
        padding: 8px;
    }
    
    .help-alert-text {
        font-family: \'Inter\', sans-serif;
        font-weight: 400;
        font-size: 12px;
        color: #212529;
        margin-bottom: 0;
    }
    
    /* Animace pro DIČ pole */
    #dic-row {
        transition: opacity 0.3s ease;
    }
    
    @media (max-width: 768px) {
        .page-title {
            font-size: 24px;
        }
        
        .form-section-title {
            font-size: 14px;
        }
        
        .btn-group-mobile {
            flex-direction: column;
            width: 100%;
        }
        
        .btn-group-mobile .btn {
            margin-bottom: 8px;
            width: 100%;
        }
    }
</style>
';
	}
}
