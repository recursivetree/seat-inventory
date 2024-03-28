<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        DB::table('seat_inventory_stocks')
            ->where('fitting_plugin_fitting_id','!=',null)
            ->update(['fitting_plugin_fitting_id'=>null]);

        DB::table('seat_inventory_stock_categories')
            ->where('fitting_plugin_doctrine_id','!=',null)
            ->update(['fitting_plugin_doctrine_id'=>null]);
    }

    public function down()
    {

    }
};

