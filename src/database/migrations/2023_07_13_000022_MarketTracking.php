<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class MarketTracking extends Migration
{
    public function up()
    {
        Schema::create('seat_inventory_tracked_markets', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("location_id")->unsigned();
            $table->bigInteger("workspace_id");
            $table->bigInteger("character_id");

            $table->index("workspace_id");
        });
    }

    public function down()
    {
        Schema::drop('seat_inventory_tracked_markets');
    }
}

