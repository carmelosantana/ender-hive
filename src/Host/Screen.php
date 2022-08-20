<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

use CarmeloSantana\EnderHive\Command as Command;
use CarmeloSantana\EnderHive\Instance as Instance;
use CarmeloSantana\EnderHive\Server;

class Screen extends Host
{
    private string $host = 'screen';

    private string $start = 'start.sh';

    /**
     * Returns server instance. Requires post ID.
     *
     * @param int $id
     */
    public function __construct(private int $id)
    {
        $this->server = new Server($this->id);

        chdir($this->server->getPath());
    }

    /**
     * Execute start.sh to start server.
     *
     * @return int Status code
     */
    public function start(): int
    {
        $command = [$this->host, '-dmS', $this->id, 'bash', $this->start];

        return $this->exec($command);
    }
    
    /**
     * Send 'stop' to console and carriage return.
     *
     * @return int
     */
    public function stop(): int
    {
        $command = [$this->host, '-S', $this->id, '-p', '0', '-X', 'stuff', 'stop^M'];

        return $this->exec($command);
    }
}
