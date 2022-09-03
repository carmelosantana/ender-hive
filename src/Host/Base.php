<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

use CarmeloSantana\EnderHive\Config\Defaults;
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
     * Get instance path but with instance ID.
     *
     * @param  array $file [filename => extension]
     * @return string
     */
    public function getPath(array $file = []): string
    {
        return Server::getInstancePath($this->post_id, $file);
    }
    
    /**
     * Returns IPv4 port from $server_properties.
     *
     * @return int
     */
    public function getPortIp4(): int
    {
        return (int) $this->server_properties['server-port'];
    }

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
     * Check if IPv6 is enabled via $server_properties.
     *
     * @return bool
     */
    public function isIp6Enabled(): bool
    {
        return Utils::isEnabled($this->server_properties['enable-ipv6']);
    }
    
    /**
     * Populates $server_properties with the server.properties options.
     *
     * @return void
     */
    public function initServerProperties(): void
    {
        $this->server_properties = [];

        foreach (Defaults::serverProperties() as $property => $default) {
            // Property is a comment.
            if (is_int($property)) {
                continue;
            }

            $this->server_properties[$property] = carbon_get_post_meta($this->post_id, $property);
        }
    }
}
