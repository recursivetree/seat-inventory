<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::table("seat_inventory_tracked_corporations",function (Blueprint $table){
            $table->boolean("include_fuel_bay")->default(true);
        });
    }

    public function down()
    {
        Schema::table("seat_inventory_tracked_corporations",function (Blueprint $table){
            $table->dropColumn("include_fuel_bay");
        });
    }
};

