<?php

declare(strict_types=1);

namespace App\Presentation\Error\Error4xx;

use Nette;
use Nette\Application\Attributes\Requires;


/**
 * Handles 4xx HTTP error responses.
 */
#[Requires(methods: '*', forward: true)]
final class Error4xxPresenter extends Nette\Application\UI\Presenter
{
	public function renderDefault(\Throwable $exception): void
	{
		// OPRAVENO: Zpracování různých typů výjimek
		if ($exception instanceof Nette\Application\BadRequestException) {
			$code = $exception->getCode();
		} else {
			// Pro ostatní výjimky (jako ConnectionException) použijeme 500
			$code = 500;
		}
		
		// renders the appropriate error template based on the HTTP status code
		$file = is_file($file = __DIR__ . "/$code.latte")
			? $file
			: __DIR__ . '/4xx.latte';
		$this->template->httpCode = $code;
		$this->template->setFile($file);
	}
}