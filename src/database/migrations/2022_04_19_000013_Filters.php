<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            $table->dropColumn('filters');
        });
    }
}

