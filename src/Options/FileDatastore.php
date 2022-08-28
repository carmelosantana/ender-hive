<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Options;

use Carbon_Fields\Field\Field;
use CarmeloSantana\EnderHive\Instance;
use CarmeloSantana\EnderHive\Config\Defaults;

class FileDatastore extends \Carbon_Fields\Datastore\Datastore
{
	/**
	 * Initialization tasks for concrete datastores.
	 **/
	public function init()
	{
	}

	/**
	 * Get a raw database query results array for a field
	 *
	 * @param Field $field The field to retrieve value for.
	 * @param array $storage_key_patterns
	 * @return \stdClass[] Array of {key, value} objects
	 */
	public function load(Field $field)
	{
		if (isset(Defaults::files()[$field->get_base_name()])) {
			$file = Defaults::files()[$field->get_base_name()];
			$file_path = Instance::getConfigPath($this->object_id, $file);

			if (file_exists($file_path)) {
				$content = file_get_contents($file_path);
				return $content;
			}
		}
	}

	public function save(Field $field)
	{
		if (isset(Defaults::files()[$field->get_base_name()])) {
			$file = Defaults::files()[$field->get_base_name()];
			$file_path = Instance::getConfigPath($this->object_id, $file);

			ray($field);
			if (file_exists($file_path)) {
				return file_put_contents($file_path, $field->get_value());
			}
		}
	}

	public function delete(Field $field)
	{
		ray($this);
	}
}
