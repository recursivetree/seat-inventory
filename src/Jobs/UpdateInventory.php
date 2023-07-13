<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use RecursiveTree\Seat\Inventory\Helpers\ContractHelper;
use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Models\InventoryItem;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\TrackedAlliance;
use RecursiveTree\Seat\Inventory\Models\TrackedCorporation;
use RecursiveTree\Seat\Inventory\Models\Workspace;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class UpdateInventory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function tags()
    {
        return ["seat-inventory", "sources"];
    }

    public function handle()
    {
        $workspaces = Workspace::all();
        foreach ($workspaces as $workspace){
            $this->handleWorkspace($workspace);
        }
    }

    public function handleWorkspace(Workspace $workspace): void
    {
        UpdateCorporationAssets::dispatch($workspace);
        UpdateContracts::dispatch($workspace);

        foreach ($workspace->markets as $market) {
            UpdateStructureOrders::dispatch($market->character->refresh_token, $market->location,$workspace);
        }
    }
}