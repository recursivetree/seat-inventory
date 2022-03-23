<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

use RecursiveTree\Seat\Inventory\Helpers\DoctrineCategorySyncHelper;
use RecursiveTree\Seat\Inventory\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockCategory;


class Categories extends Migration
{
    public function up()
    {
        if(!Schema::hasTable('recursive_tree_seat_inventory_stock_categories')) {
            Schema::create('recursive_tree_seat_inventory_stock_categories', function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->string("name");
                $table->bigInteger("fitting_plugin_doctrine_id")->nullable()->default(null);
            });
        }

        if(!Schema::hasTable('recursive_tree_seat_inventory_stock_category_mapping')) {
            Schema::create('recursive_tree_seat_inventory_stock_category_mapping', function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->bigInteger("stock_id");
                $table->bigInteger("category_id");
            });
        }

        if(FittingPluginHelper::pluginIsAvailable()){
            DoctrineCategorySyncHelper::sync();
        }

        $category = new StockCategory();
        $category->name = "Uncategorized";
        $category->save();

        $category->stocks()->syncWithoutDetaching(Stock::pluck("id"));
    }

    public function down()
    {
        Schema::drop('recursive_tree_seat_inventory_stock_categories');
        Schema::drop('recursive_tree_seat_inventory_stock_category_mapping');
    }
}

