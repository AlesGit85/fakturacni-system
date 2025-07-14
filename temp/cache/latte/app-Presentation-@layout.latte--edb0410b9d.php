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

</head>

<body>
';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 19 */ {
			echo '	<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
		<div class="container">
			<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 22 */;
			echo '" class="navbar-brand d-flex align-items-center">
    			<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 23 */;
			echo '/images/qrdoklad_white369x80.webp" alt="QRdoklad" height="32" class="me-2">
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 31 */;
			echo '" class="nav-link">
							<i class="bi bi-house"></i><span class="nav-text">Úvod</span>
						</a>
					</li>
';
			if (isset($isUserReadonly) && $isUserReadonly) /* line 36 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Clients:default')) /* line 38 */;
				echo '" class="nav-link">
							<i class="bi bi-people"></i><span class="nav-text">Klienti</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Invoices:default')) /* line 43 */;
				echo '" class="nav-link">
							<i class="bi bi-file-earmark-text"></i><span class="nav-text">Faktury</span>
						</a>
					</li>
';
			}
			echo '					
';
			if (isset($moduleMenuItems) && !empty($moduleMenuItems)) /* line 50 */ {
				echo '					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="extensionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-puzzle-fill"></i><span class="nav-text">Rozšíření</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="extensionsDropdown">
';
				foreach ($iterator = $ʟ_it = new Latte\Essential\CachingIterator($moduleMenuItems, $ʟ_it ?? null) as $moduleKey => $moduleData) /* line 56 */ {
					if (!empty($moduleData['menuItems'])) /* line 57 */ {
						echo '									<li class="dropdown-submenu">
										<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
											<i class="';
						echo LR\Filters::escapeHtmlQuotes($moduleData['moduleInfo']['icon']) /* line 61 */;
						echo ' me-2"></i>
											';
						echo LR\Filters::escapeHtmlText($moduleData['moduleInfo']['name']) /* line 62 */;
						echo '
											<i class="bi bi-chevron-right ms-auto"></i>
										</a>
										<ul class="dropdown-menu dropdown-submenu-menu">
';
						foreach ($moduleData['menuItems'] as $menuKey => $menuItem) /* line 66 */ {
							echo '												<li>
';
							if ($menuItem['linkType'] === 'nette') /* line 68 */ {
								echo '														<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 69 */;
								echo '">
';
								if (isset($menuItem['icon'])) /* line 70 */ {
									echo '															<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 70 */;
									echo ' me-2"></i>';
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 71 */;
								echo '
														</a>
';
							} elseif ($menuItem['linkType'] === 'javascript') /* line 73 */ {
								echo '														<a class="dropdown-item" href="#" onclick="';
								echo LR\Filters::escapeHtmlQuotes($menuItem['onclick']) /* line 74 */;
								echo '; return false;">
';
								if (isset($menuItem['icon'])) /* line 75 */ {
									echo '															<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 75 */;
									echo ' me-2"></i>';
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 76 */;
								echo '
														</a>
';
							} else /* line 78 */ {
								echo '														<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 79 */;
								echo '">
';
								if (isset($menuItem['icon'])) /* line 80 */ {
									echo '															<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 80 */;
									echo ' me-2"></i>';
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 81 */;
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
						if (!$iterator->isLast()) /* line 89 */ {
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
			if (isset($isUserAdmin) && $isUserAdmin) /* line 99 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Settings:default')) /* line 101 */;
				echo '" class="nav-link">
							<i class="bi bi-gear"></i><span class="nav-text">Nastavení</span>
						</a>
					</li>
					
';
				if ($isSuperAdmin) /* line 107 */ {
					echo '						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="moduleAdminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="bi bi-gear-fill"></i><span class="nav-text">Správa modulů</span>
							</a>
							<ul class="dropdown-menu" aria-labelledby="moduleAdminDropdown">
								<li>
									<a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 115 */;
					echo '" class="dropdown-item">
										<i class="bi bi-gear me-2"></i>Správa vlastních modulů
									</a>
								</li>
								<li>
									<a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:users')) /* line 120 */;
					echo '" class="dropdown-item">
										<i class="bi bi-people me-2"></i>Správa uživatelských modulů
									</a>
								</li>
							</ul>
						</li>
';
				} else /* line 126 */ {
					echo '						<li class="nav-item">
							<a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 129 */;
					echo '" class="nav-link">
								<i class="bi bi-gear-fill"></i><span class="nav-text">Správa modulů</span>
							</a>
						</li>
';
				}
				echo '					
';
				if ($isSuperAdmin) /* line 136 */ {
					echo '					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="superAdminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: linear-gradient(135deg, rgba(177, 210, 53, 0.2) 0%, rgba(149, 177, 31, 0.2) 100%); border-radius: 0.375rem; padding: 0.5rem 0.75rem;">
							<i class="bi bi-shield-check" style="color: #B1D235;"></i><span class="nav-text" style="color: #B1D235; font-weight: 600;">Super Admin</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="superAdminDropdown">
							<li>
								<a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tenants:default')) /* line 143 */;
					echo '" class="dropdown-item">
									<i class="bi bi-building me-2" style="color: #B1D235;"></i>
									Správa tenantů
								</a>
							</li>
							<li>
								<a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Users:default')) /* line 149 */;
					echo '" class="dropdown-item">
									<i class="bi bi-people me-2" style="color: #95B11F;"></i>
									Všichni uživatelé
								</a>
							</li>
							<li><hr class="dropdown-divider"></li>
							<li>
								<a href="';
					echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tenants:add')) /* line 156 */;
					echo '" class="dropdown-item">
									<i class="bi bi-plus-circle me-2" style="color: #6c757d;"></i>
									Vytvořit tenant
								</a>
							</li>
						</ul>
					</li>
';
				}
			}
			echo '				</ul>
				
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-person-circle"></i><span class="nav-text">
							';
			if (isset($currentUser) && $currentUser) /* line 171 */ {
				echo LR\Filters::escapeHtmlText($currentUser->username) /* line 171 */;
			} else /* line 171 */ {
				echo 'Uživatel';
			}
			echo "\n";
			if (isset($currentUserRole)) /* line 172 */ {
				if ($isSuperAdmin) /* line 173 */ {
					echo '								<span class="badge ms-1" style="background-color: #B1D235; color: #212529; font-weight: 600;">Super Admin</span>
';
				} elseif ($currentUserRole === 'admin') /* line 175 */ {
					echo '								<span class="badge bg-danger ms-1">Admin</span>
';
				} elseif ($currentUserRole === 'accountant') /* line 177 */ {
					echo '								<span class="badge bg-warning ms-1">Účetní</span>
';
				} else /* line 179 */ {
					echo '								<span class="badge bg-secondary ms-1">Pouze čtení</span>
';
				}


			}
			echo '					</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:profile')) /* line 186 */;
			echo '" class="dropdown-item">
								<i class="bi bi-person"></i> Můj profil
							</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Sign:out')) /* line 190 */;
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
		foreach ($flashes as $flash) /* line 202 */ {
			echo '		<div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 202 */;
			echo '">
';
			if ($flash->type === 'success') /* line 203 */ {
				echo '			<i class="bi bi-check-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'info') /* line 204 */ {
				echo '			<i class="bi bi-info-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'warning') /* line 205 */ {
				echo '			<i class="bi bi-exclamation-triangle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'danger') /* line 206 */ {
				echo '			<i class="bi bi-x-circle-fill me-2"></i>';
			}
			echo '
			';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 207 */;
			echo '
		</div>
';

		}

		echo "\n";
		$this->renderBlock('content', [], 'html') /* line 210 */;
		echo '	</div>

';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 213 */ {
			echo '	<footer class="mt-5 py-4 text-center">
		<div class="container">
			<p class="mb-0">
				QRdoklad (verze 1.9.4) &copy; ';
			echo LR\Filters::escapeHtmlText(date('Y')) /* line 217 */;
			echo ' | Moderní fakturační systém - Proudly crafted by <a href="https://allimedia.cz">Allimedia.cz</a>
			</p>
		</div>
	</footer>
';
		}
		echo '
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 224 */;
		echo '/js/main.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 225 */;
		echo '/js/invoice-form.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 226 */;
		echo '/js/settings.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 227 */;
		echo '/js/tables.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 228 */;
		echo '/js/search.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 229 */;
		echo '/js/ares-lookup.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 230 */;
		echo '/js/modules.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 231 */;
		echo '/js/invoices.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 232 */;
		echo '/js/tenants.js"></script>
	
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
			foreach (array_intersect_key(['moduleKey' => '56', 'moduleData' => '56', 'menuKey' => '66', 'menuItem' => '66', 'flash' => '202'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}
}
