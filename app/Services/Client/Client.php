<?php

namespace App\Services\Client;

final class Client
{
	public function __construct(
		public ?int $id = null,
		public string $name = '',
		public string $contactPerson = '',
		public string $phone = '',
		public string $email = '',
		public ClientStatus $status = ClientStatus::NEW,
		public ?\DateTimeImmutable $createdAt = null,
		public ?\DateTimeImmutable $updatedAt = null,
	) {
	}

	public function setStatus(ClientStatus|string $status): self
	{
		if (is_string($status)) {
			$status = ClientStatus::from($status);
		}

		$this->status = $status;
		return $this;
	}

	public function getStatusLabel(): string
	{
		return $this->status->getLabel();
	}

	public function getBadgeClass(): string
	{
		return $this->status->getBadgeClass();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toDbArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'contact_person' => $this->contactPerson,
			'phone' => $this->phone,
			'email' => $this->email,
			'status' => $this->status->value,
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
				(string) ($data['name'] ?? ''),
				(string) ($data['contact_person'] ?? ''),
				(string) ($data['phone'] ?? ''),
				(string) ($data['email'] ?? ''),
				ClientStatus::tryFrom((string) ($data['status'] ?? ClientStatus::NEW->value)) ?? ClientStatus::NEW,
				isset($data['created_at']) ? new \DateTimeImmutable((string) $data['created_at']) : null,
				isset($data['updated_at']) ? new \DateTimeImmutable((string) $data['updated_at']) : null
		);
	}
}
