<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation/@layout.latte */
final class Template_88337ed53b extends Latte\Runtime\Template
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
			echo '" class="navbar-brand">
    			<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 18 */;
			echo '/images/qr-webp-white.webp" alt="QRdoklad" height="30" class="d-inline-block align-text-top">
    			<span class="brand-text">QRdoklad</span>
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 27 */;
			echo '" class="nav-link">
							<i class="bi bi-house"></i><span class="nav-text">Úvod</span>
						</a>
					</li>
';
			if (isset($isUserReadonly) && $isUserReadonly) /* line 32 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Clients:default')) /* line 34 */;
				echo '" class="nav-link">
							<i class="bi bi-people"></i><span class="nav-text">Klienti</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Invoices:default')) /* line 39 */;
				echo '" class="nav-link">
							<i class="bi bi-file-earmark-text"></i><span class="nav-text">Faktury</span>
						</a>
					</li>
';
			}
			echo '					
';
			if (isset($moduleMenuItems) && !empty($moduleMenuItems)) /* line 46 */ {
				echo '					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="extensionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-puzzle-fill"></i><span class="nav-text">Rozšíření</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="extensionsDropdown">
';
				foreach ($iterator = $ʟ_it = new Latte\Essential\CachingIterator($moduleMenuItems, $ʟ_it ?? null) as $moduleKey => $moduleData) /* line 52 */ {
					if (!empty($moduleData['menuItems'])) /* line 53 */ {
						echo '									<li><h6 class="dropdown-header">
										<i class="';
						echo LR\Filters::escapeHtmlQuotes($moduleData['moduleInfo']['icon']) /* line 55 */;
						echo ' me-2"></i>
										';
						echo LR\Filters::escapeHtmlText($moduleData['moduleInfo']['name']) /* line 56 */;
						echo '
									</h6></li>
';
						foreach ($moduleData['menuItems'] as $menuKey => $menuItem) /* line 58 */ {
							echo '										<li>
';
							if ($menuItem['linkType'] === 'nette') /* line 60 */ {
								echo '												<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 61 */;
								echo '">
';
								if (isset($menuItem['icon'])) /* line 62 */ {
									echo '													<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 62 */;
									echo ' me-2"></i>';
								}
								echo '
													';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 63 */;
								echo '
												</a>
';
							} elseif ($menuItem['linkType'] === 'javascript') /* line 65 */ {
								echo '												<a class="dropdown-item" href="#" onclick="';
								echo LR\Filters::escapeHtmlQuotes($menuItem['onclick']) /* line 66 */;
								echo '; return false;">
';
								if (isset($menuItem['icon'])) /* line 67 */ {
									echo '													<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 67 */;
									echo ' me-2"></i>';
								}
								echo '
													';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 68 */;
								echo '
												</a>
';
							} else /* line 70 */ {
								echo '												<a class="dropdown-item" href="';
								echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($menuItem['link'])) /* line 71 */;
								echo '">
';
								if (isset($menuItem['icon'])) /* line 72 */ {
									echo '													<i class="';
									echo LR\Filters::escapeHtmlQuotes($menuItem['icon']) /* line 72 */;
									echo ' me-2"></i>';
								}
								echo '
													';
								echo LR\Filters::escapeHtmlText($menuItem['label']) /* line 73 */;
								echo '
												</a>
';
							}

							echo '										</li>
';

						}

						if (!$iterator->isLast()) /* line 78 */ {
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
			if (isset($isUserAdmin) && $isUserAdmin) /* line 88 */ {
				echo '					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Settings:default')) /* line 90 */;
				echo '" class="nav-link">
							<i class="bi bi-gear"></i><span class="nav-text">Nastavení</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:default')) /* line 95 */;
				echo '" class="nav-link">
							<i class="bi bi-people-fill"></i><span class="nav-text">Uživatelé</span>
						</a>
					</li>
					<li class="nav-item">
    					<a href="';
				echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('ModuleAdmin:default')) /* line 100 */;
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
			if (isset($currentUser) && $currentUser) /* line 111 */ {
				echo LR\Filters::escapeHtmlText($currentUser->username) /* line 111 */;
			} else /* line 111 */ {
				echo 'Uživatel';
			}
			echo "\n";
			if (isset($currentUserRole)) /* line 112 */ {
				if ($currentUserRole === 'admin') /* line 113 */ {
					echo '									<span class="badge bg-danger ms-1">Admin</span>
';
				} elseif ($currentUserRole === 'accountant') /* line 115 */ {
					echo '									<span class="badge bg-warning ms-1">Účetní</span>
';
				} else /* line 117 */ {
					echo '									<span class="badge bg-secondary ms-1">Pouze čtení</span>
';
				}

			}
			echo '							</span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Users:profile')) /* line 124 */;
			echo '" class="dropdown-item">
								<i class="bi bi-person"></i> Můj profil
							</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a href="';
			echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Sign:out')) /* line 128 */;
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
		foreach ($flashes as $flash) /* line 140 */ {
			echo '		<div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 140 */;
			echo '">
';
			if ($flash->type === 'success') /* line 141 */ {
				echo '			<i class="bi bi-check-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'info') /* line 142 */ {
				echo '			<i class="bi bi-info-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'warning') /* line 143 */ {
				echo '			<i class="bi bi-exclamation-triangle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'danger') /* line 144 */ {
				echo '			<i class="bi bi-x-circle-fill me-2"></i>';
			}
			echo '
			';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 145 */;
			echo '
		</div>
';

		}

		echo "\n";
		$this->renderBlock('content', [], 'html') /* line 148 */;
		echo '	</div>

';
		if (isset($userLoggedIn) && $userLoggedIn) /* line 151 */ {
			echo '	<footer class="mt-5 py-4 text-center">
		<div class="container">
			<p class="mb-0">
				QRdoklad (verze 1.9.4) &copy; ';
			echo LR\Filters::escapeHtmlText(date('Y')) /* line 155 */;
			echo ' | Moderní fakturační systém - Proudly crafted by <a href="https://allimedia.cz">Allimedia.cz</a>
			</p>
		</div>
	</footer>
';
		}
		echo '
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 162 */;
		echo '/js/main.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 163 */;
		echo '/js/invoice-form.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 164 */;
		echo '/js/settings.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 165 */;
		echo '/js/tables.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 166 */;
		echo '/js/search.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 167 */;
		echo '/js/ares-lookup.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 168 */;
		echo '/js/modules.js"></script>
</body>
</html>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['moduleKey' => '52', 'moduleData' => '52', 'menuKey' => '58', 'menuItem' => '58', 'flash' => '140'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}
}
