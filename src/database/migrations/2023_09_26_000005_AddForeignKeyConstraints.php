<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_inventory_inventory_item', function (Blueprint $table) {
            $table->bigInteger('source_id')->unsigned()->change();
        });
        Schema::table('recursive_tree_seat_inventory_inventory_item', function (Blueprint $table) {
            $table->foreign('source_id')
                ->references('id')
                ->on('seat_inventory_inventory_source')
                ->onDelete('cascade');
        });

        Schema::table('recursive_tree_seat_inventory_stock_items', function (Blueprint $table) {
            $table->bigInteger('stock_id')->unsigned()->change();
        });
        Schema::table('recursive_tree_seat_inventory_stock_items', function (Blueprint $table) {
            $table->foreign('stock_id')
                ->references('id')
                ->on('recursive_tree_seat_inventory_stock_definitions')
                ->onDelete('cascade');
        });

        Schema::rename('recursive_tree_seat_inventory_stock_category_mapping','seat_inventory_stock_category_mapping');
        Schema::table('seat_inventory_stock_category_mapping', function (Blueprint $table) {
            $table->bigInteger('category_id')->unsigned()->change();
            $table->bigInteger('stock_id')->unsigned()->change();
        });
        Schema::table('seat_inventory_stock_category_mapping', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('recursive_tree_seat_inventory_stock_categories')
                ->onDelete('cascade');

            $table->foreign('stock_id')
                ->references('id')
                ->on('recursive_tree_seat_inventory_stock_definitions')
                ->onDelete('cascade');
        });

        Schema::table('recursive_tree_seat_inventory_stock_levels', function (Blueprint $table) {
            $table->bigInteger('stock_id')->unsigned()->change();
        });
        Schema::table('recursive_tree_seat_inventory_stock_levels', function (Blueprint $table) {
            $table->foreign('stock_id')
                ->references('id')
                ->on('recursive_tree_seat_inventory_stock_definitions')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('recursive_tree_seat_inventory_inventory_item', function (Blueprint $table) {
            $table->dropForeign('recursive_tree_seat_inventory_inventory_item_source_id_foreign');
        });

        Schema::table('recursive_tree_seat_inventory_stock_items', function (Blueprint $table) {
            $table->dropForeign('recursive_tree_seat_inventory_stock_items_stock_id_foreign');
        });

        Schema::table('seat_inventory_stock_category_mapping', function (Blueprint $table) {
            $table->dropForeign('seat_inventory_stock_category_mapping_category_id_foreign');
            $table->dropForeign('seat_inventory_stock_category_mapping_stock_id_foreign');
        });
        Schema::rename('seat_inventory_stock_category_mapping','recursive_tree_seat_inventory_stock_category_mapping');

        Schema::table('recursive_tree_seat_inventory_stock_levels', function (Blueprint $table) {
            $table->dropForeign('recursive_tree_seat_inventory_stock_levels_stock_id_foreign');
        });
    }
};

