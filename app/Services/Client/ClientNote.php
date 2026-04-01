<?php

namespace App\Services\Client;

final class ClientNote
{
	public function __construct(
		public ?int $id = null,
		public string $message,
		public int $clientId,
		public ?\DateTimeImmutable $createdAt = null,
		public ?\DateTimeImmutable $updatedAt = null,
	) {
	}

	public function toDbArray(): array
	{
		return [
			'id' => $this->id,
			'message' => $this->message,
			'client_id' => $this->clientId,
			'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
			'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
		];
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
				isset($data['id']) ? (int) $data['id'] : null,
				(string) ($data['message'] ?? ''),
				(int) $data['client_id'],
				isset($data['created_at']) ? new \DateTimeImmutable((string) $data['created_at']) : null,
				isset($data['updated_at']) ? new \DateTimeImmutable((string) $data['updated_at']) : null
		);
	}
}
