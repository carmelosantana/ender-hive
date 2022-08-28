<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Config;

class Generate
{
    public function list(array $list): string
    {
        $output = '';
        foreach ($list as $item) {
            $output .= $item . PHP_EOL;
        }
        return $output;
    }

    public function serverProperties(): array
    {
        $properties = [];

        foreach (Defaults::serverProperties() as $key => $value) {
            if (is_string($key)) {
                $meta = Options::getMeta($this->post->ID, $key);
            }

            if ($meta) {
                $properties[$key] = $meta;
            } else {
                $properties[$key] = $value;
            }
            $properties[$key] = $meta;
        }

        return $properties;
    }

    public static function arrayToIni(array $array): string
    {
        $ini = '';
        foreach ($array as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'on' : 'off';
            }

            // If key is integer treat as comment.
            if (is_int($key)) {
                $ini .= '#' . $value . PHP_EOL;
            } else {
                $ini .= $key . '=' . $value . PHP_EOL;
            }
        }
        return $ini;
    }
}
