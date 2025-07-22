<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Clients/default.latte */
final class Template_e033c46b7e extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Clients/default.latte';

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
			foreach (array_intersect_key(['client' => '45'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="clients-container">
    <!-- Záhlaví s názvem sekce a počtem klientů -->
    <div class="section-header-row mb-4">
        <div>
            <h1 class="section-title mb-0">Klienti <span class="total-count">Počet klientů v systému: ';
		echo LR\Filters::escapeHtmlText($clients->count()) /* line 7 */;
		echo '</span></h1>
            <p class="text-muted">Seznam všech klientů v systému</p>
        </div>
        <div class="header-actions">
';
		if ($isUserAccountant) /* line 12 */ {
			echo '                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 13 */;
			echo '" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Přidat klienta
                </a>
';
		}
		echo '        </div>
    </div>

    <!-- Panel s vyhledáváním -->
    <div class="search-panel">
        <div class="search-container">
            <div class="search-input-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="clientSearch" class="search-input" placeholder="Vyhledat klienta...">
            </div>
        </div>
    </div>

    <!-- Tabulka klientů -->
';
		if ($clients->count() > 0) /* line 31 */ {
			echo '    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="sortable-column">Společnost</th>
                    <th>IČ</th>
                    <th>Kontaktní osoba</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th class="text-end">Akce</th>
                </tr>
            </thead>
            <tbody>
';
			foreach ($clients as $client) /* line 45 */ {
				echo '                <tr class="data-row">
                    <td>
                        <strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->name)) /* line 49 */;
				echo '</strong>
';
				if ($client->address) /* line 50 */ {
					echo '                            <br><small class="text-muted">';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->address)) /* line 52 */;
					echo '</small>
';
				}
				echo '                    </td>
                    <td>
';
				if ($client->ic) /* line 56 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->ic)) /* line 58 */;
					echo "\n";
				} else /* line 59 */ {
					echo '                            <span class="text-muted">—</span>
';
				}
				echo '                    </td>
                    <td>
';
				if ($client->contact_person) /* line 64 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->contact_person)) /* line 66 */;
					echo "\n";
				} else /* line 67 */ {
					echo '                            <span class="text-muted">—</span>
';
				}
				echo '                    </td>
                    <td>
';
				if ($client->email) /* line 72 */ {
					echo '                            <a href="mailto:';
					echo LR\Filters::escapeHtmlAttr(($this->filters->escape)($client->email)) /* line 74 */;
					echo '">';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->email)) /* line 74 */;
					echo '</a>
';
				} else /* line 75 */ {
					echo '                            <span class="text-muted">—</span>
';
				}
				echo '                    </td>
                    <td>
';
				if ($client->phone) /* line 80 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($client->phone)) /* line 82 */;
					echo "\n";
				} else /* line 83 */ {
					echo '                            <span class="text-muted">—</span>
';
				}
				echo '                    </td>
                    <td class="actions-column">
                        <div class="action-buttons">
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('show', [$client->id])) /* line 91 */;
				echo '" class="btn btn-icon" title="Detail klienta">
                                <i class="bi bi-eye"></i>
                            </a>
';
				if ($isUserAccountant) /* line 95 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$client->id])) /* line 96 */;
					echo '" class="btn btn-icon" title="Upravit klienta">
                                    <i class="bi bi-pencil"></i>
                                </a>
';
				}
				echo "\n";
				if ($isUserAdmin) /* line 102 */ {
					$invoiceCount = $presenter->getClientInvoiceCount($client->id) /* line 103 */;
					if ($invoiceCount == 0) /* line 104 */ {
						echo '                                    <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$client->id, '_csrf_token' => $csrfToken])) /* line 106 */;
						echo '" class="btn btn-icon text-danger" onclick="return confirm(\'Opravdu chcete smazat tohoto klienta?\')" title="Smazat klienta" style="color: #dc3545;">
                                        <i class="bi bi-trash"></i>
                                    </a>
';
					} else /* line 109 */ {
						echo '                                    <a class="btn btn-icon text-muted" title="Klient má ';
						echo LR\Filters::escapeHtmlAttr($presenter->getInvoiceCountText($invoiceCount)) /* line 111 */;
						echo ' a nelze ho smazat" disabled>
                                        <i class="bi bi-trash"></i>
                                    </a>
';
					}
				}
				echo '                        </div>
                    </td>
                </tr>
';

			}

			echo '            </tbody>
        </table>
    </div>

    <!-- Stránkování -->
    <div class="pagination-container mt-3">
        <div class="pagination-info">
            Strana 1 z 1
        </div>
        <div class="pagination-controls">
            <button class="btn btn-icon pagination-button" disabled>
                <i class="bi bi-chevron-left"></i>
            </button>
            <button class="btn btn-icon pagination-button" disabled>
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
';
		} else /* line 138 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-people"></i>
        </div>
        <h3>Zatím zde nejsou žádní klienti</h3>
        <p>Začněte přidáním nového klienta do systému</p>
';
			if ($isUserAccountant) /* line 146 */ {
				echo '            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 147 */;
				echo '" class="btn btn-primary mt-3">
                <i class="bi bi-person-plus"></i> Přidat prvního klienta
            </a>
';
			} else /* line 150 */ {
				echo '            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-2"></i>
                Pro přidávání klientů potřebujete oprávnění účetní nebo administrátor.
            </div>
';
			}
			echo '    </div>
';
		}
		echo '</div>
';
	}
}
