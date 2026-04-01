<?php

namespace App\Controllers;

use App\Services\Client\ClientService;
use App\Services\Project\ProjectService;
use App\Services\Project\ProjectStatus;
use App\Services\Project\TaskService;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProjectController extends AbstractController
{
	public function __construct(
		private ClientService $clientService,
		private ProjectService $projectService,
		private TaskService $taskService,
		ContainerInterface $container,
	) {
		parent::__construct($container);
	}

	public function list(ServerRequestInterface $request): ResponseInterface
	{
		$filter = array_filter($request->getQueryParams());

		return $this->render('project/list.twig', [
			'clients' => $this->clientService->getClientsForSelect(),
			'projects' => $this->projectService->getList($filter),
			'statuses' => ProjectStatus::asArray(),
			'filter' => $filter,
		]);
	}

	public function view(ServerRequestInterface $request, int $id): ResponseInterface
	{
		$project = $this->projectService->findById($id);

		if ($project === null) {
			$this->show404();
		}
		
		return $this->render('project/view.twig', [
			'project' => $project,
			'tasks' => $this->taskService->getList(['project_id' => $project->id]),
			'clients' => $this->clientService->getClientsForSelect(),
		]);
	}

	public function add(ServerRequestInterface $request): ResponseInterface
	{
		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->projectService->create($params);
				$this->flash->success('Проект успешно создан');
				
				return $this->redirect(urlFor('projects'));
			} catch (Exception $ex) {
				$this->flash->error($ex->getMessage());
			}
		}

		return $this->render('project/form.twig', [
			'project' => null,
			'clients' => $this->clientService->getClientsForSelect(),
			'request' => ($params ?? []),
		]);
	}

	public function edit(ServerRequestInterface $request, int $id): ResponseInterface
	{
		$project = $this->projectService->findById($id);

		if ($project === null) {
			$this->show404();
		}

		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->projectService->update($id, $params);
				$this->flash->success('Проект успешно обновлён');

				return $this->redirect(urlFor('projects-view', ['id' => $id]));
			} catch (Exception $ex) {
				$this->flash->error($ex->getMessage());
			}
		}

		return $this->render('project/form.twig', [
			'project' => $project,
			'clients' => $this->clientService->getClientsForSelect(),
			'request' => ($params ?? []),
		]);
	}

	public function archive(ServerRequestInterface $request, int $id): ResponseInterface
	{
		$project = $this->projectService->findById($id);

		if ($project === null) {
			$this->show404();
		}

		$this->projectService->archive($id);
		$this->flash->success('Проект архивирован');
		return $this->redirect(urlFor('projects'));
	}

}
