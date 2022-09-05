<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host\PocketMineMP;

use CarmeloSantana\EnderHive\Host\Base;
use CarmeloSantana\EnderHive\Host\Status;
use CarmeloSantana\EnderHive\Tools\Network;
use CarmeloSantana\EnderHive\Tools\Utils;

class Server extends Base
{
    public const ASSETS = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;

    public const INSTALL_FILE = 'install.sh';

    public const LOCK_FILE = 'server.lock';

    public function assignPorts(): void
    {
        // Setup network for access to available ports.
        $this->network = new Network();

        // Set ports to meta options.
        carbon_set_post_meta($this->post_id, 'server-port', $this->network->requestPort($this->post_id));
        carbon_set_post_meta($this->post_id, 'server-portv6', $this->network->requestPort($this->post_id));
    }

    public function command()
    {
        if (!isset($this->interface)) {
            $this->initInterface();
        }

        return $this->interface;
    }

    /**
     * Shortcut to get lock file.
     *
     * @return string
     */
    public function getLockFilePath(): string
    {
        return $this->getPath(['filename' => self::LOCK_FILE]);
    }

    /**
     * Get server lock file and returns contents, server PID or 0 for none.
     *
     * @return int
     */
    public function getServerLock(): int
    {
        if (!file_exists($this->getLockFilePath())) {
            return 0;
        } else {
            return (int) file_get_contents($this->getLockFilePath());
        }
    }

    public function initInterface(): void
    {
        switch (carbon_get_post_meta($this->post_id, 'host_interface')) {
            default:
                $this->interface = new Screen($this->post_id);
                break;
        }
    }

    /**
     * Populates $this->server_properties with the server.properties options.
     *
     * @return void
     */
    public function initServerProperties(): void
    {
        $this->server_properties = [];

        foreach (self::defaultServerProperties() as $property => $default) {
            // Property is a comment.
            if (is_int($property)) {
                continue;
            }

            $this->server_properties[$property] = carbon_get_post_meta($this->post_id, $property);
        }
    }

    public function install(): int
    {
        // Setup Directory.
        wp_mkdir_p($this->getPath());

        // Move to working directory.
        chdir($this->getPath());

        // Download install.sh.
        $this->install_file = $this->getPath() . 'install.sh';
        Utils::download(carbon_get_theme_option('pmmp_install_sh_url'), $this->install_file);

        // TODO: Check if install.sh is successful.
        $this->command()->install($this->install_file);

        // Assign ports.
        $this->assignPorts();

        // Create new configs.
        $this->newFiles(self::files());

        // We made it this far we must be done!
        update_post_meta($this->post_id, '_install_status', Status::OK);

        // Send back things are ok.
        return Status::OK;
    }

    public function isRunning(): bool
    {
        // TODO Add PID check.
        if ($this->getServerLock() > 0) {
            return true;
        }

        return false;
    }

    public function removeLockFile(): void
    {
        if (file_exists($this->getLockFilePath())) {
            unlink($this->getLockFilePath());
        }
    }

    public function restart(): int
    {
        $this->stopWait();
        $this->start();

        return $this->getStatus();
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

    public function stopWait(): int
    {
        $this->stop();

        // Wait for the server to stop
        while ($this->isRunning()) {
            usleep(250000);
        }

        return $this->getStatus();
    }

    public function updateStatus(int $status = 0): void
    {
        if ($status > 0) {
            $this->status = $status;
        } elseif ($this->isRunning()) {
            $this->status = Status::OK;
        }
    }

    public static function getServerProperties($post_id): string
    {
        $properties = [];

        foreach (self::defaultServerProperties() as $key => $value) {
            if (is_string($key)) {
                $value = carbon_get_post_meta($post_id, $key);
            }
            $properties[$key] = $value;
        }

        return Utils::arrayToIni($properties);
    }

    public static function defaultServerProperties(): array
    {
        return [
            'Properties Config file',
            date('D M j H:i:s T Y'),
            'language' => 'eng',
            'motd' => 'Powered by ' . ENDER_HIVE_TITLE . ' + WordPress',
            'server-name' => 'Just another PocketMine Server',
            'enable-ipv6' => true,
            'server-port' => 19132,
            'server-portv6' => 19133,
            'white-list' => false,
            'max-players' => 20,
            'gamemode' => 'survival',
            'force-gamemode' => false,
            'hardcore' => false,
            'pvp' => true,
            'difficulty' => 2,
            'generator-settings' => '',
            'level-name' => 'world',
            'level-seed' => '',
            'level-type' => 'DEFAULT',
            'enable-query' => true,
            'auto-save' => true,
            'view-distance' => 16,
            'xbox-auth' => true,
        ];
    }

    // TODO: Callback must be static and return string.
    public static function files(): array
    {
        return [
            'htaccess' => [
                'filename' => '.htaccess',
                'callback' => [__CLASS__, 'getAsset'],
            ],
            'banned_ips_txt' => [
                'filename' => 'banned-ips',
                'extension' => 'txt',
            ],
            'banned_players_txt' => [
                'filename' => 'banned-players',
                'extension' => 'txt',
            ],
            'ops_txt' => [
                'filename' => 'ops',
                'extension' => 'txt',
            ],
            'plugin_list_yml' => [
                'filename' => 'plugin_list',
                'extension' => 'yml',
                'callback' => [__CLASS__, 'getAsset'],
            ],
            'pocketmine_yml' => [
                'filename' => 'pocketmine',
                'extension' => 'yml',
                'callback' => [__CLASS__, 'getAsset'],
            ],
            'server_properties' => [
                'filename' => 'server',
                'extension' => 'properties',
                'callback' => [__CLASS__, 'getServerProperties'],
            ],
            'white_list_txt' => [
                'filename' => 'white-list',
                'extension' => 'txt',
            ],
        ];
    }

    public static function getAsset($file_name): string
    {
        $filename = self::ASSETS . $file_name;

        return file_get_contents($filename);
    }
}