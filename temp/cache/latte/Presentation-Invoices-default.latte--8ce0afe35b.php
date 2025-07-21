<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Invoices/default.latte */
final class Template_8ce0afe35b extends Latte\Runtime\Template
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
			foreach (array_intersect_key(['invoice' => '94'], $this->params) as $ʟ_v => $ʟ_l) {
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
		if (isset($clientFilter)) /* line 9 */ {
			echo '                    <span class="client-filter-badge">klienta ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($clientFilter)) /* line 11 */;
			echo '</span>
';
		}
		echo '                <span class="total-count">Počet vystavených dokladů: ';
		echo LR\Filters::escapeHtmlText($invoices->count()) /* line 14 */;
		echo '</span>
            </h1>
            <p class="text-muted">
';
		if (isset($clientFilter)) /* line 17 */ {
			echo '                    Faktury vystavené pro klienta ';
			echo LR\Filters::escapeHtmlText(($this->filters->escape)($clientFilter)) /* line 19 */;
			echo "\n";
		} else /* line 20 */ {
			echo '                    Seznam všech faktur v systému
';
		}
		echo '            </p>
        </div>
        <div class="header-actions">
';
		if (isset($client)) /* line 27 */ {
			echo '                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 29 */;
			echo '" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Všechny faktury
                </a>
                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Clients:show', [$client])) /* line 32 */;
			echo '" class="btn btn-outline-dark">
                    <i class="bi bi-person"></i> Detail klienta
                </a>
';
		}
		if ($isUserAccountant) /* line 37 */ {
			echo '                <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 39 */;
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
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 51 */;
		echo '" class="filter-tab ';
		if (!isset($filter) || $filter == 'all') /* line 51 */ {
			echo 'filter-tab-active';
		}
		echo '">
                <i class="bi bi-grid-3x3-gap"></i> Všechny
            </a>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default', ['filter' => 'created'])) /* line 54 */;
		echo '" class="filter-tab ';
		if (isset($filter) && $filter == 'created') /* line 54 */ {
			echo 'filter-tab-active';
		}
		echo '">
                <i class="bi bi-file-earmark me-1"></i> Vystavené
            </a>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default', ['filter' => 'paid'])) /* line 57 */;
		echo '" class="filter-tab ';
		if (isset($filter) && $filter == 'paid') /* line 57 */ {
			echo 'filter-tab-active';
		}
		echo '">
                <i class="bi bi-check-circle me-1"></i> Zaplacené
            </a>
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default', ['filter' => 'overdue'])) /* line 60 */;
		echo '" class="filter-tab ';
		if (isset($filter) && $filter == 'overdue') /* line 60 */ {
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
		if ($invoices->count() > 0) /* line 78 */ {
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
			foreach ($invoices as $invoice) /* line 94 */ {
				echo '                <tr class="data-row ';
				if ($invoice->status == 'overdue') /* line 95 */ {
					echo 'row-danger';
				} elseif ($invoice->status == 'paid') /* line 95 */ {
					echo 'row-success';
				}

				echo '">
                    <td>
                        <strong>';
				echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->number)) /* line 98 */;
				echo '</strong>
                    </td>
                    <td>
';
				if ($invoice->manual_client) /* line 101 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->client_name)) /* line 103 */;
					echo "\n";
				} else /* line 104 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->escape)($invoice->ref('client_id')->name)) /* line 106 */;
					echo "\n";
				}
				echo '                    </td>
                    <td>
                        ';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->issue_date, 'd.m.Y')) /* line 111 */;
				echo '
                    </td>
                    <td>
                        ';
				echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->due_date, 'd.m.Y')) /* line 115 */;
				echo '
                    </td>
                    <td class="text-end">
                        ';
				echo LR\Filters::escapeHtmlText(($this->filters->number)($invoice->total, 2, ',', ' ')) /* line 119 */;
				echo ' Kč
                    </td>
                    <td>
';
				if ($invoice->status == 'created') /* line 122 */ {
					echo '                            <span class="status-badge status-badge-pending">Vystavena</span>
';
				} elseif ($invoice->status == 'paid') /* line 125 */ {
					echo '                            <span class="status-badge status-badge-success">
                                <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                Zaplacena
';
					if ($invoice->payment_date) /* line 130 */ {
						echo '                                    <span class="payment-date">';
						echo LR\Filters::escapeHtmlText(($this->filters->date)($invoice->payment_date, 'd.m.Y')) /* line 132 */;
						echo '</span>
';
					}
					echo '                            </span>
';
				} elseif ($invoice->status == 'overdue') /* line 135 */ {
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
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('show', [$invoice->id])) /* line 147 */;
				echo '" class="btn btn-icon" title="Detail faktury">
                                <i class="bi bi-eye"></i>
                            </a>
';
				if ($isUserAccountant) /* line 151 */ {
					echo '                                <a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$invoice->id])) /* line 153 */;
					echo '" class="btn btn-icon" title="Upravit fakturu">
                                    <i class="bi bi-pencil"></i>
                                </a>
';
				}
				echo '                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('pdf', [$invoice->id])) /* line 159 */;
				echo '" class="btn btn-icon" title="Stáhnout PDF">
                                <i class="bi bi-file-pdf"></i>
                            </a>
';
				if ($isUserAccountant) /* line 163 */ {
					echo '                                <div class="dropdown">
                                    <button class="btn btn-icon dropdown-toggle" type="button" id="dropdownMenuButton';
					echo LR\Filters::escapeHtmlAttr($invoice->id) /* line 166 */;
					echo '" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton';
					echo LR\Filters::escapeHtmlAttr($invoice->id) /* line 170 */;
					echo '">
';
					if ($invoice->status != 'paid') /* line 172 */ {
						echo '                                            <li>
                                                <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('markAsPaid!', [$invoice->id])) /* line 175 */;
						echo '" class="dropdown-item" onclick="return confirm(\'Označit fakturu jako zaplacenou?\')">
                                                    <i class="bi bi-check-circle text-success me-2"></i> Označit jako zaplacenou
                                                </a>
                                            </li>
';
					} else /* line 179 */ {
						echo '                                            <li>
                                                <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('markAsCreated!', [$invoice->id])) /* line 182 */;
						echo '" class="dropdown-item" onclick="return confirm(\'Označit fakturu jako nezaplacenou?\')">
                                                    <i class="bi bi-arrow-counterclockwise me-2"></i> Zrušit zaplaceno
                                                </a>
                                            </li>
';
					}
					if ($isUserAdmin) /* line 188 */ {
						echo '                                            <li>
                                                <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$invoice->id])) /* line 191 */;
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
		} else /* line 222 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-file-earmark-text"></i>
        </div>
        <h3>Zatím zde nejsou žádné faktury</h3>
        <p>Začněte vytvořením nové faktury</p>
';
			if ($isUserAccountant) /* line 231 */ {
				echo '            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 233 */;
				echo '" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle"></i> Vytvořit první fakturu
            </a>
';
			} else /* line 236 */ {
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
