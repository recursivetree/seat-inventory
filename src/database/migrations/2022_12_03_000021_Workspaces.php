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
            $table->boolean("enable_notifications")->default(false);
        });

        DB::table('recursive_tree_seat_inventory_workspaces')
            ->insert([
               'name'=>'Default Wokspace'
            ]);

        $default_workspace_id = DB::table('recursive_tree_seat_inventory_workspaces')->first()->id;

        //we can't use the corporation id as primary key anymore, because corporation might be added to multiple workspaces
        //because of DB weirdness, we have to do it in two steps
        //1. remove primary
        //2. add new primary
        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->dropPrimary('corporation_id');
        });
        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->bigInteger('corporation_id')->change();
            $table->bigIncrements("id");
            $table->bigInteger("workspace_id");
            $table->index("workspace_id");
        });
        DB::statement("UPDATE seat_inventory_tracked_corporations SET workspace_id = $default_workspace_id");

        //we can't use the alliance id as primary key anymore, because corporation might be added to multiple workspaces
        //because of DB weirdness, we have to do it in two steps
        //1. remove primary
        //2. add new primary
        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->dropPrimary('alliance_id');
        });
        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->bigInteger('alliance_id')->change();
            $table->bigIncrements("id");
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

        Schema::table('recursive_tree_seat_inventory_stock_definitions', function (Blueprint $table) {
            $table->bigInteger("workspace_id");
        });
        DB::statement("UPDATE recursive_tree_seat_inventory_stock_definitions SET workspace_id = $default_workspace_id");

        Schema::table('recursive_tree_seat_inventory_stock_categories', function (Blueprint $table) {
            $table->bigInteger("workspace_id");
        });
        DB::statement("UPDATE recursive_tree_seat_inventory_stock_categories SET workspace_id = $default_workspace_id");
    }

    public function down()
    {
        Schema::drop('recursive_tree_seat_inventory_workspaces');

        //db weirdness again: you need to change primary keys in two steps
        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->dropColumn("id");
            $table->dropColumn("workspace_id");
        });
        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->primary("corporation_id");
        });

        //db weirdness again: you need to change primary keys in two steps
        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->dropColumn("id");
            $table->dropColumn("workspace_id");
        });
        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->primary("alliance_id");
        });

        Schema::rename('seat_inventory_inventory_source','recursive_tree_seat_inventory_inventory_source');
        Schema::table('recursive_tree_seat_inventory_inventory_source', function (Blueprint $table) {
            $table->dropColumn("workspace_id");
            $table->dropIndex("seat_inventory_inventory_source_location_id_index");
        });

        Schema::table('recursive_tree_seat_inventory_stock_definitions', function (Blueprint $table) {
            $table->dropColumn("workspace_id");
        });

        Schema::table('recursive_tree_seat_inventory_stock_categories', function (Blueprint $table) {
            $table->dropColumn("workspace_id");
        });
    }
}

