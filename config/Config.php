<?php

namespace Config;

final class Config
{
	public static function getEnv(): string
	{
		return (string) ($_ENV['ENV'] ?? 'prod');
	}

	public static function getMysqlConfig(): array
	{
		return [
			'host' => (string) ($_ENV['DB_HOST'] ?? 'localhost'),
			'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
			'user' => (string) ($_ENV['DB_USER'] ?? 'user'),
			'password' => (string) ($_ENV['DB_PASS'] ?? 'password'),
			'dbname' => (string) ($_ENV['DB_NAME'] ?? 'dbname'),
		];
	}

}
