<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Settings/default.latte */
final class Template_50116e52d4 extends Latte\Runtime\Template
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

		echo '<h1>Nastavení</h1>

';
		if ($company) /* line 4 */ {
			echo '<div class="card mb-4">
    <div class="card-header">
        Aktuální firemní údaje
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <dl>
                    <dt>Název společnosti</dt>
                    <dd>';
			echo LR\Filters::escapeHtmlText($company->name) /* line 14 */;
			echo '</dd>
                    
                    <dt>Adresa</dt>
                    <dd>';
			echo LR\Filters::escapeHtmlText($company->address) /* line 17 */;
			echo ', ';
			echo LR\Filters::escapeHtmlText($company->zip) /* line 17 */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($company->city) /* line 17 */;
			echo ', ';
			echo LR\Filters::escapeHtmlText($company->country) /* line 17 */;
			echo '</dd>
                    
                    <dt>IČ</dt>
                    <dd>';
			echo LR\Filters::escapeHtmlText($company->ic) /* line 20 */;
			echo '</dd>
                    
';
			if ($company->dic) /* line 22 */ {
				echo '                    <dt>DIČ</dt>
                    <dd>';
				echo LR\Filters::escapeHtmlText($company->dic) /* line 24 */;
				echo '</dd>
';
			}
			echo '                </dl>
            </div>
            <div class="col-md-6">
                <dl>
                    <dt>Kontaktní údaje</dt>
                    <dd>E-mail: ';
			echo LR\Filters::escapeHtmlText($company->email) /* line 31 */;
			echo '</dd>
                    <dd>Telefon: ';
			echo LR\Filters::escapeHtmlText($company->phone) /* line 32 */;
			echo '</dd>
                    
                    <dt>Bankovní údaje</dt>
                    <dd>Účet: ';
			echo LR\Filters::escapeHtmlText($company->bank_account) /* line 35 */;
			echo '</dd>
                    <dd>Banka: ';
			echo LR\Filters::escapeHtmlText($company->bank_name) /* line 36 */;
			echo '</dd>
                </dl>
                
';
			if ($company->logo) /* line 39 */ {
				echo '                <dt>Logo</dt>
                <dd>
                    <img src="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 42 */;
				echo '/uploads/logo/';
				echo LR\Filters::escapeHtmlAttr($company->logo) /* line 42 */;
				echo '" alt="Logo" style="max-height: 100px;" class="mb-2">
                </dd>
';
			}
			echo '                
';
			if ($company->signature) /* line 46 */ {
				echo '                <dt>Podpis</dt>
                <dd>
                    <img src="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 49 */;
				echo '/uploads/signature/';
				echo LR\Filters::escapeHtmlAttr($company->signature) /* line 49 */;
				echo '" alt="Podpis" style="max-height: 100px;">
                </dd>
';
			}
			echo '            </div>
        </div>
    </div>
</div>
';
		}
		echo '
<div class="card mb-4">
    <div class="card-header">
        Upravit firemní údaje
    </div>
    <div class="card-body">
        ';
		$form = $this->global->formsStack[] = $this->global->uiControl['companyForm'] /* line 63 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-3']) /* line 63 */;
		echo '
            <div class="col-md-6">
                <div class="mb-3">
                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getLabel()) /* line 66 */;
		echo '
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 67 */;
		echo '
                </div>
                
                <div class="mb-3">
                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getLabel()) /* line 71 */;
		echo '
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('address', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 72 */;
		echo '
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getLabel()) /* line 78 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('city', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 79 */;
		echo '
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getLabel()) /* line 84 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('zip', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 85 */;
		echo '
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getLabel()) /* line 90 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('country', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 91 */;
		echo '
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getLabel()) /* line 99 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('ic', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 100 */;
		echo '
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <label class="form-check-label">
                        <input type="checkbox" id="vat-payer-checkbox" class="form-check-input" name="vat_payer" value="1" ';
		if ($company && $company->vat_payer) /* line 107 */ {
			echo 'checked';
		}
		echo '> Jsem plátce DPH
                    </label>
                </div>
                
                <div id="dic-container" class="mb-3" style="display: none;">
                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getLabel()) /* line 112 */;
		echo '
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getControl()->addAttributes(['class' => 'form-control', 'id' => 'dic-field']) /* line 113 */;
		echo '
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabel()) /* line 121 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 122 */;
		echo '
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getLabel()) /* line 127 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('phone', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 128 */;
		echo '
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('bank_account', $this->global)->getLabel()) /* line 136 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('bank_account', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 137 */;
		echo '
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('bank_name', $this->global)->getLabel()) /* line 142 */;
		echo '
                            ';
		echo Nette\Bridges\FormsLatte\Runtime::item('bank_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 143 */;
		echo '
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('logo', $this->global)->getLabel()) /* line 149 */;
		echo '
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('logo', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 150 */;
		echo "\n";
		if ($company && $company->logo) /* line 151 */ {
			echo '                        <div class="mt-2 d-flex align-items-center">
                            <img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 153 */;
			echo '/uploads/logo/';
			echo LR\Filters::escapeHtmlAttr($company->logo) /* line 153 */;
			echo '" alt="Logo" style="max-height: 100px; margin-right: 10px;">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deleteLogo!')) /* line 154 */;
			echo '" class="btn btn-sm btn-danger" onclick="return confirm(\'Opravdu chcete smazat logo?\')">Smazat logo</a>
                        </div>
';
		}
		echo '                </div>
                
                <div class="mb-3">
                    ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('signature', $this->global)->getLabel()) /* line 160 */;
		echo '
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('signature', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 161 */;
		echo "\n";
		if ($company && $company->signature) /* line 162 */ {
			echo '                        <div class="mt-2 d-flex align-items-center">
                            <img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 164 */;
			echo '/uploads/signature/';
			echo LR\Filters::escapeHtmlAttr($company->signature) /* line 164 */;
			echo '" alt="Podpis" style="max-height: 100px; margin-right: 10px;">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('deleteSignature!')) /* line 165 */;
			echo '" class="btn btn-sm btn-danger" onclick="return confirm(\'Opravdu chcete smazat podpis?\')">Smazat podpis</a>
                        </div>
';
		}
		echo '                </div>
            </div>
            
            <div class="col-12">
                <div class="mb-3">
                    ';
		echo Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControl()->addAttributes(['class' => 'btn btn-primary']) /* line 173 */;
		echo '
                </div>
            </div>
        ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 176 */;

		echo '
    </div>
</div>

<script>
// Spustí se po načtení stránky
document.addEventListener(\'DOMContentLoaded\', function() {
    const vatPayerCheckbox = document.getElementById(\'vat-payer-checkbox\');
    const dicContainer = document.getElementById(\'dic-container\');
    
    // Funkce pro zobrazení/skrytí pole DIČ
    function toggleDicField() {
        if (vatPayerCheckbox.checked) {
            dicContainer.style.display = \'block\';
        } else {
            dicContainer.style.display = \'none\';
            document.getElementById(\'dic-field\').value = \'\';
        }
    }
    
    // Přidání posluchače události na změnu checkboxu
    vatPayerCheckbox.addEventListener(\'change\', toggleDicField);
    
    // Inicializace stavu při načtení stránky
    toggleDicField();
});
</script>
';
	}
}
