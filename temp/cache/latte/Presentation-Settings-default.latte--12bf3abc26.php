<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Settings/default.latte */
final class Template_12bf3abc26 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Settings/default.latte';

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

		echo '<div class="settings-container">
    <div class="page-header">
        <h1 class="main-title">Nastavení</h1>
        <p class="text-muted">Správa firemních údajů a nastavení systému</p>
    </div>

';
		if ($company) /* line 8 */ {
			echo '    <div class="info-card mb-4">
        <div class="info-card-header">
            <i class="bi bi-info-circle me-2"></i>
            <h3>Aktuální firemní údaje</h3>
        </div>
        <div class="info-card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-building"></i>
                            Název společnosti
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText($company->name) /* line 22 */;
			echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-geo-alt"></i>
                            Adresa
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText($company->address) /* line 30 */;
			echo ', ';
			echo LR\Filters::escapeHtmlText($company->zip) /* line 30 */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($company->city) /* line 30 */;
			echo ', ';
			echo LR\Filters::escapeHtmlText($company->country) /* line 30 */;
			echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc"></i>
                            IČ
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText($company->ic) /* line 38 */;
			echo '</div>
                    </div>
                    
';
			if ($company->dic) /* line 41 */ {
				echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc-scan"></i>
                            DIČ
                        </div>
                        <div class="info-value">';
				echo LR\Filters::escapeHtmlText($company->dic) /* line 47 */;
				echo '</div>
                    </div>
';
			}
			echo '                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-envelope"></i>
                            E-mail
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText($company->email) /* line 57 */;
			echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-telephone"></i>
                            Telefon
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText($company->phone) /* line 65 */;
			echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-bank"></i>
                            Bankovní účet
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText($company->bank_account) /* line 73 */;
			echo ' (';
			echo LR\Filters::escapeHtmlText($company->bank_name) /* line 73 */;
			echo ')</div>
                    </div>
                    
';
			if ($company->logo) /* line 76 */ {
				echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-image"></i>
                            Logo
                        </div>
                        <div class="info-value">
                            <img src="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 83 */;
				echo '/uploads/logo/';
				echo LR\Filters::escapeHtmlAttr($company->logo) /* line 83 */;
				echo '" alt="Logo" style="max-height: 60px;" class="rounded">
                        </div>
                    </div>
';
			}
			echo '                    
';
			if ($company->signature) /* line 88 */ {
				echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-pen"></i>
                            Podpis
                        </div>
                        <div class="info-value">
                            <img src="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 95 */;
				echo '/uploads/signature/';
				echo LR\Filters::escapeHtmlAttr($company->signature) /* line 95 */;
				echo '" alt="Podpis" style="max-height: 60px;" class="rounded">
                        </div>
                    </div>
';
			}
			echo '                </div>
            </div>
        </div>
    </div>
';
		}
		echo '
    <div class="card shadow-sm rounded-lg border-0 mb-4">
        <div class="card-header">
            <i class="bi bi-pencil-square me-2"></i>
            <h3>Upravit firemní údaje</h3>
        </div>
        <div class="card-body">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['companyForm'] /* line 111 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 111 */;
		echo '
                <!-- Základní údaje -->
                <div class="col-12">
                    <div class="section-header">
                        <i class="bi bi-building"></i>
                        <h2 class="section-title">Základní údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 122 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 123 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 129 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 130 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 136 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 137 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 143 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 144 */;
		echo '
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 150 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 151 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 157 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 158 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 164 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 165 */;
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
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 179 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 180 */;
		echo '
                    </div>
                </div>
                
                <!-- DPH checkbox - jednoduchá verze -->
                <div class="col-md-6">
                    <div class="form-check-container">
                        <div class="form-check">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('vat_payer', $this->global)->getControl()->addAttributes(['class' => 'form-check-input', 'id' => 'vat-payer-checkbox']) /* line 188 */;
		echo '
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('vat_payer', $this->global)->getLabel())?->addAttributes(['class' => 'form-check-label', 'for' => 'vat-payer-checkbox'])?->startTag() /* line 189 */;
		echo '
                                <i class="bi bi-percent me-2"></i>
                            ';
		echo $ʟ_label?->endTag() /* line 191 */;
		echo '
                        </div>
                        <small class="text-muted">Fakturace s DPH</small>
                    </div>
                </div>
                
                <!-- DIČ pole - zobrazí se když je zaškrtnuto DPH -->
                <div id="dic-container" class="col-md-6" style="display: ';
		if ($company && $company->vat_payer) /* line 198 */ {
			echo 'block';
		} else /* line 198 */ {
			echo 'none';
		}
		echo ';">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getControl()->addAttributes(['class' => 'form-control', 'id' => 'dic-field']) /* line 200 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 201 */;
		echo '
                    </div>
                </div>
                
                <!-- Bankovní údaje -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-bank"></i>
                        <h2 class="section-title">Bankovní údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('bank_account', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 215 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('bank_account', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 216 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('bank_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 222 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('bank_name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 223 */;
		echo '
                    </div>
                </div>
                
                <!-- Soubory -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-file-earmark-image"></i>
                        <h2 class="section-title">Logo a podpis</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Logo společnosti</label>
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('logo', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 237 */;
		echo "\n";
		if ($company && $company->logo) /* line 238 */ {
			echo '                        <div class="mt-2 d-flex align-items-center gap-3">
                            <img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 240 */;
			echo '/uploads/logo/';
			echo LR\Filters::escapeHtmlAttr($company->logo) /* line 240 */;
			echo '" alt="Logo" style="max-height: 60px;" class="rounded">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deleteLogo!')) /* line 241 */;
			echo '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Opravdu chcete smazat logo?\')">
                                <i class="bi bi-trash"></i> Smazat logo
                            </a>
                        </div>
';
		}
		echo '                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Podpis</label>
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('signature', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 250 */;
		echo "\n";
		if ($company && $company->signature) /* line 251 */ {
			echo '                        <div class="mt-2 d-flex align-items-center gap-3">
                            <img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 253 */;
			echo '/uploads/signature/';
			echo LR\Filters::escapeHtmlAttr($company->signature) /* line 253 */;
			echo '" alt="Podpis" style="max-height: 60px;" class="rounded">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deleteSignature!')) /* line 254 */;
			echo '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Opravdu chcete smazat podpis?\')">
                                <i class="bi bi-trash"></i> Smazat podpis
                            </a>
                        </div>
';
		}
		echo '                </div>
                
                <!-- Nastavení barev faktury -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-palette"></i>
                        <h2 class="section-title">Nastavení barev faktury</h2>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="color-settings-grid">
                        <div class="color-setting-item">
                            <label class="form-label">Barva nadpisu "FAKTURA"</label>
                            <div class="color-input-group">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_heading_color', $this->global)->getControl() /* line 274 */;
		echo '
                                <span class="color-preview">FAKTURA</span>
                            </div>
                        </div>
                        
                        <div class="color-setting-item">
                            <label class="form-label">Barva pozadí lichoběžníku</label>
                            <div class="color-input-group">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_trapezoid_bg_color', $this->global)->getControl() /* line 282 */;
		echo '
                                <span class="color-preview">Pozadí</span>
                            </div>
                        </div>
                        
                        <div class="color-setting-item">
                            <label class="form-label">Barva textu v lichoběžníku</label>
                            <div class="color-input-group">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_trapezoid_text_color', $this->global)->getControl() /* line 290 */;
		echo '
                                <span class="color-preview">Text v pozadí</span>
                            </div>
                        </div>
                        
                        <div class="color-setting-item">
                            <label class="form-label">Barva popisků (Dodavatel, Odběratel, atd.)</label>
                            <div class="color-input-group">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_labels_color', $this->global)->getControl() /* line 298 */;
		echo '
                                <span class="color-preview">Dodavatel, Odběratel, atd.</span>
                            </div>
                        </div>
                        
                        <div class="color-setting-item">
                            <label class="form-label">Barva patičky</label>
                            <div class="color-input-group">
                                ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_footer_color', $this->global)->getControl() /* line 306 */;
		echo '
                                <span class="color-preview">Patička</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Tyto barvy budou použity při generování PDF faktur</small>
                    </div>
                </div>
                
                <!-- Akční tlačítka -->
                <div class="col-12 mt-4">
                    <div class="action-buttons-container">
                        <div class="d-flex justify-content-end">
                            <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 322 */;
		echo ' class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Uložit nastavení
                            </button>
                        </div>
                    </div>
                </div>
                
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 329 */;

		echo '
        </div>
    </div>
</div>

<style>
/* Styly pro jednoduchý checkbox */
.form-check-container {
    padding: 1rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.form-check-container:hover {
    border-color: #B1D235;
    background: rgba(177, 210, 53, 0.1);
}

.form-check {
    margin-bottom: 0.5rem;
}

.form-check-input {
    margin-right: 0.5rem;
}

.form-check-input:checked {
    background-color: #B1D235;
    border-color: #B1D235;
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(177, 210, 53, 0.25);
    border-color: #95B11F;
}

.form-check-label {
    font-weight: 600;
    color: #212529;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.form-check-label i {
    color: #6c757d;
}

.form-check-input:checked + .form-check-label i {
    color: #95B11F;
}

.form-check-container .text-muted {
    font-size: 0.875rem;
    margin-left: 1.5rem;
}

/* Animace pro DIČ container */
#dic-container {
    transition: all 0.3s ease;
    overflow: hidden;
}

/* Responsive úpravy */
@media (max-width: 767px) {
    .form-check-container {
        margin-bottom: 1rem;
    }
    
    #dic-container {
        margin-top: 1rem;
    }
}

/* Color settings grid */
.color-settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.color-setting-item {
    padding: 1rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #f8f9fa;
}

.color-input-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.color-preview {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: 500;
    min-width: 120px;
    text-align: center;
    border: 1px solid #dee2e6;
}

/* Vylepšení pro primary button */
.btn-primary {
    background-color: #B1D235;
    border-color: #B1D235;
    color: #212529;
    font-weight: 600;
}

.btn-primary:hover {
    background-color: #95B11F;
    border-color: #95B11F;
    color: #212529;
}

.btn-primary:focus {
    box-shadow: 0 0 0 0.2rem rgba(177, 210, 53, 0.25);
}
</style>

<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 458 */;
		echo '/js/settings.js"></script>
';
	}
}
