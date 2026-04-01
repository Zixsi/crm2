<?php

namespace App\Services\Client;

enum ClientStatus: string
{
	case NEW = 'new';
	case NEGOTIATION = 'negotiation';
	case CONTRACT = 'contract';
	case ARCHIVE = 'archive';

	public function getLabel(): string
	{
		return match ($this) {
			self::NEW => 'Новый',
			self::NEGOTIATION => 'Переговоры',
			self::CONTRACT => 'Договор',
			self::ARCHIVE => 'Архив',
		};
	}

	public function getBadgeClass(): string
	{
		return match ($this) {
			self::NEW => 'bg-primary',
			self::NEGOTIATION => 'bg-warning text-dark',
			self::CONTRACT => 'bg-success',
			self::ARCHIVE => 'bg-secondary',
		};
	}

	/**
	 * @return array<string, string>
	 */
	public static function asArray(): array
	{
		return [
			self::NEW->value => self::NEW->getLabel(),
			self::NEGOTIATION->value => self::NEGOTIATION->getLabel(),
			self::CONTRACT->value => self::CONTRACT->getLabel(),
			self::ARCHIVE->value => self::ARCHIVE->getLabel(),
		];
	}
}
