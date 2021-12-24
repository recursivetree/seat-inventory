<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTables extends Migration
{
    public function up()
    {
        Schema::create('recursive_tree_seat_terminusinv_tracked_locations', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("location_id")->unsigned();
            $table->boolean("is_station")->default(false);
            $table->boolean("is_structure")->default(false);
        });

        Schema::create('recursive_tree_seat_terminusinv_tracked_corporations', function (Blueprint $table) {
            $table->bigInteger("corporation_id")->unsigned();
            $table->bigIncrements("id");
        });

        Schema::create('recursive_tree_seat_terminusinv_fitting_stock', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("location_id");
            $table->bigInteger("ship_type_id");
            $table->string("name");
            $table->bigInteger("fitting_plugin_fitting_id");
            $table->integer("amount");
        });

        Schema::create('recursive_tree_seat_terminusinv_fit_items', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("fitting_id");
            $table->bigInteger("type_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recursive_tree_seat_terminusinv_tracked_locations');
        Schema::drop('recursive_tree_seat_terminusinv_tracked_corporations');
        Schema::drop('recursive_tree_seat_terminusinv_fitting_stock');
        Schema::drop('recursive_tree_seat_terminusinv_fit_items');
    }
}

