<?php

namespace App\Shared;

use Odan\Session\FlashInterface;

class FlashMessage
{
	private const SUCCESS = 'success';
	private const ERROR = 'error';
	private const KEYS = [
		self::SUCCESS, self::ERROR
	];

	public function __construct(
		private FlashInterface $flash
	) {

	}

	/**
	 * @return array<string, mixed>
	 */
	public function getMessages(): array
	{
		$messages = [];
		
		foreach (self::KEYS as $key) {
			$value = $this->flash->get($key);

			if ($value !== null) {
				$messages[$key] = implode(' ', $value);
			}
		}
		
		return $messages;
	}

	public function success(string $message): void
	{
		$this->set(self::SUCCESS, $message);
	}

	public function error(string $message): void
	{
		$this->set(self::ERROR, $message);
	}

	private function set(string $name, string $message): void
	{
		$this->flash->set($name, [$message]);
	}
}