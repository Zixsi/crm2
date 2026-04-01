<?php

use App\Services\User\UserService;
use Config\Config;
use DI\ContainerBuilder;
use DI\get;
use GuzzleHttp\Psr7\ServerRequest;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Odan\Session\FlashInterface;
use Odan\Session\Middleware\SessionStartMiddleware;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;
use Twig\TwigFunction;

use function DI\autowire;

$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAttributes(false);

$builder->addDefinitions([
	ServerRequestInterface::class => function () {
		return ServerRequest::fromGlobals();
	},
	LoggerInterface::class => function () {
		$logger = new Logger('app');
		$formatter = new LineFormatter(
			"%datetime%	%channel%	%level_name%	%message%	%context%\n",
			'Y-m-d H:i:s'
		);

		$debugHandler = new RotatingFileHandler(LOG_DIR . '/debug.log', 10, Level::Debug, true, 0777);
		$debugHandler->setFormatter($formatter);
		$logger->pushHandler(
			new FilterHandler($debugHandler, Level::Debug, Level::Warning, true)
		);

		$errorHandler = new RotatingFileHandler(LOG_DIR . '/error.log', 10, Level::Error, true, 0777);
		$errorHandler->setFormatter($formatter);
		$logger->pushHandler(
			new FilterHandler($errorHandler, Level::Error, Level::Emergency, true)
		);

		return $logger;
	},
	\PDO::class => function () {
		$mysqlConfig = Config::getMysqlConfig();
		$dsn = sprintf(
			'mysql:host=%s;dbname=%s;port=%s;charset=utf8',
			$mysqlConfig['host'],
			$mysqlConfig['dbname'],
			$mysqlConfig['port']
		);

		return 
			new \PDO(
				$dsn, 
				$mysqlConfig['user'], 
				$mysqlConfig['password'],
				[
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    				\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
				]
			);
	},
	Twig::class => function () {
		$twig = Twig::create(VIEW_DIR, ['cache' => false, 'debug' => true,]);
		$twig->addExtension(new DebugExtension());
		$twig->getEnvironment()->addFunction(new TwigFunction('urlFor', 'urlFor'));

		return $twig;
	},
	SessionManagerInterface::class => function (ContainerInterface $container) {
		return $container->get(SessionInterface::class);
	},
	FlashInterface::class => function (SessionInterface $session) {
		return $session->getFlash();
	},
	SessionInterface::class => function () {
		return 
			new PhpSession([
				'name' => 'app',
				'lifetime' => (86400 * 2),
				'path' => null,
				'domain' => null,
				'secure' => false,
				'httponly' => true,
				'cache_limiter' => 'nocache',
			]);
	},
	

	// Services
	UserService::class => autowire(),
]);

return $builder->build();
