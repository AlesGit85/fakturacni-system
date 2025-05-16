<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Odberatel/default.latte */
final class Template_61d4d88a8b extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Odberatel/default.latte';

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


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['odberatel' => '36'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}


	/** {block content} on line 1 */
	public function blockContent(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<h2>Správa odběratelů</h2>

<div class="action-bar">
    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 5 */;
		echo '" class="action-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Přidat nového odběratele
    </a>
    
    <form class="search-form" method="GET" action="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 13 */;
		echo '">
        <button type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>
        <input type="text" name="search" placeholder="Hledat odběratele..." value="';
		echo LR\Filters::escapeHtmlAttr($searchTerm ?? '') /* line 20 */;
		echo '">
    </form>
</div>

';
		if (empty($odberatele)) /* line 24 */ {
			echo '    <div class="empty-state">
';
			if (isset($searchTerm) && !empty($searchTerm)) /* line 26 */ {
				echo '            <p>Pro hledaný výraz "';
				echo LR\Filters::escapeHtmlText($searchTerm) /* line 27 */;
				echo '" nebyli nalezeni žádní odběratelé.</p>
            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 28 */;
				echo '" class="action-button">Zobrazit všechny odběratele</a>
';
			} else /* line 29 */ {
				echo '            <p>Zatím nemáte přidané žádné odběratele.</p>
            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 31 */;
				echo '" class="action-button">Přidat prvního odběratele</a>
';
			}
			echo '    </div>
';
		} else /* line 34 */ {
			echo '    <div class="card-container">
';
			foreach ($odberatele as $odberatel) /* line 36 */ {
				echo '            <div class="odberatel-card">
                <div class="card-header">
                    <h3 class="card-title">';
				echo LR\Filters::escapeHtmlText($odberatel['nazev']) /* line 39 */;
				echo '</h3>
                </div>
                
                <div class="card-body">
                    <div class="card-info">
                        <span class="card-info-label">Adresa:</span>
                        <span class="card-info-value">';
				echo nl2br($odberatel['adresa']) /* line 45 */;
				echo '</span>
                    </div>
                    
                    <div class="card-info">
                        <span class="card-info-label">IČO:</span>
                        <span class="card-info-value">';
				echo LR\Filters::escapeHtmlText($odberatel['ico']) /* line 50 */;
				echo '</span>
                    </div>
                    
';
				if (!empty($odberatel['dic'])) /* line 53 */ {
					echo '                    <div class="card-info">
                        <span class="card-info-label">DIČ:</span>
                        <span class="card-info-value">';
					echo LR\Filters::escapeHtmlText($odberatel['dic']) /* line 56 */;
					echo '</span>
                    </div>
';
				}
				echo '                </div>
                
';
				if (!empty($odberatel['kontakt']['jmeno']) || !empty($odberatel['kontakt']['telefon']) || !empty($odberatel['kontakt']['email'])) /* line 61 */ {
					echo '                    <div class="card-contact">
';
					if (!empty($odberatel['kontakt']['jmeno'])) /* line 63 */ {
						echo '                            <div><strong>';
						echo LR\Filters::escapeHtmlText($odberatel['kontakt']['jmeno']) /* line 64 */;
						echo '</strong></div>
';
					}
					echo '                        
';
					if (!empty($odberatel['kontakt']['telefon'])) /* line 67 */ {
						echo '                            <div style="margin-top: 5px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 5px;">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                ';
						echo LR\Filters::escapeHtmlText($odberatel['kontakt']['telefon']) /* line 72 */;
						echo '
                            </div>
';
					}
					echo '                        
';
					if (!empty($odberatel['kontakt']['email'])) /* line 76 */ {
						echo '                            <div style="margin-top: 5px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 5px;">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                ';
						echo LR\Filters::escapeHtmlText($odberatel['kontakt']['email']) /* line 82 */;
						echo '
                            </div>
';
					}
					echo '                    </div>
';
				}
				echo '                
                <div class="card-actions">
                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$odberatel['id']])) /* line 89 */;
				echo '" class="card-btn edit">Upravit</a>
                    <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$odberatel['id']])) /* line 90 */;
				echo '" class="card-btn delete" onclick="return confirm(\'Opravdu chcete smazat tohoto odběratele?\');">Smazat</a>
                </div>
            </div>
';

			}

			echo '    </div>
';
		}
	}
}
