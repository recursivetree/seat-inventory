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
    }

    public function down()
    {
        Schema::drop('recursive_tree_seat_inventory_workspaces');
    }
}

