<?php

namespace App\Services\User;

final class User
{
	public function __construct(
		public ?int $id = null,
		public string $login,
		public string $password
	) {
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toDbArray(): array
	{
		return [
			'id' => $this->id,
			'login' => $this->login,
			'password' => $this->password,
		];
	}

	public static function fromArray(array $data): self
	{
		return new self(
			isset($data['id']) ? (int) $data['id'] : null,
			(string) ($data['login'] ?? ''),
			(string) ($data['password'] ?? ''),
		);
	}
}
