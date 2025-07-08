<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/default.latte */
final class Template_7c11b41112 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Users/default.latte';

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
			foreach (array_intersect_key(['userItem' => '31'], $this->params) as $ʟ_v => $ʟ_l) {
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

		echo '<div class="users-container">
    <!-- Záhlaví s názvem sekce a počtem uživatelů -->
    <div class="section-header-row mb-4">
        <div>
            <h1 class="section-title mb-0">Uživatelé <span class="total-count">Počet uživatelů v systému: ';
		echo LR\Filters::escapeHtmlText($totalUsers) /* line 6 */;
		echo '</span></h1>
            <p class="text-muted">Správa uživatelských účtů v systému</p>
        </div>
        <div class="header-actions">
            <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 10 */;
		echo '" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Přidat uživatele
            </a>
        </div>
    </div>

    <!-- Tabulka uživatelů -->
';
		if ($totalUsers > 0) /* line 17 */ {
			echo '    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Uživatel</th>
                    <th>E-mail</th>
                    <th>Role</th>
                    <th>Vytvořen</th>
                    <th>Poslední přihlášení</th>
                    <th class="text-end">Akce</th>
                </tr>
            </thead>
            <tbody>
';
			foreach ($users as $userItem) /* line 31 */ {
				echo '                <tr class="data-row">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                                <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <strong>';
				echo LR\Filters::escapeHtmlText($userItem->username) /* line 39 */;
				echo '</strong>
';
				if ($userItem->id === $currentUser->id) /* line 40 */ {
					echo '                                    <span class="badge bg-info ms-2">To jste vy</span>
';
				}
				echo '                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="mailto:';
				echo LR\Filters::escapeHtmlAttr($userItem->email) /* line 47 */;
				echo '" class="client-email">';
				echo LR\Filters::escapeHtmlText($userItem->email) /* line 47 */;
				echo '</a>
                    </td>
                    <td>
';
				if ($userItem->is_super_admin) /* line 50 */ {
					echo '                            <span class="badge" style="background-color: #B1D235; color: #212529; font-weight: 600;">Super Administrátor</span>
';
				} elseif ($userItem->role === 'admin') /* line 52 */ {
					echo '                            <span class="badge bg-danger">Administrátor</span>
';
				} elseif ($userItem->role === 'accountant') /* line 54 */ {
					echo '                            <span class="badge bg-warning text-dark">Účetní</span>
';
				} else /* line 56 */ {
					echo '                            <span class="badge bg-secondary">Pouze čtení</span>
';
				}


				echo '                    </td>
                    <td>
';
				if ($userItem->created_at) /* line 61 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($userItem->created_at, 'd.m.Y')) /* line 62 */;
					echo "\n";
				} else /* line 63 */ {
					echo '                            <span class="text-muted">—</span>
';
				}
				echo '                    </td>
                    <td>
';
				if ($userItem->last_login) /* line 68 */ {
					echo '                            ';
					echo LR\Filters::escapeHtmlText(($this->filters->date)($userItem->last_login, 'd.m.Y H:i')) /* line 69 */;
					echo "\n";
				} else /* line 70 */ {
					echo '                            <span class="text-muted">Nikdy</span>
';
				}
				echo '                    </td>
                    <td class="actions-column">
                        <div class="action-buttons">
                            <a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('edit', [$userItem->id])) /* line 76 */;
				echo '" class="btn btn-icon" title="Upravit uživatele">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
';
				if ($userItem->id !== $currentUser->id) /* line 80 */ {
					$isLastAdmin = $userItem->role === 'admin' && $adminCount <= 1 /* line 81 */;
					if (!$isLastAdmin) /* line 82 */ {
						echo '                                    <a href="';
						echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('delete', [$userItem->id])) /* line 83 */;
						echo '" class="btn btn-icon text-danger" 
                                       onclick="return confirm(\'Opravdu chcete smazat uživatele ';
						echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($userItem->username)) /* line 84 */;
						echo '?\')" 
                                       title="Smazat uživatele">
                                        <i class="bi bi-trash"></i>
                                    </a>
';
					} else /* line 88 */ {
						echo '                                    <span class="btn btn-icon text-muted" title="Nelze smazat posledního administrátora">
                                        <i class="bi bi-shield-check"></i>
                                    </span>
';
					}
				} else /* line 93 */ {
					echo '                                <span class="btn btn-icon text-muted" title="Nemůžete smazat sám sebe">
                                    <i class="bi bi-person-x"></i>
                                </span>
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
';
		} else /* line 105 */ {
			echo '    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-people"></i>
        </div>
        <h3>Zatím zde nejsou žádní uživatelé</h3>
        <p>Začněte přidáním nového uživatele do systému</p>
        <a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('add')) /* line 112 */;
			echo '" class="btn btn-primary mt-3">
            <i class="bi bi-person-plus"></i> Přidat prvního uživatele
        </a>
    </div>
';
		}
		echo '</div>
';
	}
}
