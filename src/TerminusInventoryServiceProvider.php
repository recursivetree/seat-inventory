<?php

namespace RecursiveTree\Seat\TerminusInventory;

use Seat\Services\AbstractSeatPlugin;
use Illuminate\Support\Facades\Blade;

class TerminusInventoryServiceProvider extends AbstractSeatPlugin
{
    public function boot(){
        if (! $this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }

        $this->loadTranslationsFrom(__DIR__ . '/resources/lang/', 'terminusinv');
        $this->loadViewsFrom(__DIR__ . '/resources/views/', 'terminusinv');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');

        $this->publishes([
            __DIR__ . '/resources/js' => public_path('terminusinventory/js')
        ]);

        $version = $this->getVersion();
        //always reload the cache in dev builds
        if($version=="missing"){
            $version=rand();
        }

        Blade::directive('versionedAsset', function($path) use ($version) {
            return "<?php echo asset({$path}) . '?v=$version'; ?>";
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