<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

use CarmeloSantana\EnderHive\Host\Status;
use CarmeloSantana\EnderHive\Tools\Utils;

abstract class Base implements Host
{
    /**
     * Requires post ID.
     * Sets initial status starts the command interface.
     *
     * @param  int $post_id
     * @return void
     */
    public function __construct(protected int $post_id)
    {
        if (get_post_status($post_id)) {
            $this->status = Status::FOUND;
            $this->init();
        } else {
            $this->status = Status::NOT_FOUND;
        }
    }

    /**
     * Create new files with default values.
     *
     *  'plugin_list_yml' => [
     *      'filename' => 'plugin_list',
     *      'extension' => 'yml',
     *      'callback' => [__CLASS__, 'getAsset'],
     *  ],
     *
     * @param  mixed $files
     * @return void
     */
    public function newFiles(array $files): void
    {
        foreach ($files as $file) {
            // Build file path.
            $filename = $file['filename'];
            if (isset($file['extension'])) {
                $filename .= '.' . $file['extension'];
            }
            $file_path = $this->getPath($filename);

            switch ($filename) {
                case 'server.properties':
                    $file['content'] = call_user_func($file['callback'], $this->post_id);
                    break;

                default:
                    // If callback not provide an empty string for the file content.
                    $file['content'] = (isset($file['callback']) and is_callable($file['callback'])) ? call_user_func($file['callback'], $filename) : '';
                    break;
            }

            file_put_contents($file_path, $file['content']);
        }
    }

    /**
     * Get instance path but with instance ID.
     *
     * @param  array $file [filename => extension]
     * @return string
     */
    public function getPath(array|string $file = []): string
    {
        return Server::getInstancePath($this->post_id, $file);
    }

    /**
     * Returns IPv4 port from $this->server_properties.
     *
     * @return int
     */
    public function getPortIp4(): int
    {
        return (int) $this->server_properties['server-port'];
    }

    /**
     * Returns IPv6 port from $this->server_properties.
     *
     * @return int
     */
    public function getPortIp6(): int
    {
        return $this->isIp6Enabled() ? (int) $this->server_properties['server-portv6'] : 0;
    }

    /**
     * Returns status variable.
     *
     * @return int Status constant.
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Initialize components needed for the host.
     * Best practice to update status after this.
     *
     * @return void
     */
    public function init(): void
    {
        $this->initPost();
        $this->initServerProperties();
        $this->updateStatus();
    }

    /**
     * Retrieves post data and sets it to the class.
     *
     * @return void
     */
    public function initPost(): void
    {
        $this->post = get_post($this->post_id);
    }

    /**
     * Check if IPv6 is enabled via $this->server_properties.
     *
     * @return bool
     */
    public function isIp6Enabled(): bool
    {
        return Utils::isEnabled($this->server_properties['enable-ipv6']);
    }
}
