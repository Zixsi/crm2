<?php

namespace App\Services\Slim;

use App\Services\AuthenticationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function redirectFor;

final class AuthorizationMiddleware
{
	public function __construct(
		private AuthenticationService $authenticationService,
	) {}

	public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if (!$this->authenticationService->isAuthenticated()) {
			redirectFor('login');
		}

		return $handler->handle($request);
	}
}
