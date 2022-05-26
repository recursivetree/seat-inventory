<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocationCategories extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_locations',function (Blueprint $table){
            $table->bigInteger("category_id")->nullable();
        });
    }

    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_locations',function (Blueprint $table){
            $table->dropColumn("category_id");
        });
    }
}

