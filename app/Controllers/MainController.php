<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MainController extends AbstractController
{

	public function dashboard(ServerRequestInterface $request): ResponseInterface
	{	
		return $this->render('main/dashboard.twig', []);
	}
}
