<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Dodavatel/default.latte */
final class Template_059e57cb1d extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Dodavatel/default.latte';

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

		echo '<h2>Nastavení údajů dodavatele</h2>

';
		$form = $this->global->formsStack[] = $this->global->uiControl['dodavatelForm'] /* line 4 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 4 */;
		echo '
    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('nazev', $this->global)->getLabelPart())->addAttributes(['class' => null])->attributes() /* line 6 */;
		echo ' class="required">Jméno a příjmení / Název společnosti</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('nazev', $this->global)->getControlPart())->attributes() /* line 7 */;
		echo '>
    </div>

    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('adresa', $this->global)->getLabelPart())->addAttributes(['class' => null])->attributes() /* line 11 */;
		echo ' class="required">Adresa</label>
        <textarea';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('adresa', $this->global)->getControlPart())->attributes() /* line 12 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line 12 */;
		echo '</textarea>
    </div>

    <!-- IČO a DIČ na jednom řádku -->
    <div class="form-row">
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('ico', $this->global)->getLabelPart())->addAttributes(['class' => null])->attributes() /* line 19 */;
		echo ' class="required">IČO</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('ico', $this->global)->getControlPart())->attributes() /* line 20 */;
		echo '>
            </div>
        </div>
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getLabelPart())->attributes() /* line 25 */;
		echo '>DIČ</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('dic', $this->global)->getControlPart())->attributes() /* line 26 */;
		echo '>
            </div>
        </div>
    </div>

    <div class="form-group checkbox-field">
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('platce_dph', $this->global)->getControlPart())->attributes() /* line 32 */;
		echo '> <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('platce_dph', $this->global)->getLabelPart())->attributes() /* line 32 */;
		echo '>Jsem plátce DPH</label>
    </div>

    <!-- Číslo účtu a název banky na jednom řádku -->
    <div class="form-row">
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('ucet', $this->global)->getLabelPart())->addAttributes(['class' => null])->attributes() /* line 39 */;
		echo ' class="required">Číslo účtu (ve formátu číslo/kód_banky)</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('ucet', $this->global)->getControlPart())->attributes() /* line 40 */;
		echo '>
            </div>
        </div>
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('banka', $this->global)->getLabelPart())->attributes() /* line 45 */;
		echo '>Název banky</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('banka', $this->global)->getControlPart())->attributes() /* line 46 */;
		echo '>
            </div>
        </div>
    </div>

    <!-- BIC/SWIFT a IBAN na jednom řádku -->
    <div class="form-row">
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('swift', $this->global)->getLabelPart())->attributes() /* line 55 */;
		echo '>BIC/SWIFT</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('swift', $this->global)->getControlPart())->attributes() /* line 56 */;
		echo '>
            </div>
        </div>
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('iban', $this->global)->getLabelPart())->attributes() /* line 61 */;
		echo '>IBAN</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('iban', $this->global)->getControlPart())->attributes() /* line 62 */;
		echo '>
            </div>
        </div>
    </div>

    <!-- Telefon a email na jednom řádku -->
    <div class="form-row">
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('telefon', $this->global)->getLabelPart())->attributes() /* line 71 */;
		echo '>Telefon</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('telefon', $this->global)->getControlPart())->attributes() /* line 72 */;
		echo '>
            </div>
        </div>
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabelPart())->attributes() /* line 77 */;
		echo '>Email</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControlPart())->attributes() /* line 78 */;
		echo '>
            </div>
        </div>
    </div>

    <!-- Živnostenský úřad -->
    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('zivnost_1', $this->global)->getLabelPart())->attributes() /* line 85 */;
		echo '>§ citace</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('zivnost_1', $this->global)->getControlPart())->attributes() /* line 86 */;
		echo '>
    </div>

    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('zivnost_2', $this->global)->getLabelPart())->attributes() /* line 90 */;
		echo '>Živnostenský úřad</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('zivnost_2', $this->global)->getControlPart())->attributes() /* line 91 */;
		echo '>
    </div>

    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('save', $this->global)->getControlPart())->attributes() /* line 94 */;
		echo '>Uložit údaje</button>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 95 */;

		echo "\n";
	}
}
