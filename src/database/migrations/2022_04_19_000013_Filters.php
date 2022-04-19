<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


use RecursiveTree\Seat\Inventory\Jobs\GenerateStockIcon;
use RecursiveTree\Seat\Inventory\Models\Stock;



class Filters extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_stock_categories', function (Blueprint $table) {
            $table->string('filters')->nullable();
        });
    }

    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_stock_categories', function (Blueprint $table) {
            $table->dropColumns('filters');
        });
    }
}

