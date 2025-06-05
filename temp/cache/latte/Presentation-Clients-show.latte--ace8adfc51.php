<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Clients/show.latte */
final class Template_ace8adfc51 extends Latte\Runtime\Template
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
		echo LR\Filters::escapeHtmlText($client->name) /* line 6 */;
		echo '</h1>
            <p class="text-muted">Detail klienta a jeho kontaktní informace</p>
        </div>
        <div class="header-actions">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$client->id])) /* line 10 */;
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
		echo LR\Filters::escapeHtmlText($client->name) /* line 31 */;
		echo '</div>
                    </div>
                    
';
		if ($client->contact_person) /* line 34 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-person"></i>
                            Kontaktní osoba
                        </div>
                        <div class="info-value">';
			echo LR\Filters::escapeHtmlText($client->contact_person) /* line 40 */;
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
		if ($client->email) /* line 50 */ {
			echo '                                <a href="mailto:';
			echo LR\Filters::escapeHtmlAttr($client->email) /* line 51 */;
			echo '" class="client-email">';
			echo LR\Filters::escapeHtmlText($client->email) /* line 51 */;
			echo '</a>
';
		} else /* line 52 */ {
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
		if ($client->phone) /* line 64 */ {
			echo '                                <a href="tel:';
			echo LR\Filters::escapeHtmlAttr($client->phone) /* line 65 */;
			echo '" class="client-phone">';
			echo LR\Filters::escapeHtmlText($client->phone) /* line 65 */;
			echo '</a>
';
		} else /* line 66 */ {
			echo '                                <span class="text-muted">—</span>
';
		}
		echo '                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Adresa -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="bi bi-geo-alt me-2"></i>
                    <h3>Adresa</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-geo-alt"></i>
                            Ulice
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText($client->address) /* line 88 */;
		echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-house"></i>
                            Město
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText($client->city) /* line 96 */;
		echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-mailbox"></i>
                            PSČ
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText($client->zip) /* line 104 */;
		echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-globe"></i>
                            Země
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText($client->country) /* line 112 */;
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
		if ($client->ic) /* line 132 */ {
			echo '                                ';
			echo LR\Filters::escapeHtmlText($client->ic) /* line 133 */;
			echo "\n";
		} else /* line 134 */ {
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
		if ($client->dic) /* line 146 */ {
			echo '                                ';
			echo LR\Filters::escapeHtmlText($client->dic) /* line 147 */;
			echo "\n";
		} else /* line 148 */ {
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
		$invoiceCount = $presenter->getClientInvoiceCount($client->id) /* line 165 */;
		echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-file-earmark-text"></i>
                            Počet faktur
                        </div>
                        <div class="info-value">
';
		if ($invoiceCount > 0) /* line 172 */ {
			echo '                                <span class="badge bg-dark">';
			echo LR\Filters::escapeHtmlText($invoiceCount) /* line 173 */;
			echo '</span>
';
		} else /* line 174 */ {
			echo '                                <span class="text-muted">Zatím žádné faktury</span>
';
		}
		echo '                        </div>
                    </div>
                    
';
		if ($invoiceCount > 0) /* line 180 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-arrow-right"></i>
                            Akce
                        </div>
                        <div class="info-value">
                            <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Invoices:default', ['client' => $client->id])) /* line 187 */;
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
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 201 */;
		echo '" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zpět na seznam klientů
            </a>
            
            <div class="d-flex gap-2">
                <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$client->id])) /* line 206 */;
		echo '" class="btn btn-primary">
                    <i class="bi bi-pencil-square"></i> Upravit
                </a>
                
';
		$invoiceCount = $presenter->getClientInvoiceCount($client->id) /* line 210 */;
		if ($invoiceCount == 0) /* line 211 */ {
			echo '                    <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$client->id])) /* line 212 */;
			echo '" class="btn btn-danger" onclick="return confirm(\'Opravdu chcete smazat tohoto klienta?\')">
                        <i class="bi bi-trash"></i> Smazat
                    </a>
';
		} else /* line 215 */ {
			echo '                    <button class="btn btn-outline-danger" disabled title="Klient má ';
			echo LR\Filters::escapeHtmlAttr(($this->global->fn->getInvoiceCountText)($this, $invoiceCount)) /* line 216 */;
			echo ' a nelze ho smazat">
                        <i class="bi bi-trash"></i> Smazat
                    </button>
';
		}
		echo '            </div>
        </div>
        
';
		if ($invoiceCount > 0) /* line 223 */ {
			echo '        <div class="mt-2">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Pro smazání klienta je nutné nejprve smazat všechny jeho faktury (';
			echo LR\Filters::escapeHtmlText(($this->global->fn->getInvoiceCountText)($this, $invoiceCount)) /* line 227 */;
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
