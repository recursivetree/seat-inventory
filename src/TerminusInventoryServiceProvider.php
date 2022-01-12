<?php

namespace RecursiveTree\Seat\TerminusInventory;

use Exception;
use RecursiveTree\Seat\TerminusInventory\Jobs\UpdateInventory;
use RecursiveTree\Seat\TerminusInventory\Jobs\UpdateLocations;
use RecursiveTree\Seat\TerminusInventory\Observers\FittingPluginFittingObserver;
use RecursiveTree\Seat\TerminusInventory\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\TerminusInventory\Observers\UniverseStationObserver;
use RecursiveTree\Seat\TerminusInventory\Observers\UniverseStructureObserver;
use Seat\Services\AbstractSeatPlugin;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Events\JobProcessed;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Jobs\Assets\Corporation\Assets;

use  Seat\Eveapi\Jobs\Status\Status;

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


        Blade::directive('terminusinvVersionedAsset', function($path) use ($version) {
            return "<?php echo asset({$path}) . '?v=$version'; ?>";
        });

        if(FittingPluginHelper::pluginIsAvailable()) {
            FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::observe(FittingPluginFittingObserver::class);
        }

        UniverseStructure::observe(UniverseStructureObserver::class);
        UniverseStation::observe(UniverseStationObserver::class);

        Artisan::command('terminusinv:assets {--sync}', function () {
            if ($this->option("sync")){
                UpdateInventory::dispatchNow();
                $this->info("Synchronously processed inventory updates!");
            } else {
                UpdateInventory::dispatch()->onQueue('default');
                $this->info("Scheduled an inventory update!");
            }
        });

        Queue::after(function (JobProcessed $event) {
            $class = $event->job->resolveName();
            if ($class == Assets::class){
                UpdateInventory::dispatch()->onQueue('default');
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