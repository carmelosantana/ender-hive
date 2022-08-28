<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Config;

use CarmeloSantana\EnderHive\Config\Generate;
use CarmeloSantana\EnderHive\Instance;
use Symfony\Component\Yaml\Yaml;

class Create
{
    public function __construct(private \WP_Post $post)
    {
    }

    public function all(): void
    {
        foreach (Defaults::files() as $file) {
            $file_path = Instance::getPath($this->post->ID) . DIRECTORY_SEPARATOR . $file['filename'] . (isset($file['extension']) ? '.' . $file['extension'] : '');

            // If callback not provide an empty string for the file content.
            $file['content'] = (isset($file['callback']) and is_callable($file['callback'])) ? call_user_func($file['callback']) : '';

            switch ($file['extension'] ?? null) {
                case 'properties':
                    $file['content'] = Generate::arrayToIni($file['content']);
                    break;

                case 'yml':
                    $file['content'] = Yaml::dump($file['content']);
                    break;
            }

            file_put_contents($file_path, $file['content']);
        }
    }
}
