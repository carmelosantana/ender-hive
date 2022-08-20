<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use CarmeloSantana\EnderHive\Host\Screen;

class Command
{
    public function __construct(private int $post_id, private string $nonce = '')
    {
        if (!$this->checkAuth()) {
            return new WP_Error('forbidden', __('You cannot access this resource.', ENDER_HIVE), ['status' => Permissions::authorizationStatusCode()]);
        }

        $this->initHost($post_id);
    }

    public function checkAuth(string $key = 'command'): bool
    {
        if (current_user_can(Permissions::WRITE)) {
            return true;
        }

        if (!empty($this->nonce)) {
            $key = $key . '_' . $this->post_id;

            if (wp_verify_nonce($this->nonce, $key)) {
                return true;
            }
        }

        return false;
    }

    public function initHost(): void
    {
        switch (Options::get('ender_hive_host')) {
            default:
                $this->host = new Screen($this->post_id);
                break;
        }
    }

    public function start(): int
    {
        return call_user_func([$this->host, 'start']);
    }

    public function stop(): int
    {
        return call_user_func([$this->host, 'stop']);
    }
}
