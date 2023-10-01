<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::rename('recursive_tree_seat_inventory_inventory_item','seat_inventory_inventory_item');
        Schema::rename('recursive_tree_seat_inventory_locations','seat_inventory_inventory_locations');
        Schema::rename('recursive_tree_seat_inventory_stock_categories','seat_inventory_stock_categories');
        Schema::rename('recursive_tree_seat_inventory_stock_definitions','seat_inventory_stocks');
        Schema::rename('recursive_tree_seat_inventory_stock_items','seat_inventory_stock_items');
        Schema::rename('recursive_tree_seat_inventory_stock_levels','seat_inventory_stock_levels');
        Schema::rename('recursive_tree_seat_inventory_workspaces','seat_inventory_workspaces');
    }

    public function down()
    {
        Schema::rename('seat_inventory_inventory_item','recursive_tree_seat_inventory_inventory_item');
        Schema::rename('seat_inventory_inventory_locations','recursive_tree_seat_inventory_locations');
        Schema::rename('seat_inventory_stock_categories','recursive_tree_seat_inventory_stock_categories');
        Schema::rename('seat_inventory_stocks','recursive_tree_seat_inventory_stock_definitions');
        Schema::rename('seat_inventory_stock_items','recursive_tree_seat_inventory_stock_items');
        Schema::rename('seat_inventory_stock_levels','recursive_tree_seat_inventory_stock_levels');
        Schema::rename('seat_inventory_workspaces', 'recursive_tree_seat_inventory_workspaces');
    }
};

