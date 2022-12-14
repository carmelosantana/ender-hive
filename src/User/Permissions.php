<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\User;

use CarmeloSantana\EnderHive\Host\Status;

class Permissions
{
    public const SUPER_ADMIN = 'manage_network';
    public const ADMIN = 'manage_options';
    public const READ = 'read';
    public const WRITE = 'publish_posts';

    /**
     * Provides status code for authorization.
     *
     * @return int Status code.
     */
    public static function authorizationStatusCode(): int
    {
        $status = Status::UNAUTHORIZED;

        if (is_user_logged_in()) {
            $status = Status::FORBIDDEN;
        }

        return $status;
    }
}
