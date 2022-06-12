<?php

namespace Tracket\Docker\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:install
                {--with= : The services that should be included in the installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Tracket\'s default Docker Compose file';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('with')) {
            $services = $this->option('with') == 'none' ? [] : explode(',', $this->option('with'));
        } elseif ($this->option('no-interaction')) {
            $services = ['mysql', 'redis'];
        } else {
            $services = $this->gatherServicesWithSymfonyMenu();
        }

        $this->buildDockerCompose($services);
        $this->replaceEnvVariables($services);
        $this->configurePhpUnit();

        $this->info('Docker scaffolding installed successfully.');
    }

    /**
     * Gather the desired Sail services using a Symfony menu.
     *
     * @return array
     */
    protected function gatherServicesWithSymfonyMenu()
    {
        return $this->choice('Which services would you like to install?', [
            'mysql',
            'pgsql',
            'redis',
        ], 0, null, true);
    }

    /**
     * Build the Docker Compose file.
     *
     * @param  array  $services
     * @return void
     */
    protected function buildDockerCompose(array $services)
    {
        $depends = collect($services)
            ->filter(function ($service) {
                return in_array($service, ['mysql', 'pgsql', 'redis']);
            })->map(function ($service) {
                return "            - {$service}";
            })->whenNotEmpty(function ($collection) {
                return $collection->prepend('depends_on:');
            })->implode("\n");

        $stubs = rtrim(collect($services)->map(function ($service) {
            return file_get_contents(__DIR__ . "/../../stubs/{$service}.stub");
        })->implode(''));

        $volumes = collect($services)
            ->filter(function ($service) {
                return in_array($service, ['mysql', 'pgsql', 'redis']);
            })->map(function ($service) {
                return "    tracket-{$service}:\n        driver: local";
            })->whenNotEmpty(function ($collection) {
                return $collection->prepend('volumes:');
            })->implode("\n");

        $dockerCompose = file_get_contents(__DIR__ . '/../../stubs/docker-compose.stub');

        $dockerCompose = str_replace('{{depends}}', empty($depends) ? '' : '        '.$depends, $dockerCompose);
        $dockerCompose = str_replace('{{services}}', $stubs, $dockerCompose);
        $dockerCompose = str_replace('{{volumes}}', $volumes, $dockerCompose);

        // Remove empty lines...
        $dockerCompose = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $dockerCompose);

        file_put_contents($this->laravel->basePath('docker-compose.yml'), $dockerCompose);
    }

    /**
     * Replace the Host environment variables in the app's .env file.
     *
     * @param  array  $services
     * @return void
     */
    protected function replaceEnvVariables(array $services)
    {
        $environment = file_get_contents($this->laravel->basePath('.env'));

        if (in_array('pgsql', $services)) {
            $environment = str_replace('DB_CONNECTION=mysql', "DB_CONNECTION=pgsql", $environment);
            $environment = str_replace('DB_HOST=127.0.0.1', "DB_HOST=pgsql", $environment);
            $environment = str_replace('DB_PORT=3306', "DB_PORT=5432", $environment);
        } else {
            $environment = str_replace('DB_HOST=127.0.0.1', "DB_HOST=mysql", $environment);
        }

        $environment = str_replace('DB_USERNAME=root', "DB_USERNAME=sail", $environment);
        $environment = preg_replace("/DB_PASSWORD=(.*)/", "DB_PASSWORD=password", $environment);

        $environment = str_replace('REDIS_HOST=127.0.0.1', 'REDIS_HOST=redis', $environment);

        file_put_contents($this->laravel->basePath('.env'), $environment);
    }

    /**
     * Configure PHPUnit to use the dedicated testing database.
     *
     * @return void
     */
    protected function configurePhpUnit()
    {
        if (! file_exists($path = $this->laravel->basePath('phpunit.xml'))) {
            $path = $this->laravel->basePath('phpunit.xml.dist');
        }

        $phpunit = file_get_contents($path);

        $phpunit = preg_replace('/^.*DB_CONNECTION.*\n/m', '', $phpunit);
        $phpunit = str_replace('<!-- <env name="DB_DATABASE" value=":memory:"/> -->', '<env name="DB_DATABASE" value="testing"/>', $phpunit);

        file_put_contents($this->laravel->basePath('phpunit.xml'), $phpunit);
    }
}