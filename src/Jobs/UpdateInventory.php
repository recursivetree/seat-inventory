<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use RecursiveTree\Seat\Inventory\Helpers\ContractHelper;
use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Models\InventoryItem;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\TrackedAlliance;
use RecursiveTree\Seat\Inventory\Models\TrackedCorporation;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Contracts\CorporationContract;
use Illuminate\Support\Facades\DB;
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
        $corporations = TrackedCorporation::all()->pluck("corporation_id");
        $alliances = TrackedAlliance::all()->pluck("alliance_id");

        $this->handleContracts($corporations, $alliances);
        $this->handleCorporationAssets($corporations);

        $ids = Location::all()->pluck("id")->unique();
        foreach ($ids as $id) {
            UpdateStockLevels::dispatch($id)->onQueue('default');
        }
    }

    private function handleContracts($corporations, $alliances)
    {
        $valid_assingees = $corporations->merge($alliances);

        $sources = InventorySource::where("source_type","contract")->get();
        foreach ($sources as $source){
            $source->delete();
            InventoryItem::where("source_id", $source->id)->delete();
        }

        //get the time from around when the query is triggered, so in case the db updates in between, we make sure that at least some ca be from the old state
        $time = carbon();

        $contracts = ContractDetail::where("type", "item_exchange")
            ->where("status", "outstanding")
            ->whereDate("date_expired",">",$time)
            ->whereIn("assignee_id", $valid_assingees)
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

    private function handleCorporationAssets($corporations)
    {
        $time = now();

        //get locations
        $locations = DB::table("corporation_assets")
            ->select(
                "corporation_assets.location_id as game_location_id",
                "recursive_tree_seat_inventory_locations.id as inventory_location_id",
                "recursive_tree_seat_inventory_inventory_source.id as source_id"
            )
            ->whereIn("location_flag", ["OfficeFolder", "CorpDeliveries"])
            ->whereIn("corporation_id", $corporations)
            ->leftJoin("recursive_tree_seat_inventory_locations", function ($join) {
                $join
                    ->on('corporation_assets.location_id', '=', 'recursive_tree_seat_inventory_locations.structure_id')
                    ->orOn('corporation_assets.location_id', '=', 'recursive_tree_seat_inventory_locations.station_id');
            })
            ->leftJoin("recursive_tree_seat_inventory_inventory_source", function ($join) {
                $join
                    ->on("recursive_tree_seat_inventory_locations.id", "=", "recursive_tree_seat_inventory_inventory_source.location_id");
                //->where("source_type","corporation_hangar");
            })
            ->groupBy("corporation_assets.location_id", "recursive_tree_seat_inventory_locations.id", "recursive_tree_seat_inventory_inventory_source.id")
            ->get();

        //fill location table if no value is found
        $locations = $locations->map(function ($e) {
            if ($e->inventory_location_id == null) {
                $location = $this->getOrCreateLocation($e->game_location_id,$e->game_location_id);
                $e->inventory_location_id = $location->id;
            }
            return $e;
        });

        //fill inventory source ids
        $locations = $locations->map(function ($e) {
            if ($e->source_id == null) {
                $source = new InventorySource();
                $source->source_type = "corporation_hangar";
                $source->location_id = $e->inventory_location_id;

                $location = Location::find($e->inventory_location_id);
                if ($location->station_id) {
                    $source->source_name = "Station Hangar";
                } else {
                    $source->source_name = "Structure Hangar";
                }

                $source->save();
                $e->source_id = $source->id;
            }
            return $e;
        });

        //delete old stuff
        $deleteable_ids = DB::table("recursive_tree_seat_inventory_inventory_source")
            ->select("id")
            ->where("source_type", "corporation_hangar")
            ->whereNotIn("id", $locations->pluck("source_id"))
            ->pluck("id");
        InventoryItem::whereIn("source_id", $deleteable_ids)->delete();
        InventorySource::whereIn("id", $deleteable_ids)->delete();

        foreach ($locations as $location) {
            $item_list = [];

            //because laravel is weird once again, we eager load up to a depth of 3: hangar/container/item (infinite would be perfect)
            $assets = CorporationAsset::with("content.content.content")->where("location_id", $location->game_location_id)->get();
            foreach ($assets as $asset){
                $this->handleAssetItem($asset, $item_list);
            }

            $item_list = ItemHelper::simplifyItemList($item_list);

            $item_list = array_map(function ($e) use ($location) {
                return [
                    "type_id" => $e->type_id,
                    "amount" => $e->amount,
                    "source_id" => $location->source_id
                ];
            }, $item_list);


            InventoryItem::where("source_id", $location->source_id)->delete();
            InventoryItem::insert($item_list);

            $source = InventorySource::find($location->source_id);
            $source->last_updated = $time;
            $source->save();
        }
    }

    private function handleAssetItem($item, &$list)
    {
        $list[] = new ItemHelper($item->type_id, $item->quantity);

        //chunking bugs it out, don't optimize it
        foreach ($item->content as $content){
            $this->handleAssetItem($content, $list);
        }
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