<?php

namespace App\Services\Project;

use PDO;

final class ProjectService
{
	public function __construct(
		private PDO $pdo,
	) {
	}

	/**
	 * @return list<Project>
	 */
	public function getList(array $filter): array
	{
		$binds = [];
		$conditions = [
			'deleted = 0'
		];

		if (isset($filter['status']) && ProjectStatus::tryFrom($filter['status'])) {
			$status = ProjectStatus::from($filter['status']);

			if ($status === ProjectStatus::ACTIVE) {
				$conditions[] = 'end_date >= CURDATE()';
			} elseif ($status === ProjectStatus::COMPLETED) {
				$conditions[] = 'end_date < CURDATE()';
			}
		}

		if (isset($filter['client_id']) && (int) $filter['client_id'] > 0) {
			$conditions[] = 'client_id = :client_id';
			$binds['client_id'] = (int) $filter['client_id'];
		}

		$sql = sprintf(
			"SELECT * FROM projects %s ORDER BY id DESC",
			($conditions == []) ? '' : 'WHERE ' . implode(' AND ', $conditions)
		);

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($binds);

		return array_map(
			static fn(array $row) => Project::fromArray($row), 
			$stmt->fetchAll()
		);
	}

	public function getListForSelect(): array
	{
		$stmt = $this->pdo->prepare('SELECT id, name FROM projects WHERE deleted = 0 ORDER BY name ASC');
		$stmt->execute();
		
		return array_column($stmt->fetchAll(), null, 'id');
	}

	public function findById(int $id): ?Project
	{
		$stmt = $this->pdo->prepare('SELECT * FROM projects WHERE id = :id');
		$stmt->execute(['id' => $id]);
		$data = $stmt->fetch();

		return $data ? Project::fromArray($data) : null;
	}

	public function create(
		array $params
	): Project {
		$this->validateProjectData($params);
		$project = Project::fromArray($params);

		$this->save($project);

		return $project;
	}

	public function update(
		int $id,
		array $params
	): Project {
		$project = $this->findById($id);

		if ($project === null) {
			throw new \RuntimeException('Проект не найден');
		}

		$this->validateProjectData($params);

		$project->name = (string) ($params['name'] ?? $project->name);
		$project->startDate = 
			isset($params['start_date']) 
			? new \DateTimeImmutable((string) $params['start_date']) 
			: $project->startDate;
		$project->endDate = 
			isset($params['end_date']) 
			? new \DateTimeImmutable((string) $params['end_date']) 
			: $project->endDate;
		$project->clientId = (int) ($params['client_id'] ?? $project->clientId);
		$project->description = (string) ($params['description'] ?? $project->description);

		$this->save($project);

		return $project;
	}

	public function archive(int $id): void
	{
		$stmt = $this->pdo->prepare(
			'UPDATE projects SET deleted = :deleted WHERE id = :id'
		);
		$stmt->execute([
			'id' => $id,
			'deleted' => 1
		]);
	}

	private function save(Project $project): void
	{
		$binds = $project->toDbArray();

		if ($project->id === null) {
			$stmt = $this->pdo->prepare(
				'INSERT INTO projects (name, start_date, end_date, client_id, description, deleted) 
			 VALUES (:name, :start_date, :end_date, :client_id, :description, :deleted)'
			);

			unset($binds['id']);
			$stmt->execute($binds);
			$project->id = (int) $this->pdo->lastInsertId();
		} else {
			$stmt = $this->pdo->prepare(
				'UPDATE projects 
				 SET name = :name, 
					start_date = :start_date, 
					end_date = :end_date, 
					client_id = :client_id, 
					description = :description, 
					deleted = :deleted 
				 WHERE id = :id'
			);

			$stmt->execute($binds);
		}
	}

	private function validateProjectData(array $data): void
	{
		// if (empty(trim($data['name'] ?? ''))) {
		// 	throw new \InvalidArgumentException('Наименование обязательно для заполнения');
		// }

		// if (empty(trim($data['contact_person'] ?? ''))) {
		// 	throw new \InvalidArgumentException('Контактное лицо обязательно для заполнения');
		// }

		// if (empty(trim($data['phone'] ?? ''))) {
		// 	throw new \InvalidArgumentException('Телефон обязателен для заполнения');
		// }

		// if (!empty(trim($data['email'] ?? '')) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
		// 	throw new \InvalidArgumentException('Некорректный формат email');
		// }
	}

}
