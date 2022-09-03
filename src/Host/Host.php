<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

interface Host
{    
    /**
     * Host wrapper for the server binary or the host container.
     *
     * @param  mixed $post_id Instance post ID of the server.
     * @return void
     */
    public function __construct(int $post_id);
    
    /**
     * Retrieve IPv4 port.
     *
     * @return int
     */
    public function getPortIp4(): int;
    
    /**
     * Retrieve IPv6 port.
     *
     * @return int
     */
    public function getPortIp6(): int;
    
    /**
     * Install and start server.
     *
     * @return int Status constant.
     */
    public function install(): int;
    
    /**
     * Checks if server is running.
     *
     * @return bool True if server is running, false otherwise.
     */
    public function isRunning(): bool;
    
    /**
     * Starts the server.
     *
     * @return int Status constant.
     */
    public function start(): int;

    /**
     * Stops the server.
     *
     * @return int Status constant.
     */
    public function stop(): int;

    /**
     * Waits till the server is fully stopped before returning.
     *
     * @return int Status constant.
     */
    public function stopWait(): int;
    
    /**
     * Performs all processes necessary to update the server status.
     * Hydrates the host with the latest status.
     *
     * @return void
     */
    public function updateStatus(): void;
}
