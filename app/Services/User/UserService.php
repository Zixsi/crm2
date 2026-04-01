<?php

namespace App\Services\User;

use PDO;

final class UserService
{
	public function __construct(
		private PDO $pdo,
	) {
	}

	public function findByLogin(string $login): ?User
	{
		$stmt = $this->pdo->prepare(
			'SELECT * FROM users WHERE login = :login LIMIT 1'
		);
		$stmt->execute(['login' => $login]);
		$data = $stmt->fetch();

		if ($data === false) {
			return null;
		}

		return User::fromArray($data);
	}

	public function findById(int $id): ?User
	{
		$stmt = $this->pdo->prepare(
			'SELECT id, login, password FROM users WHERE id = :id LIMIT 1'
		);
		$stmt->execute(['id' => $id]);
		$data = $stmt->fetch();

		if ($data === false) {
			return null;
		}

		return User::fromArray($data);
	}

	public function create(string $login, string $password): User
	{
		$user = new User(
			null,
			$login,
			password_hash($password, PASSWORD_DEFAULT)
		);
		$this->save($user);

		return $user;
	}

	public function updatePassword(int $userId, string $newPassword): void
	{
		$user = $this->findById($userId);

		if ($user === null) {
			throw new \InvalidArgumentException("User with id $userId not found");
		}

		$user->password = password_hash($newPassword, PASSWORD_DEFAULT);
		$this->save($user);
	}

	private function save(User $user): void
	{
		$binds = $user->toDbArray();

		if ($user->id === null) {
			$stmt = $this->pdo->prepare(
				'INSERT INTO users (login, password) VALUES (:login, :password)'
			);

			unset($binds['id']);
			$stmt->execute($binds);
			$user->id = (int) $this->pdo->lastInsertId();
		} else {
			$stmt = $this->pdo->prepare(
				'UPDATE users 
				 SET password = :password
				 WHERE id = :id'
			);

			unset($binds['login']);
			$stmt->execute($binds);
		}
	}

}
