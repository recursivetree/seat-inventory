<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Workspaces extends Migration
{
    public function up()
    {
        Schema::create('recursive_tree_seat_inventory_workspaces', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("name");
        });

        $default = new \RecursiveTree\Seat\Inventory\Models\Workspace();
        $default->name = "Default Workspace";
        $default->save();

        $default_workspace_id = $default->id;

        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->bigInteger("workspace_id");
            $table->index("workspace_id");
        });
        DB::statement("UPDATE seat_inventory_tracked_corporations SET workspace_id = $default_workspace_id");

        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->bigInteger("workspace_id");
            $table->index("workspace_id");
        });
        DB::statement("UPDATE seat_inventory_tracked_alliances SET workspace_id = $default_workspace_id");

        Schema::rename('recursive_tree_seat_inventory_inventory_source','seat_inventory_inventory_source');

        Schema::table('seat_inventory_inventory_source', function (Blueprint $table) {
            $table->bigInteger("workspace_id");
            $table->index("workspace_id");
            $table->index("location_id");
        });
        DB::statement("UPDATE seat_inventory_inventory_source SET workspace_id = $default_workspace_id");
    }

    public function down()
    {
        Schema::drop('recursive_tree_seat_inventory_workspaces');
        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->dropColumn("workspace_id");
        });
        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->dropColumn("workspace_id");
        });
        Schema::rename('seat_inventory_inventory_source','recursive_tree_seat_inventory_inventory_source');
        Schema::table('recursive_tree_seat_inventory_inventory_source', function (Blueprint $table) {
            $table->dropColumn("workspace_id");
        });
    }
}

