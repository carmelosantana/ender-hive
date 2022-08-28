<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use CarmeloSantana\EnderHive\Config\Defaults;

class Server
{
    public const LOCK_FILE = 'server.lock';

    private string $nonce = '';

    public function __construct(private int $post_id)
    {
        if (!get_post_status($post_id)) {
            $this->status = Status::NOT_FOUND;
        } else {
            $this->status = Status::FOUND;
            $this->init();
        }
    }

    public function command()
    {
        if (!isset($this->command)) {
            $this->command = new Command($this->post->ID, $this->nonce);
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

        foreach (Defaults::serverProperties() as $property => $default) {
            // Property is a comment.
            if (is_int($property)) {
                continue;
            }

            $this->server_properties[$property] = Options::getMeta($this->post_id, $property);
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
            return 0;
        } else {
            return (int) file_get_contents($this->getLockFilePath());
        }
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
        }
    }

    public function isIp6Enabled(): bool
    {
        return Utils::isEnabled($this->server_properties['enable-ipv6']);
    }

    public function setAuth(string $auth): void
    {
        $this->nonce = $auth;
    }

    public function start(): int
    {
        switch (get_post_status($this->post->ID)) {
            case 'publish':
            case 'draft':
                return $this->command()->start();
                break;
        }
        return Status::NOT_FOUND;
    }

    public function stop(): int
    {
        if (!$this->isRunning()) {
            return Status::OK;
        }

        $status = $this->command()->stop();

        switch ($status) {
            case Status::INTERNAL_SERVER_ERROR:
                $this->removeLockFile();
                break;
        }

        return $status;
    }

    public function removeLockFile(): void
    {
        if (file_exists($this->getLockFilePath())) {
            unlink($this->getLockFilePath());
        }
    }

    public function stopWait(): int
    {
        $this->stop();

        // Wait for the server to stop
        while ($this->isRunning()) {
            usleep(250000);
        }

        return $this->getStatus();
    }
}
