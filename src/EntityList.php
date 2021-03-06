<?php

namespace PopUnderAdvertiser;

use PopUnderAdvertiser\Exception\Exception;

class EntityList extends \ArrayIterator
{
	public const NAME_ALL_LANGUAGES = 'All languages';
	public const NAME_ALL_TOPICS 	= 'All categories';
	public const NAME_ALL_LOCATIONS = 'All locations';
	public const NAME_ALL_BROWSERS 	= 'All browsers';

	public function getByName(string $name): array
	{
		foreach ($this as $value) {
			if (0 === \strcasecmp(trim($value['name']), $name)) {
				return $value;
			}
		}
		throw new Exception('Option with name "'.$name.'" not found');
	}

	public function getById(int $id): ?array
	{
		foreach ($this as $value) {
			if ((int) $value['id'] === $id) {
				return $value;
			}
		}
		throw new Exception('Option with id "'.$id.'" not found');
	}
}