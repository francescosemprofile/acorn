<?php

namespace Roots\Acorn\Console\Commands;

use Throwable;
use LogicException;
use Roots\Acorn\Filesystem\Filesystem;

class ConfigCacheCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'config:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cache file for faster configuration loading';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new config cache command instance.
     *
     * @param  Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws LogicException
     */
    public function handle()
    {
        $this->call('config:clear');

        $config = $this->getConfig();

        $configPath = $this->app->getCachedConfigPath();

        $this->files->put(
            $configPath,
            '<?php return ' . var_export($config, true) . ';' . PHP_EOL
        );

        try {
            require $configPath;
        } catch (Throwable $e) {
            $this->files->delete($configPath);

            throw new LogicException('Your configuration files are not serializable.', 0, $e);
        }

        $this->info('Configuration cached successfully!');
    }

    /**
     * Return a copy of the application configuration.
     *
     * @return array
     */
    protected function getConfig()
    {
        return $this->app['config']->all();
    }
}
