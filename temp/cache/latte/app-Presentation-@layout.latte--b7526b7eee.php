<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation/@layout.latte */
final class Template_b7526b7eee extends Latte\Runtime\Template
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
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 11 */;
		echo '/css/base.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 11 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 12 */;
		echo '/css/components.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 12 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 13 */;
		echo '/css/layout.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 13 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 14 */;
		echo '/css/tables.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 14 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 15 */;
		echo '/css/pages.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 15 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 16 */;
		echo '/css/modules.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 16 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 17 */;
		echo '/css/responsive.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 17 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 18 */;
		echo '/css/print.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 18 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 19 */;
		echo '/css/auth.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 19 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 20 */;
		echo '/css/settings.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 20 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 21 */;
		echo '/css/hierarchical-menu.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 21 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 22 */;
		echo '/css/tenants.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 22 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 23 */;
		echo '/css/security.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 23 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 24 */;
		echo '/css/session-timeouts.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 24 */;
		echo '">
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 25 */;
		echo '/css/anti-spam.css?v=';
		echo LR\Filters::escapeHtmlAttr(time()) /* line 25 */;
		echo '">

	
';
		if ($this->hasBlock('head')) /* line 29 */ {
			$this->renderBlock('head', [], 'html') /* line 29 */;
		}
		echo '	

</head>

<body>
';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 35 */ {
			echo '	<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
		<div class="container">
			<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 38 */;
			echo '" class="navbar-brand d-flex align-items-center">
    			<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 39 */;
			echo '/images/qrdoklad_white369x80.webp" alt="QRdoklad" height="32" class="me-2">
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 47 */;
			echo '" class="nav-link">
							<i class="bi bi-house"></i><span class="nav-text">Úvod</span>
						</a>
					</li>
';
			if (isset($isUserReadonly) && $isUserReadonly) /* line 52 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Clients:default')) /* line 54 */;
				echo '" class="nav-link">
							<i class="bi bi-people"></i><span class="nav-text">Klienti</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Invoices:default')) /* line 59 */;
				echo '" class="nav-link">
							<i class="bi bi-file-earmark-text"></i><span class="nav-text">Faktury</span>
						</a>
					</li>
';
			}
			echo '					
