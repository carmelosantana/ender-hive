<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

class Server
{
    public const LOCK_FILE = 'server.lock';

    public function __construct(private int $post_id)
    {
        if (!get_post_status($post_id)) {
            $this->status = Status::NOT_FOUND;
        } else {
            $this->status = Status::FOUND;
            $this->init();
        }

        return $this;
    }

    public function command()
    {
        if (!isset($this->command)) {
            $this->command = new Command($this->post->ID);
            $this->init();
        }

        return $this->command;
    }

    public function init(): void
    {
        $this->initPost();
        $this->initServerProperties();
        $this->updateStatus();
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

    public function isRunning(): bool
    {
        if ($this->getServerLock() > 0) {
            return true;
        }

        return false;
    }

    public function getServerLock(): int
    {
        if (!file_exists($this->getLockFilePath())) {
            $this->server_lock = 0;
        } else {
            $this->server_lock = (int) file_get_contents($this->getLockFilePath());
        }

        return $this->server_lock;
    }

    public function getLockFilePath(): string
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . self::LOCK_FILE;
    }

    public function getPath(): string
    {
        return Instance::getPath($this->post->ID);
    }

    public function getPortIp4()
    {
        return $this->server_properties['server-port'];
    }

    public function getPortIp6()
    {
        return $this->isIp6Enabled() ? $this->server_properties['server-portv6'] : 0;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function updateStatus(int $status = 0): void
    {
        if ($status > 0) {
            $this->status = $status;
        } elseif ($this->isRunning()) {
            $this->status = Status::OK;
        } else {
            $this->status = Status::NOT_FOUND;
        }
    }

    public function isIp6Enabled(): bool
    {
        return Utils::isEnabled($this->server_properties['enable-ipv6']);
    }

    public function start(): void
    {
        if ($this->isRunning()) {
            return;
        }

        $this->command()->start();
    }

    public function stop(): void
    {
        if (!$this->isRunning()) {
            return;
        }

        $message = $this->command()->stop();

        if (!$message) {
            $this->removeLockFile();
        }
    }

    public function removeLockFile(): void
    {
        if (file_exists($this->getLockFilePath())) {
            unlink($this->getLockFilePath());
        }
    }

    public function stopWait(): void
    {
        $this->stop();

        // Wait for the server to stop
        while ($this->isRunning()) {
            usleep(250000);
        }
    }
}
