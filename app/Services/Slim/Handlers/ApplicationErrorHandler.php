<?php

namespace App\Services\Slim\Handlers;

use Slim\Error\Renderers\PlainTextErrorRenderer;
use Slim\Handlers\ErrorHandler;

final class ApplicationErrorHandler extends ErrorHandler
{
	private array $ignoreToLogExceptions = [];

	protected function writeToErrorLog(): void
    {
        $renderer = $this->callableResolver->resolve($this->logErrorRenderer);
        $error = $renderer($this->exception, $this->logErrorDetails);

        if ($this->logErrorRenderer === PlainTextErrorRenderer::class && !$this->displayErrorDetails) {
            $error .= "\nTips: To display error details in HTTP response ";
            $error .= 'set "displayErrorDetails" to true in the ErrorHandler constructor.';
        }

		$matchIgnoreList = array_filter($this->ignoreToLogExceptions, function (string $class) {
			return ($this->exception instanceof $class);
		});

		if (count($matchIgnoreList) === 0) {
			$this->logError($error);
		}
    }
	
	public function setIgnoreToLogExceptions(array $list = []): void
	{
		$this->ignoreToLogExceptions = $list;
	}
}
