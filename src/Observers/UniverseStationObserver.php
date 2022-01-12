<?php

namespace RecursiveTree\Seat\TerminusInventory\Observers;

use Exception;
use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\TerminusInventory\Helpers\ItemHelper;
use RecursiveTree\Seat\TerminusInventory\Helpers\LocationHelper;
use RecursiveTree\Seat\TerminusInventory\Helpers\Parser;
use RecursiveTree\Seat\TerminusInventory\Models\Location;
use RecursiveTree\Seat\TerminusInventory\Models\Stock;
use RecursiveTree\Seat\TerminusInventory\Models\StockItem;

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