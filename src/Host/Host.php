<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

interface Host
{
    public function start(): bool;

    public function stop(): bool;
}
