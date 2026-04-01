<?php

use Psr\Container\ContainerInterface;
use Slim\App;

function container(): ContainerInterface
{
	/** @var ContainerInterface $instance */
	static $instance = null;

	if ($instance === null) {
		$instance = require_once CONFIG_DIR . '/di.php';
	}

	return $instance;
}

function redirect(string $uri): void
{
	header('Location: ' . $uri);
	exit();
}

function objectToArray($data): array
{
	if (is_array($data) || is_object($data)) {
		$result = [];

		foreach ($data as $key => $value) {
			$result[$key] = objectToArray($value);
		}

		return $result;
	}

	return $data;
}

/**
 * @psalm-suppress ForbiddenCode
 */
function debug($data): void
{
	if (PHP_SAPI !== 'cli') {
		echo '<pre>';
	}

	var_dump($data);

	if (PHP_SAPI !== 'cli') {
		echo '<pre>';
	}
}

function urlFor(string $routeName, array $data = [], array $queryParams = []): string
{
	/** @var App $app */
	$app = container()->get(App::class);
	$routeParser = $app->getRouteCollector()->getRouteParser();
	
	return $routeParser->urlFor($routeName, $data, $queryParams);
}

function redirectFor(string $routeName, array $data = [], array $queryParams = []): void
{
	redirect(urlFor($routeName, $data, $queryParams));
}