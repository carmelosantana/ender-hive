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
     * Access post ID initialized in constructor.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Get primary IP address of the host.
     *
     * @return string
     */
    public function getIp(): string;

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
     * Retrieves current status.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Install and start server.
     *
     * @return int Status code.
     */
    public function install(): int;

    /**
     * Checks if IPv6 is enabled.
     *
     * @return int Status code.
     */
    public function isIp6Enabled(): bool;

    /**
     * Checks if server is running.
     *
     * @return bool True if server is running, false otherwise.
     */
    public function isRunning(): bool;
    
    /**
     * Returns current server log file as an array.
     *
     * @return array
     */
    public function logs(): array;

    /**
     * Restart server.
     *
     * @return int Status code.
     */
    public function restart(): int;

    /**
     * Start server.
     *
     * @return int Status code.
     */
    public function start(): int;

    /**
     * Stop server.
     *
     * @return int Status code.
     */
    public function stop(): int;

    /**
     * Waits till the server is fully stopped before returning.
     *
     * @return int Status code.
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
