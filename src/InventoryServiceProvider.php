<?php

namespace RecursiveTree\Seat\Inventory;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use RecursiveTree\Seat\TreeLib\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\Inventory\Jobs\GenerateStockIcon;
use RecursiveTree\Seat\Inventory\Jobs\SendStockLevelNotifications;
use RecursiveTree\Seat\Inventory\Jobs\UpdateCategoryMembers;
use RecursiveTree\Seat\Inventory\Jobs\UpdateInventory;
use RecursiveTree\Seat\Inventory\Jobs\UpdateStockLevels;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Observers\AllianceMemberObserver;
use RecursiveTree\Seat\Inventory\Observers\FittingPluginDoctrineObserver;
use RecursiveTree\Seat\Inventory\Observers\FittingPluginFittingObserver;
use RecursiveTree\Seat\Inventory\Observers\StockObserver;
use RecursiveTree\Seat\Inventory\Observers\UniverseStationObserver;
use RecursiveTree\Seat\Inventory\Observers\UniverseStructureObserver;
use Seat\Eveapi\Jobs\Assets\Corporation\Assets;
use Seat\Eveapi\Models\Alliances\AllianceMember;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Services\AbstractSeatPlugin;

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

        $this->publishes([
            __DIR__.'/Config/inventory.sources.php' => config_path('inventory.sources.php')],["config","seat"]
        );


        Blade::directive('inventoryVersionedAsset', function($path) use ($version) {
            return "<?php echo asset({$path}) . '?v=$version'; ?>";
        });

        if(FittingPluginHelper::pluginIsAvailable()) {
            FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::observe(FittingPluginFittingObserver::class);
            FittingPluginHelper::$FITTING_PLUGIN_DOCTRINE_MODEL::observe(FittingPluginDoctrineObserver::class);
        }

        UniverseStructure::observe(UniverseStructureObserver::class);
        UniverseStation::observe(UniverseStationObserver::class);
        AllianceMember::observe(AllianceMemberObserver::class);

        $this->mergeConfigFrom(
            __DIR__ . '/Config/notifications.alerts.php', 'notifications.alerts'
        );

        Artisan::command('inventory:assets {--sync}', function () {
            if ($this->option("sync")){
                $this->info("processing...");
                UpdateInventory::dispatchSync();
                $this->info("Synchronously processed inventory updates!");
            } else {
                UpdateInventory::dispatch()->onQueue('default');
                $this->info("Scheduled an inventory update!");
            }
        });

        Artisan::command('inventory:notifications {--sync}', function () {
            if ($this->option("sync")){
                $this->info("processing...");
                SendStockLevelNotifications::dispatchSync();
                $this->info("Synchronously sent notification!");
            } else {
                SendStockLevelNotifications::dispatch()->onQueue('notifications');
                $this->info("Scheduled notifications!");
            }
        });

        Artisan::command('inventory:stocks {location_id} {workspace_id} {--sync}', function ($location_id, $workspace_id) {
            $location = Location::find($location_id);
            if ($location == null){
                $this->error("Location not found");
                return;
            }

            if ($this->option("sync")){
                $this->info("processing...");
                UpdateStockLevels::dispatchSync($location_id,$workspace_id, true);
                $this->info("Synchronously processed stock level updates!");
            } else {
                UpdateStockLevels::dispatch($location_id, $workspace_id)->onQueue('default');
                $this->info("Scheduled an stock level update!");
            }
        });

        Artisan::command('inventory:fix', function () {
            DB::table("recursive_tree_seat_inventory_stock_items")
                ->leftJoin("recursive_tree_seat_inventory_stock_definitions","stock_id","id")
                ->where("id",null)
                ->delete();
            $this->info("Deleted floating stock items!");
        });

        Artisan::command('inventory:categories {--sync}', function () {
            if ($this->option("sync")){
                $this->info("processing...");
                UpdateCategoryMembers::dispatchSync();
                $this->info("Synchronously processed stock level updates!");
            } else {
                UpdateCategoryMembers::dispatch()->onQueue('default');
                $this->info("Scheduled an stock level update!");
            }
        });

        Artisan::command('inventory:images', function () {
            $stocks = Stock::select("id")->pluck("id");
            foreach ($stocks as $id){
                GenerateStockIcon::dispatch($id);
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
        return 'https://github.com/recursivetree/seat-inventory';
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