<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('recursive_tree_seat_inventory_tracked_corporations')) {
            Schema::create('recursive_tree_seat_inventory_tracked_corporations', function (Blueprint $table) {
                $table->bigInteger("corporation_id")->unsigned();
                $table->bigIncrements("id");
            });
        }

        if (!Schema::hasTable('recursive_tree_seat_inventory_stock_definitions')) {
            Schema::create('recursive_tree_seat_inventory_stock_definitions', function (Blueprint $table) {
                $table->bigIncrements("id");                                //id
                $table->bigInteger("location_id");                          //location of the item
                $table->string("name");                                     //name of the stock definition(fit name or something else)
                $table->bigInteger("fitting_plugin_fitting_id")->nullable();//used to reload the data from the fitting plugin
                $table->integer("amount");                                  //how many multiples of this stock definition should be kept in stock
                $table->boolean("check_contracts");                         //should we check contracts for this stock definition
                $table->boolean("check_corporation_hangars");               //should we check corporation hangars for this stock definition
            });
        }

        if (!Schema::hasTable('recursive_tree_seat_inventory_stock_items')) {
            Schema::create('recursive_tree_seat_inventory_stock_items', function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->bigInteger("stock_id");
                $table->bigInteger("type_id");
                $table->bigInteger("amount")->unsigned();
            });
        }

        if (!Schema::hasTable('recursive_tree_seat_inventory_inventory_source')) {
            Schema::create('recursive_tree_seat_inventory_inventory_source', function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->bigInteger("location_id")->nullable();                  //location of the item
                $table->string("source_name");                                  //the name of this inventory source, e.g. a corporation hangar or contract
                $table->enum('source_type', ['corporation_hangar', 'contract']);//type of source
            });
        }

        if (!Schema::hasTable('recursive_tree_seat_inventory_inventory_item')) {
            Schema::create('recursive_tree_seat_inventory_inventory_item', function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->bigInteger("source_id");
                $table->bigInteger("type_id");
                $table->bigInteger("amount")->unsigned();
            });
        }

        if (!Schema::hasTable('recursive_tree_seat_inventory_locations')) {
            Schema::create('recursive_tree_seat_inventory_locations', function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->bigInteger("structure_id")->nullable();                 //citadel where the item is stored
                $table->bigInteger("station_id")->nullable();                   //npc station of the item
                $table->string("name");
            });
        }

        $stations = DB::table('universe_stations')->get();
        foreach ($stations as $station){
            DB::table('recursive_tree_seat_inventory_locations')->insert([
                'station_id'=>$station->station_id,
                'name'=>$station->name,
            ]);
        }

        $structures = DB::table('universe_structures')->get();
        foreach ($structures as $structure){
            DB::table('recursive_tree_seat_inventory_locations')->insert([
                'structure_id'=>$structure->structure_id,
                'name'=>$structure->name,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recursive_tree_seat_inventory_tracked_corporations');
        Schema::drop('recursive_tree_seat_inventory_stock_definitions');
        Schema::drop('recursive_tree_seat_inventory_stock_items');
        Schema::drop('recursive_tree_seat_inventory_inventory_source');
        Schema::drop('recursive_tree_seat_inventory_inventory_item');
        Schema::drop('recursive_tree_seat_inventory_locations');
    }
}

