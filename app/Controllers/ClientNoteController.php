<?php

namespace App\Controllers;

use App\Services\Client\ClientService;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClientNoteController extends AbstractController
{
	public function __construct(
		private ClientService $clientService,
		ContainerInterface $container,
	) {
		parent::__construct($container);
	}

	public function add(ServerRequestInterface $request, int $clientId): ResponseInterface
	{
		$client = $this->clientService->findById($clientId);

		if ($client === null) {
			$this->show404();
		}

		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->clientService->createNote($clientId, (string) $params['message']);
				$this->flash->success('Заметка успешно создана');
				
				return $this->redirect(urlFor('clients-view', ['id' => $clientId]));
			} catch (Exception $ex) {
				$this->flash->error($ex->getMessage());
			}
		}

		return $this->render('client/note_form.twig', [
			'client' => $client,
			'request' => ($params ?? []),
		]);
	}

	public function edit(ServerRequestInterface $request, int $clientId, int $id): ResponseInterface
	{
		$client = $this->clientService->findById($clientId);

		if ($client === null) {
			$this->show404();
		}

		$note = $this->clientService->findNoteById($id);

		if ($note === null || $note->clientId !== $client->id) {
			$this->show404();
		}

		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->clientService->updateNote($id, (string) $params['message']);
				$this->flash->success('Заметка успешно обновлена');

				return $this->redirect(urlFor('clients-view', ['id' => $clientId]));
			} catch (Exception $ex) {
				$this->flash->error($ex->getMessage());
			}
		}

		return $this->render('client/note_form.twig', [
			'client' => $client,
			'item' => $note,
			'request' => ($params ?? []),
		]);
	}

}