';
			if (isset($moduleMenuItems) && !empty($moduleMenuItems)) /* line 66 */ {
				echo '					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="extensionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-puzzle-fill"></i><span class="nav-text">Rozšíření</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="extensionsDropdown">
';
				foreach ($iterator = $ʟ_it = new Latte\Essential\CachingIterator($moduleMenuItems, $ʟ_it ?? null) as $moduleKey => $moduleData) /* line 72 */ {
					if (!empty($moduleData['menuItems'])) /* line 73 */ {
						echo '									<li class="dropdown-submenu">
										<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
											';
						echo LR\Filters::escapeHtmlText(($this->global->fn->safeIcon)($this, $moduleData['moduleInfo']['icon'])) /* line 77 */;
						echo '
											';
						echo LR\Filters::escapeHtmlText($moduleData['moduleInfo']['name']) /* line 78 */;
						echo '
											<i class="bi bi-chevron-right ms-auto"></i>
										</a>
										<ul class="dropdown-menu dropdown-submenu-menu">
';
						foreach ($moduleData['menuItems'] as $menuKey => $menuItem) /* line 82 */ {
							echo '												<li>
';
							if ($menuItem['linkType'] === 'nette') /* line 84 */ {
								echo '														<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 85 */;
								echo '">
															';
								if (isset($menuItem['icon'])) /* line 86 */ {
									echo LR\Filters::escapeHtmlText(($this->global->fn->safeIcon)($this, $menuItem['icon'])) /* line 86 */;
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 87 */;
								echo '
														</a>
';
							} elseif ($menuItem['linkType'] === 'javascript') /* line 89 */ {
								echo '														<a class="dropdown-item" href="#" ';
								$safeOnclick = ($this->global->fn->safeOnclick)($this, $menuItem['onclick']) /* line 90 */;
								echo "\n";
								if ($safeOnclick !== '') /* line 91 */ {
									echo 'onclick="';
									echo LR\Filters::escapeHtmlAttr(LR\Filters::escapeJs($safeOnclick)) /* line 91 */;
									echo '; return false;"';
								} else /* line 91 */ {
									echo 'href="#" class="disabled"';
								}
								echo '>
															';
								if (isset($menuItem['icon'])) /* line 92 */ {
									echo LR\Filters::escapeHtmlText(($this->global->fn->safeIcon)($this, $menuItem['icon'])) /* line 92 */;
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 93 */;
								echo '
														</a>
';
							} else /* line 95 */ {
								echo '														<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 96 */;
								echo '">
															';
								if (isset($menuItem['icon'])) /* line 97 */ {
									echo LR\Filters::escapeHtmlText(($this->global->fn->safeIcon)($this, $menuItem['icon'])) /* line 97 */;
								}
								echo '
															';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 98 */;
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
						if (!$iterator->isLast()) /* line 106 */ {
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
			if (isset($isUserAdmin) && $isUserAdmin && !$isSuperAdmin) /* line 116 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Settings:default')) /* line 118 */;
				echo '" class="nav-link">
							<i class="bi bi-gear"></i><span class="nav-text">Nastavení</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:default')) /* line 123 */;
				echo '" class="nav-link">
							<i class="bi bi-people-fill"></i><span class="nav-text">Uživatelé</span>
						</a>
					</li>
					
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="toolsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-tools"></i><span class="nav-text">Nástroje</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="toolsDropdown">
							<li class="dropdown-submenu">
								<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
									<i class="bi bi-shield-lock me-2"></i>Bezpečnost
									<i class="bi bi-chevron-right ms-auto"></i>
								</a>
								<ul class="dropdown-menu dropdown-submenu-menu">
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:default')) /* line 141 */;
				echo '" class="dropdown-item">
											<i class="bi bi-house-door me-2"></i>Přehled nástrojů
										</a>
									</li>
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 146 */;
				echo '" class="dropdown-item">
											<i class="bi bi-speedometer2 me-2"></i>Security Dashboard
										</a>
									</li>
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:sqlAudit')) /* line 151 */;
				echo '" class="dropdown-item">
											<i class="bi bi-search me-2"></i>SQL Security Audit
										</a>
									</li>
								</ul>
							</li>
							<li class="dropdown-submenu">
								<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
									<i class="bi bi-arrow-repeat me-2"></i>Migrace
									<i class="bi bi-chevron-right ms-auto"></i>
								</a>
								<ul class="dropdown-menu dropdown-submenu-menu">
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Migration:default')) /* line 164 */;
				echo '" class="dropdown-item">
											<i class="bi bi-house-door me-2"></i>Přehled nástrojů
										</a>
									</li>
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Migration:encryptOldData')) /* line 169 */;
				echo '" class="dropdown-item">
											<i class="bi bi-lock-fill me-2"></i>Šifrování starých dat
										</a>
									</li>
								</ul>
							</li>
							<li><hr class="dropdown-divider"></li>
							<li>
								<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 177 */;
				echo '" class="dropdown-item">
									<i class="bi bi-gear-fill me-2"></i>Správa modulů
								</a>
							</li>
						</ul>
					</li>
';
			}
			echo '					
';
			if ($isSuperAdmin) /* line 186 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Settings:default')) /* line 188 */;
				echo '" class="nav-link">
							<i class="bi bi-gear"></i><span class="nav-text">Nastavení</span>
						</a>
					</li>
					
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="moduleAdminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-gear-fill"></i><span class="nav-text">Správa modulů</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="moduleAdminDropdown">
							<li>
								<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 200 */;
				echo '" class="dropdown-item">
									<i class="bi bi-gear me-2"></i>Správa vlastních modulů
								</a>
							</li>
							<li>
								<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:users')) /* line 205 */;
				echo '" class="dropdown-item">
									<i class="bi bi-people me-2"></i>Správa uživatelských modulů
								</a>
							</li>
						</ul>
					</li>
					
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="superAdminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: linear-gradient(135deg, rgba(177, 210, 53, 0.2) 0%, rgba(149, 177, 31, 0.2) 100%); border-radius: 0.375rem; padding: 0.5rem 0.75rem;">
							<i class="bi bi-shield-check" style="color: #B1D235;"></i><span class="nav-text" style="color: #B1D235; font-weight: 600;">Super Admin</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="superAdminDropdown">
							<li class="dropdown-submenu">
								<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
									<i class="bi bi-shield-lock me-2" style="color: #B1D235;"></i>Bezpečnost
									<i class="bi bi-chevron-right ms-auto"></i>
								</a>
								<ul class="dropdown-menu dropdown-submenu-menu">
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:dashboard')) /* line 226 */;
				echo '" class="dropdown-item">
											<i class="bi bi-speedometer2 me-2"></i>Security Dashboard
										</a>
									</li>
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:rateLimitStats')) /* line 231 */;
				echo '" class="dropdown-item">
											<i class="bi bi-bar-chart me-2"></i>Rate Limiting Statistiky
										</a>
									</li>
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:sqlAudit')) /* line 236 */;
				echo '" class="dropdown-item">
											<i class="bi bi-search me-2"></i>SQL Security Audit
										</a>
									</li>
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Security:default')) /* line 241 */;
				echo '" class="dropdown-item">
											<i class="bi bi-house-door me-2"></i>Přehled nástrojů
										</a>
									</li>
								</ul>
							</li>
							<li><hr class="dropdown-divider"></li>
							<li class="dropdown-submenu">
								<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
									<i class="bi bi-arrow-repeat me-2" style="color: #B1D235;"></i>Migrace
									<i class="bi bi-chevron-right ms-auto"></i>
								</a>
								<ul class="dropdown-menu dropdown-submenu-menu">
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Migration:default')) /* line 256 */;
				echo '" class="dropdown-item">
											<i class="bi bi-house-door me-2"></i>Přehled nástrojů
										</a>
									</li>
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Migration:encryptOldData')) /* line 261 */;
				echo '" class="dropdown-item">
											<i class="bi bi-lock-fill me-2"></i>Šifrování starých dat
										</a>
									</li>
								</ul>
							</li>
							<li><hr class="dropdown-divider"></li>
							<li class="dropdown-submenu">
								<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
									<i class="bi bi-building me-2" style="color: #B1D235;"></i>Tenants
									<i class="bi bi-chevron-right ms-auto"></i>
								</a>
								<ul class="dropdown-menu dropdown-submenu-menu">
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tenants:default')) /* line 276 */;
				echo '" class="dropdown-item">
											<i class="bi bi-building me-2"></i>Správa tenantů
										</a>
									</li>
									<li>
										<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Tenants:add')) /* line 281 */;
				echo '" class="dropdown-item">
											<i class="bi bi-plus-circle me-2"></i>Vytvořit tenant
										</a>
									</li>
								</ul>
							</li>
							
							<li><hr class="dropdown-divider"></li>
							
							<li>
								<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('Users:default')) /* line 292 */;
				echo '" class="dropdown-item">
									<i class="bi bi-people me-2" style="color: #95B11F;"></i>
									Všichni uživatelé
								</a>
							</li>
						</ul>
					</li>
';
			}
			echo '				</ul>
				
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-person-circle"></i><span class="nav-text">
							';
			if (isset($currentUser) && $currentUser) /* line 306 */ {
				echo LR\Filters::escapeHtmlText($currentUser->username) /* line 306 */;
			} else /* line 306 */ {
				echo 'Uživatel';
			}
			echo "\n";
			if (isset($currentUserRole)) /* line 307 */ {
				if ($isSuperAdmin) /* line 308 */ {
					echo '								<span class="badge ms-1" style="background-color: #B1D235; color: #212529; font-weight: 600;">Super Admin</span>
';
				} elseif ($currentUserRole === 'admin') /* line 310 */ {
					echo '								<span class="badge bg-danger ms-1">Admin</span>
';
				} elseif ($currentUserRole === 'accountant') /* line 312 */ {
					echo '								<span class="badge bg-warning ms-1">Účetní</span>
';
				} else /* line 314 */ {
					echo '								<span class="badge bg-secondary ms-1">Pouze čtení</span>
';
				}


			}
			echo '					</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:profile')) /* line 321 */;
			echo '" class="dropdown-item">
								<i class="bi bi-person"></i> Můj profil
							</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Sign:out')) /* line 325 */;
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
		foreach ($flashes as $flash) /* line 337 */ {
			echo '		<div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 337 */;
			echo '">
';
			if ($flash->type === 'success') /* line 338 */ {
				echo '			<i class="bi bi-check-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'info') /* line 339 */ {
				echo '			<i class="bi bi-info-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'warning') /* line 340 */ {
				echo '			<i class="bi bi-exclamation-triangle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'danger') /* line 341 */ {
				echo '			<i class="bi bi-x-circle-fill me-2"></i>';
			}
			echo '
			';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 342 */;
			echo '
		</div>
';

		}

		echo "\n";
		$this->renderBlock('content', [], 'html') /* line 345 */;
		echo '	</div>

';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 348 */ {
			echo '	<footer class="mt-5 py-4 text-center">
		<div class="container">
			<p class="mb-0">
				QRdoklad (verze 1.9.4) &copy; ';
			echo LR\Filters::escapeHtmlText(date('Y')) /* line 352 */;
			echo ' | Moderní fakturační systém - Proudly crafted by <a href="https://allimedia.cz">Allimedia.cz</a>
			</p>
		</div>
	</footer>
';
		}
		echo '
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 359 */;
		echo '/js/main.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 360 */;
		echo '/js/invoice-form.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 361 */;
		echo '/js/settings.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 362 */;
		echo '/js/tables.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 363 */;
		echo '/js/search.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 364 */;
		echo '/js/ares-lookup.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 365 */;
		echo '/js/modules.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 366 */;
		echo '/js/invoices.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 367 */;
		echo '/js/tenants.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 368 */;
		echo '/js/security.js"></script>
	
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
			foreach (array_intersect_key(['moduleKey' => '72', 'moduleData' => '72', 'menuKey' => '82', 'menuItem' => '82', 'flash' => '337'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}
}
