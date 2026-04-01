<?php

namespace App\Services\Project;

use DateTimeImmutable;

final class Project
{
	public function __construct(
		public ?int $id = null,
		public string $name,
		public DateTimeImmutable $startDate,
		public DateTimeImmutable $endDate,
		public int $clientId,
		public string $description = '',
		public bool $deleted = false
	) {
	}

	public function getStatusLabel(): string
	{
		return $this->getStatus()->getLabel();
	}

	public function getBadgeClass(): string
	{
		return $this->getStatus()->getBadgeClass();
	}

	private function getStatus(): ProjectStatus
	{
		if ($this->endDate < new DateTimeImmutable('today')) {
			return ProjectStatus::COMPLETED;
		}

		return ProjectStatus::ACTIVE;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toDbArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'start_date' => $this->startDate->format('Y-m-d'),
			'end_date' => $this->endDate->format('Y-m-d'),
			'client_id' => $this->clientId,
			'description' => $this->description,
			'deleted' => (int) $this->deleted,
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
				new \DateTimeImmutable((string) $data['start_date'] ?? 'now'),
				new \DateTimeImmutable((string) $data['end_date'] ?? 'now'),
				(int) ($data['client_id'] ?? 0),
				(string) ($data['description'] ?? ''),
				(bool) ($data['deleted'] ?? false),
		);
	}
}
