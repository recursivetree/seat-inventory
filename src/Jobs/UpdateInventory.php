<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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

class UpdateInventory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function tags()
    {
        return ["seat-inventory", "assets" ];
    }

    private static $ITEM_BLACKLIST = [
        27 //Corporation Hangar Office
    ];

    public function handle(){
        DB::transaction(function () {
            $sources = InventorySource::where("source_type","corporation_hangar")->orWhere("source_type","contract")->get();
            foreach ($sources as $source){
                $source->delete();
                InventoryItem::where("source_id", $source->id)->delete();
            }

            $corporations = TrackedCorporation::all()->pluck("corporation_id");
            $alliances = TrackedAlliance::all()->pluck("alliance_id");

            $this->handleContracts($corporations, $alliances);
            $this->handleCorporationAssets($corporations);

        });

        $ids = Location::all()->pluck("id")->unique();
        foreach ($ids as $id) {
            UpdateStockLevels::dispatch($id)->onQueue('default');
        }
    }

    private function handleContracts($corporations, $alliances){

        //get the time from around when the query is triggered, so in case the db updates in between, we make sure that at least some ca be from the old state
        $time = now();

        $contracts = ContractDetail::where("type","item_exchange")
            ->where("status","outstanding")
            ->whereIn("assignee_id",$corporations)
            ->orWhereIn("assignee_id",$alliances)
            ->get();

        foreach ($contracts as $contract){
            $items = [];

            foreach ( $contract->lines as $item){
                $items[] = new ItemHelper($item->type_id,$item->quantity);
            }

            $station_id = $contract->end_location->station_id;
            $structure_id = $contract->end_location->structure_id;

            $location = $this->getOrCreateLocation($station_id, $structure_id);

            $source = new InventorySource();
            $source->location_id = $location->id;
            $source->source_name = "$contract->title";
            $source->source_type = "contract";
            $source->last_updated = $time;
            $source->save();

            $simplified = ItemHelper::simplifyItemList($items);

            $source_id = $source->id;
            foreach ($simplified as $item_helper){
                $item = $item_helper->asSourceItem();
                $item->source_id = $source_id;
                $item->save();
            }
        }
        //throw new Exception(json_encode(InventoryItem::all()));
    }

    private function handleCorporationAssets($corporations){

        //get the time from around when the query is triggered, so in case the db updates in between, we make sure that at least some ca be from the old state
        $time = now();

        $items = CorporationAsset::whereIn("corporation_id",$corporations)->get(); //TODO check tracked corporations
        $item_dict = [];

        foreach ($items as $item){
            $this->handleAssetItem($item, $item_dict);
        }

        foreach ($item_dict as $id => $items){
            $simplified = ItemHelper::simplifyItemList($items);

            $location = $this->getOrCreateLocation($id, $id);

            $source = new InventorySource();
            if($location->structure_id != null) {
                $source->source_name = "Structure Hangar";
            } else {
                $source->source_name = "Station Hangar";
            }
            $source->source_type = "corporation_hangar";
            $source->location_id = $location->id;
            $source->last_updated = $time;

            $source->save();

            $source_id = $source->id;
            foreach ($simplified as $item_helper){
                $item = $item_helper->asSourceItem();
                $item->source_id = $source_id;
                $item->save();
            }
        }
    }

    private function handleAssetItem($item, &$item_dict){
        if(in_array($item->type_id,self::$ITEM_BLACKLIST)){
            return;
        }

        $current_parent = $item;
        while (true) {

            $parent = CorporationAsset::where("item_id",$current_parent->location_id)->first();

            if ($parent == null) {
                break;
            } elseif ($parent->location_flag="OfficeFolder") {
                $current_parent = $parent;
                break;
            }

            $current_parent = $parent;
        }

        $item_data = new ItemHelper($item->type_id, $item->quantity);

        if($current_parent->station()->exists()){
            $station = $current_parent->station->station_id;
            if (!array_key_exists($station,$item_dict)){
                $item_dict[$station] = [];
            }
            $item_dict[$station][] = $item_data;
        }
        if($current_parent->structure()->exists()){
            $structure = $current_parent->structure->structure_id;
            if (!array_key_exists($structure,$item_dict)){
                $item_dict[$structure] = [];
            }
            $item_dict[$structure][] = $item_data;
        }
    }

    private function getOrCreateLocation($station_id, $structure_id){
        $location = null;

        //search location
        if($station_id!=null){
            $t = Location::where("station_id",$station_id)->first();
            if($t!=null){
                $location = $t;
            }
        }

        if ($structure_id!=null){
            $t = Location::where("structure_id",$structure_id)->first();
            if($t!=null){
                $location = $t;
            }
        }

        //for some reason, the destination is not in the database
        if($location == null){
            $location = new Location();
            $location->station_id = $station_id;
            $location->structure_id = $structure_id;
            $location->name = "Unknown location";
            $location->save();
        }
        return $location;
    }
}