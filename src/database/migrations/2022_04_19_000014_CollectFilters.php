<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\StockCategory;


class CollectFilters extends Migration
{
    public function up()
    {
        // we can't cleanly do this anymore after the seat 5 update, just reset the data
        // this is why updating to the latest seat 4 version is required
        DB::table('recursive_tree_seat_inventory_stock_categories')
            ->update(['filters'=>json_encode([])]);
    }

    public function down()
    {
        //there is no way to undo this
    }
}

