<?php

namespace Tracket\Docker\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the Tracket Docker files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('vendor:publish', ['--tag' => 'tracket-docker']);
        $this->call('vendor:publish', ['--tag' => 'tracket-bin']);

        chmod($this->laravel->basePath('bin/artisan'), 0755);
        chmod($this->laravel->basePath('bin/composer'), 0755);
        chmod($this->laravel->basePath('bin/env'), 0755);
        chmod($this->laravel->basePath('bin/npm'), 0755);

        file_put_contents(
            $this->laravel->basePath('docker-compose.yml'),
            str_replace(
                [
                    './vendor/tracket/docker/runtimes/8.1',
                ],
                [
                    './docker/php',
                ],
                file_get_contents($this->laravel->basePath('docker-compose.yml'))
            )
        );
    }
}