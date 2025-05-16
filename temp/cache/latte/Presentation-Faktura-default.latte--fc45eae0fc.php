<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Faktura/default.latte */
final class Template_fc45eae0fc extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Faktura/default.latte';

	public const Blocks = [
		['content' => 'blockContent', 'scripts' => 'blockScripts'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		$this->renderBlock('content', get_defined_vars()) /* line 1 */;
		echo "\n";
		$this->renderBlock('scripts', get_defined_vars()) /* line 77 */;
	}


	/** {block content} on line 1 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<h2>Generování faktur</h2>

<div class="info">
    Tento generátor vytváří faktury s moderním designem a QR kódem pro platby.
</div>

';
		$form = $this->global->formsStack[] = $this->global->uiControl['fakturaForm'] /* line 8 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 8 */;
		echo "\n";
		if (isset($dodavatel['logo']) && !empty($dodavatel['logo']) && file_exists("../www/{$dodavatel['logo']}")) /* line 9 */ {
			echo '    <div class="form-group checkbox-field logo-option">
        <input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('pridat_logo', $this->global)->getControlPart())->attributes() /* line 11 */;
			echo '> <label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('pridat_logo', $this->global)->getLabelPart())->attributes() /* line 11 */;
			echo '>Přidat logo na fakturu</label>
    </div>
';
		}
		echo '
    <div class="odberatel-selector">
        <div class="selector-group">
            <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('odberatel_id', $this->global)->getLabelPart())->attributes() /* line 17 */;
		echo '>Vyberte odběratele:</label>
            <select';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('odberatel_id', $this->global)->getControlPart())->addAttributes(['class' => null, 'style' => null])->attributes() /* line 18 */;
		echo ' class="odberatel-dropdown" style="flex-grow: 1;">';
		echo $ʟ_elem->getHtml() /* line 18 */;
		echo '</select>
        </div>

        <div class="manual-toggle">
            <label class="toggle-switch">
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('manual_toggle', $this->global)->getControlPart())->attributes() /* line 23 */;
		echo '>
                <span class="toggle-slider"></span>
            </label>
            <span class="toggle-label">Zadat odběratele ručně</span>
        </div>
    </div>

    <div class="form-group" id="odberatel-manual-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('odberatel', $this->global)->getLabelPart())->attributes() /* line 31 */;
		echo '>Odběratel (název a adresa):</label>
        <textarea';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('odberatel', $this->global)->getControlPart())->addAttributes(['rows' => null])->attributes() /* line 32 */;
		echo ' rows="6">';
		echo $ʟ_elem->getHtml() /* line 32 */;
		echo '</textarea>
        <div class="tip">Pro každý řádek adresy stiskněte Enter. K dispozici je až 6 řádků.</div>
    </div>

    <div class="form-row">
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('castka', $this->global)->getLabelPart())->attributes() /* line 39 */;
		echo '>Částka (Kč):</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('castka', $this->global)->getControlPart())->attributes() /* line 40 */;
		echo '>
            </div>
        </div>
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('vs', $this->global)->getLabelPart())->attributes() /* line 45 */;
		echo '>Variabilní symbol:</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('vs', $this->global)->getControlPart())->attributes() /* line 46 */;
		echo '>
                <div class="tip">Pouze číslice, např. číslo faktury</div>
            </div>
        </div>
        <div class="form-column">
            <div class="form-group">
                <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('splatnost', $this->global)->getLabelPart())->attributes() /* line 52 */;
		echo '>Datum splatnosti:</label>
                <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('splatnost', $this->global)->getControlPart())->attributes() /* line 53 */;
		echo '>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('predmet1', $this->global)->getLabelPart())->attributes() /* line 59 */;
		echo '>Předmět fakturace 1:</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('predmet1', $this->global)->getControlPart())->attributes() /* line 60 */;
		echo '>
    </div>

    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('predmet2', $this->global)->getLabelPart())->attributes() /* line 64 */;
		echo '>Předmět fakturace 2 (volitelné):</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('predmet2', $this->global)->getControlPart())->attributes() /* line 65 */;
		echo '>
    </div>

    <div class="form-group">
        <label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('predmet3', $this->global)->getLabelPart())->attributes() /* line 69 */;
		echo '>Předmět fakturace 3 (volitelné):</label>
        <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('predmet3', $this->global)->getControlPart())->attributes() /* line 70 */;
		echo '>
    </div>

    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('generate', $this->global)->getControlPart())->attributes() /* line 73 */;
		echo '>Vygenerovat fakturu (PDF)</button>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 74 */;

		echo "\n";
	}


	/** {block scripts} on line 77 */
	public function blockScripts(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 78 */;
		echo '/js/section-generovani.js"></script>
';
	}
}
