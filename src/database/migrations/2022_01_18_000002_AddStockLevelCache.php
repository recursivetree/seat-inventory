<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use RecursiveTree\Seat\Inventory\Jobs\UpdateInventory;
use RecursiveTree\Seat\Inventory\Jobs\UpdateStockLevels;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use RecursiveTree\Seat\Inventory\Observers\UniverseStationObserver;
use RecursiveTree\Seat\Inventory\Observers\UniverseStructureObserver;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class AddStockLevelCache extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_stock_definitions',function (Blueprint $table){
            $table->integer("available_on_contracts")->unsigned()->default(0);
            $table->integer("available_in_hangars")->unsigned()->default(0);
        });

        Schema::table('recursive_tree_seat_inventory_stock_items',function (Blueprint $table){
            $table->integer("missing_items")->unsigned()->default(0);
        });

        $ids = InventorySource::all()->pluck("location_id")->unique();
        foreach ($ids as $id) {
            UpdateStockLevels::dispatch($id)->onQueue('default');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_stock_definitions',function (Blueprint $table){
            $table->dropColumn("available_on_contracts");
            $table->dropColumn("available_in_hangars")->unsigned()->default(0);
        });

        Schema::table('recursive_tree_seat_inventory_stock_items',function (Blueprint $table){
            $table->dropColumn("missing_items");
        });
    }
}

