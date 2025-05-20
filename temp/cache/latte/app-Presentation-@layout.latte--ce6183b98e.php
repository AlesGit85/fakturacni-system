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
	<link rel="stylesheet" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 8 */;
		echo '/css/style.css">
</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
		<div class="container">
			<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 14 */;
		echo '" class="navbar-brand">QRdoklad</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Home:default')) /* line 21 */;
		echo '" class="nav-link">Úvod</a>
					</li>
					<li class="nav-item">
						<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Clients:default')) /* line 24 */;
		echo '" class="nav-link">Klienti</a>
					</li>
					<li class="nav-item">
						<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Invoices:default')) /* line 27 */;
		echo '" class="nav-link">Faktury</a>
					</li>
					<li class="nav-item">
						<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(':Settings:default')) /* line 30 */;
		echo '" class="nav-link">Nastavení</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<div class="container">
';
		foreach ($flashes as $flash) /* line 38 */ {
			echo '		<div class="alert alert-';
			echo LR\Filters::escapeHtmlAttr($flash->type) /* line 38 */;
			echo '">';
			echo LR\Filters::escapeHtmlText($flash->message) /* line 38 */;
			echo '</div>
';

		}

		echo "\n";
		$this->renderBlock('content', [], 'html') /* line 40 */;
		echo '	</div>

	<footer class="mt-5 py-4 bg-light">
		<div class="container text-center">
			<p>QRdoklad &copy; ';
		echo LR\Filters::escapeHtmlText(date('Y')) /* line 45 */;
		echo '</p>
		</div>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 50 */;
		echo '/js/main.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 51 */;
		echo '/js/invoice-form.js"></script>
	<script src="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 52 */;
		echo '/js/settings.js"></script>
</body>
</html>';
	}


	public function prepare(): array
	{
		extract($this->params);

		if (!$this->getReferringTemplate() || $this->getReferenceType() === 'extends') {
			foreach (array_intersect_key(['flash' => '38'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		return get_defined_vars();
	}
}
