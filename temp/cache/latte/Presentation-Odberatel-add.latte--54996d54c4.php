<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Odberatel/add.latte */
final class Template_54996d54c4 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Odberatel/add.latte';

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

		echo '<h2>Správa odběratelů</h2>
<h3>Přidat nového odběratele</h3>

';
		$form = $this->global->formsStack[] = $this->global->uiControl['odberatelForm'] /* line 5 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 5 */;
		echo '
    <h4 class="section-title">Firemní údaje</h4>
    
    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('nazev', $this->global)->getLabelPart())->addAttributes(['class' => null])->attributes() /* line 9 */;
		echo ' class="required">Název společnosti / Jméno a příjmení</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('nazev', $this->global)->getControlPart())->attributes() /* line 10 */;
		echo '>
    </div>
    
    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('adresa', $this->global)->getLabelPart())->addAttributes(['class' => null])->attributes() /* line 14 */;
		echo ' class="required">Adresa</label>
        <textarea';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('adresa', $this->global)->getControlPart())->attributes() /* line 15 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line 15 */;
		echo '</textarea>
        <div class="tip">Každý řádek adresy oddělte klávesou Enter</div>
    </div>
    
    <div class="form-row">
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('ico', $this->global)->getLabelPart())->addAttributes(['class' => null])->attributes() /* line 22 */;
		echo ' class="required">IČO</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('ico', $this->global)->getControlPart())->attributes() /* line 23 */;
		echo '>
            </div>
        </div>
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getLabelPart())->attributes() /* line 28 */;
		echo '>DIČ</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getControlPart())->attributes() /* line 29 */;
		echo '>
            </div>
        </div>
    </div>
    
    <h4 class="section-title">Kontaktní osoba</h4>
    <div class="tip" style="margin-top: -10px; margin-bottom: 15px;">
        Tato sekce je nepovinná a slouží pouze pro interní účely. Údaje o kontaktní osobě se na faktuře nezobrazí.
    </div>
    
    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('kontakt_jmeno', $this->global)->getLabelPart())->attributes() /* line 40 */;
		echo '>Jméno a příjmení</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('kontakt_jmeno', $this->global)->getControlPart())->attributes() /* line 41 */;
		echo '>
    </div>
    
    <div class="form-row">
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('kontakt_telefon', $this->global)->getLabelPart())->attributes() /* line 47 */;
		echo '>Telefon</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('kontakt_telefon', $this->global)->getControlPart())->attributes() /* line 48 */;
		echo '>
            </div>
        </div>
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('kontakt_email', $this->global)->getLabelPart())->attributes() /* line 53 */;
		echo '>E-mail</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('kontakt_email', $this->global)->getControlPart())->attributes() /* line 54 */;
		echo '>
            </div>
        </div>
    </div>
    
    <div class="form-group" style="margin-top: 20px;">
        <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('save', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 60 */;
		echo ' class="action-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Přidat odběratele
        </button>
        <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 67 */;
		echo '" style="margin-left: 10px; text-decoration: none;">Zrušit</a>
    </div>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 69 */;

		echo "\n";
	}
}
