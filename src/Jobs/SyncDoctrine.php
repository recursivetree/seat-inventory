<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use RecursiveTree\Seat\Inventory\Helpers\ContractHelper;
use RecursiveTree\Seat\Inventory\Helpers\DoctrineCategorySyncHelper;
use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Models\InventoryItem;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\TrackedAlliance;
use RecursiveTree\Seat\Inventory\Models\TrackedCorporation;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Contracts\CorporationContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class SyncDoctrine implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function tags()
    {
        return ["seat-inventory", "categories","seat-fitting"];
    }

    public function handle()
    {
        DoctrineCategorySyncHelper::syncDoctrine($this->doctrine);
    }
}