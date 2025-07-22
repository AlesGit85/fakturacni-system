<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Clients/show.latte */
final class Template_7c1c5a5179 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Clients/show.latte';

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

		echo '<div class="client-detail-container">
    <!-- Záhlaví stránky -->
    <div class="page-header mb-4">
        <div class="header-content">
            <h1 class="main-title mb-2">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->name)) /* line 7 */;
		echo '</h1>
            <p class="text-muted">Detail klienta a jeho kontaktní informace</p>
        </div>
        <div class="header-actions">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$client->id])) /* line 11 */;
		echo '" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Upravit klienta
            </a>
        </div>
    </div>

    <!-- Obsah stránky -->
    <div class="row g-4">
        <!-- Základní informace -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    <h3>Základní informace</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-building"></i>
                            Název společnosti
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->name)) /* line 33 */;
		echo '</div>
                    </div>
                    
';
		if ($client->contact_person) /* line 36 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-person"></i>
                            Kontaktní osoba
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->contact_person)) /* line 43 */;
			echo '</div>
                    </div>
';
		}
		echo '                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-envelope"></i>
                            E-mail
                        </div>
                        <div class="info-value">
';
		if ($client->email) /* line 53 */ {
			echo '                                <a href="mailto:';
			echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($client->email)) /* line 55 */;
			echo '" class="client-email">';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->email)) /* line 55 */;
			echo '</a>
';
		} else /* line 56 */ {
			echo '                                <span class="text-muted">—</span>
';
		}
		echo '                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-telephone"></i>
                            Telefon
                        </div>
                        <div class="info-value">
';
		if ($client->phone) /* line 68 */ {
			echo '                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->phone)) /* line 70 */;
			echo "\n";
		} else /* line 71 */ {
			echo '                                <span class="text-muted">—</span>
';
		}
		echo '                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-geo-alt"></i>
                            Adresa
                        </div>
                        <div class="info-value">
';
		if ($client->address) /* line 83 */ {
			echo '                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->address)) /* line 85 */;
			echo '<br>
                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->zip)) /* line 86 */;
			echo ' ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->city)) /* line 86 */;
			echo "\n";
			if ($client->country) /* line 87 */ {
				echo '                                    <br>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->country)) /* line 88 */;
				echo "\n";
			}
		} else /* line 90 */ {
			echo '                                <span class="text-muted">—</span>
';
		}
		echo '                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-globe"></i>
                            Země
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->country)) /* line 102 */;
		echo '</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fakturační údaje -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-receipt me-2"></i>
                    <h3>Fakturační údaje</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc"></i>
                            IČ
                        </div>
                        <div class="info-value">
';
		if ($client->ic) /* line 122 */ {
			echo '                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->ic)) /* line 124 */;
			echo "\n";
		} else /* line 125 */ {
			echo '                                <span class="text-muted">—</span>
';
		}
		echo '                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc-scan"></i>
                            DIČ
                        </div>
                        <div class="info-value">
';
		if ($client->dic) /* line 137 */ {
			echo '                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->dic)) /* line 139 */;
			echo "\n";
		} else /* line 140 */ {
			echo '                                <span class="text-muted">—</span>
';
		}
		echo '                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiky faktur -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <h3>Faktury</h3>
                </div>
                <div class="info-card-body">
';
		$invoiceCount = $presenter->getClientInvoiceCount($client->id) /* line 157 */;
		echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-file-earmark-text"></i>
                            Počet faktur
                        </div>
                        <div class="info-value">
';
		if ($invoiceCount > 0) /* line 164 */ {
			echo '                                <span class="badge bg-dark">';
			echo LR\Filters::escapeHtmlText($invoiceCount) /* line 166 */;
			echo '</span>
';
		} else /* line 167 */ {
			echo '                                <span class="text-muted">Zatím žádné faktury</span>
';
		}
		echo '                        </div>
                    </div>
                    
';
		if ($invoiceCount > 0) /* line 173 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-arrow-right"></i>
                            Akce
                        </div>
                        <div class="info-value">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['client' => $client->id])) /* line 180 */;
			echo '" class="btn btn-sm btn-outline-dark">
                                Zobrazit faktury klienta
                            </a>
                        </div>
                    </div>
';
		}
		echo '                </div>
            </div>
        </div>
    </div>

    <!-- Akční tlačítka -->
    <div class="action-buttons-container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 194 */;
		echo '" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zpět na seznam klientů
            </a>
            
            <div class="d-flex gap-2">
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$client->id])) /* line 200 */;
		echo '" class="btn btn-primary">
                    <i class="bi bi-pencil-square"></i> Upravit
                </a>
                
';
		$invoiceCount = $presenter->getClientInvoiceCount($client->id) /* line 204 */;
		if ($invoiceCount == 0) /* line 205 */ {
			echo '                    <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$client->id, '_csrf_token' => $csrfToken])) /* line 207 */;
			echo '" class="btn btn-danger" onclick="return confirm(\'Opravdu chcete smazat tohoto klienta?\')">
                        <i class="bi bi-trash"></i> Smazat
                    </a>
';
		} else /* line 210 */ {
			echo '                    <button class="btn btn-outline-danger" disabled title="Klient má ';
			echo LR\Filters::escapeHtmlAttr($presenter->getInvoiceCountText($invoiceCount)) /* line 212 */;
			echo ' a nelze ho smazat">
                        <i class="bi bi-trash"></i> Smazat
                    </button>
';
		}
		echo '            </div>
        </div>
        
';
		if ($invoiceCount > 0) /* line 219 */ {
			echo '        <div class="mt-2">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Pro smazání klienta je nutné nejprve smazat všechny jeho faktury (';
			echo LR\Filters::escapeHtmlText($presenter->getInvoiceCountText($invoiceCount)) /* line 224 */;
			echo ')
            </small>
        </div>
';
		}
		echo '    </div>
</div>
';
	}
}
