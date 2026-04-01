<?php

namespace App\Controllers;

use App\Services\Project\ProjectService;
use App\Services\Project\TaskService;
use App\Services\Project\TaskStatus;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TaskController extends AbstractController
{
	public function __construct(
		private ProjectService $projectService,
		private TaskService $taskService,
		ContainerInterface $container,
	) {
		parent::__construct($container);
	}

	public function list(ServerRequestInterface $request): ResponseInterface
	{
		$filter = array_filter($request->getQueryParams());

		return $this->render('project-task/list.twig', [
			'items' => $this->taskService->getList($filter),
			'projects' => $this->projectService->getListForSelect(),
			'statuses' => TaskStatus::asArray(),
			'filter' => $filter,
		]);
	}

	public function view(ServerRequestInterface $request, int $id): ResponseInterface
	{
		$task = $this->taskService->findById($id);

		if ($task === null) {
			$this->show404();
		}
		
		return $this->render('project-task/view.twig', [
			'item' => $task,
			'projects' => $this->projectService->getListForSelect(),
		]);
	}

	public function add(ServerRequestInterface $request, int $projectId): ResponseInterface
	{
		$project = $this->projectService->findById($projectId);

		if ($project === null) {
			$this->show404();
		}

		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->taskService->create($projectId, $params);
				$this->flash->success('Задача успешно создана');
				
				return $this->redirect(urlFor('projects-view', ['id' => $project->id]));
			} catch (Exception $ex) {
				$this->flash->error($ex->getMessage());
			}
		}

		return $this->render('project-task/form.twig', [
			'item' => null,
			'project' => $project,
			'statuses' => TaskStatus::asArray(),
			'request' => ($params ?? []),
		]);
	}

	public function edit(ServerRequestInterface $request, int $projectId, int $id): ResponseInterface
	{
		$project = $this->projectService->findById($projectId);

		if ($project === null) {
			$this->show404();
		}

		$task = $this->taskService->findById($id);

		if ($task === null) {
			$this->show404();
		}

		if ($request->getMethod() === 'POST') {
			try {
				$params = (array) $request->getParsedBody();
				$this->taskService->update($id, $params);
				$this->flash->success('Задача успешно обновлена');

				return $this->redirect(urlFor('tasks-view', ['projectId' => $task->projectId,'id' => $id]));
			} catch (Exception $ex) {
				$this->flash->error($ex->getMessage());
			}
		}

		return $this->render('project-task/form.twig', [
			'item' => $task,
			'statuses' => TaskStatus::asArray(),
			'request' => ($params ?? []),
		]);
	}

	public function archive(ServerRequestInterface $request, int $id): ResponseInterface
	{
		$task = $this->taskService->findById($id);

		if ($task === null) {
			$this->show404();
		}

		$this->taskService->archive($id);
		$this->flash->success('Задача архивирована');

		if ($request->getAttribute('projectId')) {
			return $this->redirect(urlFor('projects-view', ['id' => $task->projectId]));
		} else {
			return $this->redirect(urlFor('tasks'));
		}
	}

}
