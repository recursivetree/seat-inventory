<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use Seat\Services\Models\Schedule;

class AddprimaryKeys extends Migration
{
    public function up()
    {
        //rename because table names are too long...
        Schema::rename('recursive_tree_seat_inventory_tracked_corporations','seat_inventory_tracked_corporations');
        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->primary("corporation_id");
        });

        //rename because table names are too long...
        Schema::rename('recursive_tree_seat_inventory_tracked_alliances','seat_inventory_tracked_alliances');
        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->primary("alliance_id");
        });
    }

    public function down()
    {
        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->dropPrimary("alliance_id_primary");
        });
        Schema::rename('seat_inventory_tracked_alliances', 'recursive_tree_seat_inventory_tracked_alliances');

        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->dropPrimary("corporation_id_primary");
        });
        Schema::rename('seat_inventory_tracked_corporations', 'recursive_tree_seat_inventory_tracked_corporations');
    }
}

