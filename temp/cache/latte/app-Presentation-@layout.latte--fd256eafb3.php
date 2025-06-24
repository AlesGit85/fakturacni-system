<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation/@layout.latte */
final class Template_fd256eafb3 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation/@layout.latte';


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		echo '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>';
		if ($this->hasBlock('title')) /* line 6 */ {
			$this->renderBlock('title', [], function ($s, $type) {
				$ʟ_fi = new LR\FilterInfo($type);
				return LR\Filters::convertTo($ʟ_fi, 'html', $this->filters->filterContent('stripHtml', $ʟ_fi, $s));
			}) /* line 6 */;
			echo ' | ';
		}
		echo 'QRdoklad</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 10 */;
		echo '/css/style.css">
</head>

<body>
';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 14 */ {
			echo '	<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
		<div class="container">
			<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 17 */;
			echo '" class="navbar-brand d-flex align-items-center">
    			<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 18 */;
			echo '/images/qrdoklad_white369x80.webp" alt="QRdoklad" height="32" class="me-2">
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 26 */;
			echo '" class="nav-link">
							<i class="bi bi-house"></i><span class="nav-text">Úvod</span>
						</a>
					</li>
';
			if (isset($isUserReadonly) && $isUserReadonly) /* line 31 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Clients:default')) /* line 33 */;
				echo '" class="nav-link">
							<i class="bi bi-people"></i><span class="nav-text">Klienti</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Invoices:default')) /* line 38 */;
				echo '" class="nav-link">
							<i class="bi bi-file-earmark-text"></i><span class="nav-text">Faktury</span>
						</a>
					</li>
';
			}
			echo '					
';
			if (isset($moduleMenuItems) && !empty($moduleMenuItems)) /* line 45 */ {
				echo '					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="extensionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-puzzle-fill"></i><span class="nav-text">Rozšíření</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="extensionsDropdown">
';
				foreach ($iterator = $ʟ_it = new Latte\Essential\CachingIterator($moduleMenuItems, $ʟ_it ?? null) as $moduleKey => $moduleData) /* line 51 */ {
					if (!empty($moduleData['menuItems'])) /* line 52 */ {
						echo '									<li><h6 class="dropdown-header">
										<i class="';
						echo LR\Filters::escapeHtmlQuotes($moduleData['moduleInfo']['icon']) /* line 54 */;
						echo ' me-2"></i>
										';
						echo LR\Filters::escapeHtmlText($moduleData['moduleInfo']['name']) /* line 55 */;
						echo '
									</h6></li>
';
						foreach ($moduleData['menuItems'] as $menuKey => $menuItem) /* line 57 */ {
							echo '										<li>
';
							if ($menuItem['linkType'] === 'nette') /* line 59 */ {
								echo '												<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 60 */;
								echo '">
';
								if (isset($menuItem['icon'])) /* line 61 */ {
									echo '													<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 61 */;
									echo ' me-2"></i>';
								}
								echo '
													';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 62 */;
								echo '
												</a>
';
							} elseif ($menuItem['linkType'] === 'javascript') /* line 64 */ {
								echo '												<a class="dropdown-item" href="#" onclick="';
								echo LR\Filters::escapeHtmlQuotes($menuItem['onclick']) /* line 65 */;
								echo '; return false;">
';
								if (isset($menuItem['icon'])) /* line 66 */ {
									echo '													<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 66 */;
									echo ' me-2"></i>';
								}
								echo '
													';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 67 */;
								echo '
												</a>
';
							} else /* line 69 */ {
								echo '												<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 70 */;
								echo '">
';
								if (isset($menuItem['icon'])) /* line 71 */ {
									echo '													<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 71 */;
									echo ' me-2"></i>';
								}
								echo '
													';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 72 */;
								echo '
												</a>
';
							}

							echo '										</li>
';

						}

						if (!$iterator->isLast()) /* line 77 */ {
							echo '										<li><hr class="dropdown-divider"></li>
';
						}
					}

				}
				$iterator = $ʟ_it = $ʟ_it->getParent();

				echo '						</ul>
					</li>
';
			}
			echo '					
';
			if (isset($isUserAdmin) && $isUserAdmin) /* line 87 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Settings:default')) /* line 89 */;
				echo '" class="nav-link">
							<i class="bi bi-gear"></i><span class="nav-text">Nastavení</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:default')) /* line 94 */;
				echo '" class="nav-link">
							<i class="bi bi-people-fill"></i><span class="nav-text">Uživatelé</span>
						</a>
					</li>
					<li class="nav-item">
    					<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 99 */;
				echo '" class="nav-link">
        					<i class="bi bi-gear-fill"></i><span class="nav-text">Správa modulů</span>
    					</a>
					</li>
';
			}
			echo '				</ul>
				
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-person-circle"></i><span class="nav-text">
							';
			if (isset($currentUser) && $currentUser) /* line 110 */ {
				echo LR\Filters::escapeHtmlText($currentUser->username) /* line 110 */;
			} else /* line 110 */ {
				echo 'Uživatel';
			}
			echo "\n";
			if (isset($currentUserRole)) /* line 111 */ {
				if ($currentUserRole === 'admin') /* line 112 */ {
					echo '									<span class="badge bg-danger ms-1">Admin</span>
';
				} elseif ($currentUserRole === 'accountant') /* line 114 */ {
					echo '									<span class="badge bg-warning ms-1">Účetní</span>
';
				} else /* line 116 */ {
					echo '									<span class="badge bg-secondary ms-1">Pouze čtení</span>
';
				}

			}
			echo '							</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:profile')) /* line 123 */;
			echo '" class="dropdown-item">
								<i class="bi bi-person"></i> Můj profil
							</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Sign:out')) /* line 127 */;
			echo '" class="dropdown-item">
								<i class="bi bi-box-arrow-right"></i> Odhlásit se
							</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</nav>
';
		}
		echo '
	<div class="container">
';
		foreach ($flashes as $flash) /* line 139 */ {
			echo '		<div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 139 */;
			echo '">
';
			if ($flash->type === 'success') /* line 140 */ {
				echo '			<i class="bi bi-check-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'info') /* line 141 */ {
				echo '			<i class="bi bi-info-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'warning') /* line 142 */ {
				echo '			<i class="bi bi-exclamation-triangle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'danger') /* line 143 */ {
				echo '			<i class="bi bi-x-circle-fill me-2"></i>';
			}
			echo '
			';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 144 */;
			echo '
		</div>
';

		}

		echo "\n";
		$this->renderBlock('content', [], 'html') /* line 147 */;
		echo '	</div>

';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 150 */ {
			echo '	<footer class="mt-5 py-4 text-center">
		<div class="container">
			<p class="mb-0">
				QRdoklad (verze 1.9.4) &copy; ';
			echo LR\Filters::escapeHtmlText(date('Y')) /* line 154 */;
			echo ' | Moderní fakturační systém - Proudly crafted by <a href="https://allimedia.cz">Allimedia.cz</a>
			</p>
		</div>
	</footer>
';
		}
		echo '
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 161 */;
		echo '/js/main.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 162 */;
		echo '/js/invoice-form.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 163 */;
		echo '/js/settings.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 164 */;
		echo '/js/tables.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 165 */;
		echo '/js/search.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 166 */;
		echo '/js/ares-lookup.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 167 */;
		echo '/js/modules.js"></script>
</body>
</html>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['moduleKey' => '51', 'moduleData' => '51', 'menuKey' => '57', 'menuItem' => '57', 'flash' => '139'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}
}
