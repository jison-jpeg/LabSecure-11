<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunAllServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:all-services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run queue:work, mqtt:listen, and reverb:start commands concurrently';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define the commands to run concurrently
        $commands = [
            'queue:work --daemon --tries=3',
            'mqtt:listen',
            'reverb:start',
        ];

        // Array to store running processes
        $processes = [];

        // Start each command as a background process
        foreach ($commands as $command) {
            $process = Process::fromShellCommandline('php artisan ' . $command);
            $process->setTimeout(null); // Ensure the process doesn't time out
            $process->start();

            $this->info("Started command: php artisan $command");

            $processes[] = [
                'process' => $process,
                'command' => $command
            ];
        }

        // Output real-time process logs
        while (count($processes) > 0) {
            foreach ($processes as $index => $data) {
                /** @var Process $process */
                $process = $data['process'];
                $command = $data['command'];

                if ($process->isRunning()) {
                    $output = $process->getIncrementalOutput();
                    $errorOutput = $process->getIncrementalErrorOutput();

                    if (!empty($output)) {
                        $this->line("[{$command}] " . trim($output));
                    }

                    if (!empty($errorOutput)) {
                        $this->error("[{$command}] " . trim($errorOutput));
                    }
                } else {
                    // Remove completed process
                    unset($processes[$index]);

                    if ($process->isSuccessful()) {
                        $this->info("Command finished: php artisan $command");
                    } else {
                        $this->error("Command failed: php artisan $command");
                        $this->error($process->getErrorOutput());
                    }
                }
            }

            // Sleep for a short time to avoid high CPU usage
            usleep(100000); // 100 milliseconds
        }

        return 0;
    }
}
