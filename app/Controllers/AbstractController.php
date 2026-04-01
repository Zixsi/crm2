<?php

namespace App\Controllers;

use App\Shared\FlashMessage;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use function redirect;

abstract class AbstractController
{

	private Twig $view;
	protected FlashMessage $flash;

	public function __construct(
		private ContainerInterface $container
	)
	{
		$this->view = $container->get(Twig::class);
		$this->flash = $container->get(FlashMessage::class);
	}

	final protected function render(string $template, array $data = []): ResponseInterface
	{
		$data['flash'] = $this->flash->getMessages();
		return $this->view->render(new Response(), $template, $data);
	}


	final protected function redirect(string $uri): ResponseInterface
	{
		redirect($uri);
		return $this->createResponse('');
	}

	final protected function show404(): void
	{
		throw $this->container->get(HttpNotFoundException::class);
	}

	final protected function createResponse(string $data, int $status = 200): ResponseInterface
	{
		return new Response($status, [], $data);
	}

}
