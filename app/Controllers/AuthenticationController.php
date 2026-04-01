<?php

namespace App\Controllers;

use App\Services\AuthenticationService;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function redirectFor;

final class AuthenticationController extends AbstractController
{

	public function __construct(
		private AuthenticationService $authenticationService,
		ContainerInterface $container
	) {
		parent::__construct($container);
	}

	public function login(ServerRequestInterface $request): ResponseInterface
	{	
		if ($this->authenticationService->isAuthenticated()) {
			redirectFor('main');
		}

		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->authenticationService->login(
					(string) ($params['login'] ?? ''),
					(string) ($params['password'] ?? ''),
				);

				redirectFor('main');
			} catch (Exception $ex) {
				$error = $ex->getMessage();
			}
		}

		return $this->render('authentication/login.twig', [
			'request' => (array) $request->getParsedBody(),
			'error' => $error ?? null,
		]);
	}

	public function logout(): void
	{	
		$this->authenticationService->logout();
		redirectFor('login');
	}
}
