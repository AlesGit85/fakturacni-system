<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Settings/default.latte */
final class Template_20723b66fe extends Latte\Runtime\Template
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
                
                <!-- Identifikační údaje -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-upc"></i>
                        <h2 class="section-title">Identifikační údaje</h2>
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
                
                <div class="col-md-6">
                    <div class="form-check-container">
                        <div class="form-check">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('vat_payer', $this->global)->getControl()->addAttributes(['class' => 'form-check-input', 'id' => 'vat-payer-checkbox']) /* line 187 */;
		echo '
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('vat_payer', $this->global)->getLabel())?->addAttributes(['class' => 'form-check-label']) /* line 188 */;
		echo '
                        </div>
                        <small class="form-text text-muted">Zaškrtněte, pokud jste plátce DPH</small>
                    </div>
                </div>
                
                <div class="col-md-6" id="dic-container" style="display: ';
		if ($company && $company->vat_payer) /* line 194 */ {
			echo 'block';
		} else /* line 194 */ {
			echo 'none';
		}
		echo ';">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getControl()->addAttributes(['class' => 'form-control', 'id' => 'dic-field']) /* line 196 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 197 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('bank_account', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 211 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('bank_account', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 212 */;
		echo '
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('bank_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 218 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('bank_name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 219 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('logo', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 233 */;
		echo '
                    
';
		if ($company && $company->logo) /* line 235 */ {
			echo '                        <div class="mt-2 d-flex align-items-center gap-3">
                            <img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 237 */;
			echo '/uploads/logo/';
			echo LR\Filters::escapeHtmlAttr($company->logo) /* line 237 */;
			echo '" alt="Logo" style="max-height: 60px;" class="rounded">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deleteLogo!')) /* line 238 */;
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
		echo Nette\Bridges\FormsLatte\Runtime::item('signature', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 247 */;
		echo "\n";
		if ($company && $company->signature) /* line 248 */ {
			echo '                        <div class="mt-2 d-flex align-items-center gap-3">
                            <img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 250 */;
			echo '/uploads/signature/';
			echo LR\Filters::escapeHtmlAttr($company->signature) /* line 250 */;
			echo '" alt="Podpis" style="max-height: 60px;" class="rounded">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deleteSignature!')) /* line 251 */;
			echo '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Opravdu chcete smazat podpis?\')">
                                <i class="bi bi-trash"></i> Smazat podpis
                            </a>
                        </div>
';
		}
		echo '                </div>
                
                <!-- Barvy pro faktury -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-palette"></i>
                        <h2 class="section-title">Barvy pro faktury</h2>
                    </div>
                </div>
                
                <div class="color-settings-grid">
                    <div class="color-setting-item">
                        <label class="form-label">Barva nadpisu faktury</label>
                        <div class="color-input-group">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_heading_color', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-color']) /* line 270 */;
		echo '
                            <div class="color-preview" style="background-color: #B1D235; color: #212529;">Ukázka barvy</div>
                        </div>
                    </div>
                    
                    <div class="color-setting-item">
                        <label class="form-label">Barva pozadí lichoběžníku</label>
                        <div class="color-input-group">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_trapezoid_bg_color', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-color']) /* line 278 */;
		echo '
                            <div class="color-preview" style="background-color: #B1D235; color: #212529;">Ukázka barvy</div>
                        </div>
                    </div>
                    
                    <div class="color-setting-item">
                        <label class="form-label">Barva textu v lichoběžníku</label>
                        <div class="color-input-group">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_trapezoid_text_color', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-color']) /* line 286 */;
		echo '
                            <div class="color-preview" style="background-color: #212529; color: #B1D235;">Ukázka barvy</div>
                        </div>
                    </div>
                    
                    <div class="color-setting-item">
                        <label class="form-label">Barva štítků (Dodavatel, Odběratel)</label>
                        <div class="color-input-group">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_labels_color', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-color']) /* line 294 */;
		echo '
                            <div class="color-preview" style="background-color: #95B11F; color: #212529;">Ukázka barvy</div>
                        </div>
                    </div>
                    
                    <div class="color-setting-item">
                        <label class="form-label">Barva patičky</label>
                        <div class="color-input-group">
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('invoice_footer_color', $this->global)->getControl()->addAttributes(['class' => 'form-control form-control-color']) /* line 302 */;
		echo '
                            <div class="color-preview" style="background-color: #6c757d; color: #ffffff;">Ukázka barvy</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 mt-5">
                    <div class="d-flex gap-3">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary px-4']) /* line 310 */;
		echo '
                        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 311 */;
		echo '" class="btn btn-outline-secondary">Zrušit</a>
                    </div>
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 314 */;

		echo '
        </div>
    </div>
</div>




';
	}
}
