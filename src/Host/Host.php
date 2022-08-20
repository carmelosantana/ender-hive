<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host;

use CarmeloSantana\EnderHive\Status;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class Host
{
    public function exec(array $command): int
    {
        $process = new Process($command);

        try {
            $process->mustRun();

            $log = $process->getOutput();
            $status = Status::ACCEPTED;
        } catch (ProcessFailedException $exception) {
            $log = $exception->getMessage();
            $status = Status::INTERNAL_SERVER_ERROR;
        }

        ray($command)->label('Process() $command');
        ray($log)->label('Process() $log');

        return $status;
    }

    public function start(): int
    {
    }

    public function stop(): int
    {
    }
}
