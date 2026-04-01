<?php

namespace App\Services;

use App\Services\User\User;
use App\Services\User\UserService;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;

class AuthenticationService
{
	private const USER_SESSION_KEY = 'user_id';

	public function __construct(
		private SessionInterface $session,
		private SessionManagerInterface $sessionManager,
		private UserService $userService,
	) {
	}

	public function login(string $login, string $password): void
	{
		$user = $this->userService->findByLogin($login);

		if ($user === null) {
			throw new \InvalidArgumentException('Неверный логин или пароль');
		}

		if (!password_verify($password, $user->password)) {
			throw new \InvalidArgumentException('Неверный логин или пароль');
		}

		$this->session->set(self::USER_SESSION_KEY, $user->id);
	}

	public function logout(): void
	{
		$this->sessionManager->destroy();
	}

	public function isAuthenticated(): bool
	{
		return $this->session->has(self::USER_SESSION_KEY);
	}

	public function getCurrentUser(): ?User
	{
		$userId = $this->session->get(self::USER_SESSION_KEY);

		return $userId ? $this->userService->findById((int) $userId) : null;
	}

}