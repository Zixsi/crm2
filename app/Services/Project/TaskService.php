<?php

namespace App\Services\Project;

use PDO;

final class TaskService
{
	public function __construct(
		private PDO $pdo,
	) {
	}

	/**
	 * @return list<Task>
	 */
	public function getList(array $filter): array
	{
		$binds = [];
		$conditions = [
			't.deleted = 0 AND p.deleted = 0'
		];

		if (isset($filter['status']) && TaskStatus::tryFrom($filter['status'])) {
			$status = TaskStatus::from($filter['status']);
			$conditions[] = 't.status = :status';
			$binds['status'] = $status->value;
		}

		if (isset($filter['project_id']) && (int) $filter['project_id'] > 0) {
			$conditions[] = 't.project_id = :project_id';
			$binds['project_id'] = (int) $filter['project_id'];
		}

		$sql = sprintf(
			"SELECT 
				t.* 
			FROM tasks as t
			LEFT JOIN projects as p ON(t.project_id = p.id)
			%s 
			ORDER BY t.id DESC",
			($conditions == []) ? '' : 'WHERE ' . implode(' AND ', $conditions)
		);

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($binds);

		return array_map(
			static fn(array $row) => Task::fromArray($row), 
			$stmt->fetchAll()
		);

		return [];
	}

	public function findById(int $id): ?Task
	{
		$stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = :id');
		$stmt->execute(['id' => $id]);
		$data = $stmt->fetch();

		return $data ? Task::fromArray($data) : null;
	}

	public function create(
		int $projectId,
		array $params
	): Task {
		$this->validateTaskData($params);
		$task = Task::fromArray($params);
		$task->projectId = $projectId;

		$this->save($task);

		return $task;
	}

	public function update(
		int $id,
		array $params
	): Task {
		$task = $this->findById($id);

		if ($task === null) {
			throw new \RuntimeException('Задача не найдена');
		}

		$this->validateTaskData($params);

		$task->name = (string) ($params['name'] ?? $task->name);
		$task->endDate = 
			isset($params['end_date']) 
			? new \DateTimeImmutable((string) $params['end_date']) 
			: $task->endDate;
		$task->projectId = (int) ($params['project_id'] ?? $task->projectId);
		$task->description = (string) ($params['description'] ?? $task->description);
		$task->status = 
			TaskStatus::tryFrom((string) ($params['status'] ?? '')) 
			? TaskStatus::from((string) $params['status']) 
			: $task->status;

		$this->save($task);

		return $task;
	}

	public function archive(int $id): void
	{
		$stmt = $this->pdo->prepare(
			'UPDATE tasks SET deleted = :deleted WHERE id = :id'
		);
		$stmt->execute([
			'id' => $id,
			'deleted' => 1
		]);
	}

	private function save(Task $task): void
	{
		$binds = $task->toDbArray();

		if ($task->id === null) {
			$stmt = $this->pdo->prepare(
				'INSERT INTO tasks (name, description, end_date, project_id, status, deleted) 
			 VALUES (:name, :description, :end_date, :project_id, :status, :deleted)'
			);

			unset($binds['id']);
			$stmt->execute($binds);
			$task->id = (int) $this->pdo->lastInsertId();
		} else {
			$stmt = $this->pdo->prepare(
				'UPDATE tasks 
				 SET name = :name, 
					description = :description, 
					end_date = :end_date, 
					project_id = :project_id, 
					status = :status, 
					deleted = :deleted 
				 WHERE id = :id'
			);

			$stmt->execute($binds);
		}
	}

	private function validateTaskData(array $data): void
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
