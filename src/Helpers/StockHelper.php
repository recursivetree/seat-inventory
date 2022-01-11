<?php

namespace RecursiveTree\Seat\TerminusInventory\Helpers;

use RecursiveTree\Seat\TerminusInventory\Models\Stock;

class StockHelper
{
    public static function getStocksFromLocation($location){
        if(!$location->valid){
            return collect();
        }

        if($location->station_id != null){
            $stocks = Stock::where("station_id",$location->station_id)->get();
        } else if ($location->structure_id != null){
            $stocks = Stock::where("structure_id",$location->structure_id)->get();
        }

        return $stocks;
    }

    public static function computeStockLevels(){

    }
}