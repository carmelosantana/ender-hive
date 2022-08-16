<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Utils
{
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

    /**
     * Check "Booleanic" Conditions :)
     * https://www.php.net/manual/en/function.is-bool.php#124179
     *
     * @param  [mixed]  $variable  Can be anything (string, bol, integer, etc.)
     * @return [boolean]           Returns TRUE  for "1", "true", "on" and "yes"
     *                             Returns FALSE for "0", "false", "off" and "no"
     *                             Returns NULL otherwise.
     */
    public static function isEnabled($variable): bool
    {
        return filter_var($variable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    // Static function check if port is free.
    public static function isPortFree(int $port, string $host = '127.0.0.1'): bool
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, 1);
        if ($socket) {
            fclose($socket);
            return false;
        }
        return true;
    }
}
