<?php

namespace PopUnderAdvertiser;

class EntityList extends \ArrayIterator
{
	public const NAME_ALL_LANGUAGES = 'All languages';
	public const NAME_ALL_TOPICS 	= 'All categories';
	public const NAME_ALL_LOCATIONS = 'All locations';
	public const NAME_ALL_BROWSERS 	= 'All browsers';

	public function getByName(string $name): ?array
	{
		foreach ($this as $value) {
			if (0 === \strcasecmp($value['name'], $name)) {
				return $value;
			}
		}
		return null;
	}

	public function getById(int $id): ?array
	{
		foreach ($this as $value) {
			if ((int) $value['id'] === $id) {
				return $value;
			}
		}
		return null;
	}
}