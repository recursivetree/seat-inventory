<?php

namespace RecursiveTree\Seat\Inventory\Observers;

use Exception;
use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Helpers\LocationHelper;
use RecursiveTree\Seat\Inventory\Helpers\Parser;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockItem;

class UniverseStationObserver
{
    public function saved($station){
        $location = Location::where("station_id",$station->station_id)->first();

        if($location == null){
            $location = new Location();
        }

        $location->station_id = $station->station_id;
        $location->name = $station->name;
        $location->save();
    }
}