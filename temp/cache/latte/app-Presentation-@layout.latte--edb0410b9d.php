<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation/@layout.latte */
final class Template_edb0410b9d extends Latte\Runtime\Template
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
	
';
		if ($this->hasBlock('head')) /* line 13 */ {
			$this->renderBlock('head', [], 'html') /* line 13 */;
		}
		echo '	
	<style>
		/* Hierarchické dropdown menu CSS */
		.dropdown-submenu {
			position: relative;
		}

		.dropdown-submenu .dropdown-menu {
			top: 0;
			left: 100%;
			margin-top: -1px;
			border-radius: 0.375rem;
			box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
			border: 1px solid rgba(0, 0, 0, 0.1);
			min-width: 200px;
		}

		/* Skryjeme submenu by default */
		.dropdown-submenu .dropdown-submenu-menu {
			display: none;
		}

		/* Zobrazíme submenu při hover */
		.dropdown-submenu:hover .dropdown-submenu-menu {
			display: block;
		}

		/* Styling pro dropdown toggle v submenu */
		.dropdown-submenu .dropdown-toggle::after {
			display: none; /* Skryjeme default šipku */
		}

		.dropdown-submenu .dropdown-toggle {
			display: flex;
			justify-content: space-between;
			align-items: center;
			white-space: nowrap;
		}

		/* Šipka vpravo pro indikaci submenu */
		.dropdown-submenu .bi-chevron-right {
			font-size: 0.75rem;
			opacity: 0.6;
			margin-left: auto;
			margin-right: 0;
		}

		/* Hover efekty */
		.dropdown-submenu:hover > .dropdown-toggle {
			background-color: var(--bs-dropdown-link-hover-bg);
			color: var(--bs-dropdown-link-hover-color);
		}

		/* Responsive - na mobilních zařízeních ukážeme submenu při kliknutí */
		@media (max-width: 767.98px) {
			.dropdown-submenu .dropdown-submenu-menu {
				position: static;
				float: none;
				width: auto;
				margin-top: 0;
				background-color: transparent;
				border: 0;
				box-shadow: none;
				padding-left: 1rem;
			}
			
			.dropdown-submenu:hover .dropdown-submenu-menu {
				display: none;
			}
			
			.dropdown-submenu.open .dropdown-submenu-menu {
				display: block;
			}
		}

		/* Barevné schema podle projektu QRdoklad */
		.dropdown-submenu .dropdown-submenu-menu .dropdown-item:hover {
			background-color: rgba(177, 210, 53, 0.1);
			color: #212529;
		}

		.dropdown-submenu .dropdown-submenu-menu .dropdown-item:focus {
			background-color: rgba(177, 210, 53, 0.2);
			color: #212529;
		}
	</style>
</head>

<body>
';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 104 */ {
			echo '	<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
		<div class="container">
			<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 107 */;
			echo '" class="navbar-brand d-flex align-items-center">
    			<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 108 */;
			echo '/images/qrdoklad_white369x80.webp" alt="QRdoklad" height="32" class="me-2">
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 116 */;
			echo '" class="nav-link">
							<i class="bi bi-house"></i><span class="nav-text">Úvod</span>
						</a>
					</li>
';
			if (isset($isUserReadonly) && $isUserReadonly) /* line 121 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Clients:default')) /* line 123 */;
				echo '" class="nav-link">
							<i class="bi bi-people"></i><span class="nav-text">Klienti</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Invoices:default')) /* line 128 */;
				echo '" class="nav-link">
							<i class="bi bi-file-earmark-text"></i><span class="nav-text">Faktury</span>
						</a>
					</li>
';
			}
			echo '					
