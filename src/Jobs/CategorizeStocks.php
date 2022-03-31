<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use RecursiveTree\Seat\Inventory\Models\StockCategory;


class CategorizeStocks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function tags()
    {
        return ["seat-inventory", "stock","categories"];
    }

    public function handle()
    {
        $stocks = Stock::all();

        foreach ($stocks as $stock){
            $location = Location::find($stock->location_id);

            if(!$location->category()->exists()){
                $category = new StockCategory();
                $category->name = "Location: $location->name";
                $category->save();

                $location->category_id = $category->id;
                $location->save();
            } else {
                $category = $location->category;
            }

            $stock->categories()->syncWithoutDetaching($category->id);
        }
    }
}