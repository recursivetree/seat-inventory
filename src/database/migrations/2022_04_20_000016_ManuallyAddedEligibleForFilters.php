<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveTree\Seat\Inventory\Jobs\UpdateCategoryMembers;

class ManuallyAddedEligibleForFilters extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_stock_category_mapping', function (Blueprint $table) {
            $table->boolean("category_eligible")->default(false);
        });

        //update with new data
        UpdateCategoryMembers::dispatchNow();
    }

    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_stock_category_mapping', function (Blueprint $table) {
            $table->dropColumn("category_eligible");
        });
    }
}

