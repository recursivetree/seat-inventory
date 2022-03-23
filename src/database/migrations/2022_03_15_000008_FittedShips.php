<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

use RecursiveTree\Seat\Inventory\Jobs\UpdateInventory;
use RecursiveTree\Seat\Inventory\Models\InventorySource;

class FittedShips extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_inventory_source', function (Blueprint $table) {
            DB::statement("ALTER TABLE `recursive_tree_seat_inventory_inventory_source` CHANGE `source_type` `source_type` ENUM('corporation_hangar', 'contract', 'in_transport','fitted_ship');");
        });

        UpdateInventory::dispatch()->onQueue('default');
    }

    public function down()
    {
        //data will be ignored
    }
}

