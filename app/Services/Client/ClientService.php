<?php

namespace App\Services\Client;

use PDO;

final class ClientService
{
	public function __construct(
		private PDO $pdo,
	) {
	}

	/**
	 * @return list<Client>
	 */
	public function getList(array $filter): array
	{
		$binds = [];
		$conditions = [];

		if (isset($filter['status']) && ClientStatus::tryFrom($filter['status'])) {
			$conditions[] = 'status = :status';
			$binds['status'] = $filter['status'];
		}

		$sql = sprintf(
			"SELECT * FROM clients %s ORDER BY created_at DESC",
			($conditions == []) ? '' : 'WHERE ' . implode(' AND ', $conditions)
		);

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($binds);

		return array_map(
			static fn(array $row) => Client::fromArray($row), 
			$stmt->fetchAll()
		);
	}

	public function getClientsForSelect(): array
	{
		$stmt = $this->pdo->prepare('SELECT id, name FROM clients ORDER BY name ASC');
		$stmt->execute();
		
		return array_column($stmt->fetchAll(), null, 'id');
	}

	public function findById(int $id): ?Client
	{
		$stmt = $this->pdo->prepare('SELECT * FROM clients WHERE id = :id');
		$stmt->execute(['id' => $id]);
		$data = $stmt->fetch();

		return $data ? Client::fromArray($data) : null;
	}

	public function create(
		array $params
	): Client {
		$this->validateClientData($params);

		$now = new \DateTimeImmutable();
		$client = new Client(
			null,
			(string) $params['name'],
			(string) $params['contact_person'],
			(string) $params['phone'],
			(string) $params['email'],
			ClientStatus::from((string) $params['status'] ?? '') ?? ClientStatus::NEW,
			$now,
			$now
		);

		$this->save($client);

		return $client;
	}

	public function update(
		int $id,
		array $params
	): Client {
		$client = $this->findById($id);

		if ($client === null) {
			throw new \RuntimeException('Клиент не найден');
		}

		$this->validateClientData($params);

		$client->name = (string) $params['name'] ?? $client->name;
		$client->contactPerson = (string) $params['contact_person'] ?? $client->contactPerson;
		$client->phone = (string) $params['phone'] ?? $client->phone;
		$client->email = (string) $params['email'] ?? $client->email;
		$client->setStatus($params['status'] ?? $client->status);
		$client->updatedAt = new \DateTimeImmutable();

		$this->save($client);

		return $client;
	}

	public function archive(int $id): void
	{
		$stmt = $this->pdo->prepare(
			'UPDATE clients SET status = :status, updated_at = :updated_at WHERE id = :id'
		);
		$stmt->execute([
			'id' => $id,
			'status' => ClientStatus::ARCHIVE->value,
			'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
		]);
	}

	public function findNoteById(int $id): ?ClientNote
	{
		$stmt = $this->pdo->prepare('SELECT * FROM client_notes WHERE id = :id');
		$stmt->execute(['id' => $id]);
		$data = $stmt->fetch();

		return $data ? ClientNote::fromArray($data) : null;
	}

	public function createNote(int $clientId, string $message): ClientNote
	{
		if ($this->findById($clientId) === null) {
			throw new \RuntimeException('Клиент не найден');
		}

		if (empty(trim($message))) {
			throw new \InvalidArgumentException('Сообщение не может быть пустым');
		}

		$clientNote = new ClientNote(
			null,
			trim($message),
			$clientId,
			new \DateTimeImmutable(),
			new \DateTimeImmutable()
		);

		$this->saveNote($clientNote);

		return $clientNote;
	}

	public function updateNote(int $id, string $message): ClientNote
	{
		$clientNote = $this->findNoteById($id);

		if ($clientNote === null) {
			throw new \RuntimeException('Заметка не найдена');
		}

		if (empty(trim($message))) {
			throw new \InvalidArgumentException('Сообщение не может быть пустым');
		}

		$clientNote->message = trim($message);
		$this->saveNote($clientNote);

		return $clientNote;
	}

	public function save(Client $client): void
	{
		$client->updatedAt = new \DateTimeImmutable();
		$binds = $client->toDbArray();

		if ($client->id === null) {
			$stmt = $this->pdo->prepare(
				'INSERT INTO clients (name, contact_person, phone, email, status, created_at, updated_at) 
			 VALUES (:name, :contact_person, :phone, :email, :status, :created_at, :updated_at)'
			);

			unset($binds['id']);
			$stmt->execute($binds);
			$client->id = (int) $this->pdo->lastInsertId();
		} else {
			$stmt = $this->pdo->prepare(
				'UPDATE clients 
				 SET name = :name, 
					contact_person = :contact_person, 
					phone = :phone, 
					email = :email, 
					status = :status, 
					updated_at = :updated_at 
				 WHERE id = :id'
			);

			unset($binds['created_at']);
			$stmt->execute($binds);
		}
	}

	private function saveNote(ClientNote $clientNote): void
	{
		$clientNote->updatedAt = new \DateTimeImmutable();
		$binds = $clientNote->toDbArray();

		if ($clientNote->id === null) {
			$stmt = $this->pdo->prepare(
				'INSERT INTO client_notes (message, client_id, created_at, updated_at) 
				 VALUES (:message, :client_id, :created_at, :updated_at)'
			);

			unset($binds['id']);
			$stmt->execute($binds);
			$clientNote->id = (int) $this->pdo->lastInsertId();
		} else {
			$stmt = $this->pdo->prepare(
				'UPDATE client_notes 
				 SET message = :message, 
					 client_id = :client_id, 
					 updated_at = :updated_at 
				 WHERE id = :id'
			);

			unset($binds['created_at']);
			$stmt->execute($binds);
		}
	}

	public function getNotesByClientId(int $clientId): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM client_notes WHERE client_id = :client_id ORDER BY created_at DESC');
		$stmt->execute(['client_id' => $clientId]);
		$notesData = $stmt->fetchAll();

		return array_map(
			static fn(array $row) => ClientNote::fromArray($row), 
			$notesData
		);
	}

	private function validateClientData(array $data): void
	{
		if (empty(trim($data['name'] ?? ''))) {
			throw new \InvalidArgumentException('Наименование обязательно для заполнения');
		}

		if (empty(trim($data['contact_person'] ?? ''))) {
			throw new \InvalidArgumentException('Контактное лицо обязательно для заполнения');
		}

		if (empty(trim($data['phone'] ?? ''))) {
			throw new \InvalidArgumentException('Телефон обязателен для заполнения');
		}

		if (!empty(trim($data['email'] ?? '')) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			throw new \InvalidArgumentException('Некорректный формат email');
		}
	}

}
