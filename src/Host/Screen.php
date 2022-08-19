<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

use CarmeloSantana\EnderHive\Command as Command;
use CarmeloSantana\EnderHive\Instance as Instance;
use CarmeloSantana\EnderHive\Server;

class Screen implements Host
{
    private string $host = 'screen';

    private string $start = 'start.sh';

    /**
     * Returns server instance. Requires post ID.
     *
     * @param  int $id
     * @return void
     */
    public function __construct(private int $id)
    {
        $this->server = new Server($this->id);

        chdir($this->server->getPath());
    }

    /**
     * Starts server with screen name of $id.
     *
     * @return bool
     */
    public function start(): bool
    {
        $command = [$this->host, '-dmS', $this->id, 'bash', $this->start];

        return Command::exec($command);
    }

    public function stop(): bool
    {
        $command = [$this->host, '-S', $this->id, '-p', '0', '-X', 'stuff', 'stop^M'];

        return Command::exec($command);
    }
}
