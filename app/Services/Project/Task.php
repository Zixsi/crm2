<?php

namespace App\Services\Project;

use DateTimeImmutable;

final class Task
{
	public function __construct(
		public ?int $id = null,
		public string $name,
		public string $description,
		public \DateTimeImmutable $endDate,
		public int $projectId,
		public TaskStatus $status = TaskStatus::NEW,
		public bool $deleted = false
	) {
	}

	public function getStatusLabel(): string
	{
		return $this->status->getLabel();
	}

	public function getBadgeClass(): string
	{
		return $this->status->getBadgeClass();
	}

	public function isExpired(): bool
	{
		return $this->status !== TaskStatus::COMPLETED 
			&& $this->endDate < (new \DateTimeImmutable('today'));
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toDbArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'end_date' => $this->endDate->format('Y-m-d'),
			'project_id' => $this->projectId,
			'status' => $this->status->value,
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
				(string) ($data['description'] ?? ''),
				new \DateTimeImmutable((string) $data['end_date'] ?? 'now'),
				(int) ($data['project_id'] ?? 0),
				TaskStatus::tryFrom((string) ($data['status'] ?? TaskStatus::NEW->value)) ?? TaskStatus::NEW,
				(bool) ($data['deleted'] ?? false),
		);
	}
}
