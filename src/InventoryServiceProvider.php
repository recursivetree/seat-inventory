<?php

namespace RecursiveTree\Seat\Inventory;

use Exception;
use RecursiveTree\Seat\Inventory\Jobs\UpdateInventory;
use RecursiveTree\Seat\Inventory\Jobs\UpdateLocations;
use RecursiveTree\Seat\Inventory\Jobs\UpdateStockLevels;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Observers\AllianceMemberObserver;
use RecursiveTree\Seat\Inventory\Observers\FittingPluginFittingObserver;
use RecursiveTree\Seat\Inventory\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\Inventory\Observers\UniverseStationObserver;
use RecursiveTree\Seat\Inventory\Observers\UniverseStructureObserver;
use Seat\Eveapi\Models\Alliances\AllianceMember;
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

class InventoryServiceProvider extends AbstractSeatPlugin
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

        $this->loadTranslationsFrom(__DIR__ . '/resources/lang/', 'inventory');
        $this->loadViewsFrom(__DIR__ . '/resources/views/', 'inventory');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');

        $this->publishes([
            __DIR__ . '/resources/js' => public_path('inventory/js')
        ]);


        Blade::directive('inventoryVersionedAsset', function($path) use ($version) {
            return "<?php echo asset({$path}) . '?v=$version'; ?>";
        });

        if(FittingPluginHelper::pluginIsAvailable()) {
            FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::observe(FittingPluginFittingObserver::class);
        }

        UniverseStructure::observe(UniverseStructureObserver::class);
        UniverseStation::observe(UniverseStationObserver::class);
        AllianceMember::observe(AllianceMemberObserver::class);

        Artisan::command('inventory:assets {--sync}', function () {
            if ($this->option("sync")){
                $this->info("processing...");
                UpdateInventory::dispatchNow();
                $this->info("Synchronously processed inventory updates!");
            } else {
                UpdateInventory::dispatch()->onQueue('default');
                $this->info("Scheduled an inventory update!");
            }
        });

        Artisan::command('inventory:stocks {location_id} {--sync}', function ($location_id) {
            $location = Location::find($location_id);
            if ($location == null){
                $this->error("Location not found");
                return;
            }

            if ($this->option("sync")){
                $this->info("processing...");
                UpdateStockLevels::dispatchNow($location_id);
                $this->info("Synchronously processed stock level updates!");
            } else {
                UpdateStockLevels::dispatch($location_id)->onQueue('default');
                $this->info("Scheduled an stock level update!");
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
        $this->mergeConfigFrom(__DIR__ . '/Config/inventory.sidebar.php','package.sidebar');
        $this->registerPermissions(__DIR__ . '/Config/inventory.permissions.php', 'inventory');
    }

    public function getName(): string
    {
        return 'SeAT Inventory Manager';
    }

    public function getPackageRepositoryUrl(): string
    {
        return 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    }

    public function getPackagistPackageName(): string
    {
        return 'seat-inventory';
    }

    public function getPackagistVendorName(): string
    {
        return 'recursivetree';
    }
}