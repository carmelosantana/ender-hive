<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Options;

use Carbon_Fields\Field\Field;
use CarmeloSantana\EnderHive\Config\Defaults;
use CarmeloSantana\EnderHive\Host\Server;

class FileDatastore extends \Carbon_Fields\Datastore\Datastore
{
	public function fileInit(Field $field): void
	{
		$this->file = Defaults::files()[$field->get_base_name()];
		$this->file_path = Server::getInstancePath($this->object_id, $this->file);
	}

	/**
	 * Initialization tasks for concrete datastores.
	 **/
	public function init()
	{
	}

	/**
	 * Get file contents of the given field.
	 *
	 * @param Field $field The field to retrieve value for.
	 * @return string Config file contents.
	 */
	public function load(Field $field): string
	{
		if (Defaults::files()[$field->get_base_name()]) {
			$this->fileInit($field);
			if (file_exists($this->file_path)) {
				return file_get_contents($this->file_path);
			}
		}
	}

	/**
	 * Replace file contents of the given field.
	 *
	 * @param Field $field The field to retrieve value for.
	 * @return int File size.
	 */
	public function save(Field $field): int
	{
		if (Defaults::files()[$field->get_base_name()]) {
			$this->fileInit($field);
			if (file_exists($this->file_path)) {
				return file_put_contents($this->file_path, $field->get_full_value()[0]['value']);
			}
		}
	}

	/**
	 * Empty value triggers delete updates file with empty value.
	 *
	 * @param Field $field The field to retrieve value for.
	 * @return int File size.
	 */
	public function delete(Field $field): int
	{
		return $this->save($field);
	}
}
