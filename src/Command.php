<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use CarmeloSantana\EnderHive\Host\Screen;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Command
{
    public function __construct(private string $instance)
    {
        $this->host = $this->getHost($instance);
    }

    // TODO: Add auth check.
    public static function exec(array $command): string
    {
        ray($command)->label('Process, $command');

        $process = new Process($command);
        $process->run();

        $out = '';
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $out .= $process->getOutput();

        ray($out)->label('Process, $out');
        
        return $out;
    }

    public function getHost(): object
    {
        switch (\carbon_get_theme_option('ender_hive_host')) {
            default:
                return new Screen($this->instance);
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
