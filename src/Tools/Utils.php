<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Tools;

class Utils
{
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
}
