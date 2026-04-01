<?php

use App\Services\Slim\Error\TwigErrorRenderer;
use App\Services\Slim\Handlers\ApplicationErrorHandler;
use DI\Bridge\Slim\Bridge;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

/** @var ContainerInterface $container */
$container = require_once '../app/bootstrap.php';

$app = Bridge::create($container);

$errorMiddleware = $app->addErrorMiddleware(
	true, 
	true, 
	true, 
	$container->get(LoggerInterface::class)
);

/** @var TwigErrorRenderer $errorRenderer */
$errorRenderer = $container->get(TwigErrorRenderer::class);
$errorRenderer->setTemplate('error/default.twig');

$errorHandler = new ApplicationErrorHandler(
	$app->getCallableResolver(), 
	$app->getResponseFactory(),
	$container->get(LoggerInterface::class)
);
$errorHandler->setIgnoreToLogExceptions([
	HttpNotFoundException::class,
	HttpForbiddenException::class,
	HttpMethodNotAllowedException::class,
]);
$errorHandler->registerErrorRenderer('text/html', $errorRenderer);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$routes = require_once CONFIG_DIR . '/routes.php';
$routes($app);

$app->run($container->get(ServerRequestInterface::class));