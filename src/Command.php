<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use CarmeloSantana\EnderHive\Host\Screen;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Command
{
    public function __construct(private int $id)
    {
        $this->host = $this->getHost($id);
    }

    public static function exec(array $command): bool
    {
        ray($command)->label('Process, $command');

        $process = new Process($command);

        try {
            $process->mustRun();

            $log = $process->getOutput();
            $out = true;

        } catch (ProcessFailedException $exception) {
            $log = $exception->getMessage();
            $out = false;
        }

        ray($log)->label('Process, $out');

        return $out;
    }

    public function getHost(): object
    {
        switch (\carbon_get_theme_option('ender_hive_host')) {
            default:
                return new Screen($this->id);
        }
    }

    public function start(): bool
    {
        return call_user_func([$this->host, 'start']);
    }

    public function stop(): bool
    {
        return call_user_func([$this->host, 'stop']);
    }
}
