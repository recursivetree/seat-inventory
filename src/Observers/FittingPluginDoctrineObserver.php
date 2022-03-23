<?php

namespace RecursiveTree\Seat\Inventory\Observers;

use Exception;
use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\Inventory\Helpers\DoctrineCategorySyncHelper;
use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Helpers\Parser;
use RecursiveTree\Seat\Inventory\Jobs\SyncDoctrine;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockItem;

class FittingPluginDoctrineObserver
{
    public function saved($doctrine){
        //due to the inner workings of seat-fitting, the observer is triggerred before the fittings are saved
        SyncDoctrine::dispatch($doctrine)->delay(carbon()->addSeconds(10));
    }

    public function created($doctrine){
        //due to the inner workings of seat-fitting, the observer is triggerred before the fittings are saved
        SyncDoctrine::dispatch($doctrine)->delay(carbon()->addSeconds(10));
    }

    public function deleted($doctrine){
        DoctrineCategorySyncHelper::removeDoctrine($doctrine);
    }
}