<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssetTrackingTables extends Migration
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
    }
}

