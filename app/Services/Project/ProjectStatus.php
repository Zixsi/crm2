<?php

namespace App\Services\Project;

enum ProjectStatus: string
{
	case ACTIVE = 'active';
	case COMPLETED = 'completed';

	public function getLabel(): string
	{
		return match ($this) {
			self::ACTIVE => 'Активный',
			self::COMPLETED => 'Завершенный',
		};
	}

	public function getBadgeClass(): string
	{
		return match ($this) {
			self::ACTIVE => 'bg-success',
			self::COMPLETED => 'bg-warning text-dark',
		};
	}

	/**
	 * @return array<string, string>
	 */
	public static function asArray(): array
	{
		return [
			self::ACTIVE->value => self::ACTIVE->getLabel(),
			self::COMPLETED->value => self::COMPLETED->getLabel(),
		];
	}
}
