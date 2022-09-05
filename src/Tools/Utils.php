<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Tools;

class Utils
{    
    /**
     * Converts array to ini file.
     *
     * @param array $array Array of properties to convert.
     * @return string
     */
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
     * Download wrapper ends execution if file not found.
     *
     * @param string $url
     * @param string $filename
     * @return void
     */
    public static function download(string $url, string $filename): void
    {
        $return = wp_remote_get(
            $url,
            [
                'timeout' => 15,
                'stream' => true,
                'filename' => $filename,
            ]
        );

        // Check if file was downloaded.
        if (!file_exists($filename)) {
            wp_die(__('Failed to download file.', ENDER_HIVE));
        }
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

    
    /**
     * Converts an array a string of items separated by line breaks.
     *
     * @param array $list Array of items.
     * @return string
     */
    public static function list(array $list): string
    {
        $output = '';
        foreach ($list as $item) {
            $output .= $item . PHP_EOL;
        }
        return $output;
    }
}
