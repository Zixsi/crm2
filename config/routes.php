<?php

use App\Controllers\AuthenticationController;
use App\Controllers\ClientController;
use App\Controllers\ClientNoteController;
use App\Controllers\MainController;
use App\Controllers\ProjectController;
use App\Controllers\TaskController;
use App\Services\Slim\AuthorizationMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
	$container = $app->getContainer();

	$app->add(SessionStartMiddleware::class);
	$app->any('/login', [AuthenticationController::class, 'login'])
			->setName('login');

	$app->any('/logout', [AuthenticationController::class, 'logout'])
		->setName('logout');

	$app->group('', function (RouteCollectorProxy $group) {
		$group->any('/', [MainController::class, 'dashboard'])->setName('main');

		$group->group('/clients', function (RouteCollectorProxy $group) {
			$group->any('[/]', [ClientController::class, 'list'])->setName('clients');
			$group->any('/add[/]', [ClientController::class, 'add'])->setName('clients-add');
			$group->any('/{id}[/]', [ClientController::class, 'view'])->setName('clients-view');
			$group->any('/{id}/edit[/]', [ClientController::class, 'edit'])->setName('clients-edit');
			$group->any('/{id}/archive[/]', [ClientController::class, 'archive'])->setName('clients-archive');
		});

		$group->group('/clients/{clientId}/notes', function (RouteCollectorProxy $group) {
			$group->any('[/]', [ClientNoteController::class, 'list'])->setName('client-notes');
			$group->any('/add[/]', [ClientNoteController::class, 'add'])->setName('client-notes-add');
			$group->any('/{id}/edit[/]', [ClientNoteController::class, 'edit'])->setName('client-notes-edit');
		});

		$group->group('/projects', function (RouteCollectorProxy $group) {
			$group->any('[/]', [ProjectController::class, 'list'])->setName('projects');
			$group->any('/add[/]', [ProjectController::class, 'add'])->setName('projects-add');
			$group->any('/{id}[/]', [ProjectController::class, 'view'])->setName('projects-view');
			$group->any('/{id}/edit[/]', [ProjectController::class, 'edit'])->setName('projects-edit');
			$group->any('/{id}/archive[/]', [ProjectController::class, 'archive'])->setName('projects-archive');
		});

		$group->group('/projects/{projectId}/tasks', function (RouteCollectorProxy $group) {
			$group->any('/add[/]', [TaskController::class, 'add'])->setName('tasks-add');
			$group->any('/{id}[/]', [TaskController::class, 'view'])->setName('tasks-view');
			$group->any('/{id}/edit[/]', [TaskController::class, 'edit'])->setName('tasks-edit');
			$group->any('/{id}/archive[/]', [TaskController::class, 'archive'])->setName('tasks-archive');
		});

		$group->group('/tasks', function (RouteCollectorProxy $group) {
			$group->any('[/]', [TaskController::class, 'list'])->setName('tasks');
			$group->any('/{id}/archive[/]', [TaskController::class, 'archive'])->setName('tasks-archive-list');
		});
	})->add($container->get(AuthorizationMiddleware::class));
};
