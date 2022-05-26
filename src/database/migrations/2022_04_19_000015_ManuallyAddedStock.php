<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class ManuallyAddedStock extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_stock_category_mapping', function (Blueprint $table) {
            $table->boolean("manually_added")->default(true);
        });
    }

    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_stock_category_mapping', function (Blueprint $table) {
            $table->dropColumn("manually_added");
        });
    }
}

