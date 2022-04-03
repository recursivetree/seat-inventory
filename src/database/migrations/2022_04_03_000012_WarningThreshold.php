<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


use RecursiveTree\Seat\Inventory\Jobs\GenerateStockIcon;
use RecursiveTree\Seat\Inventory\Models\Stock;



class WarningThreshold extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_stock_definitions',function (Blueprint $table){
            $table->integer("warning_threshold")->unsigned()->nullable();
        });

        DB::statement("update recursive_tree_seat_inventory_stock_definitions set warning_threshold=amount;");

        Schema::table('recursive_tree_seat_inventory_stock_definitions',function (Blueprint $table){
            $table->integer("warning_threshold")->unsigned()->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_stock_definitions',function (Blueprint $table){
            $table->dropColumn("warning_threshold");
        });
    }
}

