<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

use CarmeloSantana\EnderHive\Host\Status;
use CarmeloSantana\EnderHive\Tools\Utils;
use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;

abstract class Base implements Host
{
    /**
     * Requires post ID.
     * Sets initial status starts the command interface.
     *
     * @param  int $post_id
     * @return void
     */
    public function __construct(public int $post_id)
    {
        if (get_post_status($post_id)) {
            $this->status = Status::FOUND;
            $this->init();
        } else {
            $this->status = Status::NOT_FOUND;
        }
    }

    public function getId(): int
    {
        return $this->post_id;
    }

    public function getIp(): string
    {
        return gethostbyname(gethostname());
    }

    /**
     * Get last known status.
     *
     * @return int Status code.
     */
    public function getLastKnownState(): int
    {
        $state = get_post_meta($this->post_id, '_last_known_state', true);

        return $state ? (int) $state : Status::UNKNOWN;
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
     * Retrieves the post data of this instance.
     *
     * @return WP_Post
     */
    public function getPost(): \WP_Post
    {
        return $this->post;
    }

    /**
     * Retrieves the server properties of this instance.
     *
     * @return array
     */
    public function getServerProperties(): array
    {
        return $this->server_properties;
    }

    public function getServerSettings(): array
    {
        return $this->server_settings;
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
        $this->initServerSettings();
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

    public function initServerSettings(): void
    {
        $this->server_settings = [
            'autorestart' => carbon_get_post_meta($this->post_id, 'autorestart'),
        ];
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

    public function query(): array
    {
        $query = new MinecraftQuery();

        try {
            $query->ConnectBedrock($this->getIp(), $this->getPortIp4());
        } catch (MinecraftQueryException $e) {
            // Update status and last known state.
            $this->updateStatus(Status::SERVICE_UNAVAILABLE);

            return [];
        }

        // Remove unwanted data.
        $response = $query->GetInfo();
        unset($response['NintendoLimited'], $response['IPv4Port'], $response['IPv6Port'], $response['Extra']);

        // Return $response with lowercase keys.
        return array_change_key_case($response, CASE_LOWER);
    }

    /**
     * Update current status and _last_known_state meta.
     *
     * @param  mixed $status
     * @return void
     */
    public function updateStatus(int $status = 0): void
    {
        if ($status > 0) {
            $this->status = $status;
        } elseif ($this->isRunning()) {
            $this->status = Status::OK;
        } else {
            $this->status = Status::NO_CONTENT;
        }

        $this->updateLastKnownState();
    }

    /**
     * Updates _last_known_state meta with the current status.
     *
     * @return void
     */
    public function updateLastKnownState(): void
    {
        update_post_meta($this->post_id, '_last_known_state', $this->getStatus());
    }
}
