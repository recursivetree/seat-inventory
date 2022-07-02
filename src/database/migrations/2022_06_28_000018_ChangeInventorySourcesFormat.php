<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use Seat\Services\Models\Schedule;

class ChangeInventorySourcesFormat extends Migration
{
    public function up()
    {

        DB::statement("ALTER TABLE `recursive_tree_seat_inventory_inventory_source` CHANGE COLUMN `source_type` `source_type` varchar(255);");

        if (!Schema::hasTable('recursive_tree_seat_inventory_stock_levels')) {
            Schema::create('recursive_tree_seat_inventory_stock_levels', function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->bigInteger("stock_id");
                $table->string("source_type");
                $table->bigInteger("amount")->unsigned();
            });
        }

        Schema::table('recursive_tree_seat_inventory_stock_definitions', function (Blueprint $table) {
            $table->bigInteger("available")->unsigned()->default(0);
            $table->dropColumn("available_on_contracts");
            $table->dropColumn("available_in_hangars");
        });
    }

    public function down()
    {
        //remove invalid sources
        InventorySource::whereNotIn("source_type",['corporation_hangar', 'contract', 'in_transport','fitted_ship'])->delete();
        //change it back to an enum
        DB::statement("ALTER TABLE `recursive_tree_seat_inventory_inventory_source` CHANGE COLUMN `source_type` `source_type` ENUM('corporation_hangar', 'contract', 'in_transport','fitted_ship');");

        Schema::table('recursive_tree_seat_inventory_stock_definitions', function (Blueprint $table) {
            $table->dropColumn("available");
            $table->integer("available_on_contracts")->unsigned()->default(0);
            $table->integer("available_in_hangars")->unsigned()->default(0);
        });
    }
}

