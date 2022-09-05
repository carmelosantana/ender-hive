<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Host\PocketMineMP;

use CarmeloSantana\EnderHive\Host\Server;
use CarmeloSantana\EnderHive\Host\Status;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Screen
{
    const HOST = 'screen';

    const START = 'start.sh';

    /**
     * Returns server instance. Requires post ID.
     *
     * @param int $post_id
     */
    public function __construct(private int $post_id)
    {
        // Change to instance directory.
        chdir(Server::getInstancePath($this->post_id));
    }

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

    public function install($install_file): int
    {
        $commands = [
            // Make file writable for execution.
            ['chmod', '+x', $install_file],

            // Run the installer.
            ['bash', $install_file],
        ];

        foreach ($commands as $command) {
            $status = $this->exec($command);
            if ($status !== Status::ACCEPTED) {
                return $status;
            }
        }

        return $status;
    }

    /**
     * Execute start.sh to start server.
     *
     * @return int Status code
     */
    public function start(): int
    {
        // Run start.sh in new screen session.
        $command = [self::HOST, '-dmS', (string) $this->post_id, 'bash', self::START];

        return $this->exec($command);
    }

    /**
     * Send 'stop' to console and carriage return.
     *
     * @return int
     */
    public function stop(): int
    {
        $command = [self::HOST, '-S', $this->post_id, '-p', '0', '-X', 'stuff', 'stop^M'];

        return $this->exec($command);
    }
}
