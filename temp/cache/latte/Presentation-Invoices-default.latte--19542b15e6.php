<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Invoices/default.latte */
final class Template_19542b15e6 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Invoices/default.latte';

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
			foreach (array_intersect_key(['invoice' => '84'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="invoices-container">
    <!-- Záhlaví s názvem sekce a počtem faktur -->
    <div class="section-header-row mb-4">
        <div>
            <h1 class="section-title mb-0">
                Faktury 
';
		if (isset($clientFilter)) /* line 8 */ {
			echo '                    <span class="client-filter-badge">klienta ';
			echo LR\Filters::escapeHtmlText($clientFilter) /* line 9 */;
			echo '</span>
';
		}
		echo '                <span class="total-count">Počet vystavených dokladů: ';
		echo LR\Filters::escapeHtmlText($invoices->count()) /* line 11 */;
		echo '</span>
            </h1>
            <p class="text-muted">
';
		if (isset($clientFilter)) /* line 14 */ {
			echo '                    Faktury vystavené pro klienta ';
			echo LR\Filters::escapeHtmlText($clientFilter) /* line 15 */;
			echo "\n";
		} else /* line 16 */ {
			echo '                    Seznam všech faktur v systému
';
		}
		echo '            </p>
        </div>
        <div class="header-actions">
';
		if (isset($client)) /* line 22 */ {
			echo '                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 23 */;
			echo '" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Všechny faktury
                </a>
                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:show', [$client])) /* line 26 */;
			echo '" class="btn btn-outline-dark">
                    <i class="bi bi-person"></i> Detail klienta
                </a>
';
		}
		if ($isUserAccountant) /* line 31 */ {
			echo '                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 32 */;
			echo '" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Vytvořit fakturu
                </a>
';
		}
		echo '        </div>
    </div>

    <!-- Filtry a vyhledávání v jednom řádku -->
    <div class="filters-search-row mb-3">
        <!-- Panel s filtry vlevo -->
        <div class="filters-container">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 43 */;
		echo '" class="filter-tab ';
		if (!isset($filter) || $filter == 'all') /* line 43 */ {
			echo 'filter-tab-active';
		}
		echo '">
                <i class="bi bi-grid-3x3-gap"></i> Všechny
            </a>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default', ['filter' => 'created'])) /* line 46 */;
		echo '" class="filter-tab ';
		if (isset($filter) && $filter == 'created') /* line 46 */ {
			echo 'filter-tab-active';
		}
		echo '">
                <i class="bi bi-file-earmark me-1"></i> Vystavené
            </a>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default', ['filter' => 'paid'])) /* line 49 */;
		echo '" class="filter-tab ';
		if (isset($filter) && $filter == 'paid') /* line 49 */ {
			echo 'filter-tab-active';
		}
		echo '">
                <i class="bi bi-check-circle me-1"></i> Zaplacené
            </a>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default', ['filter' => 'overdue'])) /* line 52 */;
		echo '" class="filter-tab ';
		if (isset($filter) && $filter == 'overdue') /* line 52 */ {
			echo 'filter-tab-active';
		}
		echo '">
                <i class="bi bi-exclamation-circle me-1"></i> Po splatnosti
            </a>
        </div>
        
        <!-- Panel s vyhledáváním vpravo -->
        <div class="search-panel">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="invoiceSearch" class="search-input" placeholder="Vyhledat fakturu...">
                </div>
            </div>
        </div>
    </div>

<!-- Tabulka faktur -->
';
		if ($invoices->count() > 0) /* line 69 */ {
			echo '    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Číslo faktury</th>
                    <th>Klient</th>
                    <th>Vystaveno</th>
                    <th>Splatnost</th>
                    <th class="text-end">Částka</th>
                    <th>Stav</th>
                    <th class="text-end">Akce</th>
                </tr>
            </thead>
            <tbody>
';
			foreach ($invoices as $invoice) /* line 84 */ {
				echo '                <tr class="data-row ';
				if ($invoice->status == 'overdue') /* line 85 */ {
					echo 'row-danger';
				} elseif ($invoice->status == 'paid') /* line 85 */ {
					echo 'row-success';
				}

				echo '">
                    <td><strong>';
				echo LR\Filters::escapeHtmlText($invoice->number) /* line 86 */;
				echo '</strong></td>
                    <td>';
				if ($invoice->manual_client) /* line 87 */ {
					echo LR\Filters::escapeHtmlText($invoice->client_name) /* line 87 */;
				} else /* line 87 */ {
					echo LR\Filters::escapeHtmlText($invoice->ref('client_id')->name) /* line 87 */;
				}
				echo '</td>
                    <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->issue_date, 'd.m.Y')) /* line 88 */;
				echo '</td>
                    <td>';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->due_date, 'd.m.Y')) /* line 89 */;
				echo '</td>
                    <td class="text-end">';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 2, ',', ' ')) /* line 90 */;
				echo ' Kč</td>
                    <td>
';
				if ($invoice->status == 'created') /* line 92 */ {
					echo '                            <span class="status-badge status-badge-pending">Vystavena</span>
';
				} elseif ($invoice->status == 'paid') /* line 94 */ {
					echo '                            <span class="status-badge status-badge-success">
                                <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                Zaplacena
';
					if ($invoice->payment_date) /* line 98 */ {
						echo '                                    <span class="payment-date">';
						echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->payment_date, 'd.m.Y')) /* line 99 */;
						echo '</span>
';
					}
					echo '                            </span>
';
				} elseif ($invoice->status == 'overdue') /* line 102 */ {
					echo '                            <span class="status-badge status-badge-danger">
                                <i class="bi bi-exclamation-circle-fill me-1 text-danger"></i>
                                <span class="text-danger">Po splatnosti</span>
                            </span>
';
				}


				echo '                    </td>
                    <td class="actions-column">
                        <div class="action-buttons">
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('show', [$invoice->id])) /* line 112 */;
				echo '" class="btn btn-icon" title="Detail faktury">
                                <i class="bi bi-eye"></i>
                            </a>
