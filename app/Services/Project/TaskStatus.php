<?php

namespace App\Services\Project;

enum TaskStatus: string
{
	case NEW = 'new';
	case AT_WORK = 'at_work';
	case COMPLETED = 'completed';
	
	public function getLabel(): string
	{
		return match ($this) {
			self::NEW => 'Новая',
			self::AT_WORK => 'В работе',
			self::COMPLETED => 'Выполнена',
		};
	}

	public function getBadgeClass(): string
	{
		return match ($this) {
			self::NEW => 'bg-primary',
			self::AT_WORK => 'bg-warning text-dark',
			self::COMPLETED => 'bg-success',
		};
	}

	/**
	 * @return array<string, string>
	 */
	public static function asArray(): array
	{
		return [
			self::NEW->value => self::NEW->getLabel(),
			self::AT_WORK->value => self::AT_WORK->getLabel(),
			self::COMPLETED->value => self::COMPLETED->getLabel(),
		];
	}
}
