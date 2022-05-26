<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllianceTracking extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('recursive_tree_seat_inventory_tracked_alliances')) {
            Schema::create('recursive_tree_seat_inventory_tracked_alliances', function (Blueprint $table) {
                $table->bigInteger("alliance_id")->unsigned();
                $table->bigIncrements("id");
                $table->boolean("automate_corporations")->default(false);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('recursive_tree_seat_inventory_tracked_alliances')) {
            Schema::drop('recursive_tree_seat_inventory_tracked_alliances');
        }
    }
}

