<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use Seat\Services\Models\Schedule;

class RemoveSourceChecks extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_stock_definitions', function (Blueprint $table) {
            $table->dropColumn("check_contracts");
            $table->dropColumn("check_corporation_hangars");
        });
    }

    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_stock_definitions', function (Blueprint $table) {
            $table->boolean("check_contracts");                         //should we check contracts for this stock definition
            $table->boolean("check_corporation_hangars");               //should we check corporation hangars for this stock definition
        });
    }
}

