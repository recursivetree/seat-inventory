<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTables extends Migration
{
    public function up()
    {
        Schema::create('recursive_tree_seat_terminusinv_tracked_corporations', function (Blueprint $table) {
            $table->bigInteger("corporation_id")->unsigned();
            $table->bigIncrements("id");
        });

        Schema::create('recursive_tree_seat_terminusinv_stock_definitions', function (Blueprint $table) {
            $table->bigIncrements("id"); //id
            $table->bigInteger("structure_id")->nullable();             //citadel where the item is stored
            $table->bigInteger("station_id")->nullable();               //npc station of the item
            $table->string("name");                                     //name of the stock definition(fit name or something else)
            $table->bigInteger("fitting_plugin_fitting_id")->nullable();//used to reload the data from the fitting plugin
            $table->integer("amount");                                  //how many multiples of this stock definition should be kept in stock
            $table->boolean("check_contracts");                         //should we check contracts for this stock definition
            $table->boolean("check_corporation_hangars");               //should we check corporation hangars for this stock definition
        });

        Schema::create('recursive_tree_seat_terminusinv_stock_items', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("stock_id");
            $table->bigInteger("type_id");
            $table->bigInteger("amount")->unsigned();
        });

        Schema::create('recursive_tree_seat_terminusinv_inventory_source', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("structure_id")->nullable();                 //citadel where the item is stored
            $table->bigInteger("station_id")->nullable();                   //npc station of the item
            $table->string("source_name");                                  //the name of this inventory source, e.g. a corporation hangar or contract
            $table->enum('source_type', ['corporation_hangar', 'contract']);//type of source
        });

        Schema::create('recursive_tree_seat_terminusinv_inventory_item', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("source_id");
            $table->bigInteger("type_id");
            $table->bigInteger("amount")->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recursive_tree_seat_terminusinv_tracked_corporations');
        Schema::drop('recursive_tree_seat_terminusinv_stock');
        Schema::drop('recursive_tree_seat_terminusinv_stock_items');
        Schema::drop('recursive_tree_seat_terminusinv_inventory_source');
        Schema::drop('recursive_tree_seat_terminusinv_inventory_item');
    }
}

