<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Tools;

use CarmeloSantana\EnderHive\Host\Server;

class Network
{
    const DEFAULT = 19132;

    const PORT_MIN = 1024;

    const PORT_MAX = 65535;

    public function getAvailablePorts(): array
    {
        if (!isset($this->available_ports)) {
            $this->available_ports = array_diff($this->buildPortPool(), $this->buildActivePorts());
        }

        return $this->available_ports;
    }

    public function buildActivePorts(): array
    {
        $ports = [];

        // Remove draft and trash from query results.
        $post_statuses = array_diff(get_post_stati(), ['draft', 'trash']);

        // Get all servers.
        $query = new \WP_Query([
            'post_type' => 'instance',
            'post_status' => $post_statuses,
            'post__not_in' => [($this->post_id ?? 0)],
        ]);

        // Loop through all instances. Add IP4 to list, check if IP6 is enabled, if so add port.
        while ($query->have_posts()) {
            $query->the_post();
            $instance = new Server(get_the_ID());
            $ports[] = $instance->getPortIp4();
            if ($instance->isIp6Enabled()) {
                $ports[] = $instance->getPortIp6();
            }
        }

        return $ports;
    }

    public function buildPortPool(): array
    {
        $ports = [];
        $ranges = carbon_get_theme_option('available_ports');

        foreach ($ranges as $port_range) {
            // Skip if we're out of range
            if ($port_range['start'] < self::PORT_MIN or $port_range['end'] > self::PORT_MAX) {
                continue;
            }

            // Add valid range to a list
            if ($port_range['start'] <= $port_range['end']) {
                $ports = array_merge($ports, range($port_range['start'], $port_range['end']));
            }
        }

        // Sort ports and remove duplicates
        sort($ports);
        $ports = array_unique($ports);

        return $ports;
    }

    public function requestPort(int $post_id = 0): int
    {
        if ($post_id > 0) {
            $this->post_id = $post_id;
        }

        $this->getAvailablePorts();

        switch (carbon_get_theme_option('port_assignment')) {
            case 'random':
                $key = array_rand($this->available_ports);
                $port = $this->available_ports[$key];
                unset($this->available_ports[$key]);
                break;

            default:
                $port = array_shift($this->available_ports);
                break;
        }

        return $port;
    }

    public static function isPortFree(int $port, string $host = '127.0.0.1'): bool
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, 1);
        if ($socket) {
            fclose($socket);
            return false;
        }
        return true;
    }
}
