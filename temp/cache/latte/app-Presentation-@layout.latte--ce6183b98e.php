<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation/@layout.latte */
final class Template_ce6183b98e extends Latte\Runtime\Template
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
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
		<div class="container">
			<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 16 */;
		echo '" class="navbar-brand">
    			<img src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 17 */;
		echo '/images/qr-webp-white.webp" alt="QRdoklad" height="30" class="d-inline-block align-text-top me-2">
    			QRdoklad
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 26 */;
		echo '" class="nav-link">
							<i class="bi bi-house"></i> Úvod
						</a>
					</li>
					<li class="nav-item">
						<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Clients:default')) /* line 31 */;
		echo '" class="nav-link">
							<i class="bi bi-people"></i> Klienti
						</a>
					</li>
					<li class="nav-item">
						<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Invoices:default')) /* line 36 */;
		echo '" class="nav-link">
							<i class="bi bi-file-earmark-text"></i> Faktury
						</a>
					</li>
					<li class="nav-item">
						<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Settings:default')) /* line 41 */;
		echo '" class="nav-link">
							<i class="bi bi-gear"></i> Nastavení
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<div class="container">
';
		foreach ($flashes as $flash) /* line 51 */ {
			echo '		<div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 51 */;
			echo '">
';
			if ($flash->type === 'success') /* line 52 */ {
				echo '			<i class="bi bi-check-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'info') /* line 53 */ {
				echo '			<i class="bi bi-info-circle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'warning') /* line 54 */ {
				echo '			<i class="bi bi-exclamation-triangle-fill me-2"></i>';
			}
			echo "\n";
			if ($flash->type === 'danger') /* line 55 */ {
				echo '			<i class="bi bi-x-circle-fill me-2"></i>';
			}
			echo '
			';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 56 */;
			echo '
		</div>
';

		}

		echo "\n";
		$this->renderBlock('content', [], 'html') /* line 59 */;
		echo '	</div>

	<footer class="mt-5 py-4 text-center">
		<div class="container">
			<p class="mb-0">
				QRdoklad &copy; ';
		echo LR\Filters::escapeHtmlText(date('Y')) /* line 65 */;
		echo ' | Moderní fakturační systém - Proudly crafted by <a href="https://allimedia.cz">Allimedia.cz</a>
			</p>
		</div>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 71 */;
		echo '/js/main.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 72 */;
		echo '/js/invoice-form.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 73 */;
		echo '/js/settings.js"></script>
</body>
</html>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['flash' => '51'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}
}
