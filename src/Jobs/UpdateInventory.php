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
        return ["seat-inventory", "assets"];
    }

    private static $ITEM_BLACKLIST = [
        27 //Corporation Hangar Office
    ];

    public function handle()
    {
        Redis::funnel('seat-inventory-asset-lock')->limit(1)->then(function () {
            //get all workspaces
            $workspaces = Workspace::all();

            //update each workspace
            foreach ($workspaces as $workspace){
                $this->handleWorkspace($workspace);
            }
        }, function () {
            $this->delete(); // update is already in progress
        });
    }

    public function handleWorkspace($workspace){
        $corporations = TrackedCorporation::where("workspace_id",$workspace->id)->pluck("corporation_id");
        $alliances = TrackedAlliance::where("workspace_id",$workspace->id)->pluck("alliance_id");

        $this->handleContracts($corporations, $alliances, $workspace->id);
        $this->handleCorporationAssets($corporations, $workspace->id);

        //TODO
//        $ids = Stock::pluck("location_id")->unique();
//        foreach ($ids as $id) {
//            UpdateStockLevels::dispatch($id)->onQueue('default');
//        }
    }

    private function handleContracts($corporation_ids, $alliance_ids, $workspace_id)
    {
        //collect both corporations and alliances
        $valid_assignee_ids = $corporation_ids->merge($alliance_ids);

        $sources = InventorySource::where("source_type","contract")
            ->where("workspace_id", $workspace_id)
            ->get();
        foreach ($sources as $source){
            $source->delete();
            InventoryItem::where("source_id", $source->id)->delete();
        }

        //get the time from around when the query is triggered, so in case the db updates assets in between, we only guarantee the old state
        $time = carbon();

        $contracts = ContractDetail::where("type", "item_exchange")
            ->where("status", "outstanding")
            ->whereDate("date_expired",">",$time)
            ->whereIn("assignee_id", $valid_assignee_ids)
            ->get();

        foreach ($contracts as $contract) {
            $items = [];

            foreach ($contract->lines as $item) {
                $items[] = new ItemHelper($item->type_id, $item->quantity);
            }

            $station_id = $contract->end_location->station_id;
            $structure_id = $contract->end_location->structure_id;

            $location = $this->getOrCreateLocation($station_id, $structure_id);

            if ($contract->title != "") {
                $name = $contract->title;
            } else {
                $name = ContractHelper::getDescriptiveContractName($contract);
            }

            $source = new InventorySource();
            $source->location_id = $location->id;
            $source->source_name = $name;
            $source->source_type = "contract";
            $source->last_updated = $time;
            $source->workspace_id = $workspace_id;
            $source->save();

            $simplified = ItemHelper::simplifyItemList($items);

            $source_id = $source->id;
            foreach ($simplified as $item_helper) {
                $item = $item_helper->asSourceItem();
                $item->source_id = $source_id;
                $item->save();
            }
        }
    }

    private function handleCorporationAssets($corporation_ids, $workspace_id)
    {
        $time = now();

        //remove all assembled ships
        $ids = InventorySource::where("source_type","fitted_ship")
            ->where("workspace_id",$workspace_id)
            ->pluck("id");
        InventorySource::whereIn("id",$ids)->delete();
        InventoryItem::whereIn("source_id",$ids)->delete();

        //get locations with corporation assets
        $locations = DB::table("corporation_assets")
            ->select(
                "corporation_assets.location_id as game_location_id",
                "recursive_tree_seat_inventory_locations.id as inventory_location_id",
                "seat_inventory_inventory_source.id as source_id"
            )
            ->whereIn("location_flag", ["OfficeFolder", "CorpDeliveries"])
            ->whereIn("corporation_id", $corporation_ids)
            ->leftJoin("recursive_tree_seat_inventory_locations", function ($join) {
                $join
                    ->on('corporation_assets.location_id', '=', 'recursive_tree_seat_inventory_locations.structure_id')
                    ->orOn('corporation_assets.location_id', '=', 'recursive_tree_seat_inventory_locations.station_id');
            })
            ->leftJoin("seat_inventory_inventory_source", function ($join) use ($workspace_id) {
                $join
                    ->on("recursive_tree_seat_inventory_locations.id", "=", "seat_inventory_inventory_source.location_id")
                    ->where("workspace_id",$workspace_id) // only consider it if the workspace is also correct
                    ->where("source_type","corporation_hangar");
            })
            ->groupBy("corporation_assets.location_id", "recursive_tree_seat_inventory_locations.id", "seat_inventory_inventory_source.id")
            ->get();

        //create a Location object if it doesn't already exist
        $locations = $locations->map(function ($e) {
            if ($e->inventory_location_id == null) {
                $location = $this->getOrCreateLocation($e->game_location_id,$e->game_location_id);
                $e->inventory_location_id = $location->id;
            }
            return $e;
        });

        //fill inventory source ids
        $locations = $locations->map(function ($asset_location) use ($workspace_id) {
            // if there isn't an inventory source, create a new one
            if ($asset_location->source_id == null) {
                $source = new InventorySource();
                $source->source_type = "corporation_hangar";
                $source->location_id = $asset_location->inventory_location_id;
                $source->workspace_id = $workspace_id;

                $location = Location::find($asset_location->inventory_location_id);
                if ($location->station_id) {
                    $source->source_name = "Station Hangar";
                } else {
                    $source->source_name = "Structure Hangar";
                }

                $source->save();
                // update the asset location with the corresponding inventory source
                $asset_location->source_id = $source->id;
            }
            return $asset_location;
        });

        //delete old stuff
        $deleteable_ids = DB::table("seat_inventory_inventory_source")
            ->select("id")
            ->where("source_type", "corporation_hangar")
            ->whereNotIn("id", $locations->pluck("source_id"))
            ->where("workspace_id",$workspace_id)
            ->pluck("id");
        InventoryItem::whereIn("source_id", $deleteable_ids)->delete();
        InventorySource::whereIn("id", $deleteable_ids)->delete();

        //go over each location
        foreach ($locations as $location) {
            $item_list = [];

            //because laravel is weird once again, we eager load up to a depth of 3: hangar/container/item (infinite would be perfect)
            $assets = CorporationAsset::with("content.content.content")
                ->where("location_id", $location->game_location_id)
                ->get();
            foreach ($assets as $asset){
                $this->handleAssetItem($asset, $item_list,$location->inventory_location_id, true, $workspace_id);
            }

            $item_list = ItemHelper::simplifyItemList($item_list);
            $item_list = ItemHelper::prepareBulkInsertionSourceItems($item_list,$location->source_id);

            InventoryItem::where("source_id", $location->source_id)->delete();
            InventoryItem::insert($item_list);

            $source = InventorySource::find($location->source_id);
            $source->last_updated = $time;
            $source->save();
        }
    }

    private function handleAssetItem($item, &$list,$location, $handle_ship, $workspace_id)
    {
        //check for ships
        if($item->type->group->categoryID === 6 && $item->is_singleton && $handle_ship){
            //it is an assembled ship, handle it differently
            $this->handleAssembledShip($item,$location, $workspace_id);
        } else {

            $list[] = new ItemHelper($item->type_id, $item->quantity);

            //chunking bugs it out, don't optimize it
            foreach ($item->content as $content) {
                $this->handleAssetItem($content, $list, $location, true, $workspace_id);
            }
        }
    }

    private function handleAssembledShip($ship,$location, $workspace_id){
        $source = new InventorySource();
        $source->source_type = "fitted_ship";
        $source->location_id = $location;
        $ship_type_name = $ship->type->typeName;
        $source->source_name = "$ship->name($ship_type_name)";
        $source->workspace_id = $workspace_id;
        $source->save();

        $item_list = [];
        $this->handleAssetItem($ship,$item_list,$location,false, $workspace_id);
        $item_list = ItemHelper::simplifyItemList($item_list);
        $bulk = ItemHelper::prepareBulkInsertionSourceItems($item_list,$source->id);

        InventoryItem::insert($bulk);
    }

    private function createLocation($location_id): Location
    {
        $structure = UniverseStructure::find($location_id);
        $station = UniverseStation::find($location_id);

        if ($structure) {
            $location = new Location();
            $location->structure_id = $location_id;
            $location->name = $structure->name;
        } else if ($station) {
            $location = new Location();
            $location->station_id = $location_id;
            $location->name = $station->name;
        } else {
            $location = new Location();
            $location->station_id = $location_id;
            $location->structure_id = $location_id;
            $location->name = "Unknown Location";
        }

        $location->save();

        return $location;
    }

    private function getOrCreateLocation($station_id, $structure_id)
    {
        $location = null;

        //search location
        if ($station_id != null) {
            $t = Location::where("station_id", $station_id)->first();
            if ($t != null) {
                $location = $t;
            }
        }

        if ($structure_id != null) {
            $t = Location::where("structure_id", $structure_id)->first();
            if ($t != null) {
                $location = $t;
            }
        }

        //for some reason, the destination is not in the database
        if ($location == null) {
            if ($station_id) {
                $location = $this->createLocation($station_id);
            } else {
                $location = $this->createLocation($structure_id);
            }
        }

        return $location;
    }
}