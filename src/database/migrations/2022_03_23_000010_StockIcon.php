<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveTree\Seat\Inventory\Jobs\GenerateStockIcon;
use RecursiveTree\Seat\Inventory\Models\Stock;


class StockIcon extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_stock_definitions',function (Blueprint $table){
            $table->mediumText("icon")->nullable();
        });

        foreach (Stock::pluck("id") as $stock_id){
            GenerateStockIcon::dispatchNow($stock_id,null);
        }
    }

    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_stock_definitions',function (Blueprint $table){
            $table->dropColumn("icon");
        });
    }
}

