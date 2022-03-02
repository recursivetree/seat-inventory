<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

use RecursiveTree\Seat\Inventory\Jobs\UpdateInventory;

class AddInMoveStocks extends Migration
{
    public function up()
    {

        DB::statement("ALTER TABLE `recursive_tree_seat_inventory_inventory_source` CHANGE `source_type` `source_type` ENUM('corporation_hangar', 'contract', 'in_transport');");

        UpdateInventory::dispatch()->onQueue('default');
    }

    public function down()
    {
        DB::statement("ALTER TABLE `recursive_tree_seat_inventory_inventory_source` CHANGE `source_type` `source_type` ENUM('corporation_hangar', 'contract');");
    }
}

