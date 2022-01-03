<?php

namespace RecursiveTree\Seat\TerminusInventory;

use Exception;
use RecursiveTree\Seat\TerminusInventory\Jobs\UpdateTerminusInv;
use RecursiveTree\Seat\TerminusInventory\Observers\FittingPluginFittingObserver;
use RecursiveTree\Seat\TerminusInventory\Helpers\FittingPluginHelper;
use Seat\Services\AbstractSeatPlugin;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Events\JobProcessed;

class TerminusInventoryServiceProvider extends AbstractSeatPlugin
{
    public function boot(){
        $version = $this->getVersion();
        //always reload the cache in dev builds
        $is_release = true;
        if($version=="missing"){
            $version=rand();
            $is_release = false;
        }

        if (!$this->app->routesAreCached() || !$is_release) {
            include __DIR__ . '/Http/routes.php';
        }

        $this->loadTranslationsFrom(__DIR__ . '/resources/lang/', 'terminusinv');
        $this->loadViewsFrom(__DIR__ . '/resources/views/', 'terminusinv');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');

        $this->publishes([
            __DIR__ . '/resources/js' => public_path('terminusinventory/js')
        ]);


        Blade::directive('versionedAsset', function($path) use ($version) {
            return "<?php echo asset({$path}) . '?v=$version'; ?>";
        });

        if(FittingPluginHelper::pluginIsAvailable()) {
            FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::observe(FittingPluginFittingObserver::class);
        }

        Artisan::command('terminusinv:update {--sync}', function () {
            if ($this->option("sync")){
                UpdateTerminusInv::dispatchNow();
                $this->info("Synchronously processed inventory updates!");
            } else {
                UpdateTerminusInv::dispatch()->onQueue('default');
                $this->info("Scheduled an inventory update!");
            }
        });
    }

    public function register(){
        $this->mergeConfigFrom(__DIR__ . '/Config/terminusinventory.sidebar.php','package.sidebar');
    }

    public function getName(): string
    {
        return 'SeAT Terminus Inventory Manager';
    }

    public function getPackageRepositoryUrl(): string
    {
        return 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    }

    public function getPackagistPackageName(): string
    {
        return 'seat-terminus-inventory';
    }

    public function getPackagistVendorName(): string
    {
        return 'recursivetree';
    }
}