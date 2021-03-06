<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ViewCacheCommand extends Command
{
   /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'view:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile all of the application\'s Blade templates';

   /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('view:clear');

        $this->paths()->each(function ($path) {
            $this->compileViews($this->bladeFilesIn([$path]));
        });

        $this->info('Blade templates cached successfully!');
    }

    /**
     * Compile the given view files.
     *
     * @param  Collection  $views
     * @return void
     */
    protected function compileViews(Collection $views)
    {
        $compiler = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();

        $views->map(function (SplFileInfo $file) use ($compiler) {
            $compiler->compile($file->getRealPath());
        });
    }

    /**
     * Get the Blade files in the given path.
     *
     * @param  array  $paths
     * @return Collection
     */
    protected function bladeFilesIn(array $paths)
    {
        return collect(
            Finder::create()
                ->in($paths)
                ->exclude('vendor')
                ->name('*.blade.php')
                ->files()
        );
    }

    /**
     * Get all of the possible view paths.
     *
     * @return Collection
     */
    protected function paths()
    {
        $finder = $this->app['view.finder'];

        return collect($finder->getPaths())->merge(
            collect($finder->getHints())->flatten()
        );
    }
}
