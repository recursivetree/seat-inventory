<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


use RecursiveTree\Seat\Inventory\Jobs\GenerateStockIcon;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockCategory;


class CollectFilters extends Migration
{
    public function up()
    {
        //migrate doctrine filters
        $categories = StockCategory::where("fitting_plugin_doctrine_id","!=",null)->get();
        foreach ($categories as $category){
            if($category->filters) {
                $filters = json_decode($category->filters);
            } else {
                $filters = [];
            }

            $filters[] = [
                "type"=>"doctrine",
                "id"=>$category->fitting_plugin_doctrine_id
            ];

            $category->filters = json_encode($filters);
            $category->save();
        }

        //migrate location categories
        $locations = Location::where("category_id","!=",null)->get();
        foreach ($locations as $location){
            $category = StockCategory::find($location->category_id);
            if(!$category) return;

            if($category->filters) {
                $filters = json_decode($category->filters);
            } else {
                $filters = [];
            }

            $filters[] = [
                "type"=>"location",
                "id"=>$location->id
            ];

            $category->filters = json_encode($filters);
            $category->save();
        }
    }

    public function down()
    {
        //there is no way to undo this
    }
}

