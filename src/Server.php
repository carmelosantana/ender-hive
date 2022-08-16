<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

class Server
{
    const START = 'EnderHive\Instance\start';

    const STOP = 'EnderHive\Instance\stop';

    public function __construct(private int $post_id)
    {
        // Available actions
        add_action(self::START, [$this, 'start']);
        add_action(self::STOP, [$this, 'stop']);

        // Setup server interactions
        $this->initPost();
        $this->initServerProperties();

        return $this;
    }

    public function command()
    {
        if (!isset($this->command)) {
            $this->command = new Command($this->post->post_name);
        }

        return $this->command;
    }

    public function initPost(): void
    {
        $this->post = get_post($this->post_id);
    }

    public function initServerProperties(): void
    {
        $this->server_properties = [];

        foreach (Config::serverProperties() as $property => $default) {
            // Property is a comment.
            if (is_int($property)) {
                continue;
            }

            $this->server_properties[$property] = carbon_get_post_meta($this->post_id, $property);
        }
    }

    public function getPath(): string
    {
        return Instance::getPath($this->post->post_name);
    }

    public function getPortIp4()
    {
        return $this->server_properties['server-port'];
    }

    public function getPortIp6()
    {
        return $this->isIp6Enabled() ? $this->server_properties['server-portv6'] : 0;
    }

    public function isIp6Enabled(): bool
    {
        return Utils::isEnabled($this->server_properties['enable-ipv6']);
    }

    public function start(): void
    {
        $this->command()->start();
    }

    public function stop(): void
    {
        $this->command()->stop();
    }
}
