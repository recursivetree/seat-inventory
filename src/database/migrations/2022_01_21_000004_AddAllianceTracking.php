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

class AddAllianceTracking extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('recursive_tree_seat_inventory_tracked_alliances')) {
            Schema::create('recursive_tree_seat_inventory_tracked_alliances', function (Blueprint $table) {
                $table->bigInteger("alliance_id")->unsigned();
                $table->bigIncrements("id");
                $table->boolean("automate_corporations")->default(false);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('recursive_tree_seat_inventory_tracked_alliances')) {
            Schema::drop('recursive_tree_seat_inventory_tracked_alliances');
        }
    }
}

