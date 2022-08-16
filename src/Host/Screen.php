<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

use CarmeloSantana\EnderHive\Command as Command;
use CarmeloSantana\EnderHive\Instance as Instance;

class Screen implements Host
{
    private string $host = 'screen';

    private string $start = 'start.sh';

    public function __construct(private string $instance)
    {
        $path = Instance::getPath($this->instance);

        chdir($path);
    }

    public function start(): bool
    {
        $command = [$this->host, '-dmS', $this->instance, 'bash', $this->start];

        $output = Command::exec($command);

        return true;
    }

    public function stop(): bool
    {
        $command = [$this->host, '-S', $this->instance, '-p', '0', '-X', 'stop^M'];

        $output = Command::exec($command);

        return true;
    }
}