';
			if (isset($moduleMenuItems) && !empty($moduleMenuItems)) /* line 135 */ {
				echo '					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="extensionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-puzzle-fill"></i><span class="nav-text">Rozšíření</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="extensionsDropdown">
';
				foreach ($iterator = $ʟ_it = new Latte\Essential\CachingIterator($moduleMenuItems, $ʟ_it ?? null) as $moduleKey => $moduleData) /* line 141 */ {
					if (!empty($moduleData['menuItems'])) /* line 142 */ {
						echo '									<li class="dropdown-submenu">
										<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
											<i class="';
						echo LR\Filters::escapeHtmlQuotes($moduleData['moduleInfo']['icon']) /* line 146 */;
						echo ' me-2"></i>
											';
						echo LR\Filters::escapeHtmlText($moduleData['moduleInfo']['name']) /* line 147 */;
						echo '
											<i class="bi bi-chevron-right ms-auto"></i>
										</a>
										<ul class="dropdown-menu dropdown-submenu-menu">
';
						foreach ($moduleData['menuItems'] as $menuKey => $menuItem) /* line 151 */ {
							echo '												<li>
';
							if ($menuItem['linkType'] === 'nette') /* line 153 */ {
								echo '														<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 154 */;
								echo '">
';
								if (isset($menuItem['icon'])) /* line 155 */ {
									echo '															<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 155 */;
									echo ' me-2"></i>';
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 156 */;
								echo '
														</a>
';
							} elseif ($menuItem['linkType'] === 'javascript') /* line 158 */ {
								echo '														<a class="dropdown-item" href="#" onclick="';
								echo LR\Filters::escapeHtmlQuotes($menuItem['onclick']) /* line 159 */;
								echo '; return false;">
';
								if (isset($menuItem['icon'])) /* line 160 */ {
									echo '															<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 160 */;
									echo ' me-2"></i>';
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 161 */;
								echo '
														</a>
';
							} else /* line 163 */ {
								echo '														<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 164 */;
								echo '">
';
								if (isset($menuItem['icon'])) /* line 165 */ {
									echo '															<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 165 */;
									echo ' me-2"></i>';
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 166 */;
								echo '
														</a>
';
							}

							echo '												</li>
';

						}

						echo '										</ul>
									</li>
									
';
						if (!$iterator->isLast()) /* line 174 */ {
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
			if (isset($isUserAdmin) && $isUserAdmin) /* line 184 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Settings:default')) /* line 186 */;
				echo '" class="nav-link">
							<i class="bi bi-gear"></i><span class="nav-text">Nastavení</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:default')) /* line 191 */;
				echo '" class="nav-link">
							<i class="bi bi-people-fill"></i><span class="nav-text">Uživatelé</span>
						</a>
					</li>
					<li class="nav-item">
    					<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 196 */;
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
			if (isset($currentUser) && $currentUser) /* line 207 */ {
				echo LR\Filters::escapeHtmlText($currentUser->username) /* line 207 */;
			} else /* line 207 */ {
				echo 'Uživatel';
			}
			echo "\n";
			if (isset($currentUserRole)) /* line 208 */ {
				if ($isSuperAdmin) /* line 209 */ {
					echo '								<span class="badge ms-1" style="background-color: #B1D235; color: #212529; font-weight: 600;">Super Admin</span>
';
				} elseif ($currentUserRole === 'admin') /* line 211 */ {
					echo '								<span class="badge bg-danger ms-1">Admin</span>
';
				} elseif ($currentUserRole === 'accountant') /* line 213 */ {
					echo '								<span class="badge bg-warning ms-1">Účetní</span>
';
				} else /* line 215 */ {
					echo '								<span class="badge bg-secondary ms-1">Pouze čtení</span>
';
				}


			}
			echo '					</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:profile')) /* line 222 */;
			echo '" class="dropdown-item">
								<i class="bi bi-person"></i> Můj profil
							</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Sign:out')) /* line 226 */;
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
		foreach ($flashes as $flash) /* line 238 */ {
			echo '		<div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 238 */;
			echo '">
';
			if ($flash->type === 'success') /* line 239 */ {
				echo '			<i class="bi bi-check-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'info') /* line 240 */ {
				echo '			<i class="bi bi-info-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'warning') /* line 241 */ {
				echo '			<i class="bi bi-exclamation-triangle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'danger') /* line 242 */ {
				echo '			<i class="bi bi-x-circle-fill me-2"></i>';
			}
			echo '
			';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 243 */;
			echo '
		</div>
';

		}

		echo "\n";
		$this->renderBlock('content', [], 'html') /* line 246 */;
		echo '	</div>

';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 249 */ {
			echo '	<footer class="mt-5 py-4 text-center">
		<div class="container">
			<p class="mb-0">
				QRdoklad (verze 1.9.4) &copy; ';
			echo LR\Filters::escapeHtmlText(date('Y')) /* line 253 */;
			echo ' | Moderní fakturační systém - Proudly crafted by <a href="https://allimedia.cz">Allimedia.cz</a>
			</p>
		</div>
	</footer>
';
		}
		echo '
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 260 */;
		echo '/js/main.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 261 */;
		echo '/js/invoice-form.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 262 */;
		echo '/js/settings.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 263 */;
		echo '/js/tables.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 264 */;
		echo '/js/search.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 265 */;
		echo '/js/ares-lookup.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 266 */;
		echo '/js/modules.js"></script>
	
	<script>
		// Hierarchické dropdown menu
		document.addEventListener(\'DOMContentLoaded\', function() {
			// Podpora pro hierarchické dropdown menu na mobilních zařízeních
			const dropdownSubmenus = document.querySelectorAll(\'.dropdown-submenu\');
			
			dropdownSubmenus.forEach(function(submenu) {
				const toggle = submenu.querySelector(\'.dropdown-toggle\');
				
				// Na mobilních zařízeních reagujeme na kliknutí místo hover
				if (window.innerWidth <= 767) {
					toggle.addEventListener(\'click\', function(e) {
						e.preventDefault();
						e.stopPropagation();
						
						// Zavřeme všechny ostatní submenu
						dropdownSubmenus.forEach(function(otherSubmenu) {
							if (otherSubmenu !== submenu) {
								otherSubmenu.classList.remove(\'open\');
							}
						});
						
						// Přepneme toto submenu
						submenu.classList.toggle(\'open\');
					});
				}
			});
			
			// Zavřeme submenu při kliknutí mimo
			document.addEventListener(\'click\', function(e) {
				if (!e.target.closest(\'.dropdown-submenu\')) {
					dropdownSubmenus.forEach(function(submenu) {
						submenu.classList.remove(\'open\');
					});
				}
			});
		});
	</script>
</body>
</html>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['moduleKey' => '141', 'moduleData' => '141', 'menuKey' => '151', 'menuItem' => '151', 'flash' => '238'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}
}
