<?php

namespace App\Controllers;

use App\Services\Client\ClientService;
use App\Services\Client\ClientStatus;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClientController extends AbstractController
{
	public function __construct(
		private ClientService $clientService,
		ContainerInterface $container,
	) {
		parent::__construct($container);
	}

	public function list(ServerRequestInterface $request): ResponseInterface
	{
		$filter = array_filter($request->getQueryParams());

		return $this->render('client/list.twig', [
			'clients' => $this->clientService->getList($filter),
			'statuses' => ClientStatus::asArray(),
			'filter' => $filter,
		]);
	}

	public function view(ServerRequestInterface $request, int $id): ResponseInterface
	{
		$client = $this->clientService->findById($id);

		if ($client === null) {
			$this->show404();
		}

		return $this->render('client/view.twig', [
			'client' => $client,
			'statuses' => ClientStatus::asArray(),
			'notes' => $this->clientService->getNotesByClientId($client->id),
		]);
	}

	public function add(ServerRequestInterface $request): ResponseInterface
	{
		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->clientService->create($params);
				$this->flash->success('Клиент успешно создан');
				
				return $this->redirect(urlFor('clients'));
			} catch (Exception $ex) {
				$this->flash->error($ex->getMessage());
			}
		}

		return $this->render('client/form.twig', [
			'client' => null,
			'statuses' => ClientStatus::asArray(),
			'request' => ($params ?? []),
		]);
	}

	public function edit(ServerRequestInterface $request, int $id): ResponseInterface
	{
		$client = $this->clientService->findById($id);

		if ($client === null) {
			$this->show404();
		}

		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->clientService->update($id, $params);
				$this->flash->success('Клиент успешно обновлён');

				return $this->redirect(urlFor('clients-view', ['id' => $id]));
			} catch (Exception $ex) {
				$this->flash->error($ex->getMessage());
			}
		}

		return $this->render('client/form.twig', [
			'client' => $client,
			'statuses' => ClientStatus::asArray(),
			'request' => ($params ?? []),
		]);
	}

	public function archive(ServerRequestInterface $request, int $id): ResponseInterface
	{
		$client = $this->clientService->findById($id);

		if ($client === null) {
			$this->show404();
		}

		$this->clientService->archive($id);
		$this->flash->success('Клиент архивирован');
		return $this->redirect(urlFor('clients'));
	}

}
