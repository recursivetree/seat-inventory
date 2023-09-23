<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarketTracking extends Migration
{
    public function up()
    {
        Schema::create('seat_inventory_tracked_markets', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("location_id")->unsigned();
            $table->bigInteger("workspace_id")->unsigned();
            $table->bigInteger("character_id")->unsigned();

            $table->index('workspace_id');
        });
    }

    public function down()
    {
        Schema::drop('recursive_tree_seat_inventory_tracked_alliances');
    }
}

