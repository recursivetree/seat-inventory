<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::table('seat_inventory_tracked_markets', function (Blueprint $table) {
            $table->foreign('workspace_id')
                ->references('id')
                ->on('seat_inventory_workspaces')
                ->onDelete('cascade');
        });

        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->bigInteger('workspace_id')->unsigned()->change();
        });
        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->foreign('workspace_id')
                ->references('id')
                ->on('seat_inventory_workspaces')
                ->onDelete('cascade');
        });

        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->bigInteger('workspace_id')->unsigned()->change();
        });
        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->foreign('workspace_id')
                ->references('id')
                ->on('seat_inventory_workspaces')
                ->onDelete('cascade');
        });

        Schema::table('seat_inventory_stocks', function (Blueprint $table) {
            $table->bigInteger('workspace_id')->unsigned()->change();
        });
        Schema::table('seat_inventory_stocks', function (Blueprint $table) {
            $table->foreign('workspace_id')
                ->references('id')
                ->on('seat_inventory_workspaces')
                ->onDelete('cascade');
        });

        Schema::table('seat_inventory_stock_categories', function (Blueprint $table) {
            $table->bigInteger('workspace_id')->unsigned()->change();
        });
        Schema::table('seat_inventory_stock_categories', function (Blueprint $table) {
            $table->foreign('workspace_id')
                ->references('id')
                ->on('seat_inventory_workspaces')
                ->onDelete('cascade');
        });

        Schema::table('seat_inventory_inventory_source', function (Blueprint $table) {
            $table->bigInteger('workspace_id')->unsigned()->change();
        });
        Schema::table('seat_inventory_inventory_source', function (Blueprint $table) {
            $table->foreign('workspace_id')
                ->references('id')
                ->on('seat_inventory_workspaces')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('seat_inventory_tracked_markets', function (Blueprint $table) {
            $table->dropForeign('seat_inventory_tracked_markets_workspace_id_foreign');
        });

        Schema::table('seat_inventory_tracked_corporations', function (Blueprint $table) {
            $table->dropForeign('seat_inventory_tracked_corporations_workspace_id_foreign');
        });

        Schema::table('seat_inventory_tracked_alliances', function (Blueprint $table) {
            $table->dropForeign('seat_inventory_tracked_alliances_workspace_id_foreign');
        });

        Schema::table('seat_inventory_stocks', function (Blueprint $table) {
            $table->dropForeign('seat_inventory_stocks_workspace_id_foreign');
        });

        Schema::table('seat_inventory_stock_categories', function (Blueprint $table) {
            $table->dropForeign('seat_inventory_stock_categories_workspace_id_foreign');
        });

        Schema::table('seat_inventory_inventory_source', function (Blueprint $table) {
            $table->dropForeign('seat_inventory_inventory_source_workspace_id_foreign');
        });
    }
};

