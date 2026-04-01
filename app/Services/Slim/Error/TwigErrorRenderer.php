<?php

declare(strict_types=1);

namespace App\Services\Slim\Error;

use Slim\Error\AbstractErrorRenderer;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Slim\Views\Twig;
use Throwable;
use function get_class;

final class TwigErrorRenderer extends AbstractErrorRenderer
{
	private string $template;

	public function __construct(
		private Twig $twig
	) {

	}

    #[\Override]
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
		$context = [
			'title' => $this->getErrorTitle($exception),
			'description' => $this->getErrorDescription($exception),
			'displayErrorDetails' => $displayErrorDetails,
			'details' => [
				'type' => get_class($exception),
				'code' => $exception->getCode(),
				'message' => $exception->getMessage(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTraceAsString(),
			],
		];

		if (empty($this->template)) {
			return (new HtmlErrorRenderer())($exception, $displayErrorDetails);
		}

		return $this->twig->getEnvironment()->render($this->template, $context);
    }

	public function setTemplate(string $template): void
	{
		$this->template = $template;
	}

}
