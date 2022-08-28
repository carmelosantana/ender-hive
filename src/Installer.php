<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use CarmeloSantana\EnderHive\Config\Create as CreateConfig;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Installer
{
    public function __construct(private \WP_Post $post)
    {
    }

    public function assignPorts(): void
    {
        $this->network = new Network();

        Options::setMeta($this->post->ID, 'server-port', $this->network->requestPort());
        Options::setMeta($this->post->ID, 'server-portv6', $this->network->requestPort());
    }

    public function createInstanceDirectory(): void
    {
        $this->instance_dir = Instance::getPath($this->post->ID);

        if (!file_exists($this->instance_dir)) {
            wp_mkdir_p($this->instance_dir);
        }

        chdir($this->instance_dir);
    }

    public function download(): void
    {
        $this->install_file = $this->instance_dir . '/install.sh';

        wp_remote_get(
            Options::get('pmmp_install_sh_url'),
            [
                'timeout' => 15,
                'stream' => true,
                'filename' => $this->install_file,
            ]
        );
    }

    public function new()
    {
        // Setup Directory.
        $this->createInstanceDirectory();

        // Download install.sh.
        $this->download();

        // Run install.sh.
        $this->run();

        // Assign ports.
        $this->assignPorts();

        // Create new configs.
        (new CreateConfig($this->post))->all();
    }

    public function run()
    {
        if (!file_exists($this->install_file)) {
            wp_die('Installer file does not exist.');
        }

        $commands = [
            // Make file writable for execution.
            ['chmod', '+x', $this->install_file],

            // Run the installer.
            ['bash', $this->install_file],
        ];

        $out = '';

        foreach ($commands as $command) {
            $process = new Process($command);
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $out .= $process->getOutput();
        }

        return $out;
    }
}
