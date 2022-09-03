<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

class Server
{
    /**
     * Server dynamically implements instances of Host.
     *
     * @param  mixed $post_id Post ID of instance we want to interact with.
     * @return void
     */
    public function __construct(private int $post_id)
    {
        $this->initHost($post_id);
    }

    /**
     * Magic method to dynamically call methods on the host.
     *
     * @param  mixed $name Method name.
     * @param  mixed $arguments Array of arguments.
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->host, $name], $arguments);
    }

    /**
     * Initialize the host container or communication wrapper for the server binary.
     *
     * @return void
     */
    private function initHost(): void
    {
        switch (carbon_get_post_meta($this->post_id, 'host_type')) {
            default:
                // Default host is PocketMineMP-MP via screen
                $this->host = new PocketMineMP\Server($this->post_id);
                break;
        }

        // Is $this->host implemented from Base?
        if (!$this->host instanceof Base) {
            wp_die('Host is not implemented from Base');
        }
    }

    /**
     * Helper to get instance path without initializing the host.
     *
     * @param  mixed $post_id Post ID of instance we want to interact with.
     * @param  mixed $file [filename => file, extension => txt]
     * @return string
     */
    public static function getInstancePath(int|string $post_id, array $file = []): string
    {
        $path = DIRECTORY_SEPARATOR;

        if (!empty($file)) {
            $path .= $file['filename'] . (isset($file['extension']) ? '.' . $file['extension'] : '');
        }

        return carbon_get_theme_option('instance_path') . DIRECTORY_SEPARATOR . (string) $post_id . $path;
    }
}