';
				if ($isUserAccountant) /* line 116 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$invoice->id])) /* line 117 */;
					echo '" class="btn btn-icon" title="Upravit fakturu">
                                    <i class="bi bi-pencil"></i>
                                </a>
';
				}
				echo '                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('pdf', [$invoice->id])) /* line 122 */;
				echo '" class="btn btn-icon" title="Stáhnout PDF">
                                <i class="bi bi-file-pdf"></i>
                            </a>
';
				if ($isUserAccountant) /* line 126 */ {
					echo '                                <div class="dropdown">
                                    <button class="btn btn-icon dropdown-toggle" type="button" id="dropdownMenuButton';
					echo LR\Filters::escapeHtmlAttr($invoice->id) /* line 128 */;
					echo '" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton';
					echo LR\Filters::escapeHtmlAttr($invoice->id) /* line 131 */;
					echo '">
';
					if ($invoice->status != 'paid') /* line 133 */ {
						echo '                                            <li>
                                                <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('markAsPaid!', [$invoice->id])) /* line 135 */;
						echo '" class="dropdown-item" onclick="return confirm(\'Označit fakturu jako zaplacenou?\')">
                                                    <i class="bi bi-check-circle text-success me-2"></i> Označit jako zaplacenou
                                                </a>
                                            </li>
';
					} else /* line 139 */ {
						echo '                                            <li>
                                                <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('markAsCreated!', [$invoice->id])) /* line 141 */;
						echo '" class="dropdown-item" onclick="return confirm(\'Označit fakturu jako nezaplacenou?\')">
                                                    <i class="bi bi-arrow-counterclockwise me-2"></i> Zrušit zaplaceno
                                                </a>
                                            </li>
';
					}
					if ($isUserAdmin) /* line 147 */ {
						echo '                                            <li>
                                                <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$invoice->id])) /* line 149 */;
						echo '" class="dropdown-item text-danger" onclick="return confirm(\'Opravdu chcete smazat tuto fakturu?\')">
                                                    <i class="bi bi-trash me-2"></i> Smazat fakturu
                                                </a>
                                            </li>
';
					}
					echo '                                    </ul>
                                </div>
';
				}
				echo '                        </div>
                    </td>
                </tr>
';

			}

			echo '            </tbody>
        </table>
    </div>

<script>
// Jednoduchý JavaScript pro dropdown v tabulkách
document.addEventListener(\'DOMContentLoaded\', function() {
    const dropdownToggles = document.querySelectorAll(\'.data-table .dropdown-toggle\');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener(\'click\', function(e) {
            const row = this.closest(\'tr\');
            const tableBody = this.closest(\'tbody\');
            const allRows = Array.from(tableBody.querySelectorAll(\'tr\'));
            const rowIndex = allRows.indexOf(row);
            const totalRows = allRows.length;
            
            // Pokud je to jeden z posledních dvou řádků, přidáme CSS třídu
            if (rowIndex >= totalRows - 2) {
                row.classList.add(\'show-dropdown-up\');
            } else {
                row.classList.remove(\'show-dropdown-up\');
            }
        });
        
        // Při zavření dropdown odebereme třídu
        toggle.addEventListener(\'hidden.bs.dropdown\', function() {
            const row = this.closest(\'tr\');
            row.classList.remove(\'show-dropdown-up\');
        });
    });
    
    // Odebereme třídu při kliknutí mimo
    document.addEventListener(\'click\', function(e) {
        if (!e.target.closest(\'.dropdown\')) {
            document.querySelectorAll(\'tr.show-dropdown-up\').forEach(row => {
                row.classList.remove(\'show-dropdown-up\');
            });
        }
    });
});
</script>

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
		} else /* line 218 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-file-earmark-text"></i>
        </div>
        <h3>Zatím zde nejsou žádné faktury</h3>
        <p>Začněte vytvořením nové faktury</p>
';
			if ($isUserAccountant) /* line 226 */ {
				echo '            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 227 */;
				echo '" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle"></i> Vytvořit první fakturu
            </a>
';
			} else /* line 230 */ {
				echo '            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-2"></i>
                Pro vytváření faktur potřebujete oprávnění účetní nebo administrátor.
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
